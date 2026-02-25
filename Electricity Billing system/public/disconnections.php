<?php
// public/disconnections.php
require_once '../includes/header.php';
require_once '../classes/Disconnection.php';

if (!hasRole(['Admin', 'Technician'])) {
    redirect('dashboard.php');
}

$discModel = new Disconnection();
$overdue = $discModel->getUnpaidOverdue();
$history = $discModel->getAll();

$msg = '';
$msgClass = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action_disconnect'])) {
        $data = [
            'customer_id' => $_POST['customer_id'],
            'bill_id' => $_POST['bill_id'],
            'reason' => $_POST['reason']
        ];
        if ($discModel->disconnect($data)) {
            $msg = "Customer has been flagged for disconnection.";
            $msgClass = "alert-warning";
        }
    } elseif (isset($_POST['action_reconnect'])) {
        if ($discModel->reconnect($_POST['disc_id'])) {
            $msg = "Customer connection restored.";
            $msgClass = "alert-success";
        }
    }
    // Refresh lists
    $overdue = $discModel->getUnpaidOverdue();
    $history = $discModel->getAll();
}
?>

<div class="mb-4">
    <h3 class="fw-bold">Disconnections & Reconnections</h3>
    <p class="text-muted">Manage service status for overdue accounts.</p>
</div>

<?php if ($msg): ?>
    <div class="alert <?php echo $msgClass; ?> alert-dismissible fade show" role="alert">
        <?php echo $msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Notice List -->
    <div class="col-md-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-danger">Pending Disconnections</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-3">Customer</th>
                                <th class="text-end px-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($overdue): foreach($overdue as $o): ?>
                            <tr>
                                <td class="px-3 py-2">
                                    <div class="fw-bold"><?php echo $o->first_name . ' ' . $o->last_name; ?></div>
                                    <small class="text-muted">Overdue by: $<?php echo number_format($o->total_payable, 2); ?></small>
                                </td>
                                <td class="text-end px-3 py-2">
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#discModal<?php echo $o->id; ?>">
                                        Disconnect
                                    </button>
                                </td>
                            </tr>

                            <!-- Disconnect Modal -->
                            <div class="modal fade" id="discModal<?php echo $o->id; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Confirm Disconnection</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="" method="POST">
                                            <input type="hidden" name="customer_id" value="<?php echo $o->customer_id; ?>">
                                            <input type="hidden" name="bill_id" value="<?php echo $o->id; ?>">
                                            <div class="modal-body text-center">
                                                <i class="fas fa-plug fa-3x text-danger mb-3"></i>
                                                <p>Are you sure you want to disconnect <strong><?php echo $o->first_name; ?></strong>?</p>
                                                <textarea name="reason" class="form-control" placeholder="Reason for disconnection" required>Non-payment of bill #BL-<?php echo $o->id; ?></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="action_disconnect" class="btn btn-danger">Confirm</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <?php endforeach; else: ?>
                            <tr><td colspan="2" class="text-center py-4 text-muted">No pending disconnections.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- History -->
    <div class="col-md-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary">Disconnection History</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 text-center">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-start px-3">Customer</th>
                                <th>Disc. Date</th>
                                <th>Status</th>
                                <th class="text-end px-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($history): foreach($history as $h): ?>
                            <tr>
                                <td class="text-start px-3 py-2">
                                    <div class="fw-bold"><?php echo $h->first_name . ' ' . $h->last_name; ?></div>
                                    <small class="text-muted"><?php echo $h->meter_number; ?></small>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($h->disconnection_date)); ?></td>
                                <td>
                                    <span class="badge <?php echo $h->status == 'Disconnected' ? 'bg-danger' : 'bg-success'; ?>">
                                        <?php echo $h->status; ?>
                                    </span>
                                </td>
                                <td class="text-end px-3 py-2">
                                    <?php if ($h->status == 'Disconnected'): ?>
                                    <form action="" method="POST" style="display:inline;">
                                        <input type="hidden" name="disc_id" value="<?php echo $h->id; ?>">
                                        <button type="submit" name="action_reconnect" class="btn btn-sm btn-outline-success">
                                            Reconnect
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($h->reconnection_date)); ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">No history found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
