<?php include 'account_logic.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
   
    <link rel="stylesheet" href="Css/verifystyles.css">
    <link rel="stylesheet" href="Css/footerstyles.css">
    <link rel="stylesheet" href="Css/responsive.css">

    <link rel="icon" href="Images/LocalLoopLogo.png">
    <title>My Account</title>
    
</head>
<body>

    <header id="home">

        <img src="Images/LocalLoopWhite.png" alt="LOGO" class="logoimg">

        <nav class="navbar">

            <?php if ($role === 'admin'): ?>
                <a href="admin_page.php">← Back</a>
            <?php else: ?>
                <a href="user_page.php">← Back</a>
            <?php endif; ?>

        </nav>

        <div class="Login-Container">

            <?php if ($isVerified): ?>
                <a href="account.php"><img src="Images/pfpIconverified.png" alt="Verified" class="Loginpfp"></a>
            <?php else: ?>
                <a href="account.php"><img src="Images/pfpIcon.png" alt="Profile" class="Loginpfp"></a>
            <?php endif; ?>
        </div>

    </header>

    <div class="account-box">

        <?php if ($message): ?>
            <div class="message <?= $type ?>"><?= $message ?></div>
        <?php endif; ?>

        <h2>Details</h2>
        <form method="POST">
            
            <label>Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($name) ?>">

            <label>Email:</label>
            <input type="text" value="<?= htmlspecialchars($email) ?>" disabled>
            <small>Email cannot be changed.</small>

            <label>Role:</label>
            <input type="text" value="<?= htmlspecialchars($role) ?>" disabled>
            <small>Role is set by an admin.</small>

            <button type="submit" name="save_details" class="btn-save">Save Details</button>
        </form>

        <hr>

        <h2>Verify</h2>

        <?php if ($isVerified): ?>
            <p class="message success">✅ Your account is verified!</p>
        <?php endif; ?>

        <form method="POST">
            <label>Area:</label>
            <input type="text" name="area" value="<?= htmlspecialchars($area) ?>">

            <label>ID Number:</label>
            <input type="text" name="id_number" value="<?= htmlspecialchars($idNum) ?>">

            <label>Phone:</label>
            <input type="tel" name="phone" value="<?= htmlspecialchars($phone) ?>">

            <button type="submit" name="save_verify" class="btn-verify">Verify</button>
        </form>

        <hr>

        <a href="logout.php"><button class="btn-logout">Logout</button></a>

    </div>

    <footer class="site-footer">

        <div class="footer-inner">
            
            <img src="Images/LocalLoopWhite.png" alt="LocalLoop" class="footer-logo">
            <p class="footer-tagline">Connecting communities, one listing at a time.</p>
            <p class="footer-copy">© <?= date('Y') ?> LocalLoop. All rights reserved.</p>
        </div>

    </footer>

</body>
</html>
