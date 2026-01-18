<?php
require_once __DIR__ . '/../config/database.php';

class PatternDetector {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Detect high-risk missing locations (hotspots)
     */
    public function detectHighRiskLocations($threshold = 2) {
        $stmt = $this->db->prepare("
            SELECT 
                missing_location_city,
                missing_location_area,
                missing_location_landmark,
                COUNT(*) as case_count,
                GROUP_CONCAT(case_number) as case_numbers
            FROM missing_children
            WHERE missing_location_area IS NOT NULL
            GROUP BY missing_location_city, missing_location_area, missing_location_landmark
            HAVING case_count >= ?
            ORDER BY case_count DESC
        ");
        $stmt->execute([$threshold]);
        return $stmt->fetchAll();
    }

    /**
     * Detect time-based patterns (peak hours/dates)
     */
    public function detectTimePatterns() {
        // Peak hours
        $stmt = $this->db->query("
            SELECT 
                HOUR(missing_date) as hour,
                COUNT(*) as count
            FROM missing_children
            GROUP BY hour
            ORDER BY count DESC
        ");
        $peak_hours = $stmt->fetchAll();

        // Peak days of week
        $stmt = $this->db->query("
            SELECT 
                DAYNAME(missing_date) as day_name,
                DAYOFWEEK(missing_date) as day_num,
                COUNT(*) as count
            FROM missing_children
            GROUP BY day_name, day_num
            ORDER BY count DESC
        ");
        $peak_days = $stmt->fetchAll();

        // Peak months
        $stmt = $this->db->query("
            SELECT 
                MONTHNAME(missing_date) as month_name,
                MONTH(missing_date) as month_num,
                COUNT(*) as count
            FROM missing_children
            GROUP BY month_name, month_num
            ORDER BY count DESC
        ");
        $peak_months = $stmt->fetchAll();

        return [
            'peak_hours' => $peak_hours,
            'peak_days' => $peak_days,
            'peak_months' => $peak_months
        ];
    }

    /**
     * Detect repeat suspects across reports
     */
    public function detectRepeatSuspects() {
        $stmt = $this->db->query("
            SELECT 
                s.suspect_id,
                s.first_name,
                s.last_name,
                s.alias,
                COUNT(sc.child_id) as case_count,
                GROUP_CONCAT(mc.case_number) as case_numbers,
                GROUP_CONCAT(mc.missing_date) as missing_dates
            FROM suspects s
            JOIN suspect_cases sc ON s.suspect_id = sc.suspect_id
            JOIN missing_children mc ON sc.child_id = mc.child_id
            GROUP BY s.suspect_id
            HAVING case_count > 1
            ORDER BY case_count DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Detect children missing from same area or school
     */
    public function detectAreaClustering() {
        // By area
        $stmt = $this->db->query("
            SELECT 
                missing_location_area,
                missing_location_city,
                COUNT(*) as case_count,
                GROUP_CONCAT(case_number) as case_numbers,
                MIN(missing_date) as first_case,
                MAX(missing_date) as last_case
            FROM missing_children
            WHERE missing_location_area IS NOT NULL
            GROUP BY missing_location_area, missing_location_city
            HAVING case_count > 1
            ORDER BY case_count DESC
        ");
        $by_area = $stmt->fetchAll();

        // By school
        $stmt = $this->db->query("
            SELECT 
                school_name,
                missing_location_city,
                COUNT(*) as case_count,
                GROUP_CONCAT(case_number) as case_numbers
            FROM missing_children
            WHERE school_name IS NOT NULL
            GROUP BY school_name, missing_location_city
            HAVING case_count > 1
            ORDER BY case_count DESC
        ");
        $by_school = $stmt->fetchAll();

        return [
            'by_area' => $by_area,
            'by_school' => $by_school
        ];
    }

    /**
     * Identify suspicious activity zones (multiple sightings)
     */
    public function detectSuspiciousZones($threshold = 2) {
        $stmt = $this->db->prepare("
            SELECT 
                s.location_city,
                s.location_area,
                s.location_landmark,
                COUNT(DISTINCT s.child_id) as unique_children,
                COUNT(*) as total_sightings,
                GROUP_CONCAT(DISTINCT mc.case_number) as case_numbers
            FROM sightings s
            JOIN missing_children mc ON s.child_id = mc.child_id
            WHERE s.location_area IS NOT NULL
            GROUP BY s.location_city, s.location_area, s.location_landmark
            HAVING total_sightings >= ?
            ORDER BY total_sightings DESC
        ");
        $stmt->execute([$threshold]);
        return $stmt->fetchAll();
    }

    /**
     * Match found children with missing records
     */
    public function findPotentialMatches($found_id) {
        // Get found child details
        $stmt = $this->db->prepare("SELECT * FROM found_children WHERE found_id = ?");
        $stmt->execute([$found_id]);
        $found = $stmt->fetch();

        if (!$found) {
            return [];
        }

        // Build matching query
        $conditions = ["mc.case_status = 'Open'"];
        $params = [];

        // Age match (±2 years)
        if ($found['age']) {
            $conditions[] = "mc.age BETWEEN ? AND ?";
            $params[] = max(0, $found['age'] - 2);
            $params[] = $found['age'] + 2;
        }

        // Gender match
        if ($found['gender']) {
            $conditions[] = "mc.gender = ?";
            $params[] = $found['gender'];
        }

        // Location proximity (within 50km - simplified check)
        if ($found['found_location_latitude'] && $found['found_location_longitude']) {
            // Using Haversine formula approximation
            $conditions[] = "
                (6371 * acos(
                    cos(radians(?)) * cos(radians(mc.missing_location_latitude)) *
                    cos(radians(mc.missing_location_longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(mc.missing_location_latitude))
                )) <= 50
            ";
            $params[] = $found['found_location_latitude'];
            $params[] = $found['found_location_longitude'];
            $params[] = $found['found_location_latitude'];
        } else if ($found['found_location_city']) {
            // Fallback to city match
            $conditions[] = "mc.missing_location_city LIKE ?";
            $params[] = '%' . $found['found_location_city'] . '%';
        }

        // Time window (within 30 days)
        if ($found['found_date']) {
            $conditions[] = "mc.missing_date BETWEEN DATE_SUB(?, INTERVAL 30 DAY) AND ?";
            $params[] = $found['found_date'];
            $params[] = $found['found_date'];
        }

        $sql = "
            SELECT 
                mc.*,
                CASE
                    WHEN mc.age = ? THEN 10
                    WHEN ABS(mc.age - ?) = 1 THEN 8
                    WHEN ABS(mc.age - ?) = 2 THEN 6
                    ELSE 4
                END as age_score,
                CASE
                    WHEN mc.missing_location_city = ? THEN 10
                    ELSE 5
                END as location_score
            FROM missing_children mc
            WHERE " . implode(" AND ", $conditions) . "
            ORDER BY (age_score + location_score) DESC
            LIMIT 10
        ";

        // Add age for scoring
        array_unshift($params, $found['age'] ?? 0, $found['age'] ?? 0, $found['age'] ?? 0);
        array_unshift($params, $found['found_location_city'] ?? '');

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get all patterns summary
     */
    public function getAllPatterns() {
        return [
            'high_risk_locations' => $this->detectHighRiskLocations(),
            'time_patterns' => $this->detectTimePatterns(),
            'repeat_suspects' => $this->detectRepeatSuspects(),
            'area_clustering' => $this->detectAreaClustering(),
            'suspicious_zones' => $this->detectSuspiciousZones()
        ];
    }
}

