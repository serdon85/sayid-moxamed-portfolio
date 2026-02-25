<?php
// classes/Log.php
require_once __DIR__ . '/Database.php';

class Log {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function add($action, $table_name = null, $record_id = null, $details = null) {
        $user_id = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'];

        $this->db->query("INSERT INTO audit_logs (user_id, action, table_name, record_id, ip_address, details) 
                          VALUES (:user_id, :action, :table_name, :record_id, :ip_address, :details)");
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':action', $action);
        $this->db->bind(':table_name', $table_name);
        $this->db->bind(':record_id', $record_id);
        $this->db->bind(':ip_address', $ip);
        $this->db->bind(':details', $details);

        return $this->db->execute();
    }

    public function getAll() {
        $this->db->query("SELECT l.*, u.username, u.full_name 
                          FROM audit_logs l 
                          LEFT JOIN users u ON l.user_id = u.id 
                          ORDER BY l.created_at DESC");
        return $this->db->resultSet();
    }
}
?>
