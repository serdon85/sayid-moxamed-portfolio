<?php
// public/logs.php
require_once '../includes/header.php';
require_once '../classes/Log.php';

if (!hasRole('Admin')) {
    redirect('dashboard.php');
}

$logModel = new Log();
$logs = $logModel->getAll();
?>

<div class="mb-4">
    <h3 class="fw-bold">System Audit Logs</h3>
    <p class="text-muted">History of all user actions and system changes.</p>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4">Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>IP Address</th>
                        <th class="px-4">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($logs): foreach ($logs as $l): ?>
                    <tr>
                        <td class="px-4">
                            <small class="text-muted d-block"><?php echo date('M d, Y', strtotime($l->created_at)); ?></small>
                            <?php echo date('H:i:s', strtotime($l->created_at)); ?>
                        </td>
                        <td>
                            <div class="fw-bold"><?php echo $l->full_name ?: 'System'; ?></div>
                            <small class="text-muted">@<?php echo $l->username ?: 'auto'; ?></small>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?php echo $l->action; ?></span>
                            <?php if ($l->table_name): ?>
                            <small class="d-block text-muted">Ref: <?php echo $l->table_name; ?> (ID: <?php echo $l->record_id; ?>)</small>
                            <?php endif; ?>
                        </td>
                        <td><code><?php echo $l->ip_address; ?></code></td>
                        <td class="px-4"><small><?php echo $l->details; ?></small></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No logs found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
