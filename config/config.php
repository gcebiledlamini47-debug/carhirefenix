<?php
/**
 * config.php - Central configuration for the application
 */

// Database configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'fenix');

// Application constants
define('APP_NAME', 'Fenix Car Hire');
define('APP_VERSION', '2.0.0');
define('APP_TITLE', APP_NAME . ' - Professional Car Rental Service');

// Security constants
define('SESSION_TIMEOUT', 3600); // 1 hour
define('CSRF_TOKEN_LENGTH', 32);
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_ATTEMPT_WINDOW', 300); // 5 minutes

// Pagination
define('ITEMS_PER_PAGE', 20);
define('ADMIN_ITEMS_PER_PAGE', 50);

// File upload
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// Date format
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'M d, Y');
define('DISPLAY_DATETIME_FORMAT', 'M d, Y h:i A');

// Email
define('SUPPORT_EMAIL', getenv('SUPPORT_EMAIL') ?: 'support@fenixcarhire.com');
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'admin@fenixcarhire.com');
define('NOREPLY_EMAIL', getenv('NOREPLY_EMAIL') ?: 'noreply@fenixcarhire.com');

// Booking
define('MIN_RENTAL_DAYS', 1);
define('MAX_RENTAL_DAYS', 365);
define('DAILY_RATE_STANDARD', 49.99);
define('DAILY_RATE_PREMIUM', 99.99);

// Vehicle statuses
define('VEHICLE_STATUS_AVAILABLE', 'available');
define('VEHICLE_STATUS_RENTED', 'rented');
define('VEHICLE_STATUS_MAINTENANCE', 'maintenance');
define('VEHICLE_STATUS_RETIRED', 'retired');

// Booking statuses
define('BOOKING_STATUS_PENDING', 'pending');
define('BOOKING_STATUS_CONFIRMED', 'confirmed');
define('BOOKING_STATUS_ACTIVE', 'active');
define('BOOKING_STATUS_COMPLETED', 'completed');
define('BOOKING_STATUS_CANCELLED', 'cancelled');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Create logs directory if needed
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

// Create uploads directory if needed
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
?>
