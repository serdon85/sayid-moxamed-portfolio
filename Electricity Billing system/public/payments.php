<?php
// public/payments.php
require_once '../includes/header.php';
require_once '../classes/Payment.php';

$msg = '';
$msgClass = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['void_payment'])) {
    if ($paymentModel->voidPayment($_POST['id'])) {
        $msg = "Payment has been voided and bill status reverted!";
        $msgClass = "alert-success";
    } else {
        $msg = "Error voiding payment.";
        $msgClass = "alert-danger";
    }
}

$payments = $paymentModel->getAll();

// Calculate Stats
$totalCollection = 0;
$todayCollection = 0;
$cashTotal = 0;
$mobileTotal = 0;

$today = date('Y-m-d');
if ($payments) {
    foreach ($payments as $p) {
        $totalCollection += $p->amount_paid;
        if (date('Y-m-d', strtotime($p->payment_date)) == $today) {
            $todayCollection += $p->amount_paid;
        }
        if ($p->payment_method == 'Cash') $cashTotal += $p->amount_paid;
        if ($p->payment_method == 'Mobile') $mobileTotal += $p->amount_paid;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h2 class="fw-bold text-dark mb-1">Payment Transactions</h2>
        <p class="text-secondary mb-0">Monitor all revenue streams and transaction history.</p>
    </div>
    <div class="stat-group d-flex gap-3">
        <div class="px-3 py-2 bg-white rounded-4 shadow-sm border d-flex align-items-center">
            <div class="stat-icon-square soft-emerald me-2" style="width: 32px; height: 32px;">
                <i class="fas fa-money-bill-wave small"></i>
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 10px; line-height: 1;">TOTAL REVENUE</small>
                <span class="fw-bold text-dark">$<?php echo number_format($totalCollection, 2); ?></span>
            </div>
        </div>
    </div>
</div>

<?php if ($msg): ?>
    <div class="alert <?php echo $msgClass; ?> border-0 shadow-sm alert-dismissible fade show mb-4" role="alert" style="border-radius: 16px;">
        <i class="fas <?php echo $msgClass == 'alert-success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
        <?php echo $msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-4" style="border-radius: 20px; background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);">
            <div class="stat-icon-square soft-indigo mb-3">
                <i class="fas fa-calendar-check"></i>
            </div>
            <h6 class="text-secondary fw-semibold mb-1">Today's Collection</h6>
            <h3 class="fw-bold text-dark mb-0">$<?php echo number_format($todayCollection, 2); ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
            <div class="stat-icon-square soft-emerald mb-3">
                <i class="fas fa-mobile-alt"></i>
            </div>
            <h6 class="text-secondary fw-semibold mb-1">Mobile Money</h6>
            <h3 class="fw-bold text-dark mb-0">$<?php echo number_format($mobileTotal, 2); ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
            <div class="stat-icon-square soft-amber mb-3">
                <i class="fas fa-wallet"></i>
            </div>
            <h6 class="text-secondary fw-semibold mb-1">Cash Payments</h6>
            <h3 class="fw-bold text-dark mb-0">$<?php echo number_format($cashTotal, 2); ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-4 text-white" style="border-radius: 20px; background: #4f46e5;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="stat-icon-square bg-white bg-opacity-25 text-white">
                    <i class="fas fa-chart-line"></i>
                </div>
                <span class="badge bg-white bg-opacity-25 rounded-pill small">Daily +12%</span>
            </div>
            <h6 class="text-white text-opacity-75 fw-medium mb-1">Transaction Count</h6>
            <h3 class="fw-bold mb-0"><?php echo count($payments); ?> Items</h3>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="px-4">Time & ID</th>
                        <th>Consumer Details</th>
                        <th>Amount Details</th>
                        <th>Medium</th>
                        <th class="text-end px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($payments): foreach ($payments as $p): ?>
                    <tr>
                        <td class="px-4">
                            <div class="small fw-bold text-dark"><?php echo date('M d, Y', strtotime($p->payment_date)); ?></div>
                            <div class="text-muted" style="font-size: 11px;"><?php echo date('H:i A', strtotime($p->payment_date)); ?></div>
                            <small class="text-primary font-monospace mt-1 d-block">#<?php echo str_pad($p->id, 6, '0', STR_PAD_LEFT); ?></small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="stat-icon-square soft-indigo me-3 m-0" style="width: 35px; height: 35px;">
                                    <i class="fas fa-user small"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark"><?php echo $p->first_name . ' ' . $p->last_name; ?></div>
                                    <small class="text-muted font-monospace"><?php echo $p->meter_number; ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="h6 fw-bold text-emerald mb-0">$<?php echo number_format($p->amount_paid, 2); ?></div>
                            <small class="text-muted">Against: #BL-<?php echo str_pad($p->bill_id, 5, '0', STR_PAD_LEFT); ?></small>
                        </td>
                        <td>
                            <?php 
                                $methodClass = 'bg-light text-dark';
                                $icon = 'fa-money-bill';
                                if($p->payment_method == 'Mobile') { $methodClass = 'soft-emerald'; $icon = 'fa-mobile-alt'; }
                                if($p->payment_method == 'Bank') { $methodClass = 'soft-indigo'; $icon = 'fa-university'; }
                            ?>
                            <div class="d-flex align-items-center">
                                <span class="badge <?php echo $methodClass; ?> px-3 py-2" style="border-radius: 10px;">
                                    <i class="fas <?php echo $icon; ?> me-1"></i> <?php echo $p->payment_method; ?>
                                </span>
                                <?php if($p->transaction_id): ?>
                                    <small class="ms-2 text-muted font-monospace" title="Ref ID"><?php echo $p->transaction_id; ?></small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="text-end px-4">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-light text-primary me-2 border-0 shadow-none" title="Print Receipt">
                                    <i class="fas fa-print"></i>
                                </button>
                                <?php if(hasRole('Admin')): ?>
                                <button class="btn btn-sm btn-light text-danger border-0 shadow-none void-payment-btn"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#voidPaymentModal"
                                        data-id="<?php echo $p->id; ?>"
                                        data-name="<?php echo $p->first_name; ?>"
                                        data-amount="<?php echo number_format($p->amount_paid, 2); ?>">
                                    <i class="fas fa-undo"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted">No transactions recorded yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Void Payment Modal -->
<div class="modal fade" id="voidPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-body p-4 text-center">
                <div class="stat-icon-square soft-rose mx-auto mb-4" style="width: 60px; height: 60px;">
                    <i class="fas fa-history fa-lg"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Void Transaction?</h5>
                <p class="text-secondary small mb-4">You are about to nullify payment of <strong class="text-dark">$<span id="void_amount"></span></strong> by <strong id="void_name"></strong>. This will set the bill status back to Unpaid.</p>
                
                <form action="" method="POST">
                    <input type="hidden" name="id" id="void_id">
                    <div class="d-grid gap-2">
                        <button type="submit" name="void_payment" class="btn btn-danger py-2 shadow-none">Yes, Void Payment</button>
                        <button type="button" class="btn btn-light py-2" data-bs-dismiss="modal">Cancel Action</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const voidBtns = document.querySelectorAll('.void-payment-btn');
    voidBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('void_id').value = this.dataset.id;
            document.getElementById('void_name').innerText = this.dataset.name;
            document.getElementById('void_amount').innerText = this.dataset.amount;
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
