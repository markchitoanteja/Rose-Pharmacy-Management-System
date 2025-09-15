<?php
// Load database class
require_once __DIR__ . '/config/Database.php';

// Initialize Database (this ensures DB and tables exist)
$db = new Database();

// Redirect to login page
header("Location: login.php");

exit;
