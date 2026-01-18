<?php
require_once __DIR__ . '/../config/database.php';

class Location {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Record location history of sightings
     */
    public function addSighting($data) {
        $sql = "INSERT INTO sightings (
            child_id, sighting_date_time, location_city, location_area,
            location_landmark, location_latitude, location_longitude,
            reported_by_witness, witness_contact, description,
            reliability_score, reported_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['child_id'],
            $data['sighting_date_time'],
            $data['location_city'],
            $data['location_area'] ?? null,
            $data['location_landmark'] ?? null,
            $data['location_latitude'] ?? null,
            $data['location_longitude'] ?? null,
            $data['reported_by_witness'] ?? null,
            $data['witness_contact'] ?? null,
            $data['description'] ?? null,
            $data['reliability_score'] ?? 5,
            $data['reported_by']
        ]);
    }

    /**
     * Get sightings for a child
     */
    public function getSightingsByChild($child_id) {
        $stmt = $this->db->prepare("
            SELECT s.*, mc.case_number, mc.first_name, mc.last_name
            FROM sightings s
            JOIN missing_children mc ON s.child_id = mc.child_id
            WHERE s.child_id = ?
            ORDER BY s.sighting_date_time DESC
        ");
        $stmt->execute([$child_id]);
        return $stmt->fetchAll();
    }

    /**
     * Get all sightings
     */
    public function getAllSightings() {
        $stmt = $this->db->query("
            SELECT s.*, mc.case_number, mc.first_name, mc.last_name
            FROM sightings s
            JOIN missing_children mc ON s.child_id = mc.child_id
            ORDER BY s.sighting_date_time DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Search by location
     */
    public function searchByLocation($city, $area = null, $landmark = null) {
        $conditions = ["s.location_city LIKE ?"];
        $params = ['%' . $city . '%'];

        if ($area) {
            $conditions[] = "s.location_area LIKE ?";
            $params[] = '%' . $area . '%';
        }

        if ($landmark) {
            $conditions[] = "s.location_landmark LIKE ?";
            $params[] = '%' . $landmark . '%';
        }

        $sql = "SELECT s.*, mc.case_number, mc.first_name, mc.last_name
                FROM sightings s
                JOIN missing_children mc ON s.child_id = mc.child_id
                WHERE " . implode(" AND ", $conditions) . "
                ORDER BY s.sighting_date_time DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

