<?php
// classes/Reading.php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Log.php';

class Reading {
    private $db;
    private $log;

    public function __construct() {
        $this->db = new Database();
        $this->log = new Log();
    }

    public function getLatestByCustomerId($customer_id) {
        $this->db->query("SELECT current_reading FROM meter_readings WHERE customer_id = :id ORDER BY reading_date DESC LIMIT 1");
        $this->db->bind(':id', $customer_id);
        $result = $this->db->single();
        return $result ? $result->current_reading : 0;
    }

    public function record($data) {
        $this->db->query("INSERT INTO meter_readings (customer_id, previous_reading, current_reading, reading_date, recorded_by) 
                          VALUES (:customer_id, :previous_reading, :current_reading, :reading_date, :recorded_by)");
        $this->db->bind(':customer_id', $data['customer_id']);
        $this->db->bind(':previous_reading', $data['previous_reading']);
        $this->db->bind(':current_reading', $data['current_reading']);
        $this->db->bind(':reading_date', $data['reading_date']);
        $this->db->bind(':recorded_by', $_SESSION['user_id']);

        if ($this->db->execute()) {
            $id = $this->db->lastInsertId();
            $this->log->add("Recorded Meter Reading", "meter_readings", $id, "Customer ID: {$data['customer_id']}, Reading: {$data['current_reading']}");
            return $id;
        } else {
            return false;
        }
    }

    public function getAll() {
        $this->db->query("SELECT r.*, c.first_name, c.last_name, c.meter_number 
                          FROM meter_readings r 
                          JOIN customers c ON r.customer_id = c.id 
                          ORDER BY r.reading_date DESC");
        return $this->db->resultSet();
    }

    public function delete($id) {
        $this->db->query("DELETE FROM meter_readings WHERE id = :id");
        $this->db->bind(':id', $id);
        if ($this->db->execute()) {
            $this->log->add("Deleted Meter Reading", "meter_readings", $id);
            return true;
        }
        return false;
    }
}
?>
