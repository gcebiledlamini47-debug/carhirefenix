<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($conn)) { require_once 'db.php'; }
$adminName = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' — Fenix Admin' : 'Fenix Admin'; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">
<div class="admin-layout">

    <!-- SIDEBAR: icon-only, expands on hover -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="logo.png.jpg" alt="Fenix" title="Fenix Car Hire">
            <div class="brand-text">
                <span class="brand-name">FENIX</span>
                <span class="brand-sub">ADMIN PANEL</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <?php
            $navItems = array(
                array('dashboard.php',   '&#128202;', 'Dashboard'),
                array('fleet_1.php',     '&#128664;', 'Fleet Management'),
                array('bookings.php',    '&#128203;', 'Bookings'),
                array('invoices.php',    '&#128176;', 'Invoices'),
                array('quotations.php',  '&#128196;', 'Quotations'),
                array('checksheets.php', '&#128269;', 'Check Sheets'),
            );
            $current = basename($_SERVER['PHP_SELF']);
            foreach ($navItems as $item) {
                $href  = $item[0]; $icon = $item[1]; $label = $item[2];
                $cls   = ($current === $href) ? 'active' : '';
                echo '<a href="' . $href . '" class="sidebar-link ' . $cls . '" title="' . $label . '">';
                echo '<span class="nav-icon">' . $icon . '</span>';
                echo '<span class="nav-label">' . $label . '</span>';
                echo '<span class="nav-tooltip">' . $label . '</span>';
                echo '</a>';
            }
            ?>
        </nav>
        <div class="sidebar-footer">
            <a href="index.php" class="sidebar-link" target="_blank" title="View Website">
                <span class="nav-icon">&#127760;</span>
                <span class="nav-label">View Website</span>
                <span class="nav-tooltip">View Website</span>
            </a>
            <a href="logout.php" class="sidebar-link logout" title="Sign Out">
                <span class="nav-icon">&#128682;</span>
                <span class="nav-label">Sign Out</span>
                <span class="nav-tooltip">Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- MAIN -->
    <div class="admin-main">
        <div class="admin-topbar">
            <h2 class="topbar-title"><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h2>
            <div class="topbar-right">
                <?php
                $unread = 0;
                if (isset($conn) && $conn) {
                    $ur = mysqli_query($conn, "SELECT COUNT(*) as c FROM notifications WHERE is_read=0");
                    if ($ur) { $ur2 = mysqli_fetch_assoc($ur); $unread = (int)$ur2['c']; }
                }
                ?>
                <div class="notif-wrap" onclick="toggleNotif()">
                    <span class="notif-btn">&#128276;
                        <?php if ($unread > 0): ?>
                        <span class="notif-badge"><?php echo $unread; ?></span>
                        <?php endif; ?>
                    </span>
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header">Notifications</div>
                        <?php
                        if (isset($conn) && $conn) {
                            $notifs = mysqli_query($conn, "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 8");
                            if ($notifs) {
                                while ($n = mysqli_fetch_assoc($notifs)) {
                                    $rc = $n['is_read'] ? '' : 'unread';
                                    echo '<div class="notif-item ' . $rc . '" onclick="markRead(' . $n['id'] . ')">';
                                    echo '<div class="notif-msg">' . htmlspecialchars($n['message']) . '</div>';
                                    echo '<div class="notif-time">' . timeAgo($n['created_at']) . '</div>';
                                    echo '</div>';
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="admin-user">&#128100; <?php echo htmlspecialchars($adminName); ?></div>
            </div>
        </div>
        <div class="admin-content">