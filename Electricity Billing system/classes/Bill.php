<?php
// classes/Bill.php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Tariff.php';

class Bill {
    private $db;
    private $tariffModel;

    public function __construct() {
        $this->db = new Database();
        $this->tariffModel = new Tariff();
    }

    public function calculateBill($customer_id, $units) {
        // Get customer tariff type
        $this->db->query("SELECT tariff_type FROM customers WHERE id = :id");
        $this->db->bind(':id', $customer_id);
        $customer = $this->db->single();
        
        if (!$customer) return false;

        $type = $customer->tariff_type;
        $slabs = $this->tariffModel->getSlabsByType($type);
        
        $total_amount = 0;
        $total_tax = 0;
        $remaining_units = $units;

        foreach ($slabs as $slab) {
            if ($remaining_units <= 0) break;

            $slab_limit = $slab->slab_end ? ($slab->slab_end - $slab->slab_start + 1) : $remaining_units;
            $units_in_slab = min($remaining_units, $slab_limit);
            
            $slab_amount = $units_in_slab * $slab->rate_per_unit;
            $total_amount += $slab_amount;
            
            // Tax calculation
            $total_tax += ($slab_amount * ($slab->tax_percentage / 100));
            
            $remaining_units -= $units_in_slab;
        }

        return [
            'base_amount' => $total_amount,
            'tax_amount' => $total_tax,
            'total_payable' => $total_amount + $total_tax
        ];
    }

    public function generate($customer_id, $reading_id, $amount_data) {
        $due_date = date('Y-m-d', strtotime('+15 days'));
        
        $this->db->query("INSERT INTO bills (customer_id, reading_id, amount_due, tax_amount, total_payable, due_date) 
                          VALUES (:customer_id, :reading_id, :amount_due, :tax_amount, :total_payable, :due_date)");
        $this->db->bind(':customer_id', $customer_id);
        $this->db->bind(':reading_id', $reading_id);
        $this->db->bind(':amount_due', $amount_data['base_amount']);
        $this->db->bind(':tax_amount', $amount_data['tax_amount']);
        $this->db->bind(':total_payable', $amount_data['total_payable']);
        $this->db->bind(':due_date', $due_date);

        return $this->db->execute();
    }

    public function getAll() {
        $this->db->query("SELECT b.*, c.first_name, c.last_name, c.meter_number 
                          FROM bills b 
                          JOIN customers c ON b.customer_id = c.id 
                          ORDER BY b.created_at DESC");
        return $this->db->resultSet();
    }

    public function delete($id) {
        $this->db->query("DELETE FROM bills WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
?>
