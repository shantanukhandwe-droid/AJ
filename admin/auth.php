<?php
// Admin authentication check
// Include this at the top of all admin pages

session_start();
require_once __DIR__ . '/../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Logout handler
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$admin_username = $_SESSION['admin_username'];
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
