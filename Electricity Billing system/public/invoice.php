<?php
// public/invoice.php
require_once '../config/config.php';
require_once '../utils/functions.php';
require_once '../classes/Database.php';

checkLogin();

if (!isset($_GET['id'])) {
    redirect('billing.php');
}

$db = new Database();
$db->query("SELECT b.*, c.first_name, c.last_name, c.meter_number, c.address, c.phone, c.tariff_type,
            r.previous_reading, r.current_reading, r.units_consumed, r.reading_date
            FROM bills b 
            JOIN customers c ON b.customer_id = c.id
            JOIN meter_readings r ON b.reading_id = r.id
            WHERE b.id = :id");
$db->bind(':id', $_GET['id']);
$bill = $db->single();

if (!$bill) {
    redirect('billing.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #BL-<?php echo str_pad($bill->id, 5, '0', STR_PAD_LEFT); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white; }
            .card { border: none !important; box-shadow: none !important; }
        }
        body { background-color: #f0f2f5; font-family: 'Inter', sans-serif; }
        .invoice-box {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .invoice-header { border-bottom: 2px solid #f0f0f0; margin-bottom: 30px; padding-bottom: 20px; }
        .invoice-title { font-size: 24px; font-weight: 700; color: #1e3c72; }
        .table-custom th { background-color: #f8f9fa; }
    </style>
</head>
<body>

<div class="container text-end mt-4 no-print">
    <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print me-2"></i> Print Invoice</button>
    <a href="billing.php" class="btn btn-secondary">Back to Bills</a>
</div>

<div class="invoice-box">
    <div class="invoice-header d-flex justify-content-between align-items-center">
        <div>
            <div class="invoice-title"><i class="fas fa-bolt text-primary me-2"></i>ElectricPay</div>
            <p class="text-muted mb-0">Lighting Your Future</p>
        </div>
        <div class="text-end">
            <h4 class="mb-1">INVOICE</h4>
            <div class="fw-bold">No: #BL-<?php echo str_pad($bill->id, 5, '0', STR_PAD_LEFT); ?></div>
            <div class="text-muted">Date: <?php echo date('M d, Y', strtotime($bill->created_at)); ?></div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-6">
            <h6 class="text-uppercase text-muted border-bottom pb-1">Billed To:</h6>
            <h5 class="fw-bold mb-1"><?php echo $bill->first_name . ' ' . $bill->last_name; ?></h5>
            <div><?php echo $bill->address ?: 'No address provided'; ?></div>
            <div>Phone: <?php echo $bill->phone ?: 'N/A'; ?></div>
            <div class="mt-2 fw-bold">Meter No: <?php echo $bill->meter_number; ?></div>
        </div>
        <div class="col-6 text-end">
            <h6 class="text-uppercase text-muted border-bottom pb-1">Service Details:</h6>
            <div>Tariff Type: <span class="badge bg-info text-dark"><?php echo $bill->tariff_type; ?></span></div>
            <div>Reading Date: <?php echo date('M d, Y', strtotime($bill->reading_date)); ?></div>
            <div class="mt-2 text-danger fw-bold">DUE DATE: <?php echo date('M d, Y', strtotime($bill->due_date)); ?></div>
        </div>
    </div>

    <table class="table table-bordered table-custom mb-5">
        <thead class="bg-light">
            <tr>
                <th>Description</th>
                <th class="text-center">Prev. Reading</th>
                <th class="text-center">Curr. Reading</th>
                <th class="text-center">Units (kWh)</th>
                <th class="text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Electricity Consumption for <?php echo date('F Y', strtotime($bill->reading_date)); ?></td>
                <td class="text-center"><?php echo $bill->previous_reading; ?></td>
                <td class="text-center"><?php echo $bill->current_reading; ?></td>
                <td class="text-center"><?php echo $bill->units_consumed; ?></td>
                <td class="text-end fw-bold">$<?php echo number_format($bill->amount_due, 2); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="row justify-content-end">
        <div class="col-5">
            <table class="table table-sm table-borderless">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-end">$<?php echo number_format($bill->amount_due, 2); ?></td>
                </tr>
                <tr>
                    <td>Taxes:</td>
                    <td class="text-end">$<?php echo number_format($bill->tax_amount, 2); ?></td>
                </tr>
                <?php if ($bill->penalty_amount > 0): ?>
                <tr>
                    <td>Late Penalty:</td>
                    <td class="text-end text-danger">$<?php echo number_format($bill->penalty_amount, 2); ?></td>
                </tr>
                <?php endif; ?>
                <tr class="border-top">
                    <td class="fw-bold fs-5">TOTAL DUE:</td>
                    <td class="text-end fw-bold fs-5 text-primary">$<?php echo number_format($bill->total_payable, 2); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="mt-5 pt-5 text-center text-muted border-top">
        <p class="mb-1">Thank you for your business!</p>
        <small>Payment can be made via Cash, Bank, or Mobile Money. Please pay before the due date to avoid service disconnection.</small>
    </div>
</div>

</body>
</html>
