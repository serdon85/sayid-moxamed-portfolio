<?php
// public/billing.php
require_once '../includes/header.php';
require_once '../classes/Bill.php';
require_once '../classes/Payment.php';

$billModel = new Bill();
$paymentModel = new Payment();
$bills = $billModel->getAll();

$msg = '';
$msgClass = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['pay_bill'])) {
        $data = [
            'bill_id' => $_POST['bill_id'],
            'amount_paid' => $_POST['amount_paid'],
            'payment_method' => $_POST['payment_method'],
            'transaction_id' => trim($_POST['transaction_id'])
        ];

        if ($paymentModel->record($data)) {
            $msg = "Lacag bixinta waa la diwaangeliyay!";
            $msgClass = "alert-success";
        } else {
            $msg = "Error recording payment.";
            $msgClass = "alert-danger";
        }
    }

    if (isset($_POST['delete_bill'])) {
        if ($billModel->delete($_POST['id'])) {
            $msg = "Biilka waa la masaxay!";
            $msgClass = "alert-success";
        } else {
            $msg = "Khalad ayaa dhacay.";
            $msgClass = "alert-danger";
        }
    }
    
    $bills = $billModel->getAll(); // Refresh
}
?>

<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h2 class="fw-bold text-dark mb-1">Billing & Invoices</h2>
        <p class="text-secondary mb-0">Manage payments and monitor consumer accounts.</p>
    </div>
    <div class="btn-group shadow-sm">
        <button class="btn btn-light border-end"><i class="fas fa-filter me-2 text-primary"></i> Filter</button>
        <button class="btn btn-primary"><i class="fas fa-plus me-2"></i> Generate Batch</button>
    </div>
</div>

<?php if ($msg): ?>
    <div class="alert <?php echo $msgClass; ?> border-0 shadow-sm alert-dismissible fade show mb-4" role="alert" style="border-radius: 16px;">
        <i class="fas <?php echo $msgClass == 'alert-success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
        <?php echo $msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="px-4">Bill Reference</th>
                        <th>Consumer</th>
                        <th>Amount Due</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th class="text-end px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($bills): foreach ($bills as $b): ?>
                    <tr>
                        <td class="px-4">
                            <div class="fw-bold text-primary font-monospace">#BL-<?php echo str_pad($b->id, 5, '0', STR_PAD_LEFT); ?></div>
                            <small class="text-muted">Issued: <?php echo date('M d', strtotime($b->created_at)); ?></small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="stat-icon-square soft-indigo me-3 m-0" style="width: 35px; height: 35px;">
                                    <i class="fas fa-user small"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark"><?php echo $b->first_name . ' ' . $b->last_name; ?></div>
                                    <small class="text-muted font-monospace"><?php echo $b->meter_number; ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="h6 fw-bold text-dark mb-0">$<?php echo number_format($b->total_payable, 2); ?></div>
                        </td>
                        <td>
                            <?php 
                                $isOverdue = (strtotime($b->due_date) < time()) && $b->status != 'Paid';
                                if ($isOverdue) {
                                    echo '<span class="badge soft-rose text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Overdue</span>';
                                } else {
                                    echo '<span class="text-muted small">' . date('M d, Y', strtotime($b->due_date)) . '</span>';
                                }
                            ?>
                        </td>
                        <td>
                            <?php if ($b->status == 'Paid'): ?>
                                <span class="badge soft-emerald">Paid</span>
                            <?php elseif ($b->status == 'Unpaid'): ?>
                                <span class="badge soft-amber">Unpaid</span>
                            <?php else: ?>
                                <span class="badge bg-light text-secondary"><?php echo $b->status; ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end px-4">
                            <div class="btn-group">
                                <?php if ($b->status != 'Paid'): ?>
                                <button class="btn btn-sm btn-primary px-3 shadow-none pay-bill-btn" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#paymentModal"
                                        data-id="<?php echo $b->id; ?>"
                                        data-name="<?php echo $b->first_name . ' ' . $b->last_name; ?>"
                                        data-total="<?php echo $b->total_payable; ?>"
                                        data-total_fmt="<?php echo number_format($b->total_payable, 2); ?>">
                                    Pay
                                </button>
                                <?php endif; ?>
                                <a href="invoice.php?id=<?php echo $b->id; ?>" class="btn btn-sm btn-light text-secondary ms-2" title="Print Invoice">
                                    <i class="fas fa-print"></i>
                                </a>
                                <button class="btn btn-sm btn-light text-danger ms-2 border-0 shadow-none delete-bill-btn"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteBillModal"
                                        data-id="<?php echo $b->id; ?>"
                                        data-ref="#BL-<?php echo str_pad($b->id, 5, '0', STR_PAD_LEFT); ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-file-invoice fa-3x text-light mb-3"></i>
                            <p class="text-muted">No pending bills found in the system.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h4 class="modal-title fw-bold text-dark">Confirm Payment</h4>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" onsubmit="this.querySelector('button[type=submit]').disabled=true; this.querySelector('button[type=submit]').innerHTML='<i class=\'fas fa-spinner fa-spin me-2\'></i> Processing...';">
                <input type="hidden" name="bill_id" id="pay_bill_id">
                <input type="hidden" name="amount_paid" id="pay_amount">
                <div class="modal-body p-4">
                    <div class="soft-indigo p-4 rounded-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-secondary small fw-bold text-uppercase">Consumer</span>
                            <span class="fw-bold text-dark" id="pay_name"></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-0">
                            <span class="text-secondary small fw-bold text-uppercase">Payable Amount</span>
                            <span class="h3 fw-bold text-primary mb-0">$<span id="pay_total_fmt"></span></span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Payment Medium</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="Cash">Cash Currency</option>
                            <option value="Bank">Bank Deposit</option>
                            <option value="Mobile" selected>Mobile Money (Zaad / Sahal / EVC)</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Transaction Reference ID</label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="TXN-XXXXXX" required>
                        <div class="form-text text-muted small mt-2">Fadlan geli lambarka xaqiijinta ee fariinta kuugu soo dhacay.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="pay_bill" class="btn btn-primary px-4 shadow-none">Authorize Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteBillModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-body p-4 text-center">
                <div class="stat-icon-square soft-rose mx-auto mb-4" style="width: 60px; height: 60px;">
                    <i class="fas fa-file-excel fa-lg"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Ma hubtaa?</h5>
                <p class="text-secondary small mb-4">Waxaad masaxaysaa biilka: <br><strong id="delete_ref" class="text-dark"></strong>. Talaabadan dib looguma soo laaban karo.</p>
                
                <form action="" method="POST">
                    <input type="hidden" name="id" id="delete_id">
                    <div class="d-grid gap-2">
                        <button type="submit" name="delete_bill" class="btn btn-danger py-2 shadow-none">Haa, Masax</button>
                        <button type="button" class="btn btn-light py-2" data-bs-dismiss="modal">Iska daa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pay Bill Modal
    const payBtns = document.querySelectorAll('.pay-bill-btn');
    payBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('pay_bill_id').value = this.dataset.id;
            document.getElementById('pay_amount').value = this.dataset.total;
            document.getElementById('pay_name').innerText = this.dataset.name;
            document.getElementById('pay_total_fmt').innerText = this.dataset.total_fmt;
        });
    });

    // Delete Bill Modal
    const deleteBtns = document.querySelectorAll('.delete-bill-btn');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('delete_id').value = this.dataset.id;
            document.getElementById('delete_ref').innerText = this.dataset.ref;
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
