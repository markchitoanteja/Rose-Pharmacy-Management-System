<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

    <title>Rose Pharmacy - Online Drugstore &amp; Medicine Delivery Philippines</title>

    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="dist/plugins/bootstrap/css/bootstrap.min.css?ver=<?= env('APP_VERSION') ?>">
    <link rel="stylesheet" href="dist/plugins/fontawesome/css/all.min.css?ver=<?= env('APP_VERSION') ?>">
    <link rel="stylesheet" href="dist/plugins/sweetalert/css/sweetalert.min.css?ver=<?= env('APP_VERSION') ?>">
    <link rel="stylesheet" href="dist/auth/css/style.css?ver=<?= env('APP_VERSION') ?>">
</head>

<body>
    <div class="login-container">
        <!-- Left side with two stacked images -->
        <div class="login-left">
            <div class="image-section top">
                <img src="dist/auth/images/login-bg-2.webp" alt="Pharmacy Image 2">
            </div>
            <div class="image-section bottom">
                <img src="dist/auth/images/login-bg-1.webp" alt="Pharmacy Image 1">
            </div>
        </div>

        <!-- Right side login form -->
        <div class="login-right">
            <div class="card shadow border-0">
                <div class="card-body p-5">
                    <!-- Logo + Title -->
                    <div class="text-center mb-4">
                        <img src="dist/auth/images/logo.png" alt="Rose Pharmacy Logo" class="mb-3" style="width: 80px; height: auto;">
                        <h3 class="card-title mb-2" style="color: #a6192e;">Rose Pharmacy Incorporated</h3>
                        <p class="text-muted small">Secure Login</p>
                    </div>

                    <!-- Login Form -->
                    <form action="javascript:void(0)" id="login_form">
                        <div class="form-group">
                            <label for="login_username">Username</label>
                            <input type="text" class="form-control" id="login_username" value="<?= isset($_SESSION['username']) ? $_SESSION['username'] : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="login_password">Password</label>
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
                    <div class="text-center mt-4">
                        <small class="text-muted">&copy; <?php echo date("Y"); ?> Rose Pharmacy Incorporated</small>
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
                    <h5 class="modal-title" id="firstTimeModalLabel">Welcome to Rose Pharmacy</h5>
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
        const base_url = '<?= base_url() ?>';
        const server_url = base_url + 'server';
        const notification = <?php echo isset($_SESSION['notification']) ? json_encode($_SESSION['notification']) : 'null'; ?>;
    </script>

    <script src="dist/plugins/jquery/jquery.min.js?ver=<?= env('APP_VERSION') ?>"></script>
    <script src="dist/plugins/bootstrap/js/bootstrap.bundle.min.js?ver=<?= env('APP_VERSION') ?>"></script>
    <script src="dist/plugins/sweetalert/js/sweetalert.min.js?ver=<?= env('APP_VERSION') ?>"></script>
    <script src="dist/auth/js/script.js?ver=<?= env('APP_VERSION') ?>"></script>
</body>

</html>

<?php unset($_SESSION['notification']); ?>