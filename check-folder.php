<?php
// check-folder.php
$currentFolder = basename(__DIR__);
$correctFolder = 'Rose-Pharmacy-Management-System';

if ($currentFolder !== $correctFolder) {
    header('Location: fix-folder.php');
    exit;
}
