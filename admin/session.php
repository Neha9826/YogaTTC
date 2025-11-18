<?php
include 'db.php';

session_start();

// get current script name
$current_page = basename($_SERVER['PHP_SELF']);

// pages that do NOT require login
$public_pages = ['login.php', 'register.php', 'forgot_password.php'];

if (!isset($_SESSION['emp_id']) && !in_array($current_page, $public_pages)) {
    header("Location: login.php");
    exit;
}
?>
