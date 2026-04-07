<?php
// ============================================================
// FENIX CAR HIRE - Database Connection
// File: db.php (flat structure - all files in same folder)
// ============================================================
define('BASE_URL', getenv('BASE_URL') ?: '/');
define('SITE_NAME', 'Fenix Car Hire');
define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
define('DB_PORT', (int)(getenv('MYSQLPORT') ?: 3306));
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'fenix_db');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if (!$conn) {
    die('<div style="font-family:sans-serif;padding:2rem;background:#fee2e2;color:#991b1b;border-radius:8px;margin:2rem;">
        <strong>Database Connection Failed:</strong> ' . mysqli_connect_error() . '
        <br><small>Check that the MYSQLHOST, MYSQLUSER, MYSQLPASSWORD, and MYSQLDATABASE environment variables are set correctly.</small>
    </div>');
}
mysqli_set_charset($conn, 'utf8mb4');

function clean($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}
function formatMoney($amount) {
    return 'E ' . number_format((float)$amount, 2);
}
function generateRef($prefix) {
    return $prefix . '-' . strtoupper(substr(uniqid(), -6));
}
function timeAgo($datetime) {
    $now  = new DateTime();
    $ago  = new DateTime($datetime);
    $diff = $now->diff($ago);
    if ($diff->d == 0 && $diff->h == 0) return $diff->i . 'm ago';
    if ($diff->d == 0) return $diff->h . 'h ago';
    return $diff->d . 'd ago';
}
?>