<?php
// server.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/Database.php';

header("Content-Type: application/json");

$db = new Database();

function log_user_activity($db, $user_id, $action)
{
    $db->insert("activity_logs", [
        "user_id" => $user_id,
        "action" => $action,
        "log_time" => date('Y-m-d H:i:s')
    ]);
}

$action = $_POST['action'] ?? null;

if ($action === 'login_user') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = isset($_POST['remember']) ? (int) $_POST['remember'] : 0;

    // Get user
    $user = $db->select_one("users", "username = ?", [$username]);

    if ($user && password_verify($password, $user['password_hash'])) {
        if ($user['is_active'] == 1) {
            // ✅ Only allow active users
            $_SESSION['user_id'] = $user['user_id'];

            // Get Role
            $role = $db->select_one("roles", "role_id = ?", [$user['role_id']]);

            $_SESSION['role'] = $role['role_name'];

            if ($remember) {
                $_SESSION['username'] = $username;
                $_SESSION['password'] = $password;
                $_SESSION['remember_me'] = true;
            } else {
                unset($_SESSION['username']);
                unset($_SESSION['password']);
                unset($_SESSION['remember_me']);
            }

            $_SESSION['notification'] = [
                "icon" => "success",
                "text" => "Welcome back, " . $user['full_name'] . "!",
                "title" => "Login Successful"
            ];

            // Log activity
            log_user_activity($db, $user['user_id'], 'User logged in');
        } else {
            // 🚫 User is deactivated
            $_SESSION['notification'] = [
                "icon" => "error",
                "text" => "Your account has been deactivated. Please contact the administrator.",
                "title" => "Login Failed"
            ];
        }
    } else {
        $_SESSION['notification'] = [
            "icon" => "error",
            "text" => "Invalid username or password.",
            "title" => "Login Failed"
        ];
    }

    echo json_encode(true);
}

if ($action === 'update_account') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $old_password = trim($_POST['old_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    $user_id = $_SESSION['user_id'];
    $response = ['status' => 'error', 'message' => 'Unknown error'];

    // Check if username already taken
    $existingUser = $db->select_one("users", "username = ? AND user_id != ?", [$username, $user_id]);
    if ($existingUser) {
        $response = ['status' => 'error', 'message' => 'username_exists'];
        echo json_encode($response);
        exit;
    }

    // Fetch current user
    $user = $db->select_one("users", "user_id = ?", [$user_id]);

    // Prepare update data
    $data = [
        "full_name" => $full_name,
        "username" => $username
    ];

    // Password update logic
    if (!empty($new_password)) {
        // Verify old password
        if (!password_verify($old_password, $user['password_hash'])) {
            $response = ['status' => 'error', 'message' => 'invalid_old_password'];
            echo json_encode($response);
            exit;
        }

        // Confirm new passwords match
        if ($new_password !== $confirm_password) {
            $response = ['status' => 'error', 'message' => 'password_mismatch'];
            echo json_encode($response);
            exit;
        }

        $data["password_hash"] = password_hash($new_password, PASSWORD_BCRYPT);
    }

    // Perform update
    $updated = $db->update("users", $data, "user_id = ?", [$user_id]);

    if ($updated) {
        $_SESSION['notification'] = [
            "icon" => "success",
            "text" => "Account updated successfully.",
            "title" => "Success"
        ];
        log_user_activity($db, $user_id, 'Account updated');
        $response = ['status' => 'success'];
    } else {
        $_SESSION['notification'] = [
            "icon" => "error",
            "text" => "No changes were made or an error occurred.",
            "title" => "Error"
        ];
        $response = ['status' => 'error', 'message' => 'update_failed'];
    }

    echo json_encode($response);
}

if ($action === 'add_user') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role_id = (int) ($_POST['role_id'] ?? 0);

    $response = false;

    // Check if username is taken
    $existingUser = $db->select_one("users", "username = ?", [$username]);

    if (!$existingUser) {
        // Prepare data for insertion
        $data = [
            "full_name" => $full_name,
            "username" => $username,
            "password_hash" => password_hash($password, PASSWORD_BCRYPT),
            "role_id" => $role_id,
            "created_at" => date('Y-m-d H:i:s')
        ];

        $inserted = $db->insert("users", $data);

        if ($inserted) {
            $_SESSION['notification'] = [
                "icon" => "success",
                "text" => "User added successfully.",
                "title" => "Success"
            ];
            // Log activity
            $admin_user_id = $_SESSION['user_id'] ?? null;
            if ($admin_user_id) {
                log_user_activity($db, $admin_user_id, 'Added new user: ' . $username);
            }
        } else {
            $_SESSION['notification'] = [
                "icon" => "error",
                "text" => "An error occurred while adding the user.",
                "title" => "Error"
            ];
        }

        $response = true;
    } else {
        $_SESSION['notification'] = [
            "icon" => "error",
            "text" => "Username is already taken.",
            "title" => "Error"
        ];
    }

    echo json_encode($response);
}

if ($action === 'update_user') {
    $user_id = (int) ($_POST['user_id'] ?? 0);
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role_id = (int) ($_POST['role_id'] ?? 0);

    $response = false;

    // Check if username is taken by another user
    $existingUser = $db->select_one("users", "username = ? AND user_id != ?", [$username, $user_id]);

    if (!$existingUser) {
        // Prepare data for update
        $data = [
            "full_name" => $full_name,
            "username" => $username,
            "role_id" => $role_id
        ];

        if (!empty($password)) {
            $data["password_hash"] = password_hash($password, PASSWORD_BCRYPT);
        }

        $updated = $db->update("users", $data, "user_id = ?", [$user_id]);

        if ($updated) {
            $_SESSION['notification'] = [
                "icon" => "success",
                "text" => "User updated successfully.",
                "title" => "Success"
            ];
            // Log activity
            $admin_user_id = $_SESSION['user_id'] ?? null;
            if ($admin_user_id) {
                log_user_activity($db, $admin_user_id, 'Updated user: ' . $username);
            }
        } else {
            $_SESSION['notification'] = [
                "icon" => "error",
                "text" => "No changes were made or an error occurred.",
                "title" => "Error"
            ];
        }

        $response = true;
    }

    echo json_encode($response);
}

if ($action === 'delete_user') {
    $user_id = (int) ($_POST['user_id'] ?? 0);
    $response = false;

    // Prevent deleting own account
    if ($user_id !== ($_SESSION['user_id'] ?? null)) {
        // Soft delete: set is_active = 0 instead of removing the user
        $deleted = $db->update("users", ["is_active" => 0], "user_id = ?", [$user_id]);

        if ($deleted) {
            $_SESSION['notification'] = [
                "icon" => "success",
                "text" => "User deactivated successfully.",
                "title" => "Success"
            ];
            // Log activity
            $admin_user_id = $_SESSION['user_id'] ?? null;
            if ($admin_user_id) {
                log_user_activity($db, $admin_user_id, 'Deactivated user ID: ' . $user_id);
            }
        } else {
            $_SESSION['notification'] = [
                "icon" => "error",
                "text" => "An error occurred while deactivating the user.",
                "title" => "Error"
            ];
        }

        $response = true;
    } else {
        $_SESSION['notification'] = [
            "icon" => "error",
            "text" => "You cannot deactivate your own account.",
            "title" => "Error"
        ];
    }

    echo json_encode($response);
}

if ($action === 'toggle_user') {
    $user_id = (int) ($_POST['user_id'] ?? 0);
    $status = (int) ($_POST['status'] ?? 1); // 1 = activate, 0 = deactivate
    $response = false;

    // Prevent changing own account status
    if ($user_id !== ($_SESSION['user_id'] ?? null)) {
        $updated = $db->update("users", ["is_active" => $status], "user_id = ?", [$user_id]);

        if ($updated) {
            $_SESSION['notification'] = [
                "icon" => "success",
                "text" => $status ? "User reactivated successfully." : "User deactivated successfully.",
                "title" => "Success"
            ];
            // Log activity
            $admin_user_id = $_SESSION['user_id'] ?? null;
            if ($admin_user_id) {
                log_user_activity(
                    $db,
                    $admin_user_id,
                    ($status ? 'Reactivated' : 'Deactivated') . ' user ID: ' . $user_id
                );
            }
            $response = true;
        } else {
            $_SESSION['notification'] = [
                "icon" => "error",
                "text" => "An error occurred while updating the user status.",
                "title" => "Error"
            ];
        }
    } else {
        $_SESSION['notification'] = [
            "icon" => "error",
            "text" => "You cannot change your own account status.",
            "title" => "Error"
        ];
    }

    echo json_encode($response);
}

if ($action === 'add_note') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    $response = false;

    $data = [
        "user_id" => $_SESSION["session_id"],
        "title" => $title,
        "content" => $content,
    ];

    // Check if username is taken
    $existingUser = $db->select_one("users", "username = ?", [$username]);

    if (!$existingUser) {
        // Prepare data for insertion
        $data = [
            "full_name" => $full_name,
            "username" => $username,
            "password_hash" => password_hash($password, PASSWORD_BCRYPT),
            "role_id" => $role_id,
            "created_at" => date('Y-m-d H:i:s')
        ];

        $inserted = $db->insert("users", $data);

        if ($inserted) {
            $_SESSION['notification'] = [
                "icon" => "success",
                "text" => "User added successfully.",
                "title" => "Success"
            ];
            // Log activity
            $admin_user_id = $_SESSION['user_id'] ?? null;
            if ($admin_user_id) {
                log_user_activity($db, $admin_user_id, 'Added new user: ' . $username);
            }
        } else {
            $_SESSION['notification'] = [
                "icon" => "error",
                "text" => "An error occurred while adding the user.",
                "title" => "Error"
            ];
        }

        $response = true;
    } else {
        $_SESSION['notification'] = [
            "icon" => "error",
            "text" => "Username is already taken.",
            "title" => "Error"
        ];
    }

    echo json_encode($response);
}

if ($action === 'logout') {
    $user_id = $_SESSION['user_id'] ?? null;

    unset($_SESSION['user_id']);
    unset($_SESSION['role']);

    $_SESSION['notification'] = ["icon" => "success", "text" => "You have been logged out successfully.", "title" => "Logged Out"];

    // Log activity
    if ($user_id) {
        log_user_activity($db, $user_id, 'User logged out');
    }

    echo json_encode(true);

    exit;
}
