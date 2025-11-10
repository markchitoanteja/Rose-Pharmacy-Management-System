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

<div class="content-wrapper" id="content">
    <div class="container-fluid">

        <!-- Page Title & Add Supplier Button -->
        <div class="page-title row mb-4">
            <div class="col-6">
                <h3>Suppliers</h3>
            </div>
            <div class="col-6">
                <button class="btn btn-primary float-right" data-toggle="modal" data-target="#addSupplierModal">
                    <i class="fas fa-plus mr-1"></i> Add Supplier
                </button>
            </div>
        </div>

        <!-- Suppliers Table -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0">All Suppliers</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered datatable" id="suppliersTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact Number</th>
                            <th>Address</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $suppliers = $db->custom_query("SELECT * FROM suppliers ORDER BY name ASC");

                        if ($suppliers):
                            foreach ($suppliers as $sup):
                        ?>
                                <tr>
                                    <td><?= htmlspecialchars($sup['name']) ?></td>
                                    <td><?= htmlspecialchars($sup['contact_number']) ?></td>
                                    <td><?= htmlspecialchars($sup['address']) ?></td>
                                    <td class="text-center">
                                        <i class="fas fa-pencil-alt text-primary edit_supplier" role="button" data-id="<?= $sup['supplier_id'] ?>" title="Edit"></i>
                                        <i class="fas fa-trash-alt text-danger delete_supplier" role="button" data-id="<?= $sup['supplier_id'] ?>" title="Delete"></i>
                                    </td>
                                </tr>
                        <?php endforeach;
                        endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="addSupplierForm" action="javascript:void(0)">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Supplier</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="add_supplier_name">Supplier Name</label>
                        <input type="text" id="add_supplier_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="add_supplier_contact">Contact Number</label>
                        <input type="text" id="add_supplier_contact" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="add_supplier_address">Address</label>
                        <textarea id="add_supplier_address" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Supplier Modal -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="editSupplierForm" action="javascript:void(0)">
                <input type="hidden" id="edit_supplier_id">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Supplier</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Supplier Name</label>
                        <input type="text" id="edit_supplier_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" id="edit_supplier_contact" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea id="edit_supplier_address" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once 'footer.php'; ?>