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
                <h3>Medicines</h3>
            </div>
            <div class="col-6">
                <button class="btn btn-primary float-right" data-toggle="modal" data-target="#addMedicineModal">
                    <i class="fas fa-plus mr-1"></i>
                    Add Medicine
                </button>
            </div>
        </div>

        
    </div>
</div>

<!-- Add Medicine Modal -->
<div class="modal fade" id="addMedicineModal" tabindex="-1" role="dialog" aria-labelledby="addMedicineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="addMedicineModalLabel">Add New Medicine</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="addMedicineForm" method="POST" action="add_medicine.php">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="medicine_name">Medicine Name</label>
                        <input type="text" class="form-control" id="medicine_name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <input type="text" class="form-control" id="category" name="category">
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="unit_price">Unit Price</label>
                            <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="quantity">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" required value="0" min="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="expiry_date">Expiry Date</label>
                        <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                    </div>

                    <div class="form-group">
                        <label for="supplier_id">Supplier</label>
                        <select class="form-control" id="supplier_id" name="supplier_id" required>
                            <option value="">-- Select Supplier --</option>
                            <!-- Populate dynamically from suppliers table -->
                            <?php
                            // Example: fetching suppliers
                            // $result = $conn->query("SELECT supplier_id, name FROM suppliers");
                            // while ($row = $result->fetch_assoc()) {
                            //     echo "<option value='{$row['supplier_id']}'>{$row['name']}</option>";
                            // }
                            ?>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Medicine</button>
                </div>
            </form>

        </div>
    </div>
</div>


<!-- Footer -->
<?php include_once 'footer.php'; ?>