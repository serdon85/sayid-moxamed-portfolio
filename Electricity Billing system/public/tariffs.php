<?php
// public/tariffs.php
require_once '../includes/header.php';
require_once '../classes/Tariff.php';

if (!hasRole('Admin')) {
    redirect('dashboard.php');
}

$tariffModel = new Tariff();
$tariffs = $tariffModel->getAll();

$msg = '';
$msgClass = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_tariff'])) {
        $data = [
            'type' => $_POST['type'],
            'slab_start' => $_POST['slab_start'],
            'slab_end' => !empty($_POST['slab_end']) ? $_POST['slab_end'] : null,
            'rate_per_unit' => $_POST['rate_per_unit'],
            'tax_percentage' => $_POST['tax_percentage']
        ];

        if ($tariffModel->create($data)) {
            $msg = "Qiimaha cusub waa lagu daray!";
            $msgClass = "alert-success";
        } else {
            $msg = "Khalad ayaa dhacay.";
            $msgClass = "alert-danger";
        }
    }

    if (isset($_POST['edit_tariff'])) {
        $data = [
            'id' => $_POST['id'],
            'type' => $_POST['type'],
            'slab_start' => $_POST['slab_start'],
            'slab_end' => !empty($_POST['slab_end']) ? $_POST['slab_end'] : null,
            'rate_per_unit' => $_POST['rate_per_unit'],
            'tax_percentage' => $_POST['tax_percentage']
        ];

        if ($tariffModel->update($data)) {
            $msg = "Xogta qiimaha waa la cusubaysiiyay!";
            $msgClass = "alert-success";
        } else {
            $msg = "Khalad ayaa dhacay.";
            $msgClass = "alert-danger";
        }
    }

    if (isset($_POST['delete_tariff'])) {
        if ($tariffModel->delete($_POST['id'])) {
            $msg = "Qiimaha waa la masaxay!";
            $msgClass = "alert-success";
        } else {
            $msg = "Khalad ayaa dhacay.";
            $msgClass = "alert-danger";
        }
    }
    
    $tariffs = $tariffModel->getAll();
}
?>

<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h2 class="fw-bold text-dark mb-1">Tariff Configuration</h2>
        <p class="text-secondary mb-0">Define and manage rate slabs for different customer categories.</p>
    </div>
    <button class="btn btn-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#addTariffModal">
        <i class="fas fa-plus-circle me-2"></i> Add New Slab
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
                        <th class="px-4">Category</th>
                        <th>Consumption Range</th>
                        <th>Rate per Unit</th>
                        <th>Tax Info</th>
                        <th class="text-end px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($tariffs): foreach ($tariffs as $t): ?>
                    <tr>
                        <td class="px-4">
                            <?php 
                                $catClass = 'soft-indigo';
                                $icon = 'fa-home';
                                if($t->type == 'Commercial') { $catClass = 'soft-emerald'; $icon = 'fa-building'; }
                                if($t->type == 'Industrial') { $catClass = 'soft-amber'; $icon = 'fa-industry'; }
                            ?>
                            <div class="d-flex align-items-center">
                                <div class="stat-icon-square <?php echo $catClass; ?> me-3 m-0" style="width: 35px; height: 35px;">
                                    <i class="fas <?php echo $icon; ?> small"></i>
                                </div>
                                <span class="fw-bold text-dark"><?php echo $t->type; ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="badge soft-indigo text-dark px-3 py-2" style="border-radius: 10px; font-weight: 500;">
                                <?php echo $t->slab_start; ?> - <?php echo ($t->slab_end ?: '∞'); ?> kWh
                            </div>
                        </td>
                        <td>
                            <div class="h5 fw-bold text-primary mb-0">$<?php echo number_format($t->rate_per_unit, 2); ?></div>
                        </td>
                        <td>
                            <div class="badge bg-light text-secondary border px-2 py-1"><?php echo $t->tax_percentage; ?>% Tax Fee</div>
                        </td>
                        <td class="text-end px-4">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-light text-secondary me-2 border-0 shadow-none edit-tariff-btn"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editTariffModal"
                                        data-id="<?php echo $t->id; ?>"
                                        data-type="<?php echo $t->type; ?>"
                                        data-start="<?php echo $t->slab_start; ?>"
                                        data-end="<?php echo $t->slab_end; ?>"
                                        data-rate="<?php echo $t->rate_per_unit; ?>"
                                        data-tax="<?php echo $t->tax_percentage; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-light text-danger border-0 shadow-none delete-tariff-btn"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteTariffModal"
                                        data-id="<?php echo $t->id; ?>"
                                        data-name="<?php echo $t->type . ' (' . $t->slab_start . '-' . ($t->slab_end ?: '∞') . ')'; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="fas fa-layer-group fa-3x text-light mb-3"></i>
                            <p class="text-muted">No tariff slabs defined yet.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Tariff Modal -->
<div class="modal fade" id="addTariffModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h4 class="modal-title fw-bold text-dark">Define Rate Slab</h4>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Customer Category</label>
                        <select name="type" class="form-select" required>
                            <option value="Residential">Residential</option>
                            <option value="Commercial">Commercial</option>
                            <option value="Industrial">Industrial</option>
                        </select>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Slab Start (kWh)</label>
                            <input type="number" name="slab_start" class="form-control" placeholder="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Slab End (kWh)</label>
                            <input type="number" name="slab_end" class="form-control" placeholder="Blank for max">
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Rate per Unit ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="rate_per_unit" class="form-control" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tax (%)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="tax_percentage" class="form-control" value="0.00">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Discard</button>
                    <button type="submit" name="add_tariff" class="btn btn-primary px-4 shadow-none">Save Tariff Configuration</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Tariff Modal -->
<div class="modal fade" id="editTariffModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h4 class="modal-title fw-bold text-dark">Update Rate Slab</h4>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Customer Category</label>
                        <select name="type" id="edit_type" class="form-select" required>
                            <option value="Residential">Residential</option>
                            <option value="Commercial">Commercial</option>
                            <option value="Industrial">Industrial</option>
                        </select>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Slab Start (kWh)</label>
                            <input type="number" name="slab_start" id="edit_slab_start" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Slab End (kWh)</label>
                            <input type="number" name="slab_end" id="edit_slab_end" class="form-control">
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Rate per Unit ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="rate_per_unit" id="edit_rate" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tax (%)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="tax_percentage" id="edit_tax" class="form-control">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_tariff" class="btn btn-primary px-4 shadow-none">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteTariffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-body p-4 text-center">
                <div class="stat-icon-square soft-rose mx-auto mb-4" style="width: 60px; height: 60px;">
                    <i class="fas fa-trash-alt fa-lg"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Ma hubtaa?</h5>
                <p class="text-secondary small mb-4">Waxaad qarka u saarantahay inaad tirtirto qiimahan: <br><strong id="delete_name" class="text-dark"></strong>.</p>
                
                <form action="" method="POST">
                    <input type="hidden" name="id" id="delete_id">
                    <div class="d-grid gap-2">
                        <button type="submit" name="delete_tariff" class="btn btn-danger py-2 shadow-none">Haa, Masax</button>
                        <button type="button" class="btn btn-light py-2" data-bs-dismiss="modal">Iska daa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit Tariff
    const editBtns = document.querySelectorAll('.edit-tariff-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_type').value = this.dataset.type;
            document.getElementById('edit_slab_start').value = this.dataset.start;
            document.getElementById('edit_slab_end').value = this.dataset.end;
            document.getElementById('edit_rate').value = this.dataset.rate;
            document.getElementById('edit_tax').value = this.dataset.tax;
        });
    });

    // Delete Tariff
    const deleteBtns = document.querySelectorAll('.delete-tariff-btn');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('delete_id').value = this.dataset.id;
            document.getElementById('delete_name').innerText = this.dataset.name;
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
