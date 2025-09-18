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
        <h3 class="mb-4">Suppliers</h3>

        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="fa fa-tools mr-2"></i>
            <span>This page is under development. Please check back soon for updates.</span>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include_once 'footer.php'; ?>