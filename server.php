<?php
// server.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/Database.php';

header("Content-Type: application/json");

$db = new Database();

$action = $_POST['action'] ?? null;

function log_user_activity($db, $user_id, $action)
{
    $db->insert("activity_logs", [
        "user_id" => $user_id,
        "action" => $action,
        "log_time" => date('Y-m-d H:i:s')
    ]);
}

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

    $data = [
        "user_id" => $_SESSION["user_id"],
        "title" => $title,
        "content" => $content,
        "created_at" => date('Y-m-d H:i:s')
    ];

    $inserted = $db->insert("notes", $data);

    $_SESSION['notification'] = [
        "icon" => "success",
        "text" => "User added successfully.",
        "title" => "Success"
    ];

    $admin_user_id = $_SESSION['user_id'] ?? null;

    if ($admin_user_id) {
        log_user_activity($db, $admin_user_id, "Added a new note.");
    }

    echo json_encode(true);
}

if ($action === 'view_note') {
    $note_id = (int) ($_POST['note_id'] ?? 0);
    $response = ['status' => 'error', 'message' => 'Invalid request'];

    if ($note_id > 0) {
        $note = $db->custom_query("
            SELECT n.*, u.full_name AS owner
            FROM notes n
            JOIN users u ON n.user_id = u.user_id
            WHERE n.note_id = ?
        ", [$note_id], true);

        if ($note) {
            $response = [
                'status' => 'success',
                'data' => [
                    'note_id' => $note['note_id'],
                    'title' => $note['title'],
                    'content' => $note['content'],
                    'owner' => $note['owner'],
                    'created_at' => $note['created_at'],
                    'updated_at' => $note['updated_at'] ?? null
                ]
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'Note not found'];
        }
    }

    echo json_encode($response);
    exit;
}

if ($action === 'update_note') {
    $note_id = (int) ($_POST['note_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $user_id = $_SESSION['user_id'] ?? null;
    $response = false;

    if ($note_id && $user_id) {
        $updated = $db->update("notes", [
            "title" => $title,
            "content" => $content,
            "updated_at" => date('Y-m-d H:i:s')
        ], "note_id = ?", [$note_id]);

        if ($updated) {
            $_SESSION['notification'] = [
                "icon" => "success",
                "text" => "Note updated successfully.",
                "title" => "Success"
            ];

            // Log activity
            log_user_activity($db, $user_id, "Updated note ID: {$note_id}");

            $response = true;
        } else {
            $_SESSION['notification'] = [
                "icon" => "error",
                "text" => "An error occurred while updating the note.",
                "title" => "Error"
            ];
        }
    }

    echo json_encode($response);
}

if ($action === 'delete_note') {
    $note_id = (int) ($_POST['note_id'] ?? 0);
    $user_id = $_SESSION['user_id'] ?? null;
    $response = false;

    if ($note_id && $user_id) {
        $deleted = $db->delete("notes", "note_id = ?", [$note_id]);

        if ($deleted) {
            $_SESSION['notification'] = [
                "icon" => "success",
                "text" => "Note deleted successfully.",
                "title" => "Success"
            ];

            // Log activity
            log_user_activity($db, $user_id, "Deleted note ID: {$note_id}");
            $response = true;
        } else {
            $_SESSION['notification'] = [
                "icon" => "error",
                "text" => "An error occurred while deleting the note.",
                "title" => "Error"
            ];
        }
    }

    echo json_encode($response);
}

if ($action === 'add_supplier') {
    $name = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '') {
        echo json_encode(['success' => false, 'message' => 'Supplier name is required.']);
        exit;
    }

    $data = [
        "name" => $name,
        "contact_number" => $contact,
        "address" => $address
    ];

    $inserted = $db->insert("suppliers", $data);

    if ($inserted) {
        $_SESSION['notification'] = [
            "icon" => "success",
            "text" => "Supplier added successfully.",
            "title" => "Success"
        ];

        // Log activity
        $admin_user_id = $_SESSION['user_id'] ?? null;
        if ($admin_user_id) {
            log_user_activity($db, $admin_user_id, "Added a new supplier: $name");
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add supplier.']);
    }
}

if ($action === 'get_supplier') {
    $supplier_id = trim($_POST['supplier_id'] ?? '');

    $supplier = $db->select_one("suppliers", "supplier_id = ?", [$supplier_id]);

    if ($supplier) {
        echo json_encode(['success' => true, 'data' => $supplier]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Supplier not found.']);
    }
}

if ($action === 'update_supplier') {
    $supplier_id = trim($_POST['supplier_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($supplier_id === '' || $name === '') {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
        exit;
    }

    $data = [
        'name' => $name,
        'contact_number' => $contact,
        'address' => $address
    ];

    $updated = $db->update("suppliers", $data, "supplier_id = ?", [$supplier_id]);

    if ($updated) {
        $_SESSION['notification'] = [
            "icon" => "success",
            "text" => "Supplier updated successfully.",
            "title" => "Success"
        ];

        $admin_user_id = $_SESSION['user_id'] ?? null;
        if ($admin_user_id) {
            log_user_activity($db, $admin_user_id, "Updated supplier ID $supplier_id: $name");
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update supplier.']);
    }
}

if ($action === 'delete_supplier') {
    $supplier_id = trim($_POST['supplier_id'] ?? '');

    if ($supplier_id === '') {
        echo json_encode(['success' => false, 'message' => 'Invalid supplier ID.']);
        exit;
    }

    $deleted = $db->delete("suppliers", "supplier_id = ?", [$supplier_id]);

    if ($deleted) {
        $_SESSION['notification'] = [
            "icon" => "success",
            "text" => "Supplier deleted successfully.",
            "title" => "Success"
        ];

        $admin_user_id = $_SESSION['user_id'] ?? null;
        if ($admin_user_id) {
            log_user_activity($db, $admin_user_id, "Deleted supplier ID $supplier_id");
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete supplier.']);
    }
}

if ($action === 'get_medicine') {
    $medicine_id = trim($_POST['medicine_id'] ?? '');
    $medicine = $db->select_one("medicines", "medicine_id = ?", [$medicine_id]);

    if ($medicine) {
        echo json_encode(['success' => true, 'data' => $medicine]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Medicine not found.']);
    }
}

if ($action === 'add_medicine') {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $unit_price = trim($_POST['unit_price'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $expiry_date = trim($_POST['expiry_date'] ?? '');
    $supplier_id = trim($_POST['supplier_id'] ?? '');

    // Basic validation
    if ($name === '' || $category === '' || $description === '' || $unit_price === '' || $quantity === '' || $expiry_date === '' || $supplier_id === '') {
        echo json_encode(['success' => false, 'message' => 'Please fill out all required fields.']);
        exit;
    }

    $data = [
        'name' => $name,
        'category' => $category,
        'description' => $description,
        'unit_price' => $unit_price,
        'quantity' => $quantity,
        'expiry_date' => $expiry_date,
        'supplier_id' => $supplier_id,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $inserted = $db->insert("medicines", $data);

    if ($inserted) {
        $_SESSION['notification'] = [
            "icon" => "success",
            "text" => "Medicine added successfully.",
            "title" => "Success"
        ];

        $admin_user_id = $_SESSION['user_id'] ?? null;
        if ($admin_user_id) {
            log_user_activity($db, $admin_user_id, "Added a new medicine: $name");
        }

        // Check if stock is low
        $low_stock_threshold = max(5, ceil($quantity * 0.1)); // 10% or at least 5
        if ($quantity <= $low_stock_threshold) {
            $admins = $db->select_many("users", "role_id = ?", ['1']); // Admins
            foreach ($admins as $admin) {
                $db->insert("notifications", [
                    "user_id" => $admin['user_id'],
                    "message" => "Low stock alert: '{$name}' has only {$quantity} units in stock.",
                    "is_read" => 0,
                    "created_at" => date('Y-m-d H:i:s')
                ]);
            }
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add medicine.']);
    }
}

if ($action === 'update_medicine') {
    $medicine_id = trim($_POST['medicine_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $unit_price = trim($_POST['unit_price'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $expiry_date = trim($_POST['expiry_date'] ?? '');
    $supplier_id = trim($_POST['supplier_id'] ?? '');

    if ($medicine_id === '' || $name === '' || $category === '' || $description === '' || $unit_price === '' || $quantity === '' || $expiry_date === '' || $supplier_id === '') {
        echo json_encode(['success' => false, 'message' => 'Please fill out all required fields.']);
        exit;
    }

    $data = [
        'name' => $name,
        'category' => $category,
        'description' => $description,
        'unit_price' => $unit_price,
        'quantity' => $quantity,
        'expiry_date' => $expiry_date,
        'supplier_id' => $supplier_id
    ];

    $updated = $db->update("medicines", $data, "medicine_id = ?", [$medicine_id]);

    if ($updated) {
        $_SESSION['notification'] = [
            "icon" => "success",
            "text" => "Medicine updated successfully.",
            "title" => "Success"
        ];

        $admin_user_id = $_SESSION['user_id'] ?? null;
        if ($admin_user_id) {
            log_user_activity($db, $admin_user_id, "Updated medicine ID $medicine_id: $name");
        }

        // Check if stock is low
        $low_stock_threshold = max(5, ceil($quantity * 0.1)); // 10% or at least 5
        if ($quantity <= $low_stock_threshold) {
            $admins = $db->select_many("users", "role_id = ?", ['1']); // Admins
            foreach ($admins as $admin) {
                // Check if notification already exists for this medicine and admin to avoid duplicates
                $existing = $db->select_one(
                    "notifications",
                    "user_id = ? AND message LIKE ?",
                    [$admin['user_id'], "%Low stock alert: '{$name}'%"]
                );
                if (!$existing) {
                    $db->insert("notifications", [
                        "user_id" => $admin['user_id'],
                        "message" => "Low stock alert: '{$name}' has only {$quantity} units in stock.",
                        "is_read" => 0,
                        "created_at" => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update medicine.']);
    }
}

if ($action === 'delete_medicine') {
    $medicine_id = trim($_POST['medicine_id'] ?? '');

    if ($medicine_id === '') {
        echo json_encode(['success' => false, 'message' => 'Medicine ID is required.']);
        exit;
    }

    $medicine = $db->select_one("medicines", "medicine_id = ?", [$medicine_id]);
    $name = $medicine['name'] ?? '';

    $deleted = $db->delete("medicines", "medicine_id = ?", [$medicine_id]);

    if ($deleted) {
        $_SESSION['notification'] = [
            "icon" => "success",
            "text" => "Medicine deleted successfully.",
            "title" => "Success"
        ];

        $admin_user_id = $_SESSION['user_id'] ?? null;
        if ($admin_user_id && $name !== '') {
            log_user_activity($db, $admin_user_id, "Deleted medicine ID $medicine_id: $name");
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete medicine.']);
    }
}

if ($action === 'get_notifications') {
    $user_id = $_SESSION['user_id'];
    $notifications = $db->custom_query("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ", [$user_id]);

    echo json_encode(['success' => true, 'data' => $notifications]);
}

if ($action === 'mark_notification_read') {
    $notification_id = $_POST['notification_id'];
    $db->update('notifications', ['is_read' => 1], 'notification_id = ?', [$notification_id]);
    echo json_encode(['success' => true]);
}

if ($action === 'check_expiry_date') {
    $today = date('Y-m-d');
    $threshold = date('Y-m-d', strtotime('+30 days'));

    $expiring_meds = $db->custom_query("
        SELECT medicine_id, name, expiry_date
        FROM medicines
        WHERE expiry_date <= ?
        ORDER BY expiry_date ASC
    ", [$threshold]);

    if (empty($expiring_meds)) {
        echo json_encode(['success' => true, 'message' => 'No medicines nearing expiry.']);
        exit;
    }

    $admins = $db->select_many("users", "role_id = ?", ['1']); // Admins

    foreach ($expiring_meds as $med) {
        $days_left = (strtotime($med['expiry_date']) - strtotime($today)) / (60 * 60 * 24);
        $days_left_text = $days_left > 0 ? "{$days_left} days left" : "Expired";

        $message = "Medicine '{$med['name']}' is nearing expiry ({$days_left_text}).";

        foreach ($admins as $admin) {
            // ✅ Check if same notification already exists for this user
            $exists = $db->custom_query("
                SELECT notification_id 
                FROM notifications 
                WHERE user_id = ? AND message = ? 
                LIMIT 1
            ", [$admin['user_id'], $message]);

            if (empty($exists)) {
                // Insert only if it doesn't exist
                $notif_data = [
                    "user_id" => $admin['user_id'],
                    "message" => $message,
                    "is_read" => 0,
                    "created_at" => date('Y-m-d H:i:s')
                ];
                $db->insert("notifications", $notif_data);
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => count($expiring_meds) . " medicines are nearing expiry.",
        'data' => $expiring_meds
    ]);
}

if ($action === 'check_stock_level') {
    $low_stock_threshold = 10;

    $low_stock_meds = $db->custom_query("
        SELECT medicine_id, name, quantity
        FROM medicines
        WHERE quantity <= ?
        ORDER BY quantity ASC
    ", [$low_stock_threshold]);

    if (empty($low_stock_meds)) {
        echo json_encode(['success' => true, 'message' => 'All stock levels are sufficient.']);
        exit;
    }

    $admins = $db->select_many("users", "role_id = ?", ['1']); // Admins

    foreach ($low_stock_meds as $med) {
        $message = "Low stock alert: '{$med['name']}' has only {$med['quantity']} left in inventory.";

        foreach ($admins as $admin) {
            // ✅ Check if same notification already exists for this user
            $exists = $db->custom_query("
                SELECT notification_id 
                FROM notifications 
                WHERE user_id = ? AND message = ? 
                LIMIT 1
            ", [$admin['user_id'], $message]);

            if (empty($exists)) {
                $notif_data = [
                    "user_id" => $admin['user_id'],
                    "message" => $message,
                    "is_read" => 0,
                    "created_at" => date('Y-m-d H:i:s')
                ];
                $db->insert("notifications", $notif_data);
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => count($low_stock_meds) . " medicines are low in stock.",
        'data' => $low_stock_meds
    ]);
}

if ($action === 'mark_all_notifications_read') {
    $user_id = $_SESSION['user_id'];
    $db->custom_query("UPDATE notifications SET is_read = 1 WHERE user_id = ?", [$user_id]);
    echo json_encode(['success' => true]);
}

if ($action === 'checkout_cart') {
    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'User not authenticated.']);
        exit;
    }

    $cart = json_decode($_POST['cart'] ?? '[]', true);
    if (empty($cart) || !is_array($cart)) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty.']);
        exit;
    }

    $total_amount = 0;
    foreach ($cart as $item) {
        if (!isset($item['id'], $item['qty'], $item['price'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid cart data.']);
            exit;
        }
        $total_amount += $item['qty'] * $item['price'];
    }

    $receipt_number = uniqid("RCPT-");
    $sale_data = [
        'user_id'        => $user_id,
        'total_amount'   => number_format($total_amount, 2, '.', ''),
        'receipt_number' => $receipt_number
    ];

    $sale_inserted = $db->insert("sales", $sale_data);
    if (!$sale_inserted) {
        echo json_encode(['success' => false, 'message' => 'Failed to create sale record.']);
        exit;
    }

    $sale_row = $db->custom_query(
        "SELECT sale_id FROM sales WHERE receipt_number = ?",
        [$receipt_number],
        true
    );
    if (!$sale_row) {
        echo json_encode(['success' => false, 'message' => 'Could not fetch sale record.']);
        exit;
    }
    $sale_id = $sale_row['sale_id'];

    foreach ($cart as $item) {
        $medicine_id = $item['id'];
        $qty         = (int)$item['qty'];
        $price       = number_format($item['price'], 2, '.', '');

        $medicine = $db->select_one('medicines', 'medicine_id = ?', [$medicine_id]);
        if (!$medicine || $medicine['quantity'] < $qty) {
            echo json_encode([
                'success' => false,
                'message' => "Insufficient stock for " . htmlspecialchars($medicine['name'] ?? 'unknown')
            ]);
            exit;
        }

        $item_data = [
            'sale_id'     => $sale_id,
            'medicine_id' => $medicine_id,
            'quantity'    => $qty,
            'price'       => $price,
            'discount'    => "0.00"
        ];

        if (!$db->insert('sale_items', $item_data)) {
            echo json_encode(['success' => false, 'message' => "Failed to save sale item for " . htmlspecialchars($medicine['name'])]);
            exit;
        }

        $new_quantity = $medicine['quantity'] - $qty;
        if (!$db->update('medicines', ['quantity' => $new_quantity], 'medicine_id = ?', [$medicine_id])) {
            echo json_encode(['success' => false, 'message' => "Failed to update stock for " . htmlspecialchars($medicine['name'])]);
            exit;
        }
    }

    $_SESSION['notification'] = [
        "icon" => "success",
        "text" => "Sale processed successfully. Receipt No: $receipt_number",
        "title" => "Success"
    ];

    log_user_activity($db, $user_id, "Processed sale (Receipt: $receipt_number, Amount: ₱$total_amount)");

    echo json_encode([
        'success' => true,
        'receipt_number' => $receipt_number
    ]);
}

if ($action === 'get_receipt') {
    $receipt_number = trim($_POST['receipt_number'] ?? '');

    $items = $db->custom_query("
        SELECT m.name AS medicine_name, si.quantity, si.price
        FROM sale_items si
        JOIN medicines m ON si.medicine_id = m.medicine_id
        JOIN sales s ON si.sale_id = s.sale_id
        WHERE s.receipt_number = ?
    ", [$receipt_number]);

    if ($items) {
        $grand_total = 0;

        // Receipt HTML with design
        $html = '<div style="font-family: monospace; font-size:12px; color:#333;">';

        // Logo + Header
        $html .= '<div class="text-center mb-2">';
        $html .= '<img src="' . base_url() . 'dist/auth/images/logo.png" alt="Logo" style="border-radius:50%; max-width:80px;margin-bottom:5px;"><br>';
        $html .= '<strong>JIAN\'s Pharmacy Inc.</strong><br>';
        $html .= 'Dolores, Eastern Samar<br>';
        $html .= 'Tel: 0912-345-6789';
        $html .= '</div>';

        $html .= '<hr style="border-top:1px dashed #000;">';

        // Receipt info
        $html .= '<div style="margin-bottom:5px;">';
        $html .= '<strong>Receipt #:</strong> ' . htmlspecialchars($receipt_number) . '<br>';
        $html .= '<strong>Date:</strong> ' . date("F j, Y g:i A") . '</div>';

        $html .= '<hr style="border-top:1px dashed #000;">';

        // Items table
        $html .= '<table style="width:100%; border-collapse:collapse;">';
        $html .= '<thead><tr><th style="text-align:left;">Item</th><th style="text-align:center;">Qty</th><th style="text-align:right;">Price</th><th style="text-align:right;">Total</th></tr></thead>';
        $html .= '<tbody>';

        foreach ($items as $item) {
            $total = $item['quantity'] * $item['price'];
            $grand_total += $total;

            $html .= '<tr>';
            $html .= '<td style="text-align:left;">' . htmlspecialchars($item['medicine_name']) . '</td>';
            $html .= '<td style="text-align:center;">' . $item['quantity'] . '</td>';
            $html .= '<td style="text-align:right;">₱' . number_format($item['price'], 2) . '</td>';
            $html .= '<td style="text-align:right;">₱' . number_format($total, 2) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';

        // Grand Total
        $html .= '<tfoot>';
        $html .= '<tr><th colspan="3" style="text-align:right; font-size:14px;">Grand Total:</th><th style="text-align:right; font-size:14px;">₱' . number_format($grand_total, 2) . '</th></tr>';
        $html .= '</tfoot>';
        $html .= '</table>';

        $html .= '<hr style="border-top:1px dashed #000;">';

        // Footer / Thank you + optional barcode
        $html .= '<div class="text-center mt-2">';
        $html .= 'Thank you for your purchase!<br>';
        $html .= '<small>Visit us again!</small><br>';
        $html .= '</div>';

        $html .= '</div>'; // end main div

        echo json_encode(['success' => true, 'html' => $html]);
    } else {
        echo json_encode(['success' => false]);
    }
}

if ($action === 'filter_sales') {
    $start = trim($_POST['start_date'] ?? '');
    $end = trim($_POST['end_date'] ?? '');

    $params = [];
    $query = "
        SELECT s.sale_id, s.receipt_number, s.sale_date, 
               u.full_name AS cashier,
               SUM(si.quantity * si.price) AS total
        FROM sales s
        JOIN sale_items si ON s.sale_id = si.sale_id
        JOIN users u ON s.user_id = u.user_id
    ";

    if ($start && $end) {
        $query .= " WHERE DATE(s.sale_date) BETWEEN ? AND ? ";
        $params = [$start, $end];
    }

    $query .= " GROUP BY s.sale_id ORDER BY s.sale_date DESC";

    $sales = $db->custom_query($query, $params);

    $result = [];
    if ($sales) {
        foreach ($sales as $sale) {
            $result[] = [
                'receipt_number' => htmlspecialchars($sale['receipt_number']),
                'cashier' => htmlspecialchars($sale['cashier']),
                'sale_date' => date("F j, Y g:i A", strtotime($sale['sale_date'])),
                'total' => '₱' . number_format($sale['total'], 2)
            ];
        }
    }

    echo json_encode(['success' => true, 'data' => $result]);
    exit;
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
