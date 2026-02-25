<?php
// classes/Disconnection.php
require_once __DIR__ . '/Database.php';

class Disconnection {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAll() {
        $this->db->query("SELECT d.*, c.first_name, c.last_name, c.meter_number, b.total_payable, b.due_date 
                          FROM disconnections d 
                          JOIN customers c ON d.customer_id = c.id 
                          JOIN bills b ON d.bill_id = b.id 
                          ORDER BY d.disconnection_date DESC");
        return $this->db->resultSet();
    }

    public function getUnpaidOverdue() {
        $this->db->query("SELECT b.*, c.first_name, c.last_name, c.meter_number 
                          FROM bills b 
                          JOIN customers c ON b.customer_id = c.id 
                          WHERE b.status = 'Unpaid' AND b.due_date < CURDATE()
                          AND b.id NOT IN (SELECT bill_id FROM disconnections WHERE status = 'Disconnected')");
        return $this->db->resultSet();
    }

    public function disconnect($data) {
        $this->db->query("INSERT INTO disconnections (customer_id, bill_id, disconnection_date, reason, status) 
                          VALUES (:customer_id, :bill_id, :date, :reason, 'Disconnected')");
        $this->db->bind(':customer_id', $data['customer_id']);
        $this->db->bind(':bill_id', $data['bill_id']);
        $this->db->bind(':date', date('Y-m-d'));
        $this->db->bind(':reason', $data['reason']);
        
        return $this->db->execute();
    }

    public function reconnect($id) {
        $this->db->query("UPDATE disconnections SET reconnection_date = :date, status = 'Reconnected' WHERE id = :id");
        $this->db->bind(':date', date('Y-m-d'));
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }
}
?>
