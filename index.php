<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['email'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? 'admin_page.php' : 'user_page.php'));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Css/styles.css">
    <link rel="stylesheet" href="Css/index.css">
    <link rel="stylesheet" href="Css/footerstyles.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Css/responsive.css">

    <link rel="icon" href="Images/LocalLoopLogo.png">
    <title>LocalLoop</title>

</head>
<body>

<header>

    <img src="Images/LocalLoopWhite.png" alt="LocalLoop" class="logoimg">

    <div class="Login-Container">
        <a href="register.php" class="header-login-btn">Login / Register</a>
    </div>

</header>

<section class="hero" id="about">

    <div class="hero-text">

        <p class="hero-label">Community Marketplace</p>
        <h1 class="hero-title">Buy & Sell in<br> <em>Your Neighbourhood</em> </h1>
        <p class="hero-desc">LocalLoop connects people in lower-income communities to trade goods locally — no middlemen, no barriers. Just real people helping each other thrive.</p>
        <a href="register.php" class="btn-hero">Get Started →</a>

    </div>

    <div class="hero-badge">

        <img src="Images/LocalLoopLogo.png" alt="LocalLoop">

    </div>

</section>

<footer class="site-footer">

    <div class="footer-inner">
        
        <img src="Images/LocalLoopWhite.png" alt="LocalLoop" class="footer-logo">

        <p class="footer-tagline">Connecting communities, one listing at a time.</p>
        <p class="footer-copy">© <?= date('Y') ?> LocalLoop. All rights reserved.</p>
        
    </div>

</footer>

<script src="JS/script.js"></script>

</body>
</html>