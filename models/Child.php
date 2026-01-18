<?php
require_once __DIR__ . '/../config/database.php';

class Child {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Add new missing child record
     */
    public function addMissingChild($data) {
        $sql = "INSERT INTO missing_children (
            case_number, first_name, last_name, age, gender, date_of_birth,
            missing_date, missing_location_city, missing_location_area, missing_location_landmark,
            missing_location_latitude, missing_location_longitude, physical_description,
            clothing_description, photo_url, school_name, parent_guardian_name,
            parent_guardian_contact, case_status, reported_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Open', ?)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['case_number'],
            $data['first_name'],
            $data['last_name'],
            $data['age'],
            $data['gender'],
            $data['date_of_birth'] ?? null,
            $data['missing_date'],
            $data['missing_location_city'],
            $data['missing_location_area'] ?? null,
            $data['missing_location_landmark'] ?? null,
            $data['missing_location_latitude'] ?? null,
            $data['missing_location_longitude'] ?? null,
            $data['physical_description'] ?? null,
            $data['clothing_description'] ?? null,
            $data['photo_url'] ?? null,
            $data['school_name'] ?? null,
            $data['parent_guardian_name'] ?? null,
            $data['parent_guardian_contact'] ?? null,
            $data['reported_by']
        ]);
    }

    /**
     * Update existing child information
     */
    public function updateMissingChild($child_id, $data) {
        $sql = "UPDATE missing_children SET
            first_name = ?, last_name = ?, age = ?, gender = ?, date_of_birth = ?,
            missing_date = ?, missing_location_city = ?, missing_location_area = ?,
            missing_location_landmark = ?, missing_location_latitude = ?,
            missing_location_longitude = ?, physical_description = ?,
            clothing_description = ?, photo_url = ?, school_name = ?,
            parent_guardian_name = ?, parent_guardian_contact = ?,
            case_status = ?, updated_at = NOW()
            WHERE child_id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['age'],
            $data['gender'],
            $data['date_of_birth'] ?? null,
            $data['missing_date'],
            $data['missing_location_city'],
            $data['missing_location_area'] ?? null,
            $data['missing_location_landmark'] ?? null,
            $data['missing_location_latitude'] ?? null,
            $data['missing_location_longitude'] ?? null,
            $data['physical_description'] ?? null,
            $data['clothing_description'] ?? null,
            $data['photo_url'] ?? null,
            $data['school_name'] ?? null,
            $data['parent_guardian_name'] ?? null,
            $data['parent_guardian_contact'] ?? null,
            $data['case_status'] ?? 'Open',
            $child_id
        ]);
    }

    /**
     * Delete child record
     */
    public function deleteMissingChild($child_id) {
        $stmt = $this->db->prepare("DELETE FROM missing_children WHERE child_id = ?");
        return $stmt->execute([$child_id]);
    }

    /**
     * Get child by ID
     */
    public function getChildById($child_id) {
        $stmt = $this->db->prepare("SELECT * FROM missing_children WHERE child_id = ?");
        $stmt->execute([$child_id]);
        return $stmt->fetch();
    }

    /**
     * Get child by case number
     */
    public function getChildByCaseNumber($case_number) {
        $stmt = $this->db->prepare("SELECT * FROM missing_children WHERE case_number = ?");
        $stmt->execute([$case_number]);
        return $stmt->fetch();
    }

    /**
     * Get all missing children
     */
    public function getAllMissingChildren($limit = null, $offset = 0) {
        $sql = "SELECT * FROM missing_children ORDER BY missing_date DESC";
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit, $offset]);
        } else {
            $stmt = $this->db->query($sql);
        }
        return $stmt->fetchAll();
    }

    /**
     * Search missing children
     */
    public function searchMissingChildren($filters) {
        $conditions = [];
        $params = [];

        if (!empty($filters['name'])) {
            $conditions[] = "(first_name LIKE ? OR last_name LIKE ?)";
            $searchTerm = '%' . $filters['name'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['case_number'])) {
            $conditions[] = "case_number LIKE ?";
            $params[] = '%' . $filters['case_number'] . '%';
        }

        if (!empty($filters['age'])) {
            $conditions[] = "age = ?";
            $params[] = $filters['age'];
        }

        if (!empty($filters['gender'])) {
            $conditions[] = "gender = ?";
            $params[] = $filters['gender'];
        }

        if (!empty($filters['city'])) {
            $conditions[] = "missing_location_city LIKE ?";
            $params[] = '%' . $filters['city'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = "missing_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "missing_date <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = "case_status = ?";
            $params[] = $filters['status'];
        }

        $sql = "SELECT * FROM missing_children";
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY missing_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get case history
     */
    public function getCaseHistory($child_id) {
        $stmt = $this->db->prepare("
            SELECT ch.*, a.username as updated_by_username
            FROM case_history ch
            LEFT JOIN admins a ON ch.updated_by = a.admin_id
            WHERE ch.child_id = ?
            ORDER BY ch.created_at DESC
        ");
        $stmt->execute([$child_id]);
        return $stmt->fetchAll();
    }

    /**
     * Update case status
     */
    public function updateCaseStatus($child_id, $status, $notes, $updated_by) {
        // Update child status
        $stmt = $this->db->prepare("UPDATE missing_children SET case_status = ? WHERE child_id = ?");
        $stmt->execute([$status, $child_id]);

        // Add to case history
        $stmt = $this->db->prepare("INSERT INTO case_history (child_id, status, notes, updated_by) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$child_id, $status, $notes, $updated_by]);
    }

    /**
     * Add found child report
     */
    public function addFoundChild($data) {
        $sql = "INSERT INTO found_children (
            first_name, last_name, age, gender, found_date,
            found_location_city, found_location_area, found_location_landmark,
            found_location_latitude, found_location_longitude,
            physical_description, clothing_description, condition_description,
            reported_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['age'] ?? null,
            $data['gender'] ?? null,
            $data['found_date'],
            $data['found_location_city'],
            $data['found_location_area'] ?? null,
            $data['found_location_landmark'] ?? null,
            $data['found_location_latitude'] ?? null,
            $data['found_location_longitude'] ?? null,
            $data['physical_description'] ?? null,
            $data['clothing_description'] ?? null,
            $data['condition_description'] ?? null,
            $data['reported_by']
        ]);
    }

    /**
     * Get all found children
     */
    public function getAllFoundChildren() {
        $stmt = $this->db->query("
            SELECT fc.*, mc.case_number, mc.first_name as missing_first_name, mc.last_name as missing_last_name
            FROM found_children fc
            LEFT JOIN missing_children mc ON fc.matched_with_child_id = mc.child_id
            ORDER BY fc.found_date DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Match found child with missing child
     */
    public function matchFoundChild($found_id, $child_id, $matched_by) {
        $stmt = $this->db->prepare("
            UPDATE found_children 
            SET matched_with_child_id = ?, matched_by = ?, matched_at = NOW()
            WHERE found_id = ?
        ");
        $stmt->execute([$child_id, $matched_by, $found_id]);

        // Update missing child status
        $this->updateCaseStatus($child_id, 'Matched', 'Matched with found child report #' . $found_id, $matched_by);
    }

    /**
     * Get statistics
     */
    public function getStatistics() {
        $stats = [];

        // Total missing
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM missing_children");
        $stats['total_missing'] = $stmt->fetch()['total'];

        // Total found
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM found_children");
        $stats['total_found'] = $stmt->fetch()['total'];

        // By status
        $stmt = $this->db->query("SELECT case_status, COUNT(*) as count FROM missing_children GROUP BY case_status");
        $stats['by_status'] = $stmt->fetchAll();

        // By gender
        $stmt = $this->db->query("SELECT gender, COUNT(*) as count FROM missing_children GROUP BY gender");
        $stats['by_gender'] = $stmt->fetchAll();

        // By age groups
        $stmt = $this->db->query("
            SELECT 
                CASE 
                    WHEN age < 5 THEN '0-4'
                    WHEN age < 10 THEN '5-9'
                    WHEN age < 15 THEN '10-14'
                    ELSE '15+'
                END as age_group,
                COUNT(*) as count
            FROM missing_children
            GROUP BY age_group
        ");
        $stats['by_age_group'] = $stmt->fetchAll();

        // Monthly trends
        $stmt = $this->db->query("
            SELECT 
                DATE_FORMAT(missing_date, '%Y-%m') as month,
                COUNT(*) as count
            FROM missing_children
            GROUP BY month
            ORDER BY month DESC
            LIMIT 12
        ");
        $stats['monthly_trends'] = $stmt->fetchAll();

        // Location frequency
        $stmt = $this->db->query("
            SELECT missing_location_city, COUNT(*) as count
            FROM missing_children
            GROUP BY missing_location_city
            ORDER BY count DESC
            LIMIT 10
        ");
        $stats['location_frequency'] = $stmt->fetchAll();

        return $stats;
    }
}

