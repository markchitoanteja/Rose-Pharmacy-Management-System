<?php
// dashboard.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/Database.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['notification'] = ["icon" => "error", "text" => "You need to log in to access this page.", "title" => "Access Denied"];
    header("Location: " . base_url());
    exit;
}

$db = new Database();
$user = $db->select_one("users", "user_id = ?", [$_SESSION['user_id']]);

// Detect active page (basic)
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Rose Pharmacy - <?= ucfirst(str_replace('.php', '', $currentPage)) ?></title>

    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="dist/plugins/bootstrap/css/bootstrap.min.css?ver=<?= env('APP_VERSION') ?>">
    <link rel="stylesheet" href="dist/plugins/fontawesome/css/all.min.css?ver=<?= env('APP_VERSION') ?>">
    <link rel="stylesheet" href="dist/plugins/sweetalert/css/sweetalert.min.css?ver=<?= env('APP_VERSION') ?>">
    <link rel="stylesheet" href="dist/main/css/style.css?ver=<?= env('APP_VERSION') ?>">
</head>

<body>
    <div class="wrapper">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
            <a class="navbar-brand ml-2" href="#">Rose Pharmacy</a>
            <button class="btn btn-link text-white" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>

            <ul class="navbar-nav ml-auto">
                <!-- User dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                        <?= e($user['full_name']); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="javascript:void(0)"><i class="fas fa-user mr-1"></i> Account Settings</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="javascript:void(0)"><i class="fas fa-info-circle mr-1"></i> About Us</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="javascript:void(0)" id="logout_btn"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt mr-1"></i> Dashboard</a>
            <a href="medicines.php" class="<?= $currentPage === 'medicines.php' ? 'active' : ''; ?>"><i class="fas fa-pills mr-1"></i> Medicines</a>
            <a href="suppliers.php" class="<?= $currentPage === 'suppliers.php' ? 'active' : ''; ?>"><i class="fas fa-truck mr-1"></i> Suppliers</a>
            <a href="sales.php" class="<?= $currentPage === 'sales.php' ? 'active' : ''; ?>"><i class="fas fa-shopping-cart mr-1"></i> Sales</a>
            <a href="users.php" class="<?= $currentPage === 'users.php' ? 'active' : ''; ?>"><i class="fas fa-users mr-1"></i> Users</a>
        </div>

        <!-- Main Content -->
        <div class="content-wrapper" id="content">
            <div class="container-fluid">
                <h2 class="mb-4">Dashboard</h2>

                <!-- Row of cards -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <div class="card-icon mb-2"><i class="fas fa-pills"></i></div>
                                <h5 class="card-title">Medicines</h5>
                                <p class="card-text">Manage inventory</p>
                                <a href="medicines.php" class="btn btn-sm btn-primary">View</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <div class="card-icon mb-2"><i class="fas fa-truck"></i></div>
                                <h5 class="card-title">Suppliers</h5>
                                <p class="card-text">Manage suppliers</p>
                                <a href="suppliers.php" class="btn btn-sm btn-primary">View</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <div class="card-icon mb-2"><i class="fas fa-shopping-cart"></i></div>
                                <h5 class="card-title">Sales</h5>
                                <p class="card-text">Track transactions</p>
                                <a href="sales.php" class="btn btn-sm btn-primary">View</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <div class="card-icon mb-2"><i class="fas fa-users"></i></div>
                                <h5 class="card-title">Users</h5>
                                <p class="card-text">Manage accounts</p>
                                <a href="users.php" class="btn btn-sm btn-primary">View</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Example table -->
                <div class="card mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Admin</td>
                                    <td>Logged in</td>
                                    <td>2025-09-15 10:00</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Cashier1</td>
                                    <td>Processed Sale</td>
                                    <td>2025-09-15 10:10</td>
                                </tr>
                                <!-- Later: dynamic rows from DB -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            © <?= date("Y"); ?> Rose Pharmacy Incorporated. All rights reserved.
        </footer>
    </div>

    <script>
        const base_url = '<?= base_url() ?>';
        const server_url = base_url + 'server';
        const notification = <?= isset($_SESSION['notification']) ? json_encode($_SESSION['notification']) : 'null'; ?>;
    </script>

    <script src="dist/plugins/jquery/jquery.min.js?ver=<?= env('APP_VERSION') ?>"></script>
    <script src="dist/plugins/bootstrap/js/bootstrap.bundle.min.js?ver=<?= env('APP_VERSION') ?>"></script>
    <script src="dist/plugins/sweetalert/js/sweetalert.min.js?ver=<?= env('APP_VERSION') ?>"></script>
    <script src="dist/main/js/script.js?ver=<?= env('APP_VERSION') ?>"></script>
</body>

</html>

<?php unset($_SESSION['notification']); ?>