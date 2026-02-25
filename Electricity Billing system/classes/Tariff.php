<?php
// classes/Tariff.php
require_once __DIR__ . '/Database.php';

class Tariff {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAll() {
        $this->db->query("SELECT * FROM tariffs ORDER BY type, slab_start");
        return $this->db->resultSet();
    }

    public function create($data) {
        $this->db->query("INSERT INTO tariffs (type, slab_start, slab_end, rate_per_unit, tax_percentage) 
                          VALUES (:type, :slab_start, :slab_end, :rate_per_unit, :tax_percentage)");
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':slab_start', $data['slab_start']);
        $this->db->bind(':slab_end', $data['slab_end']);
        $this->db->bind(':rate_per_unit', $data['rate_per_unit']);
        $this->db->bind(':tax_percentage', $data['tax_percentage']);

        return $this->db->execute();
    }

    public function getSlabsByType($type) {
        $this->db->query("SELECT * FROM tariffs WHERE type = :type ORDER BY slab_start");
        $this->db->bind(':type', $type);
        return $this->db->resultSet();
    }

    public function getById($id) {
        $this->db->query("SELECT * FROM tariffs WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function update($data) {
        $this->db->query("UPDATE tariffs SET type = :type, slab_start = :slab_start, slab_end = :slab_end, 
                          rate_per_unit = :rate_per_unit, tax_percentage = :tax_percentage WHERE id = :id");
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':slab_start', $data['slab_start']);
        $this->db->bind(':slab_end', $data['slab_end']);
        $this->db->bind(':rate_per_unit', $data['rate_per_unit']);
        $this->db->bind(':tax_percentage', $data['tax_percentage']);
        $this->db->bind(':id', $data['id']);

        return $this->db->execute();
    }

    public function delete($id) {
        $this->db->query("DELETE FROM tariffs WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
?>
