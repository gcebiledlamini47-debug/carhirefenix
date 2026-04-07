<?php
// ============================================================
// FENIX CAR HIRE - Database Connection & Setup
// File: db.php - Enhanced with security and class-based approach
// ============================================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load configuration
require_once __DIR__ . '/config/config.php';

// Load core classes
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/SecurityHelper.php';
require_once __DIR__ . '/classes/Validator.php';

// Initialize database connection
$db = Database::getInstance();
$conn = $db->getConnection();

// Backward compatibility - keep old connection variable
// This ensures existing code continues to work

// Legacy constants for backward compatibility
define('BASE_URL', getenv('BASE_URL') ?: '/');
define('SITE_NAME', getenv('SITE_NAME') ?: 'Fenix Car Hire');

// Legacy helper functions for backward compatibility
function clean($conn, $data) {
    if (is_object($conn) && method_exists($conn, 'real_escape_string')) {
        return $conn->real_escape_string(trim($data));
    }
    return Validator::sanitize($data);
}

function formatMoney($amount) {
    return 'E ' . number_format((float)$amount, 2);
}

function generateRef($prefix) {
    return $prefix . '-' . strtoupper(substr(uniqid(), -6));
}

function timeAgo($datetime) {
    try {
        $now  = new DateTime();
        $ago  = new DateTime($datetime);
        $diff = $now->diff($ago);
        if ($diff->d == 0 && $diff->h == 0) return $diff->i . 'm ago';
        if ($diff->d == 0) return $diff->h . 'h ago';
        return $diff->d . 'd ago';
    } catch (Exception $e) {
        return 'unknown time';
    }
}

// New helper functions
function getDatabase() {
    return Database::getInstance();
}

function sanitize($value) {
    return Validator::sanitize($value);
}

function generateCSRFToken() {
    return SecurityHelper::generateCSRFToken();
}

function verifyCSRFToken($token) {
    return SecurityHelper::verifyCSRFToken($token);
}

function hashPassword($password) {
    return SecurityHelper::hashPassword($password);
}

function verifyPassword($password, $hash) {
    return SecurityHelper::verifyPassword($password, $hash);
}
?>
