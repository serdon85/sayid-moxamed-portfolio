<?php
// public/users.php
require_once '../includes/header.php';
require_once '../classes/User.php';

if (!hasRole('Admin')) {
    redirect('dashboard.php');
}

$userModel = new User();
$users = $userModel->getAll(); // I need to make sure this method exists in User class

// Get roles for the modal
$db = new Database();
$db->query("SELECT * FROM roles");
$roles = $db->resultSet();

$msg = '';
$msgClass = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        $data = [
            'username' => trim($_POST['username']),
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'full_name' => trim($_POST['full_name']),
            'role_id' => $_POST['role_id']
        ];

        if ($userModel->register($data)) {
            $msg = "Isticmaale cusub ayaa lagu daray!";
            $msgClass = "alert-success";
        } else {
            $msg = "Khalad ayaa dhacay.";
            $msgClass = "alert-danger";
        }
    }

    if (isset($_POST['edit_user'])) {
        $data = [
            'id' => $_POST['id'],
            'username' => trim($_POST['username']),
            'full_name' => trim($_POST['full_name']),
            'role_id' => $_POST['role_id']
        ];

        // Only hash password if provided
        if (!empty($_POST['password'])) {
            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        } else {
            $data['password'] = null;
        }

        if ($userModel->update($data)) {
            $msg = "Xogta isticmaalaha waa la cusubaysiiyay!";
            $msgClass = "alert-success";
        } else {
            $msg = "Khalad ayaa dhacay.";
            $msgClass = "alert-danger";
        }
    }

    if (isset($_POST['delete_user'])) {
        if ($userModel->delete($_POST['id'])) {
            $msg = "Account-igii waa la masaxay!";
            $msgClass = "alert-success";
        } else {
            $msg = "Khalad ayaa dhacay.";
            $msgClass = "alert-danger";
        }
    }
    
    $users = $userModel->getAll();
}
?>

<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h2 class="fw-bold text-dark mb-1">System User Management</h2>
        <p class="text-secondary mb-0">Control access levels and manage administrative accounts.</p>
    </div>
    <button class="btn btn-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fas fa-user-plus me-2"></i> Create New User
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
                        <th class="px-4">User</th>
                        <th>Credentials</th>
                        <th>Access Role</th>
                        <th>Status</th>
                        <th class="text-end px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users): foreach ($users as $u): ?>
                    <tr>
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon-square soft-indigo me-3 m-0" style="width: 40px; height: 40px;">
                                    <i class="fas fa-user-shield small"></i>
                                </div>
                                <div class="fw-bold text-dark"><?php echo $u->full_name; ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="small fw-medium">@<?php echo $u->username; ?></div>
                        </td>
                        <td>
                            <?php 
                                $roleClass = 'soft-indigo';
                                if($u->role_name == 'Admin') $roleClass = 'soft-rose';
                                if($u->role_name == 'Accountant') $roleClass = 'soft-emerald';
                            ?>
                            <span class="badge <?php echo $roleClass; ?> px-3 py-2" style="border-radius: 10px;">
                                <?php echo $u->role_name; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge soft-emerald"><i class="fas fa-circle me-1 small"></i> Active</span>
                        </td>
                        <td class="text-end px-4">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-light text-secondary me-2 border-0 shadow-none edit-user-btn"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editUserModal"
                                        data-id="<?php echo $u->id; ?>"
                                        data-name="<?php echo $u->full_name; ?>"
                                        data-username="<?php echo $u->username; ?>"
                                        data-role="<?php echo $u->role_id; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-light text-danger border-0 shadow-none delete-user-btn"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteUserModal"
                                        data-id="<?php echo $u->id; ?>"
                                        data-name="<?php echo $u->full_name; ?>">
                                    <i class="fas fa-user-lock"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h4 class="modal-title fw-bold text-dark">Register New Staff</h4>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" name="full_name" class="form-control" placeholder="e.g. Ahmed Ali" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="e.g. ahmed123" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Assign Role</label>
                        <select name="role_id" class="form-select" required>
                            <?php foreach($roles as $role): ?>
                            <option value="<?php echo $role->id; ?>"><?php echo $role->role_name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Discard</button>
                    <button type="submit" name="add_user" class="btn btn-primary px-4 shadow-none">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h4 class="modal-title fw-bold text-dark">Update Staff Info</h4>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" name="full_name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password (Optional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Hubi hadii aad rabto inaad bedesho">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Assign Role</label>
                        <select name="role_id" id="edit_role" class="form-select" required>
                            <?php foreach($roles as $role): ?>
                            <option value="<?php echo $role->id; ?>"><?php echo $role->role_name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_user" class="btn btn-primary px-4 shadow-none">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-body p-4 text-center">
                <div class="stat-icon-square soft-rose mx-auto mb-4" style="width: 60px; height: 60px;">
                    <i class="fas fa-lock fa-lg"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Ma hubtaa?</h5>
                <p class="text-secondary small mb-4">Waxaad xidhi lahayd account-iga: <br><strong id="delete_name" class="text-dark"></strong>.</p>
                
                <form action="" method="POST">
                    <input type="hidden" name="id" id="delete_id">
                    <div class="d-grid gap-2">
                        <button type="submit" name="delete_user" class="btn btn-danger py-2 shadow-none">Haa, Masax</button>
                        <button type="button" class="btn btn-light py-2" data-bs-dismiss="modal">Iska daa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit User
    const editBtns = document.querySelectorAll('.edit-user-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_name').value = this.dataset.name;
            document.getElementById('edit_username').value = this.dataset.username;
            document.getElementById('edit_role').value = this.dataset.role;
        });
    });

    // Delete User
    const deleteBtns = document.querySelectorAll('.delete-user-btn');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('delete_id').value = this.dataset.id;
            document.getElementById('delete_name').innerText = this.dataset.name;
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
