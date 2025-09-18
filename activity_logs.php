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
        <div class="row mb-4">
            <div class="col-6">
                <h3>Activity Logs</h3>
            </div>
            <div class="col-6">
                <button class="btn btn-secondary float-right" onclick="location.reload();">
                    <i class="fas fa-sync-alt mr-1"></i>
                    Refresh
                </button>
            </div>
        </div>

        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="fa fa-tools mr-2"></i>
            <span>This page is under development. Please check back soon for updates.</span>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include_once 'footer.php'; ?>