<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/check-folder.php';
require_once __DIR__ . '/config/Database.php';

$db = new Database();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>JIAN's Pharmacy - Online Drugstore &amp; Medicine Delivery Philippines</title>

    <link rel="shortcut icon" href="favicon.ico?ver=<?= env('APP_VERSION') ?>" type="image/x-icon">

    <link rel="stylesheet" href="dist/plugins/bootstrap/css/bootstrap.min.css?ver=<?= env('APP_VERSION') ?>">
    <link rel="stylesheet" href="dist/plugins/fontawesome/css/all.min.css?ver=<?= env('APP_VERSION') ?>">
    <link rel="stylesheet" href="dist/plugins/sweetalert/css/sweetalert.min.css?ver=<?= env('APP_VERSION') ?>">
    <link rel="stylesheet" href="dist/auth/css/style.css?ver=<?= env('APP_VERSION') ?>">
</head>

<body>
    <div class="login-container" style="display: flex; height: 100vh; width: 100%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow: hidden;">

        <!-- Left side with blurred image -->
        <div class="login-left" style="flex: 6; position: relative; overflow: hidden;">
            <img src="dist/auth/images/bg.png" alt="Pharmacy Image"
                style="width: 100%; height: 100%; object-fit: cover; filter: blur(1px) brightness(0.8); transform: scale(1.05);">

            <!-- Overlay for better contrast -->
            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.35);"></div>

            <!-- Centered Welcome Text -->
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: white;">
                <h1 style="font-size: 48px; font-weight: 700; letter-spacing: 1px; margin: 0; text-shadow: 0 4px 8px rgba(0,0,0,0.4);">
                    Welcome
                </h1>
                <p style="font-size: 18px; font-weight: 300; margin-top: 10px; color: rgba(255,255,255,0.9);">
                    to JIAN's Pharmacy Incorporated
                </p>
            </div>
        </div>

        <!-- Right side login form -->
        <div class="login-right" style="flex: 4; display: flex; align-items: center; justify-content: center; background-color: #f9f9f9; padding: 40px;">
            <div class="card" style="width: 100%; max-width: 380px; background-color: #fff; border-radius: 12px; box-shadow: 0 6px 20px rgba(0,0,0,0.1); overflow: hidden;">

                <div class="card-body" style="padding: 40px 35px;">

                    <!-- Logo + Title -->
                    <div class="text-center" style="margin-bottom: 30px;">
                        <img src="dist/auth/images/logo.jpg?ver=<?= env('APP_VERSION') ?>" alt="JIAN's Pharmacy Logo"
                            style="width: 85px; height: auto; margin-bottom: 12px;">
                        <h3 style="color: #a6192e; font-weight: 600; margin-bottom: 4px;">JIAN's Pharmacy Inc.</h3>
                        <p style="color: #888; font-size: 14px;">Secure Login Portal</p>
                    </div>

                    <!-- Login Form -->
                    <form action="javascript:void(0)" id="login_form">
                        <div class="form-group">
                            <label for="login_username" class="mb-1">Username</label>
                            <input type="text" class="form-control" id="login_username" value="<?= isset($_SESSION['username']) ? $_SESSION['username'] : '' ?>" required>
                        </div>
                        <div class="form-group mb-1">
                            <label for="login_password" class="mb-1">Password</label>
                            <div class="password-wrapper">
                                <input type="password" class="form-control" id="login_password" value="<?= isset($_SESSION['password']) ? $_SESSION['password'] : '' ?>" required>
                                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                            </div>
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="login_remember" <?= isset($_SESSION['remember_me']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="login_remember">Remember Me</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block" id="login_submit">Login</button>
                    </form>

                    <!-- Footer -->
                    <div class="text-center" style="margin-top: 30px;">
                        <small style="color: #aaa;">&copy; <?php echo date('Y'); ?> JIAN's Pharmacy Incorporated</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- First Time Instructions Modal -->
    <div class="modal fade" id="firstTimeModal" tabindex="-1" role="dialog" aria-labelledby="firstTimeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header text-white" style="background-color: #a6192e;">
                    <h5 class="modal-title" id="firstTimeModalLabel">Welcome to JIAN's Pharmacy</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Here’s how to log in for the first time:</p>
                    <ul>
                        <li>Enter your <strong>username</strong> and <strong>password</strong> below.</li>
                        <li>You can change your password after logging in.</li>
                    </ul>
                    <hr>
                    <p class="mb-1"><strong>Default Admin Credentials:</strong></p>
                    <p class="mb-0">Username: <code>admin</code></p>
                    <p>Password: <code>admin123</code></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Got it!</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const base_url = <?= json_encode(base_url()) ?>;
        const server_url = <?= json_encode(base_url() . 'server') ?>;
        const app_validity = <?= json_encode(env('APP_VALIDITY')) ?>;
        const app_debug = <?= json_encode(env('APP_DEBUG')) ?>;
        const notification = <?= isset($_SESSION['notification']) ? json_encode($_SESSION['notification']) : 'null'; ?>;
    </script>

    <script src="dist/plugins/jquery/jquery.min.js?ver=<?= env('APP_VERSION') ?>"></script>
    <script src="dist/plugins/bootstrap/js/bootstrap.bundle.min.js?ver=<?= env('APP_VERSION') ?>"></script>
    <script src="dist/plugins/sweetalert/js/sweetalert.min.js?ver=<?= env('APP_VERSION') ?>"></script>
    <script src="dist/auth/js/script.js?ver=<?= env('APP_VERSION') ?>"></script>
</body>

</html>

<?php unset($_SESSION['notification']); ?>