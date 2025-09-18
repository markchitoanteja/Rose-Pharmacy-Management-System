<?php
include_once 'header.php';

// Restrict access to admins only
if ($user['role_id'] != 1) {
    $_SESSION['notification'] = [
        "icon" => "error",
        "text" => "You do not have permission to access this page.",
        "title" => "Access Denied"
    ];
    header("Location: dashboard");
    exit;
}
?>

<!-- Main Content -->
<div class="content-wrapper" id="content">
    <div class="container-fluid">
        <div class="page-title row mb-4">
            <div class="col-6">
                <h3>Users</h3>
            </div>
            <div class="col-6">
                <button class="btn btn-primary float-right" data-toggle="modal" data-target="#addUserModal">
                    <i class="fas fa-plus mr-1"></i>
                    Add User
                </button>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">All Users</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Member Since</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $users = $db->custom_query("
                            SELECT users.*, roles.role_name 
                            FROM users 
                            JOIN roles ON users.role_id = roles.role_id 
                            ORDER BY users.user_id DESC
                        ");
                        ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['full_name']) ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['role_name']) ?></td>
                                <td><?= date("F j, Y", strtotime($user['created_at'])) ?></td>
                                <td class="text-center">
                                    <?php if ($user['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($_SESSION["user_id"] != $user["user_id"]): ?>
                                        <i class="fas fa-pencil-alt text-primary mr-1 update_user"
                                            data-user_id="<?= $user['user_id'] ?>"
                                            data-role_id="<?= $user['role_id'] ?>"
                                            data-created_at="<?= $user['created_at'] ?>"
                                            role="button"
                                            title="Update User"></i>

                                        <?php if ($user['is_active']): ?>
                                            <!-- Deactivate button -->
                                            <i class="fas fa-user-slash text-danger toggle_user"
                                                data-user_id="<?= $user['user_id'] ?>"
                                                data-status="0"
                                                role="button"
                                                title="Deactivate User"></i>
                                        <?php else: ?>
                                            <!-- Reactivate button -->
                                            <i class="fas fa-user-check text-success toggle_user"
                                                data-user_id="<?= $user['user_id'] ?>"
                                                data-status="1"
                                                role="button"
                                                title="Reactivate User"></i>
                                        <?php endif; ?>

                                    <?php else: ?>
                                        <span class="text-muted">---</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="addUserLabel">Add User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <form action="javascript:void(0)" id="add_user_form">
                    <!-- Profile Section -->
                    <h6 class="text-uppercase text-secondary mb-3">Profile Information</h6>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="add_user_full_name">Full Name</label>
                            <input type="text" class="form-control" id="add_user_full_name" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="add_user_username">Username</label>
                            <input type="text" class="form-control" id="add_user_username" required>
                        </div>
                    </div>

                    <!-- Security Section -->
                    <h6 class="text-uppercase text-secondary mt-4 mb-3">Security</h6>
                    <div class="form-group">
                        <label for="add_user_password">Password</label>
                        <input type="password" class="form-control" id="add_user_password" required>
                    </div>

                    <!-- Account Details -->
                    <h6 class="text-uppercase text-secondary mt-4 mb-3">Account Details</h6>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="add_user_role_id">Role</label>
                            <select id="add_user_role_id" class="custom-select" required>
                                <option value="" disabled selected></option>
                                <?php $roles = $db->select_all("roles") ?>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['role_id'] ?>"><?= $role['role_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="add_user_created_at">Date Created</label>
                            <input type="text" class="form-control text-muted" id="add_user_created_at" value="<?= date('F j, Y') ?>" readonly>
                        </div>
                    </div>
                </form>
            </div>
            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="add_user_submit" form="add_user_form">Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- Update User Modal -->
<div class="modal fade" id="updateUserModal" tabindex="-1" role="dialog" aria-labelledby="updateUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="updateUserLabel">Update User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <form action="javascript:void(0)" id="update_user_form">
                    <input type="hidden" id="update_user_user_id">
                    <!-- Profile Section -->
                    <h6 class="text-uppercase text-secondary mb-3">Profile Information</h6>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="update_user_full_name">Full Name</label>
                            <input type="text" class="form-control" id="update_user_full_name" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="update_user_username">Username</label>
                            <input type="text" class="form-control" id="update_user_username" required>
                        </div>
                    </div>

                    <!-- Security Section -->
                    <h6 class="text-uppercase text-secondary mt-4 mb-3">Security</h6>
                    <div class="form-group">
                        <label for="update_user_password">New Password <small class="text-muted">(leave blank if unchanged)</small></label>
                        <input type="password" class="form-control" id="update_user_password">
                    </div>

                    <!-- Account Details -->
                    <h6 class="text-uppercase text-secondary mt-4 mb-3">Account Details</h6>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="update_user_role_id">Role</label>
                            <select id="update_user_role_id" class="custom-select" required>
                                <?php $roles = $db->select_all("roles") ?>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['role_id'] ?>"><?= $role['role_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="update_user_created_at">Member Since</label>
                            <input type="text" class="form-control text-muted" id="update_user_created_at" readonly>
                        </div>
                    </div>
                </form>
            </div>
            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="update_user_submit" form="update_user_form">Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include_once 'footer.php'; ?>