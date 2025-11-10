<?php
include_once 'header.php';

// Restrict access to logged-in users only
if (!isset($_SESSION['user_id'])) {
    $_SESSION['notification'] = [
        "icon" => "error",
        "text" => "You need to log in to access this page.",
        "title" => "Access Denied"
    ];
    header("Location: dashboard.php");
    exit;
}
?>

<style>
    .notification-item {
        cursor: pointer;
    }

    .notification-item.unread {
        background-color: #f9f9f9;
    }

    .notification-item:hover {
        background-color: #eef2f7;
    }
</style>

<!-- Main Content -->
<div class="content-wrapper" id="content">
    <div class="container-fluid">
        <div class="page-title row mb-4">
            <div class="col-6">
                <h3>All Notifications</h3>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Notifications History</h6>
                <button class="btn btn-sm btn-primary" id="markAllReadBtn">Mark All as Read</button>
            </div>
            <div class="card-body">
                <table class="table table-bordered datatable notifications-table">
                    <thead>
                        <tr>
                            <th style="width: 60%;">Message</th>
                            <th style="width: 20%;">Date</th>
                            <th class="text-center" style="width: 20%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch latest 50 notifications for this user
                        $notifications = $db->select_many(
                            "notifications",
                            "user_id = ?",
                            [$_SESSION['user_id']],
                            "created_at",
                            "DESC",
                            50
                        );

                        if ($notifications):
                            foreach ($notifications as $notif):
                                $isRead = $notif['is_read'] == 1;
                        ?>
                                <tr class="notification-row <?= $isRead ? '' : 'font-weight-bold'; ?>" data-id="<?= $notif['notification_id']; ?>">
                                    <td class="truncate-cell" title="<?= htmlspecialchars($notif['message']); ?>">
                                        <?= htmlspecialchars($notif['message']); ?>
                                    </td>
                                    <td><?= date("F j, Y g:i A", strtotime($notif['created_at'])); ?></td>
                                    <td class="status-cell text-center">
                                        <?= $isRead ? '<span class="text-success">Read</span>' : '<span class="text-danger">Unread</span>'; ?>
                                    </td>
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