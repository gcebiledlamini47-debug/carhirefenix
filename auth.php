<?php
// ============================================================
// FENIX CAR HIRE - Auth Check
// File: auth.php - Enhanced with session timeout handling
// ============================================================
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Check for session timeout
if (SecurityHelper::isSessionExpired()) {
    session_destroy();
    header('Location: login.php?expired=1');
    exit();
}

// Additional security check - ensure admin ID exists
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>
