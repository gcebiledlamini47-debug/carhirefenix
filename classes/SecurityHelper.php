<?php
/**
 * SecurityHelper.php - Security utilities for CSRF protection, password hashing, etc.
 */

class SecurityHelper {
    const SESSION_TIMEOUT = 3600; // 1 hour
    const CSRF_TOKEN_LENGTH = 32;
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(self::CSRF_TOKEN_LENGTH));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        if (empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
    }
    
    /**
     * Hash password using bcrypt
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Regenerate session ID after login
     */
    public static function regenerateSessionId() {
        if (session_id()) {
            session_regenerate_id(true);
        }
    }
    
    /**
     * Check if session has expired
     */
    public static function isSessionExpired() {
        $timeout = self::SESSION_TIMEOUT;
        
        if (isset($_SESSION['last_activity'])) {
            if ((time() - $_SESSION['last_activity']) > $timeout) {
                return true;
            }
        }
        
        $_SESSION['last_activity'] = time();
        return false;
    }
    
    /**
     * Sanitize output for HTML context
     */
    public static function sanitizeHTML($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate email format
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone format (basic)
     */
    public static function isValidPhone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return strlen($phone) >= 10 && strlen($phone) <= 15;
    }
    
    /**
     * Rate limit check (simple implementation)
     */
    public static function checkRateLimit($key, $maxAttempts = 5, $timeWindow = 300) {
        $key = 'rate_limit_' . md5($key);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'reset_time' => time()];
        }
        
        $now = time();
        $data = $_SESSION[$key];
        
        if ($now - $data['reset_time'] > $timeWindow) {
            $_SESSION[$key] = ['count' => 0, 'reset_time' => $now];
            return true;
        }
        
        if ($data['count'] >= $maxAttempts) {
            return false;
        }
        
        $_SESSION[$key]['count']++;
        return true;
    }
    
    /**
     * Log activity
     */
    public static function logActivity($action, $details = '', $userId = null) {
        $logFile = __DIR__ . '/../logs/activity.log';
        
        // Create logs directory if it doesn't exist
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userId = $userId ?? ($_SESSION['admin_id'] ?? 'guest');
        
        $logMessage = "[$timestamp] User: $userId | IP: $ip | Action: $action | Details: $details\n";
        
        error_log($logMessage, 3, $logFile);
    }
    
    /**
     * Log error
     */
    public static function logError($error, $context = '') {
        $logFile = __DIR__ . '/../logs/error.log';
        
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] Error: $error | Context: $context\n";
        
        error_log($logMessage, 3, $logFile);
    }
}
?>
