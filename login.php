<?php
// ============================================================
// FENIX CAR HIRE - Admin Login
// File: login.php - Enhanced with security and CSRF protection
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'db.php';

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // Check session timeout
    if (SecurityHelper::isSessionExpired()) {
        session_destroy();
        header('Location: login.php?expired=1');
        exit();
    }
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!SecurityHelper::verifyCSRFToken($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } else {
        // Check rate limiting
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!SecurityHelper::checkRateLimit('login_' . $ip, MAX_LOGIN_ATTEMPTS, LOGIN_ATTEMPT_WINDOW)) {
            $error = 'Too many login attempts. Please try again later.';
            SecurityHelper::logActivity('LOGIN_FAILED_RATE_LIMIT', 'IP: ' . $ip);
        } else {
            $username = isset($_POST['username']) ? sanitize($_POST['username']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';

            // Validate input
            $validator = new Validator();
            $validator->required('username', $username);
            $validator->required('password', $password);

            if (!$validator->passes()) {
                $error = 'Please enter both username and password.';
            } else {
                try {
                    $db = Database::getInstance();
                    $user = $db->queryOne(
                        "SELECT id, username, full_name, password FROM admin_users WHERE username = ? LIMIT 1",
                        [$username],
                        's'
                    );

                    if ($user && SecurityHelper::verifyPassword($password, $user['password'])) {
                        // Regenerate session ID for security
                        SecurityHelper::regenerateSessionId();

                        // Set session variables
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_name'] = $user['full_name'];
                        $_SESSION['admin_id'] = $user['id'];
                        $_SESSION['login_time'] = time();

                        // Log successful login
                        SecurityHelper::logActivity('LOGIN_SUCCESS', 'Admin logged in', $user['id']);

                        header('Location: dashboard.php');
                        exit();
                    } else {
                        $error = 'Invalid username or password.';
                        SecurityHelper::logActivity('LOGIN_FAILED', 'Invalid credentials for: ' . $username);
                    }
                } catch (Exception $e) {
                    $error = 'An error occurred. Please try again.';
                    SecurityHelper::logError('Login error', $e->getMessage());
                }
            }
        }
    }
}

// Check if session expired
if (isset($_GET['expired']) && $_GET['expired'] == 1) {
    $error = 'Your session has expired. Please login again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Admin Login — Fenix Car Hire</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">
<div class="login-wrap">
    <div class="login-card">
        <div class="login-logo">
            <img src="logo.png.jpg" alt="Fenix" style="height:56px;">
        </div>
        <h2>Admin Portal</h2>
        <p class="login-sub">Fenix Car Hire — Secure Access</p>
        <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="admin" autofocus autocomplete="username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="password" autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:16px">Sign In</button>
        </form>
        <div style="text-align:center;margin-top:16px">
            <a href="index.php" style="color:#60a5fa;font-size:13px">Back to Website</a>
        </div>
    </div>
</div>
</body>
</html>
