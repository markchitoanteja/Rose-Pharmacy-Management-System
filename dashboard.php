<?php
include_once 'header.php';

// Dashboard summary stats
$count_users = count($db->custom_query("SELECT * FROM users WHERE is_active = 1"));
$count_users_inactive = count($db->custom_query("SELECT * FROM users WHERE is_active = 0"));
$count_medicines = count($db->select_all("medicines"));
$count_suppliers = count($db->select_all("suppliers"));
$result = $db->custom_query("SELECT SUM(total_amount) as total_sales FROM sales", [], true)["total_sales"];
$total_sales = isset($result) ? $result : 0;

// Helper for currency formatting
function peso($amount)
{
    return '₱' . number_format($amount, 2);
}

// Fetch recent transactions (latest 10)
$recent_sales = $db->custom_query("
    SELECT s.receipt_number, s.sale_date, u.full_name AS cashier_name,
           SUM(si.quantity * si.price) AS total,
           GROUP_CONCAT(m.name, ' (', si.quantity, ') ' SEPARATOR ', ') AS items
    FROM sales s
    JOIN users u ON s.user_id = u.user_id
    JOIN sale_items si ON s.sale_id = si.sale_id
    JOIN medicines m ON si.medicine_id = m.medicine_id
    GROUP BY s.receipt_number
    ORDER BY s.sale_date DESC
    LIMIT 10
");
?>

<style>
    .info-box {
        border-radius: 0.75rem;
        overflow: hidden;
        position: relative;
        cursor: pointer;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.08);
    }

    .info-box:hover {
        transform: translateY(-6px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }

    .info-box.bg-primary {
        background: linear-gradient(135deg, #007bff, #0056b3);
    }

    .info-box.bg-success {
        background: linear-gradient(135deg, #28a745, #1e7e34);
    }

    .info-box.bg-warning {
        background: linear-gradient(135deg, #ffc107, #d39e00);
        color: #212529 !important;
    }

    .info-box.bg-danger {
        background: linear-gradient(135deg, #dc3545, #a71d2a);
    }

    .info-box .icon-box {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        flex-shrink: 0;
    }

    .info-box h5 {
        font-weight: 700;
        font-size: 1.25rem;
    }

    .info-box small {
        font-size: 0.85rem;
        opacity: 0.9;
    }

    .card-header h6 {
        font-weight: 600;
        color: #333;
    }
</style>

<!-- Main Content -->
<div class="content-wrapper" id="content">
    <div class="container-fluid">
        <h3 class="mb-4">Dashboard</h3>

        <!-- Info Boxes -->
        <div class="row">
            <div class="col-md-3 mb-4 loadable" id="info_users">
                <div class="card info-box bg-primary text-white">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-box mr-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h5 class="mb-0"><?= $count_users ?>
                                <small class="text-light">(Inactive: <?= $count_users_inactive ?>)</small>
                            </h5>
                            <small>Users</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4 loadable" id="info_medicines">
                <div class="card info-box bg-success text-white">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-box mr-3">
                            <i class="fas fa-pills"></i>
                        </div>
                        <div>
                            <h5 class="mb-0"><?= $count_medicines ?></h5>
                            <small>Medicines</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4 loadable" id="info_suppliers">
                <div class="card info-box bg-warning text-white">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-box mr-3">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div>
                            <h5 class="mb-0"><?= $count_suppliers ?></h5>
                            <small>Suppliers</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4 loadable" id="info_sales">
                <div class="card info-box bg-danger text-white">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-box mr-3">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div>
                            <h5 class="mb-0"><?= peso($total_sales) ?></h5>
                            <small>Sales</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions Table -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Recent Transactions</h6>
                <?php if ($_SESSION["role"] === "Admin") : ?>
                    <a href="<?= base_url('sales') ?>" class="btn btn-sm btn-outline-primary loadable">View All</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover datatable mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Receipt #</th>
                            <th>Cashier</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_sales)): ?>
                            <?php foreach ($recent_sales as $sale): ?>
                                <tr>
                                    <td><?= htmlspecialchars($sale['receipt_number']) ?></td>
                                    <td><?= htmlspecialchars($sale['cashier_name']) ?></td>
                                    <td><?= htmlspecialchars($sale['items']) ?></td>
                                    <td><?= peso($sale['total']) ?></td>
                                    <td><?= date("F j, Y g:i A", strtotime($sale['sale_date'])) ?></td>
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