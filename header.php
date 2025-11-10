<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/check-folder.php';
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
} elseif ($currentPage === 'pos.php') {
    $page_title = 'Point of Sale';
} else {
    $page_title = ucfirst(str_replace('.php', '', $currentPage));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>JIAN's Pharmacy - <?= e($page_title) ?></title>

    <link rel="shortcut icon" href="favicon.ico?ver=<?= env('APP_VERSION') ?>" type="image/x-icon">

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
                <a class="navbar-brand loadable" href="<?= base_url() ?>">JIAN's Pharmacy</a>
                <a class="btn btn-link text-white float-right" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </a>
            </div>

            <ul class="navbar-nav ml-auto">
                <?php if ($_SESSION['role'] == "Admin"): ?>
                    <!-- Notifications Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="javascript:void(0)" id="notificationDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bell fa-lg"></i>
                            <span class="badge badge-danger" id="notificationCount"></span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right shadow-sm notification-dropdown" aria-labelledby="notificationDropdown">
                            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                <h6 class="mb-0 font-weight-bold">Notifications</h6>
                                <button class="btn btn-sm btn-link text-primary p-0" id="markAllReadBtn">Mark all as read</button>
                            </div>
                            <div id="notificationsContainer" class="notification-list">
                                <p class="text-center text-muted mb-0 py-3">No new notifications</p>
                            </div>
                            <div class="dropdown-divider my-0"></div>
                            <a href="notifications" class="dropdown-item text-center text-primary font-weight-bold py-2">View All</a>
                        </div>
                    </li>
                <?php endif ?>

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
            <!-- Dashboard -->
            <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
            </a>

            <?php if ($_SESSION['role'] == "Admin"): ?>
                <!-- POS / Sales -->
                <a href="pos.php" class="loadable <?= $currentPage === 'pos.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cash-register mr-1"></i> POS
                </a>

                <a href="sales.php" class="loadable <?= $currentPage === 'sales.php' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart mr-1"></i> Sales
                </a>

                <!-- Inventory Management -->
                <a href="medicines.php" class="loadable <?= $currentPage === 'medicines.php' ? 'active' : ''; ?>">
                    <i class="fas fa-pills mr-1"></i> Medicines
                </a>

                <a href="suppliers.php" class="loadable <?= $currentPage === 'suppliers.php' ? 'active' : ''; ?>">
                    <i class="fas fa-truck mr-1"></i> Suppliers
                </a>

                <!-- User Management -->
                <a href="users.php" class="loadable <?= $currentPage === 'users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users mr-1"></i> Users
                </a>

                <!-- System Monitoring -->
                <a href="activity_logs.php" class="loadable <?= $currentPage === 'activity_logs.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt mr-1"></i> Activity Logs
                </a>

            <?php elseif ($_SESSION['role'] == "Pharmacist"): ?>
                <!-- POS / Sales -->
                <a href="pos.php" class="loadable <?= $currentPage === 'pos.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cash-register mr-1"></i> POS
                </a>

                <!-- Inventory Management -->
                <a href="medicines.php" class="loadable <?= $currentPage === 'medicines.php' ? 'active' : ''; ?>">
                    <i class="fas fa-pills mr-1"></i> Medicines
                </a>
            <?php endif; ?>

            <!-- Notes (common to all) -->
            <a href="notes.php" class="<?= $currentPage === 'notes.php' ? 'active' : ''; ?>">
                <i class="fas fa-sticky-note mr-1"></i> Notes
            </a>
        </div>