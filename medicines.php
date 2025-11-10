<?php include_once 'header.php'; ?>

<style>
    .medicines-table td.truncate-cell {
        max-width: 200px;
        /* adjust as needed */
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
    }
</style>

<div class="content-wrapper" id="content">
    <div class="container-fluid">
        <!-- Page Title & Add Button -->
        <div class="page-title row mb-4">
            <div class="col-6">
                <h3>Medicines</h3>
            </div>
            <?php if ($_SESSION['role'] == "Admin"): ?>
                <div class="col-6">
                    <button class="btn btn-primary float-right" data-toggle="modal" data-target="#addMedicineModal">
                        <i class="fas fa-plus mr-1"></i> Add Medicine
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Medicines Table -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0">All Medicines</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered datatable medicines-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Expiry</th>
                            <th>Supplier</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $expiry_threshold = date('Y-m-d', strtotime('+30 days'));

                        $medicines = $db->custom_query("
                            SELECT m.*, s.name AS supplier_name
                            FROM medicines m
                            LEFT JOIN suppliers s ON m.supplier_id = s.supplier_id
                            ORDER BY m.medicine_id DESC
                        ");

                        if ($medicines):
                            foreach ($medicines as $med):

                                // --- Dynamic Alert Levels ---
                                $low_stock_threshold = $med['stock_alert_level'] ?? 5; // fallback to 5 if null
                                $isLowStock = $med['quantity'] <= $low_stock_threshold;
                                $isExpiring = $med['expiry_date'] <= $expiry_threshold;
                                $isExpired = $med['expiry_date'] < date('Y-m-d');

                                // Assign row class
                                $rowClass = '';
                                if ($isExpired) {
                                    $rowClass = 'table-danger'; // Expired
                                } elseif ($isExpiring) {
                                    $rowClass = 'table-warning'; // Expiring soon
                                } elseif ($isLowStock) {
                                    $rowClass = 'table-danger'; // Low stock
                                }
                        ?>
                                <tr class="<?= $rowClass ?>">
                                    <td class="truncate-cell" title="<?= htmlspecialchars($med['name']) ?>">
                                        <?= htmlspecialchars($med['name']) ?>
                                    </td>
                                    <td class="truncate-cell" title="<?= htmlspecialchars($med['category']) ?>">
                                        <?= htmlspecialchars($med['category']) ?>
                                    </td>
                                    <td class="truncate-cell" title="<?= htmlspecialchars($med['description']) ?>">
                                        <?= htmlspecialchars($med['description']) ?>
                                    </td>
                                    <td>₱<?= number_format($med['unit_price'], 2) ?></td>
                                    <td>
                                        <?= $med['quantity'] ?> pc<?= $med['quantity'] > 1 ? 's' : '' ?>
                                        <?php if ($isLowStock): ?>
                                            <span class="badge badge-danger ml-1">Low (≤ <?= $low_stock_threshold ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($med['expiry_date']) ?>
                                        <?php if ($isExpired): ?>
                                            <span class="badge badge-danger ml-1">Expired</span>
                                        <?php elseif ($isExpiring): ?>
                                            <span class="badge badge-warning ml-1">Expiring Soon</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="truncate-cell" title="<?= htmlspecialchars($med['supplier_name']) ?>">
                                        <?= htmlspecialchars($med['supplier_name']) ?>
                                    </td>
                                    <td class="text-center">
                                        <i class="fas fa-pencil-alt text-primary edit_medicine" role="button" data-id="<?= $med['medicine_id'] ?>" title="Edit"></i>
                                    </td>
                                </tr>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Medicine Modal -->
<div class="modal fade" id="addMedicineModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="addMedicineForm" action="javascript:void(0)">
                <div class="modal-header">
                    <h5 class="modal-title">Add Medicine</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="add_medicine_name">Name</label>
                        <input type="text" id="add_medicine_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="add_medicine_category">Category</label>
                        <input type="text" id="add_medicine_category" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="add_medicine_description">Description</label>
                        <textarea id="add_medicine_description" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="add_medicine_unit_price">Unit Price</label>
                            <input type="number" step="0.01" id="add_medicine_unit_price" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="add_medicine_quantity">Quantity</label>
                            <input type="number" id="add_medicine_quantity" class="form-control" value="0" min="0" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_medicine_expiry_date">Expiry Date</label>
                        <input type="date" id="add_medicine_expiry_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="add_medicine_supplier">Supplier</label>
                        <select id="add_medicine_supplier" class="form-control" required>
                            <option value="" selected disabled></option>
                            <?php
                            $suppliers = $db->custom_query("SELECT supplier_id, name FROM suppliers ORDER BY name ASC");
                            foreach ($suppliers as $sup) {
                                echo "<option value='{$sup['supplier_id']}'>{$sup['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Medicine</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Medicine Modal -->
<div class="modal fade" id="editMedicineModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="editMedicineForm" action="javascript:void(0)">
                <input type="hidden" name="medicine_id" id="edit_medicine_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Medicine</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_medicine_name">Name</label>
                        <input type="text" id="edit_medicine_name" class="form-control" required <?= $_SESSION['role'] == "Admin" ? '' : 'readonly' ?>>
                    </div>
                    <div class="form-group">
                        <label for="edit_medicine_category">Category</label>
                        <input type="text" id="edit_medicine_category" class="form-control" required <?= $_SESSION['role'] == "Admin" ? '' : 'readonly' ?>>
                    </div>
                    <div class="form-group">
                        <label for="edit_medicine_description">Description</label>
                        <textarea id="edit_medicine_description" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="edit_medicine_unit_price">Unit Price</label>
                            <input type="number" step="0.01" id="edit_medicine_unit_price" class="form-control" required <?= $_SESSION['role'] == "Admin" ? '' : 'readonly' ?>>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="edit_medicine_quantity">Quantity</label>
                            <input type="number" id="edit_medicine_quantity" class="form-control" value="0" min="0" required <?= $_SESSION['role'] == "Admin" ? '' : 'readonly' ?>>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_medicine_expiry_date">Expiry Date</label>
                        <input type="date" id="edit_medicine_expiry_date" class="form-control" required <?= $_SESSION['role'] == "Admin" ? '' : 'readonly' ?>>
                    </div>
                    <div class="form-group">
                        <label for="edit_medicine_supplier">Supplier</label>
                        <select id="edit_medicine_supplier" class="form-control" required <?= $_SESSION['role'] == "Admin" ? '' : 'readonly' ?>>
                            <?php
                            $suppliers = $db->custom_query("SELECT supplier_id, name FROM suppliers ORDER BY name ASC");
                            foreach ($suppliers as $sup) {
                                echo "<option value='{$sup['supplier_id']}'>{$sup['name']}</option>";
                            }
                            ?>
                        </select>
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