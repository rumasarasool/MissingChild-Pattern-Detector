<?php
require_once __DIR__ . '/../config/database.php';

class Suspect {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Store suspect information
     */
    public function addSuspect($data) {
        $sql = "INSERT INTO suspects (
            first_name, last_name, alias, age, gender,
            physical_description, known_address, criminal_history, photo_url
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['alias'] ?? null,
            $data['age'] ?? null,
            $data['gender'] ?? null,
            $data['physical_description'] ?? null,
            $data['known_address'] ?? null,
            $data['criminal_history'] ?? null,
            $data['photo_url'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Link suspect to case
     */
    public function linkSuspectToCase($suspect_id, $child_id, $association_type, $description, $reported_by) {
        $sql = "INSERT INTO suspect_cases (suspect_id, child_id, association_type, description, reported_by)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                association_type = VALUES(association_type),
                description = VALUES(description)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$suspect_id, $child_id, $association_type, $description, $reported_by]);
    }

    /**
     * Get suspect by ID
     */
    public function getSuspectById($suspect_id) {
        $stmt = $this->db->prepare("SELECT * FROM suspects WHERE suspect_id = ?");
        $stmt->execute([$suspect_id]);
        return $stmt->fetch();
    }

    /**
     * Get all suspects
     */
    public function getAllSuspects() {
        $stmt = $this->db->query("SELECT * FROM suspects ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    /**
     * Get suspects linked to a case
     */
    public function getSuspectsByCase($child_id) {
        $stmt = $this->db->prepare("
            SELECT s.*, sc.association_type, sc.description, sc.created_at as linked_at
            FROM suspects s
            JOIN suspect_cases sc ON s.suspect_id = sc.suspect_id
            WHERE sc.child_id = ?
        ");
        $stmt->execute([$child_id]);
        return $stmt->fetchAll();
    }

    /**
     * Get suspects linked to multiple cases
     */
    public function getRepeatSuspects() {
        $stmt = $this->db->query("
            SELECT s.*, COUNT(sc.child_id) as case_count,
                   GROUP_CONCAT(mc.case_number) as case_numbers
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
     * Update suspect information
     */
    public function updateSuspect($suspect_id, $data) {
        $sql = "UPDATE suspects SET
            first_name = ?, last_name = ?, alias = ?, age = ?, gender = ?,
            physical_description = ?, known_address = ?, criminal_history = ?,
            photo_url = ?, updated_at = NOW()
            WHERE suspect_id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['alias'] ?? null,
            $data['age'] ?? null,
            $data['gender'] ?? null,
            $data['physical_description'] ?? null,
            $data['known_address'] ?? null,
            $data['criminal_history'] ?? null,
            $data['photo_url'] ?? null,
            $suspect_id
        ]);
    }

    /**
     * Delete suspect
     */
    public function deleteSuspect($suspect_id) {
        $stmt = $this->db->prepare("DELETE FROM suspects WHERE suspect_id = ?");
        return $stmt->execute([$suspect_id]);
    }
}

