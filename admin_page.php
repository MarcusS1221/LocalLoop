<?php
include 'account_logic.php';
include 'listings_logic.php';

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$email  = $_SESSION['email'];
$view   = $_GET['view'] ?? 'listings'; // 'listings' or 'users'
$unread = countUnread($conn, $email);

// Filters (listings view only)
$category = trim($_GET['category'] ?? '');
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$area     = trim($_GET['area']   ?? '');
$page     = max(1, intval($_GET['page'] ?? 1));

$data       = getListings($conn, '', $category, $minPrice, $maxPrice, $area, $page);
$listings   = $data['listings'];
$total      = $data['total'];
$totalPages = max(1, ceil($total / $data['perPage']));

// Fetch non-admin users
$users = $conn->query("SELECT id, name, email, role FROM users WHERE role != 'admin' ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Css/newStyles.css">
    <link rel="stylesheet" href="Css/listings.css">
    <link rel="stylesheet" href="Css/footerstyles.css">
    <link rel="stylesheet" href="Css/responsive.css">

    <link rel="icon" href="Images/LocalLoopLogo.png">
    <title>LocalLoop Admin</title>
</head>
<body>

<header id="home">
    <img src="Images/LocalLoopWhite.png" alt="LocalLoop" class="logoimg">
    <nav class="navbar">
        
        <a href="admin_page.php?view=listings" class="<?= $view=='listings' ? 'nav-active':'' ?>">Listings</a>
        <a href="admin_page.php?view=users"    class="<?= $view=='users'    ? 'nav-active':'' ?>">Users</a>

        <a href="notifications.php" class="notif-btn">
            Notifications<?php if ($unread > 0): ?><span class="notif-badge"><?= $unread ?></span><?php endif; ?>
        </a>

    </nav>

    <div class="Login-Container">

        <?php if ($isVerified): ?>
            <a href="account.php"><img src="Images/pfpIconverified.png" alt="Verified" class="Loginpfp"></a>
        <?php else: ?>
            <a href="account.php"><img src="Images/pfpIcon.png" alt="Profile" class="Loginpfp"></a>
        <?php endif; ?>
    </div>
</header>

<main>

    <?php if ($view === 'listings'): ?>
    <section class="listings-layout">

        <div class="listings-wrapper">

            <div class="listings-box">

                <?php if (empty($listings)): ?>
                    <p class="no-listings">No listings found.</p>
                <?php else: ?>
    
                    <?php foreach ($listings as $l): ?>
                    <div class="listing-card">
                        <?php if ($l['thumbnail']): ?>
                        <img src="Images/listings/<?= htmlspecialchars($l['thumbnail']) ?>"
                             class="listing-thumb"
                             onclick="openGallery(<?= $l['id'] ?>)"
                             alt="Listing image">
                        <?php endif; ?>
                        <div class="listing-body">
                            <div class="listing-top">
                                <span class="listing-title"><?= htmlspecialchars($l['title']) ?></span>
                                <span class="listing-price">R<?= number_format($l['price'], 2) ?></span>
                            </div>

                            <div class="listing-mid">
                                <span>👤 <?= htmlspecialchars($l['seller_name'] ?? 'Unknown') ?></span>
                                <span>📍 <?= htmlspecialchars($l['area'] ?? 'Unknown') ?></span>
                                <span>📞 <?= htmlspecialchars($l['phone'] ?? 'N/A') ?></span>
                            </div>

                            <p class="listing-desc"><?= htmlspecialchars($l['description']) ?></p>

                            <div class="listing-actions">
                                <form method="POST" action="listings_logic.php" onsubmit="return confirm('Remove this listing?')">
                                    <input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                                    <button type="submit" name="remove_listing" class="btn-remove">Remove</button>
                                </form>
                            </div>

                        </div>

                    </div>

                    <?php endforeach; ?>

                    <div class="pagination">

                        <?php if ($page > 1): ?>
                            <a href="?view=listings&page=<?= $page-1 ?>&category=<?= urlencode($category) ?>&min_price=<?= $minPrice ?>&max_price=<?= $maxPrice ?>&area=<?= urlencode($area) ?>">← Prev</a>
                        <?php endif; ?>
                        <span>Page <?= $page ?> of <?= $totalPages ?></span>
                        <?php if ($page < $totalPages): ?>
                            <a href="?view=listings&page=<?= $page+1 ?>&category=<?= urlencode($category) ?>&min_price=<?= $minPrice ?>&max_price=<?= $maxPrice ?>&area=<?= urlencode($area) ?>">Next →</a>
                        <?php endif; ?>

                    </div>

                <?php endif; ?>
            </div>

        </div>

        <div class="filters-column">

            <form method="GET">

                <input type="hidden" name="view" value="listings">

                <h3>Filters</h3>
                <label>Category</label>

                <select name="category">
                    <option value="">All</option>
                    <option value="Hardware"    <?= $category=='Hardware'    ? 'selected':'' ?>>Hardware</option>
                    <option value="Furniture"   <?= $category=='Furniture'   ? 'selected':'' ?>>Furniture</option>
                    <option value="Clothes"     <?= $category=='Clothes'     ? 'selected':'' ?>>Clothes</option>
                    <option value="Electronics" <?= $category=='Electronics' ? 'selected':'' ?>>Electronics</option>
                </select>

                <label>Min Price (R)</label>
                <input type="number" name="min_price" value="<?= $minPrice ?>" placeholder="0">
                <label>Max Price (R)</label>
                <input type="number" name="max_price" value="<?= $maxPrice ?>" placeholder="Any">
                <label>Area</label>
            
                <input type="text" name="area" value="<?= htmlspecialchars($area) ?>" placeholder="e.g. Cape Town">
                <button type="submit" class="btn-filter">Apply Filters</button>

                <a href="admin_page.php?view=listings" class="btn-clear">Clear</a>
            </form>

        </div>

    </section>

    <?php else: ?>
    <section class="users-section">

        <div class="listings-box">

            <?php if ($users->num_rows === 0): ?>
                <p class="no-listings">No users found.</p>
            <?php else: ?>
                <?php while ($u = $users->fetch_assoc()): ?>

                <div class="user-card">

                    <div class="user-info">
                        <span class="user-name">👤 <?= htmlspecialchars($u['name']) ?></span>
                        <span class="user-email"><?= htmlspecialchars($u['email']) ?></span>
                        <span class="user-role"><?= htmlspecialchars($u['role']) ?></span>
                    </div>

                    <form method="POST" action="listings_logic.php" onsubmit="return confirm('Remove this user and all their listings?')">
                        <input type="hidden" name="user_email" value="<?= htmlspecialchars($u['email']) ?>">
                        <button type="submit" name="remove_user" class="btn-remove">Remove</button>
                    </form>

                </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

    </section>

    <?php endif; ?>

</main>

    <div id="gallery-modal" class="modal hidden">

        <div class="gallery-box">
            
            <button class="gallery-close" onclick="document.getElementById('gallery-modal').classList.add('hidden')">✕</button>
            <div id="gallery-images" class="gallery-images"></div>

        </div>

    </div>

    <footer class="site-footer">

        <div class="footer-inner">
            <img src="Images/LocalLoopWhite.png" alt="LocalLoop" class="footer-logo">
            <p class="footer-tagline">Connecting communities, one listing at a time.</p>
            <p class="footer-copy">© <?= date('Y') ?> LocalLoop. All rights reserved.</p>
        </div>
        
    </footer>

    <script src="JS/script.js"></script>
    <script src="JS/listings.js"></script>

</body>
</html>
