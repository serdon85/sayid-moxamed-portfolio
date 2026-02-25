<?php
// classes/Payment.php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Log.php';

class Payment {
    private $db;
    private $log;

    public function __construct() {
        $this->db = new Database();
        $this->log = new Log();
    }

    public function record($data) {
        $this->db->beginTransaction();
        try {
            // 1. Insert Payment
            $this->db->query("INSERT INTO payments (bill_id, amount_paid, payment_method, transaction_id, recorded_by) 
                              VALUES (:bill_id, :amount_paid, :payment_method, :transaction_id, :recorded_by)");
            $this->db->bind(':bill_id', $data['bill_id']);
            $this->db->bind(':amount_paid', $data['amount_paid']);
            $this->db->bind(':payment_method', $data['payment_method']);
            $this->db->bind(':transaction_id', $data['transaction_id']);
            $this->db->bind(':recorded_by', $_SESSION['user_id']);
            $this->db->execute();

            // 2. Update Bill Status
            $this->db->query("UPDATE bills SET status = 'Paid' WHERE id = :id");
            $this->db->bind(':id', $data['bill_id']);
            $this->db->execute();

            $this->log->add("Payment Received", "payments", $data['bill_id'], "Amount: {$data['amount_paid']} via {$data['payment_method']}");

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getAll() {
        $this->db->query("SELECT p.*, b.total_payable, c.first_name, c.last_name, c.meter_number 
                          FROM payments p 
                          JOIN bills b ON p.bill_id = b.id 
                          JOIN customers c ON b.customer_id = c.id 
                          ORDER BY p.payment_date DESC");
        return $this->db->resultSet();
    }

    public function voidPayment($payment_id) {
        $this->db->beginTransaction();
        try {
            // Get bill_id first
            $this->db->query("SELECT bill_id FROM payments WHERE id = :id");
            $this->db->bind(':id', $payment_id);
            $payment = $this->db->single();

            if ($payment) {
                // Restore bill status
                $this->db->query("UPDATE bills SET status = 'Unpaid' WHERE id = :id");
                $this->db->bind(':id', $payment->bill_id);
                $this->db->execute();

                // Delete payment record
                $this->db->query("DELETE FROM payments WHERE id = :id");
                $this->db->bind(':id', $payment_id);
                $this->db->execute();

                $this->log->add("Payment Voided", "payments", $payment_id, "Reverted bill #{$payment->bill_id} to Unpaid");
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
?>
