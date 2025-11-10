<?php
$currentFolder = basename(__DIR__);
$correctFolder = 'Jian-Pharmacy-Management-System';

if ($currentFolder !== $correctFolder) {
    header("Location: fix-folder.php");
    exit;
}

// Load database class
require_once __DIR__ . '/config/Database.php';

// Initialize Database
$db = new Database();

// Redirect to login page
header("Location: login.php");
exit;
?>
