<?php require_once __DIR__ . '/config/Database.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>404 - Page Not Found</title>

    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="dist/plugins/bootstrap/css/bootstrap.min.css?ver=<?= env('APP_VERSION') ?>">

    <!-- Local Nunito Fonts -->
    <style>
        @font-face {
            font-family: 'Nunito';
            src: url('dist/plugins/google-fonts/Nunito/Nunito-Regular.ttf') format('truetype');
            font-weight: 400;
            font-style: normal;
        }

        @font-face {
            font-family: 'Nunito';
            src: url('dist/plugins/google-fonts/Nunito/Nunito-Bold.ttf') format('truetype');
            font-weight: 700;
            font-style: normal;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: #121212;
            color: #eaeaea;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .error-container {
            text-align: center;
            padding: 40px;
        }

        .error-container img {
            max-width: 300px;
            animation: float 3s ease-in-out infinite;
            filter: drop-shadow(0px 4px 8px rgba(0, 0, 0, 0.6));
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-15px);
            }
        }

        h1 {
            font-size: 6rem;
            font-weight: 700;
            color: #ffffff;
            text-shadow: 0px 4px 8px rgba(0, 0, 0, 0.5);
        }

        h2 {
            font-size: 1.5rem;
            color: #b5b5b5;
            margin-bottom: 20px;
        }

        .btn-custom {
            background-color: #00aaff;
            color: #fff;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-custom:hover {
            background-color: #0088cc;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
        }
    </style>
</head>

<body>

    <div class="error-container">
        <!-- Replace with your own animated GIF or illustration -->
        <img src="dist/not-found.webp" alt="404 animation">
        <h1>404</h1>
        <h2>Oops! The page you’re looking for isn’t here.</h2>
        <a href="<?= base_url() ?>" class="btn btn-custom">Go Back Home</a>
    </div>

    <script src="dist/plugins/jquery/jquery.min.js?ver=<?= env('APP_VERSION') ?>"></script>
    <script src="dist/plugins/bootstrap/js/bootstrap.bundle.min.js?ver=<?= env('APP_VERSION') ?>"></script>
</body>

</html>