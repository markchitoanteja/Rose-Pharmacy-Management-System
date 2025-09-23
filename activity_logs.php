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
                <button class="btn btn-secondary float-right loadable" onclick="location.reload();">
                    <i class="fas fa-sync-alt mr-1"></i>
                    Refresh
                </button>
            </div>
        </div>

        <!-- Recent Transactions Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">All Activity Logs</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Log Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $activity_logs =  $db->custom_query("SELECT * FROM activity_logs JOIN users ON activity_logs.user_id = users.user_id ORDER BY log_time DESC") ?>
                        <?php if ($activity_logs): ?>
                            <?php foreach ($activity_logs as $log): ?>
                                <tr>
                                    <td><?= e($log['full_name']) ?></td>
                                    <td><?= e($log['action']) ?></td>
                                    <td><?= e(date("F d, Y - h:i A", strtotime($log['log_time']))) ?></td>
                                </tr>
                            <?php endforeach ?>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include_once 'footer.php'; ?>