<?php
// check-folder.php
$currentFolder = basename(__DIR__);
$correctFolder = 'Jian-Pharmacy-Management-System';

if ($currentFolder !== $correctFolder) {
    header('Location: fix-folder.php');
    exit;
}
