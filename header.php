<?php
// ============================================================
// FENIX CAR HIRE - Public Header
// File: header.php (flat structure)
// ============================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' — ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="logo.png.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<nav class="navbar" id="navbar">
    <div class="nav-container">
        <a href="index.php" class="nav-brand">
            <img src="logo.png.jpg" alt="Fenix Logo" class="nav-logo">
            <div class="nav-brand-text">
                <span class="brand-name">FENIX</span>
                <span class="brand-sub">CAR HIRE</span>
            </div>
        </a>
        <button class="nav-toggle" id="navToggle">&#9776;</button>
        <ul class="nav-links" id="navLinks">
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php#about">About</a></li>
            <li><a href="fleet.php">Fleet</a></li>
            <li><a href="index.php#services">Services</a></li>
            <li><a href="terms.php">Terms</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="booking.php" class="btn-nav-book">Book Now</a></li>
        </ul>
    </div>
</nav>