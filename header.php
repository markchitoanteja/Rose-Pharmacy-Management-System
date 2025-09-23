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

if ($currentPage === 'activity_logs.php') {
    $page_title = 'Activity Logs';
} else {
    $page_title = ucfirst(str_replace('.php', '', $currentPage));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Rose Pharmacy - <?= e($page_title) ?></title>

    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="dist/plugins/bootstrap/css/bootstrap.min.css?ver=<?= env('APP_VERSION') ?>">
    <link rel="stylesheet" href="dist/plugins/fontawesome/css/all.min.css?ver=<?= env('APP_VERSION') ?>">
    <link rel="stylesheet" href="dist/plugins/sweetalert/css/sweetalert.min.css?ver=<?= env('APP_VERSION') ?>">
    <link rel="stylesheet" href="dist/plugins/datatables/css/dataTables.bootstrap4.min.css?ver=<?= env('APP_VERSION') ?>">
    <link rel="stylesheet" href="dist/main/css/style.css?ver=<?= env('APP_VERSION') ?>">
</head>

<body>
    <!-- Loading Overlay -->
    <div id="loadingOverlay">
        <div class="spinner"></div>
        <div class="loading-text mt-2" style="text-align:center;">Please Wait...</div>
    </div>

    <div class="wrapper">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
            <div class="navbar-container">
                <a class="navbar-brand loadable" href="<?= base_url() ?>">Rose Pharmacy</a>
                <a class="btn btn-link text-white float-right" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </a>
            </div>

            <ul class="navbar-nav ml-auto">
                <!-- User dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="userDropdown" role="button" data-toggle="dropdown">
                        <?= e($user['full_name']); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#accountSettingsModal"><i class="fas fa-user mr-1"></i> Account Settings</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#aboutUsModal"><i class="fas fa-info-circle mr-1"></i> About Us</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="javascript:void(0)" id="logout_btn"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
            </a>

            <?php if ($user['role_id'] == 1): // Admin only 
            ?>
                <a href="medicines.php" class="loadable <?= $currentPage === 'medicines.php' ? 'active' : ''; ?>">
                    <i class="fas fa-pills mr-1"></i> Medicines <i class="fas fa-tools float-right text-warning"></i>
                </a>

                <a href="suppliers.php" class="loadable <?= $currentPage === 'suppliers.php' ? 'active' : ''; ?>">
                    <i class="fas fa-truck mr-1"></i> Suppliers <i class="fas fa-tools float-right text-warning"></i>
                </a>

                <a href="sales.php" class="loadable <?= $currentPage === 'sales.php' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart mr-1"></i> Sales <i class="fas fa-tools float-right text-warning"></i>
                </a>

                <a href="users.php" class="loadable <?= $currentPage === 'users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users mr-1"></i> Users
                </a>

                <a href="activity_logs.php" class="loadable <?= $currentPage === 'activity_logs.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt mr-1"></i> Activity Logs
                </a>
            <?php endif; ?>

            <?php if ($user['role_id'] == 2): // Cashier only 
            ?>
                <a href="cashier.php" class="loadable <?= $currentPage === 'cashier.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cash-register mr-1"></i> Cashier
                </a>
            <?php endif; ?>
        </div>