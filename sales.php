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

// Default sales data
$sales = $db->custom_query("
    SELECT s.sale_id, s.receipt_number, s.sale_date, u.full_name AS cashier,
           SUM(si.quantity * si.price) AS total
    FROM sales s
    JOIN sale_items si ON s.sale_id = si.sale_id
    JOIN users u ON s.user_id = u.user_id
    GROUP BY s.sale_id
    ORDER BY s.sale_date DESC
");

function peso($amount)
{
    return '₱' . number_format($amount, 2);
}
?>

<!-- Main Content -->
<div class="content-wrapper" id="content">
    <div class="container-fluid">
        <h3 class="mb-4">Sales Report</h3>

        <!-- Filters -->
        <div class="card mb-4 shadow-sm border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-filter mr-2"></i>Filter Sales</h6>
            </div>
            <div class="card-body">
                <form id="salesFilterForm" class="form-inline">
                    <div class="form-group mr-2">
                        <label for="start_date" class="mr-2">Start Date</label>
                        <input type="date" id="start_date" name="start_date" class="form-control">
                    </div>
                    <div class="form-group mr-2">
                        <label for="end_date" class="mr-2">End Date</label>
                        <input type="date" id="end_date" name="end_date" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                    <button type="button" id="exportPDF" class="btn btn-success ml-2">
                        <i class="fas fa-file-pdf mr-1"></i> Export PDF
                    </button>
                </form>
            </div>
        </div>

        <!-- Sales Table -->
        <div class="card shadow-sm border-success">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-chart-line mr-2"></i>Sales Records</h6>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover" id="salesTable">
                    <thead class="thead-light">
                        <tr>
                            <th>Receipt</th>
                            <th>Cashier</th>
                            <th>Date</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sales)): ?>
                            <tr>
                                <td colspan="4" class="text-center">No sales found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sales as $sale): ?>
                                <tr>
                                    <td><?= htmlspecialchars($sale['receipt_number']) ?></td>
                                    <td><?= htmlspecialchars($sale['cashier']) ?></td>
                                    <td><?= date("F j, Y g:i A", strtotime($sale['sale_date'])) ?></td>
                                    <td><?= peso($sale['total']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include_once 'footer.php'; ?>