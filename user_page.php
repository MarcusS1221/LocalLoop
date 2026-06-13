<?php
include 'account_logic.php';
include 'listings_logic.php';

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$email    = $_SESSION['email'];
$search   = trim($_GET['search']   ?? '');
$category = trim($_GET['category'] ?? '');
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$area     = trim($_GET['area']     ?? '');
$page     = max(1, intval($_GET['page'] ?? 1));

$data       = getListings($conn, $search, $category, $minPrice, $maxPrice, $area, $page);
$listings   = $data['listings'];
$total      = $data['total'];
$totalPages = max(1, ceil($total / $data['perPage']));
$unread     = countUnread($conn, $email);

$interestedIds = [];
$ir = $conn->query("SELECT listing_id FROM interests WHERE buyer_email='$email'");
while ($row = $ir->fetch_assoc()) $interestedIds[] = $row['listing_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Css/styles.css">
    <link rel="stylesheet" href="Css/listingsScript.css">

    <link rel="icon" href="Images/LocalLoopLogo.png">
    <title>LocalLoop</title>

</head>
<body>

<header id="home">

    <img src="Images/LocalLoopWhite.png" alt="LOGO" class="logoimg">

    <nav class="navbar">

        <a href="#listings">Listings</a>
        <a href="notifications.php" class="notif-btn">
            Notifications <?php if ($unread > 0): ?><span class="notif-badge"><?= $unread ?></span><?php endif; ?>
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

    <!-- categories -->
    <div class="Categories">
        <div class="Category" onclick="filterCategory('Hardware')">
            <img src="Images/HardwareIcon.png"><p>Hardware</p>
        </div>
        <div class="Category" onclick="filterCategory('Furniture')">
            <img src="Images/FurnitureIcon.png"><p>Furniture</p>
        </div>
        <div class="Category" onclick="filterCategory('Clothes')">
            <img src="Images/ClothesIcon.png"><p>Clothes</p>
        </div>
        <div class="Category" onclick="filterCategory('Electronics')">
            <img src="Images/Electronics.png"><p>Electronics</p>
        </div>
    </div>

    <!-- searchbar -->
    <form method="GET" class="search-container" id="Search">
        <input type="text" name="search" placeholder="Search listings..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit"><img src="Images/search.png" class="searchImg"></button>
    </form>

    <!-- Listings + Filters -->
    <section class="listings-layout" id="listings">

        <div class="listings-wrapper">

            <div class="listings-header">
                <?php if ($isVerified): ?>
                    <button class="btn-add" onclick="document.getElementById('add-listing-modal').classList.remove('hidden')">+ Add Listing</button>
                <?php else: ?>
                    <p class="not-verified">⚠️ <a href="account.php">Verify your account</a> to add listings.</p>
                <?php endif; ?>
            </div>

            <div class="listings-box">

                <?php if (empty($listings)): ?>
                    <p class="no-listings">No listings found.</p>
                <?php else: ?>
                    <?php foreach ($listings as $l): ?>
                    <div class="listing-card">

                        <!-- Thumbnail -->
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
                            </div>

                            <p class="listing-desc"><?= htmlspecialchars($l['description']) ?></p>
                            <div class="listing-actions">

                                <!-- report only show on other people's listings -->
                                <?php if ($l['user_email'] !== $email): ?>
                                <form method="POST" action="listings_logic.php">
                                    <input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                                    <button type="submit" name="report_listing" class="btn-report">Report</button>
                                </form>

                                <form method="POST" action="listings_logic.php">
                                    <input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                                    <?php if (in_array($l['id'], $interestedIds)): ?>
                                        <button type="button" class="btn-interested-done" disabled>✓ Interested</button>
                                    <?php else: ?>
                                        <button type="submit" name="interested_listing" class="btn-interested">Interested</button>
                                    <?php endif; ?>
                                </form>

                                <?php else: ?>

                                <button class="btn-edit"
                                    onclick="openEditModal(<?= $l['id'] ?>, '<?= htmlspecialchars(addslashes($l['title'])) ?>', <?= $l['price'] ?>, '<?= $l['category'] ?>', '<?= htmlspecialchars(addslashes($l['description'])) ?>')">
                                    Edit
                                </button>

                                <form method="POST" action="listings_logic.php" onsubmit="return confirm('Remove this listing?')">
                                    <input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                                    <button type="submit" name="delete_own_listing" class="btn-remove">Remove</button>
                                </form>

                                <?php endif; ?>
                            </div>

                        </div>

                    </div>
                    <?php endforeach; ?>

                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&min_price=<?= $minPrice ?>&max_price=<?= $maxPrice ?>&area=<?= urlencode($area) ?>">← Prev</a>
                        <?php endif; ?>
                        <span>Page <?= $page ?> of <?= $totalPages ?></span>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&min_price=<?= $minPrice ?>&max_price=<?= $maxPrice ?>&area=<?= urlencode($area) ?>">Next →</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- filtering -->
        <div class="filters-column">
            <form method="GET">
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
                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn-filter">Apply Filters</button>
                <a href="user_page.php" class="btn-clear">Clear</a>
            </form>

        </div>

    </section>
</main>

    <!-- listing view -->
    <div id="add-listing-modal" class="modal hidden">

        <div class="modal-box">

            <h2>Add Listing</h2>
            <form method="POST" action="listings_logic.php" enctype="multipart/form-data">
                <label>Product Name</label>
                <input type="text" name="title" required>
                <label>Price (R)</label>
                <input type="number" step="0.01" name="price" required>

                <label>Category</label>
                <select name="category" required>
                    <option value="">Select category</option>
                    <option value="Hardware">Hardware</option>
                    <option value="Furniture">Furniture</option>
                    <option value="Clothes">Clothes</option>
                    <option value="Electronics">Electronics</option>
                </select>

                <label>Description</label>
                <textarea name="description" rows="3" required></textarea>
                <label>Photos <small>(up to 10, jpg/png/webp)</small></label>
                <input type="file" name="images[]" accept="image/*" multiple id="imgInput">
                <div id="img-preview" class="img-preview-row"></div>
                <button type="submit" name="add_listing" class="btn-save">Post Listing</button>
                <button type="button" class="btn-cancel" onclick="document.getElementById('add-listing-modal').classList.add('hidden')">Cancel</button>
            </form>
            
        </div>

    </div>

    <!-- image gallery -->
    <div id="gallery-modal" class="modal hidden">
        <div class="gallery-box">
            <button class="gallery-close" onclick="document.getElementById('gallery-modal').classList.add('hidden')">✕</button>
            <div id="gallery-images" class="gallery-images"></div>
        </div>
    </div>

    <!-- edit listing -->
    <div id="edit-listing-modal" class="modal hidden">

        <div class="modal-box">
    
            <h2>Edit Listing</h2>
            <form method="POST" action="listings_logic.php" enctype="multipart/form-data">
                <input type="hidden" name="listing_id" id="edit-listing-id">

                <label>Product Name</label>
                <input type="text" name="title" id="edit-title" required>

                <label>Price (R)</label>
                <input type="number" step="0.01" name="price" id="edit-price" required>

                <label>Category</label>
                <select name="category" id="edit-category" required>
                    <option value="Hardware">Hardware</option>
                    <option value="Furniture">Furniture</option>
                    <option value="Clothes">Clothes</option>
                    <option value="Electronics">Electronics</option>
                </select>

                <label>Description</label>
                <textarea name="description" id="edit-description" rows="3" required></textarea>

                <label>Add More Photos <small>(optional, jpg/png/webp)</small></label>
                <input type="file" name="images[]" accept="image/*" multiple>

                <button type="submit" name="edit_listing" class="btn-save">Save Changes</button>

                <button type="button" class="btn-cancel" onclick="document.getElementById('edit-listing-modal').classList.add('hidden')">Cancel</button>
            </form>

        </div>

    </div>

    <script src="JS/script.js"></script>
    <script src="JS/listings.js"></script>

    <script>
        
        function openEditModal(id, title, price, category, description) {
            document.getElementById('edit-listing-id').value   = id;
            document.getElementById('edit-title').value        = title;
            document.getElementById('edit-price').value        = price;
            document.getElementById('edit-category').value     = category;
            document.getElementById('edit-description').value  = description;
            document.getElementById('edit-listing-modal').classList.remove('hidden');
        }

</script>

</body>
</html>