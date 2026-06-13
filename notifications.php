<?php
include 'account_logic.php';
include 'listings_logic.php';

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$email  = $_SESSION['email'];
$unread = countUnread($conn, $email);

// gets all notifications for this user
$notifs = $conn->prepare("SELECT * FROM notifications WHERE to_email=? ORDER BY created_at DESC");
$notifs->bind_param("s", $email);
$notifs->execute();
$result = $notifs->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
$notifs->close();

// gets pending interests on the user's own listings
$pendingInterests = [];
$pi = $conn->prepare("
    SELECT i.id, i.buyer_email, i.listing_id, l.title 
    FROM interests i 
    JOIN listings l ON i.listing_id = l.id 
    WHERE l.user_email=? AND i.status='pending'
");
$pi->bind_param("s", $email);
$pi->execute();
$piResult = $pi->get_result();
$pendingInterests = $piResult->fetch_all(MYSQLI_ASSOC);
$pi->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Css/newStyles.css">
    <link rel="stylesheet" href="Css/listings.css">
    <link rel="stylesheet" href="Css/responsive.css">

    <link rel="icon" href="Images/LocalLoopLogo.png">
    <title>LocalLoop</title>

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
        
        <!-- changes usericon whether they are verified or not-->
        <?php if ($isVerified): ?>
            <a href="account.php"><img src="Images/pfpIconverified.png" alt="Verified" class="Loginpfp"></a>
        <?php else: ?>
            <a href="account.php"><img src="Images/pfpIcon.png" alt="Profile" class="Loginpfp"></a>
        <?php endif; ?>

    </div>
</header>

<main>
    <div class="notif-page">

        <h2>Notifications</h2>

        <!-- shows pending requests for your listings -->
        <?php if (!empty($pendingInterests)): ?>
        <div class="notif-section">

            <h3>Pending Interest Requests</h3>
            <?php foreach ($pendingInterests as $pi): ?>

            <div class="notif-card pending-card">

                <p><strong><?= htmlspecialchars($pi['buyer_email']) ?></strong> is interested in <strong><?= htmlspecialchars($pi['title']) ?></strong></p>
                <div class="interest-actions">

                    <form method="POST" action="listings_logic.php" style="display:inline">
                        <input type="hidden" name="interest_id" value="<?= $pi['id'] ?>">
                        <input type="hidden" name="response" value="confirmed">
                        <button type="submit" name="respond_interest" class="btn-confirm">✓ Confirm</button>
                    </form>

                    <form method="POST" action="listings_logic.php" style="display:inline">
                        <input type="hidden" name="interest_id" value="<?= $pi['id'] ?>">
                        <input type="hidden" name="response" value="denied">
                        <button type="submit" name="respond_interest" class="btn-deny">✗ Deny</button>
                    </form>

                </div>

            </div>

            <?php endforeach; ?>

        </div>

        <?php endif; ?>

        <!-- notifications -->
        <div class="notif-section">

            <h3>All Notifications</h3>

            <?php if (empty($notifications)): ?>
                <p class="no-listings">No notifications yet.</p>

            <?php else: ?>
                <?php foreach ($notifications as $n): ?>

                <div class="notif-card <?= $n['is_read'] ? 'read' : 'unread' ?>">

                    <p><?= htmlspecialchars($n['message']) ?></p>
                    <small><?= $n['created_at'] ?></small>

                    <?php if (!$n['is_read']): ?>

                    <form method="POST" action="listings_logic.php">
                        <input type="hidden" name="notif_id" value="<?= $n['id'] ?>">
                        <button type="submit" name="mark_read" class="btn-read">Mark as read</button>
                    </form>

                    <?php endif; ?>
                </div>

                <?php endforeach; ?>
            <?php endif; ?>

        </div>

    </div>

</main>

    <script src="JS/script.js"></script>
    
</body>
</html>
