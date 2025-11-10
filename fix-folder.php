<?php
$correctFolder = 'Jian-Pharmacy-Management-System';
$currentFolder = basename(__DIR__);
$parentDir = dirname(__DIR__);

if ($currentFolder === $correctFolder) {
    // Already fixed — redirect to the proper location
    $host = $_SERVER['HTTP_HOST'];
    header("Location: http://{$host}/{$correctFolder}/");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Folder Name - JIAN's Pharmacy</title>

    <!-- Bootstrap and FontAwesome -->
    <link rel="stylesheet" href="dist/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="dist/plugins/fontawesome/css/all.min.css">
</head>

<body class="bg-light d-flex justify-content-center align-items-center" style="height: 100vh;">

    <div class="card shadow-lg" style="max-width: 500px; width: 100%;">
        <div class="card-header bg-danger text-white text-center">
            <h4><i class="fa fa-exclamation-triangle"></i> Folder Name Issue</h4>
        </div>
        <div class="card-body text-center">
            <p class="mb-3">
                Your current folder name is: <br>
                <strong class="text-danger"><?= htmlspecialchars($currentFolder) ?></strong>
            </p>
            <p>Please rename your folder to:</p>
            <h5 class="text-success mb-4">"<?= htmlspecialchars($correctFolder) ?>"</h5>

            <div class="alert alert-warning" style="font-size: 0.9rem;">
                <strong>Instructions:</strong>
                <ol class="mb-0 text-left">
                    <li>Close your web browser.</li>
                    <li>Go to your XAMPP <code>htdocs</code> folder.</li>
                    <li>Right-click on the folder <strong><?= htmlspecialchars($currentFolder) ?></strong> and choose <em>Rename</em>.</li>
                    <li>Rename it to <strong><?= htmlspecialchars($correctFolder) ?></strong>.</li>
                    <li>Then open your browser and visit:<br>
                        <a href="http://localhost/<?= $correctFolder ?>/">
                            http://localhost/<?= $correctFolder ?>/
                        </a>
                    </li>
                </ol>
            </div>
        </div>
        <div class="card-footer text-muted text-center">
            JIAN's Pharmacy Management System
        </div>
    </div>

    <script src="dist/plugins/jquery/jquery.min.js"></script>
    <script src="dist/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>