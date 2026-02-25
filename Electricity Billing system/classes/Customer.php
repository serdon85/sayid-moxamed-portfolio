<?php
// classes/Customer.php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Log.php';

class Customer {
    private $db;
    private $log;

    public function __construct() {
        $this->db = new Database();
        $this->log = new Log();
    }

    public function getAll() {
        $this->db->query("SELECT * FROM customers ORDER BY created_at DESC");
        return $this->db->resultSet();
    }

    public function getById($id) {
        $this->db->query("SELECT * FROM customers WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function create($data) {
        $this->db->query("INSERT INTO customers (meter_number, first_name, last_name, email, phone, address, tariff_type) 
                          VALUES (:meter_number, :first_name, :last_name, :email, :phone, :address, :tariff_type)");
        $this->db->bind(':meter_number', $data['meter_number']);
        $this->db->bind(':first_name', $data['first_name']);
        $this->db->bind(':last_name', $data['last_name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':tariff_type', $data['tariff_type']);

        if ($this->db->execute()) {
            $id = $this->db->lastInsertId();
            $this->log->add("Added Customer", "customers", $id, "Added {$data['first_name']} {$data['last_name']} with meter {$data['meter_number']}");
            return $id;
        } else {
            return false;
        }
    }

    public function update($data) {
        $this->db->query("UPDATE customers SET first_name = :first_name, last_name = :last_name, email = :email, 
                          phone = :phone, address = :address, tariff_type = :tariff_type WHERE id = :id");
        $this->db->bind(':first_name', $data['first_name']);
        $this->db->bind(':last_name', $data['last_name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':tariff_type', $data['tariff_type']);
        $this->db->bind(':id', $data['id']);

        return $this->db->execute();
    }

    public function delete($id) {
        $this->db->query("DELETE FROM customers WHERE id = :id");
        $this->db->bind(':id', $id);
        if ($this->db->execute()) {
            $this->log->add("Deleted Customer", "customers", $id);
            return true;
        }
        return false;
    }
}
?>
