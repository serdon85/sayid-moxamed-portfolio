<?php
// public/reports.php
require_once '../includes/header.php';
require_once '../classes/Database.php';

if (!hasRole('Admin')) {
    redirect('dashboard.php');
}

$db = new Database();

// Report 1: Monthly Revenue (Last 6 Months)
$db->query("SELECT DATE_FORMAT(created_at, '%M %Y') as month, SUM(total_payable) as revenue 
            FROM bills WHERE status = 'Paid' 
            GROUP BY month ORDER BY created_at DESC LIMIT 6");
$revenue_report = $db->resultSet();

// Report 2: Defaulters List
$db->query("SELECT b.*, c.first_name, c.last_name, c.phone 
            FROM bills b JOIN customers c ON b.customer_id = c.id 
            WHERE b.status = 'Unpaid' AND b.due_date < CURDATE()");
$defaulters = $db->resultSet();
?>

<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h2 class="fw-bold text-dark mb-1">Analytics Reporting</h2>
        <p class="text-secondary mb-0">Monitor financial performance and consumer trends.</p>
    </div>
    <div class="btn-group shadow-sm" style="border-radius: 14px; overflow: hidden;">
        <button class="btn btn-light px-4 border-end"><i class="fas fa-file-excel me-2 text-success"></i> Excel</button>
        <button class="btn btn-primary px-4"><i class="fas fa-file-pdf me-2"></i> Monthly PDF</button>
    </div>
</div>

<div class="row g-4">
    <!-- Revenue Summary -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-dark mb-0">Revenue Summary</h5>
                    <div class="stat-icon-square soft-emerald m-0" style="width: 35px; height: 35px;">
                        <i class="fas fa-dollar-sign small"></i>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-secondary small text-uppercase">Month</th>
                                <th class="text-end text-secondary small text-uppercase">Amount Collected</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($revenue_report): foreach($revenue_report as $r): ?>
                            <tr>
                                <td class="fw-medium text-dark"><?php echo $r->month; ?></td>
                                <td class="text-end">
                                    <span class="fw-bold text-emerald">$<?php echo number_format($r->revenue, 2); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="2" class="text-center py-4 text-muted">No collections recorded.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Defaulters List -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100" style="border-top: 5px solid var(--danger-color) !important;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-dark mb-0 text-danger">Defaulters List</h5>
                    <div class="badge soft-rose px-3 py-1">Critical</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-secondary small text-uppercase">Consumer</th>
                                <th class="text-secondary small text-uppercase">Due Date</th>
                                <th class="text-end text-secondary small text-uppercase">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($defaulters): foreach($defaulters as $d): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo $d->first_name . ' ' . $d->last_name; ?></div>
                                    <small class="text-muted"><?php echo $d->phone; ?></small>
                                </td>
                                <td>
                                    <div class="badge bg-light text-danger border-danger px-2 py-1" style="font-size: 0.75rem;">
                                        <?php echo date('M d, Y', strtotime($d->due_date)); ?>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold text-dark">$<?php echo number_format($d->total_payable, 2); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted">Hadda ma jiraan dad lagu leeyahay.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Big Chart -->
    <div class="col-12 mt-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Consumption by Category</h5>
                        <p class="text-muted small mb-0">Distribution of power usage across different tariff sectors.</p>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">All Sectors</button>
                    </div>
                </div>
                <div style="height: 400px;">
                    <canvas id="tariffChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('tariffChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Residential', 'Commercial', 'Industrial'],
            datasets: [{
                label: 'Total Units (kWh)',
                data: [4500, 2800, 6000],
                backgroundColor: [
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(245, 158, 11, 0.8)'
                ],
                borderRadius: 12,
                barThickness: 60
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#64748b', font: { weight: 'bold' } } },
                y: { 
                    grid: { color: '#f1f5f9', drawBorder: false },
                    ticks: { color: '#94a3b8' }
                }
            }
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
