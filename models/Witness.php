<?php
require_once __DIR__ . '/../config/database.php';

class Witness {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Store witness report
     */
    public function addWitnessReport($data) {
        $sql = "INSERT INTO witness_reports (
            child_id, witness_name, witness_contact, witness_address,
            report_date, sighting_location_city, sighting_location_area,
            sighting_location_landmark, sighting_location_latitude,
            sighting_location_longitude, sighting_date_time, description,
            credibility_score, reported_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['child_id'],
            $data['witness_name'] ?? null,
            $data['witness_contact'] ?? null,
            $data['witness_address'] ?? null,
            $data['report_date'],
            $data['sighting_location_city'] ?? null,
            $data['sighting_location_area'] ?? null,
            $data['sighting_location_landmark'] ?? null,
            $data['sighting_location_latitude'] ?? null,
            $data['sighting_location_longitude'] ?? null,
            $data['sighting_date_time'] ?? null,
            $data['description'] ?? null,
            $data['credibility_score'] ?? 5,
            $data['reported_by']
        ]);
    }

    /**
     * Get witness reports for a specific case
     */
    public function getWitnessReportsByCase($child_id) {
        $stmt = $this->db->prepare("
            SELECT wr.*, mc.case_number, mc.first_name, mc.last_name
            FROM witness_reports wr
            JOIN missing_children mc ON wr.child_id = mc.child_id
            WHERE wr.child_id = ?
            ORDER BY wr.report_date DESC
        ");
        $stmt->execute([$child_id]);
        return $stmt->fetchAll();
    }

    /**
     * Get all witness reports
     */
    public function getAllWitnessReports() {
        $stmt = $this->db->query("
            SELECT wr.*, mc.case_number, mc.first_name, mc.last_name
            FROM witness_reports wr
            JOIN missing_children mc ON wr.child_id = mc.child_id
            ORDER BY wr.report_date DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get witness report by ID
     */
    public function getWitnessReportById($witness_id) {
        $stmt = $this->db->prepare("
            SELECT wr.*, mc.case_number, mc.first_name, mc.last_name
            FROM witness_reports wr
            JOIN missing_children mc ON wr.child_id = mc.child_id
            WHERE wr.witness_id = ?
        ");
        $stmt->execute([$witness_id]);
        return $stmt->fetch();
    }
}

