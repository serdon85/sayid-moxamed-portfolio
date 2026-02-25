<?php
// public/customers.php
require_once '../includes/header.php';
require_once '../classes/Customer.php';

$customerModel = new Customer();
$customers = $customerModel->getAll();

$msg = '';
$msgClass = '';

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_customer'])) {
        $data = [
            'meter_number' => trim($_POST['meter_number']),
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone']),
            'address' => trim($_POST['address']),
            'tariff_type' => $_POST['tariff_type']
        ];

        if ($customerModel->create($data)) {
            $msg = "Macaamiil cusub ayaa lagu daray!";
            $msgClass = "alert-success";
        } else {
            $msg = "Khalad ayaa dhacay markii macaamiilka la darayay.";
            $msgClass = "alert-danger";
        }
    }

    if (isset($_POST['edit_customer'])) {
        $data = [
            'id' => $_POST['id'],
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone']),
            'address' => trim($_POST['address']),
            'tariff_type' => $_POST['tariff_type']
        ];

        if ($customerModel->update($data)) {
            $msg = "Xogta macaamiilka waa la cusubaysiiyay!";
            $msgClass = "alert-success";
        } else {
            $msg = "Khalad ayaa dhacay markii xogta la cusubaysiinayay.";
            $msgClass = "alert-danger";
        }
    }

    if (isset($_POST['delete_customer'])) {
        if ($customerModel->delete($_POST['id'])) {
            $msg = "Macaamiilka waa la masaxay!";
            $msgClass = "alert-success";
        } else {
            $msg = "Khalad ayaa dhacay markii macaamiilka la masaxayay.";
            $msgClass = "alert-danger";
        }
    }
    
    $customers = $customerModel->getAll(); // Refresh list after any operation
}
?>

<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h2 class="fw-bold text-dark mb-1">Customer Directory</h2>
        <p class="text-secondary mb-0">Manage and track your electricity consumers.</p>
    </div>
    <button class="btn btn-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
        <i class="fas fa-plus-circle me-2"></i> Add New Customer
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
                        <th class="px-4">Meter Details</th>
                        <th>Customer Name</th>
                        <th>Contact info</th>
                        <th>Tariff Type</th>
                        <th>Status</th>
                        <th class="text-end px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($customers): foreach ($customers as $customer): ?>
                    <tr>
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon-square soft-indigo me-3 m-0" style="width: 40px; height: 40px;">
                                    <i class="fas fa-bolt small"></i>
                                </div>
                                <span class="fw-bold text-primary font-monospace"><?php echo $customer->meter_number; ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark"><?php echo $customer->first_name . ' ' . $customer->last_name; ?></div>
                            <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i> <?php echo substr($customer->address, 0, 30); ?>...</small>
                        </td>
                        <td>
                            <div class="small fw-medium"><i class="fas fa-envelope me-2 text-muted"></i><?php echo $customer->email; ?></div>
                            <div class="small text-muted"><i class="fas fa-phone me-2 text-muted"></i><?php echo $customer->phone; ?></div>
                        </td>
                        <td>
                            <?php 
                                $tariffClass = 'soft-indigo';
                                if($customer->tariff_type == 'Commercial') $tariffClass = 'soft-emerald';
                                if($customer->tariff_type == 'Industrial') $tariffClass = 'soft-amber';
                            ?>
                            <span class="badge <?php echo $tariffClass; ?> px-3 py-2" style="border-radius: 10px;">
                                <?php echo $customer->tariff_type; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge soft-emerald">Active</span>
                        </td>
                        <td class="text-end px-4">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-light text-secondary me-2 border-0 shadow-none edit-customer-btn" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editCustomerModal"
                                        data-id="<?php echo $customer->id; ?>"
                                        data-firstname="<?php echo $customer->first_name; ?>"
                                        data-lastname="<?php echo $customer->last_name; ?>"
                                        data-email="<?php echo $customer->email; ?>"
                                        data-phone="<?php echo $customer->phone; ?>"
                                        data-address="<?php echo $customer->address; ?>"
                                        data-tariff="<?php echo $customer->tariff_type; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-light text-danger border-0 shadow-none delete-customer-btn"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteCustomerModal"
                                        data-id="<?php echo $customer->id; ?>"
                                        data-name="<?php echo $customer->first_name . ' ' . $customer->last_name; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-users-slash fa-3x text-light mb-3"></i>
                            <p class="text-muted">No customers found in the system.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h4 class="modal-title fw-bold text-dark">Enroll New Customer</h4>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Meter ID <span class="text-danger">*</span></label>
                            <input type="text" name="meter_number" class="form-control" placeholder="MTR-0000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tariff Category <span class="text-danger">*</span></label>
                            <select name="tariff_type" class="form-select" required>
                                <option value="Residential">Residential</option>
                                <option value="Commercial">Commercial</option>
                                <option value="Industrial">Industrial</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" placeholder="Enter first name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" placeholder="Enter last name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="name@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="+252 ...">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Residential Address</label>
                            <textarea name="address" class="form-control" rows="3" placeholder="Street, City, District..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Discard</button>
                    <button type="submit" name="add_customer" class="btn btn-primary px-4 shadow-none">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h4 class="modal-title fw-bold text-dark">Update Customer Info</h4>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted">Meter ID (Locked)</label>
                            <input type="text" id="edit_meter" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tariff Category</label>
                            <select name="tariff_type" id="edit_tariff" class="form-select" required>
                                <option value="Residential">Residential</option>
                                <option value="Commercial">Commercial</option>
                                <option value="Industrial">Industrial</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">First Name</label>
                            <input type="text" name="first_name" id="edit_firstname" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Last Name</label>
                            <input type="text" name="last_name" id="edit_lastname" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea name="address" id="edit_address" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_customer" class="btn btn-primary px-4 shadow-none">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-body p-4 text-center">
                <div class="stat-icon-square soft-rose mx-auto mb-4" style="width: 60px; height: 60px;">
                    <i class="fas fa-trash-alt fa-lg"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Ma hubtaa?</h5>
                <p class="text-secondary small mb-4">Waxaad qarka u saarantahay inaad tirtirto macaamiilka <br><strong id="delete_name" class="text-dark"></strong>. Talaabadan dib looguma soo laaban karo.</p>
                
                <form action="" method="POST">
                    <input type="hidden" name="id" id="delete_id">
                    <div class="d-grid gap-2">
                        <button type="submit" name="delete_customer" class="btn btn-danger py-2 shadow-none">Haa, Masax</button>
                        <button type="button" class="btn btn-light py-2" data-bs-dismiss="modal">Iska daa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Populate Edit Modal
    const editBtns = document.querySelectorAll('.edit-customer-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_meter').value = this.closest('tr').querySelector('.font-monospace').innerText;
            document.getElementById('edit_firstname').value = this.dataset.firstname;
            document.getElementById('edit_lastname').value = this.dataset.lastname;
            document.getElementById('edit_email').value = this.dataset.email;
            document.getElementById('edit_phone').value = this.dataset.phone;
            document.getElementById('edit_address').value = this.dataset.address;
            document.getElementById('edit_tariff').value = this.dataset.tariff;
        });
    });

    // Populate Delete Modal
    const deleteBtns = document.querySelectorAll('.delete-customer-btn');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('delete_id').value = this.dataset.id;
            document.getElementById('delete_name').innerText = this.dataset.name;
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
