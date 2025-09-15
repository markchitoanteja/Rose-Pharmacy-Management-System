<?php
// server.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/Database.php';

header("Content-Type: application/json");

$db = new Database();

$action = $_POST['action'] ?? null;

if ($action === 'login_user') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = isset($_POST['remember']) ? (int) $_POST['remember'] : 0;

    // Get user
    $user = $db->select_one("users", "username = ?", [$username]);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Set session
        $_SESSION['user_id'] = $user['user_id'];

        if ($remember) {
            $_SESSION['username'] = $username;
            $_SESSION['password'] = $password;
            $_SESSION['remember_me'] = true;
        } else {
            unset($_SESSION['username']);
            unset($_SESSION['password']);
            unset($_SESSION['remember_me']);
        }

        $_SESSION['notification'] = ["icon" => "success", "text" => "Welcome back, " . $user['full_name'] . "!", "title" => "Login Successful"];
    } else {
        $_SESSION['notification'] = ["icon" => "error", "text" => "Invalid username or password.", "title" => "Login Failed"];
    }

    echo json_encode(true);

    exit;
}

if ($action === 'logout') {
    unset($_SESSION['user_id']);

    $_SESSION['notification'] = ["icon" => "success", "text" => "You have been logged out successfully.", "title" => "Logged Out"];

    echo json_encode(true);

    exit;
}
