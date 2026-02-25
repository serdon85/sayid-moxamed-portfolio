<?php
// public/dashboard.php
require_once '../includes/header.php';
require_once '../classes/Database.php';

$db = new Database();

// Get Stats
$db->query("SELECT COUNT(*) as total FROM customers");
$total_customers = $db->single()->total;

$db->query("SELECT SUM(total_payable) as total FROM bills WHERE status = 'Paid'");
$total_revenue = $db->single()->total ?? 0;

$db->query("SELECT SUM(total_payable) as total FROM bills WHERE status = 'Unpaid'");
$unpaid_bills = $db->single()->total ?? 0;

$db->query("SELECT COUNT(*) as total FROM bills WHERE status = 'Unpaid' AND due_date < CURDATE()");
$defaulters = $db->single()->total;

?>

<div class="mb-5">
    <h2 class="fw-bold text-dark">Welcome back, <?php echo explode(' ', $_SESSION['full_name'])[0]; ?>! 👋</h2>
    <p class="text-secondary">Halkan ka arag xaaladda guud ee nidaamka korontada.</p>
</div>

<div class="row g-4 mb-5">
    <!-- Stat Cards v2 -->
    <div class="col-md-3">
        <div class="card stat-card-v2 h-100 p-4">
            <i class="fas fa-users stat-bg-icon"></i>
            <div class="stat-icon-square soft-indigo">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <h6 class="text-secondary fw-semibold mb-1">Total Customers</h6>
                <h2 class="fw-bold mb-0 text-dark"><?php echo number_format($total_customers); ?></h2>
                <small class="text-success fw-medium"><i class="fas fa-arrow-up me-1"></i> +5.2%</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card-v2 h-100 p-4">
            <i class="fas fa-wallet stat-bg-icon"></i>
            <div class="stat-icon-square soft-emerald">
                <i class="fas fa-wallet"></i>
            </div>
            <div>
                <h6 class="text-secondary fw-semibold mb-1">Total Revenue</h6>
                <h2 class="fw-bold mb-0 text-dark">$<?php echo number_format($total_revenue, 2); ?></h2>
                <small class="text-success fw-medium"><i class="fas fa-arrow-up me-1"></i> +12.5%</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card-v2 h-100 p-4">
            <i class="fas fa-file-invoice-dollar stat-bg-icon"></i>
            <div class="stat-icon-square soft-amber">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div>
                <h6 class="text-secondary fw-semibold mb-1">Unpaid Bills</h6>
                <h2 class="fw-bold mb-0 text-dark">$<?php echo number_format($unpaid_bills, 2); ?></h2>
                <small class="text-danger fw-medium"><i class="fas fa-arrow-down me-1"></i> -3.1%</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card-v2 h-100 p-4">
            <i class="fas fa-user-slash stat-bg-icon"></i>
            <div class="stat-icon-square soft-rose">
                <i class="fas fa-user-slash"></i>
            </div>
            <div>
                <h6 class="text-secondary fw-semibold mb-1">Defaulters</h6>
                <h2 class="fw-bold mb-0 text-dark"><?php echo $defaulters; ?></h2>
                <small class="text-muted fw-medium">Action required</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Monthly Consumption Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-dark mb-0">Consumption Analytics</h5>
                    <select class="form-select form-select-sm w-auto border-0 bg-light">
                        <option>Last 6 Months</option>
                        <option>Yearly</option>
                    </select>
                </div>
                <div style="height: 350px;">
                    <canvas id="consumptionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold text-dark mb-4">Recent System Activity</h5>
                <div class="timeline">
                    <?php
                    $db->query("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 6");
                    $logs = $db->resultSet();
                    if($logs):
                        foreach($logs as $log):
                    ?>
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0 me-3">
                            <div class="stat-icon-square soft-indigo m-0" style="width: 40px; height: 40px; font-size: 0.9rem;">
                                <i class="fas fa-history"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 border-bottom pb-3">
                            <div class="d-flex justify-content-between">
                                <h6 class="fw-bold text-dark mb-1" style="font-size: 0.9rem;"><?php echo $log->action; ?></h6>
                                <small class="text-muted"><?php echo date('H:i', strtotime($log->created_at)); ?></small>
                            </div>
                            <p class="text-muted mb-0" style="font-size: 0.8rem;"><?php echo $log->table_name; ?> record #<?php echo $log->record_id; ?></p>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-ghost fa-3x text-light mb-3"></i>
                        <p class="text-muted">No activity yet</p>
                    </div>
                    <?php endif; ?>
                </div>
                <a href="logs.php" class="btn btn-light w-100 mt-2 fw-semibold">View Full Audit Log</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('consumptionChart').getContext('2d');
        
        // Gradient for chart
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(99, 102, 241, 0.2)');
        gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Units Consumed (kWh)',
                    data: [1250, 1850, 2400, 2100, 2300, 3100],
                    backgroundColor: gradient,
                    borderColor: '#6366f1',
                    borderWidth: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#6366f1',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94a3b8' } },
                    y: { 
                        grid: { color: '#f1f5f9' }, 
                        ticks: { color: '#94a3b8' },
                        beginAtZero: true 
                    }
                }
            }
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>
