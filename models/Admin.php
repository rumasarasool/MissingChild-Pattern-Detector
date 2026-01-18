<?php
require_once __DIR__ . '/../config/database.php';

class Admin {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Add admin user
     */
    public function addAdmin($data) {
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO admins (username, password_hash, full_name, email, role)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['username'],
            $password_hash,
            $data['full_name'],
            $data['email'] ?? null,
            $data['role'] ?? 'investigator'
        ]);
    }

    /**
     * Remove admin user
     */
    public function removeAdmin($admin_id) {
        // Don't delete, just deactivate
        $stmt = $this->db->prepare("UPDATE admins SET is_active = 0 WHERE admin_id = ?");
        return $stmt->execute([$admin_id]);
    }

    /**
     * Get all admins
     */
    public function getAllAdmins() {
        $stmt = $this->db->query("
            SELECT admin_id, username, full_name, email, role, created_at, last_login, is_active
            FROM admins
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get admin by ID
     */
    public function getAdminById($admin_id) {
        $stmt = $this->db->prepare("
            SELECT admin_id, username, full_name, email, role, created_at, last_login, is_active
            FROM admins
            WHERE admin_id = ?
        ");
        $stmt->execute([$admin_id]);
        return $stmt->fetch();
    }

    /**
     * Update admin
     */
    public function updateAdmin($admin_id, $data) {
        $sql = "UPDATE admins SET full_name = ?, email = ?, role = ? WHERE admin_id = ?";
        $params = [$data['full_name'], $data['email'] ?? null, $data['role'], $admin_id];

        if (!empty($data['password'])) {
            $sql = "UPDATE admins SET full_name = ?, email = ?, role = ?, password_hash = ? WHERE admin_id = ?";
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $params = [$data['full_name'], $data['email'] ?? null, $data['role'], $password_hash, $admin_id];
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}

