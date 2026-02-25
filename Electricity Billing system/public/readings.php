<?php
// public/readings.php
require_once '../includes/header.php';
require_once '../classes/Customer.php';
require_once '../classes/Reading.php';
require_once '../classes/Bill.php';

$customerModel = new Customer();
$readingModel = new Reading();
$billModel = new Bill();

$customers = $customerModel->getAll();
$readings = $readingModel->getAll();

$msg = '';
$msgClass = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['record_reading'])) {
        $customer_id = $_POST['customer_id'];
        $current_reading = $_POST['current_reading'];
        $reading_date = $_POST['reading_date'];

        $previous_reading = $readingModel->getLatestByCustomerId($customer_id);

        if ($current_reading < $previous_reading) {
            $msg = "Khalad: Akhriska hadda kama yaraan karo kii hore ($previous_reading).";
            $msgClass = "alert-danger";
        } else {
            $data = [
                'customer_id' => $customer_id,
                'previous_reading' => $previous_reading,
                'current_reading' => $current_reading,
                'reading_date' => $reading_date
            ];

            $reading_id = $readingModel->record($data);
            if ($reading_id) {
                // Auto-calculate and generate bill
                $units = $current_reading - $previous_reading;
                $bill_data = $billModel->calculateBill($customer_id, $units);
                
                if ($billModel->generate($customer_id, $reading_id, $bill_data)) {
                    $msg = "Akhriskii waa la diwaangeliyay, biilkiina waa la soo saaray!";
                    $msgClass = "alert-success";
                } else {
                    $msg = "Akhriskii waa la diwaangeliyay laakiin biilka ayaa guuldaraystay.";
                    $msgClass = "alert-warning";
                }
            } else {
                $msg = "Khalad ayaa dhacay markii la diwaangelinayay akhriska.";
                $msgClass = "alert-danger";
            }
        }
    }

    if (isset($_POST['delete_reading'])) {
        if ($readingModel->delete($_POST['id'])) {
            $msg = "Akhriskii waa la masaxay!";
            $msgClass = "alert-success";
        } else {
            $msg = "Khalad ayaa dhacay.";
            $msgClass = "alert-danger";
        }
    }

    $readings = $readingModel->getAll(); // Refresh list
}
?>

<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h2 class="fw-bold text-dark mb-1">Meter Readings</h2>
        <p class="text-secondary mb-0">Record and track energy consumption for all meters.</p>
    </div>
    <button class="btn btn-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#addReadingModal">
        <i class="fas fa-plus-circle me-2"></i> Record New Reading
    </button>
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
                        <th class="px-4">Reading Date</th>
                        <th>Consumer Details</th>
                        <th>Meter Status</th>
                        <th>Consumption</th>
                        <th class="text-end px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($readings): foreach ($readings as $r): ?>
                    <tr>
                        <td class="px-4">
                            <div class="fw-bold text-dark"><?php echo date('M d, Y', strtotime($r->reading_date)); ?></div>
                            <small class="text-muted"><i class="fas fa-calendar-alt me-1"></i> Recorded</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="stat-icon-square soft-indigo me-3 m-0" style="width: 35px; height: 35px;">
                                    <i class="fas fa-user small"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark"><?php echo $r->first_name . ' ' . $r->last_name; ?></div>
                                    <small class="text-primary font-monospace"><?php echo $r->meter_number; ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="small mb-1"><span class="text-muted">Prev:</span> <span class="fw-bold"><?php echo $r->previous_reading; ?></span></div>
                            <div class="small"><span class="text-muted">Curr:</span> <span class="fw-bold text-indigo"><?php echo $r->current_reading; ?></span></div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="badge soft-emerald px-3 py-2 me-2" style="border-radius: 10px;">
                                    <i class="fas fa-bolt me-1"></i> <?php echo $r->units_consumed; ?> kWh
                                </div>
                            </div>
                        </td>
                        <td class="text-end px-4">
                            <div class="btn-group">
                                <a href="billing.php?reading_id=<?php echo $r->id; ?>" class="btn btn-sm btn-light text-primary me-2 border-0 shadow-none"><i class="fas fa-file-invoice"></i></a>
                                <button class="btn btn-sm btn-light text-danger border-0 shadow-none delete-reading-btn"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteReadingModal"
                                        data-id="<?php echo $r->id; ?>"
                                        data-info="<?php echo $r->first_name . ' (' . date('M d', strtotime($r->reading_date)) . ')'; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="fas fa-clock fa-3x text-light mb-3"></i>
                            <p class="text-muted">No meter readings recorded yet.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Reading Modal -->
<div class="modal fade" id="addReadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h4 class="modal-title fw-bold text-dark">Log Meter Reading</h4>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" onsubmit="this.querySelector('button[type=submit]').disabled=true; this.querySelector('button[type=submit]').innerHTML='<i class=\'fas fa-spinner fa-spin me-2\'></i> Recording...';">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Select Consumer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">Choose a consumer...</option>
                            <?php foreach($customers as $c): ?>
                            <option value="<?php echo $c->id; ?>"><?php echo $c->first_name . ' ' . $c->last_name . ' (' . $c->meter_number . ')'; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Current Reading (kWh) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-2 border-end-0"><i class="fas fa-tachometer-alt"></i></span>
                                <input type="number" step="0.01" name="current_reading" class="form-control border-2 border-start-0" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Reading Date <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-2 border-end-0"><i class="fas fa-calendar-day"></i></span>
                                <input type="date" name="reading_date" class="form-control border-2 border-start-0" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 p-3 soft-indigo rounded-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle me-3 fa-lg"></i>
                            <small class="fw-medium">Biilka macaamilka si otomaatig ah ayaa loo xisaabin doonaa marka aad kaydiso akhriskan cusub.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Discard</button>
                    <button type="submit" name="record_reading" class="btn btn-primary px-4 shadow-none">Save & Generate Bill</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteReadingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-body p-4 text-center">
                <div class="stat-icon-square soft-rose mx-auto mb-4" style="width: 60px; height: 60px;">
                    <i class="fas fa-exclamation-triangle fa-lg"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Ma hubtaa?</h5>
                <p class="text-secondary small mb-4">Waxaad masaxaysaa akhriska: <br><strong id="delete_info" class="text-dark"></strong>. Talaabadan waxay sidoo kale saameyn kartaa biilka xidhiidhsan.</p>
                
                <form action="" method="POST">
                    <input type="hidden" name="id" id="delete_id">
                    <div class="d-grid gap-2">
                        <button type="submit" name="delete_reading" class="btn btn-danger py-2 shadow-none">Haa, Masax</button>
                        <button type="button" class="btn btn-light py-2" data-bs-dismiss="modal">Iska daa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete Reading
    const deleteBtns = document.querySelectorAll('.delete-reading-btn');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('delete_id').value = this.dataset.id;
            document.getElementById('delete_info').innerText = this.dataset.info;
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
