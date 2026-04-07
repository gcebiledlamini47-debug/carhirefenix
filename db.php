<?php
// ============================================================
// FENIX CAR HIRE - Database Connection
// File: db.php (flat structure - all files in same folder)
// ============================================================
define('BASE_URL', '/fenix-car-hire/');
define('SITE_NAME', 'Fenix Car Hire');
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'fenix_db');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die('<div style="font-family:sans-serif;padding:2rem;background:#fee2e2;color:#991b1b;border-radius:8px;margin:2rem;">
        <strong>Database Connection Failed:</strong> ' . mysqli_connect_error() . '
        <br><small>Make sure XAMPP MySQL is running and fenix_db exists.</small>
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