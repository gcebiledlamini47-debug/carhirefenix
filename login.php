<?php
// ============================================================
// FENIX CAR HIRE - Admin Login
// File: login.php (flat structure)
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'db.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php'); exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? clean($conn, $_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $res  = mysqli_query($conn, "SELECT * FROM admin_users WHERE username='$username' LIMIT 1");
    $user = mysqli_fetch_assoc($res);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name']      = $user['full_name'];
        $_SESSION['admin_id']        = $user['id'];
        header('Location: dashboard.php'); exit();
    } else {
        $error = 'Invalid username or password.';
    }
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
        <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="admin" autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="password">
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