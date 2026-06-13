<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// add listing and image
if (isset($_POST['add_listing'])) {
    $userEmail   = $_SESSION['email'];
    $sellerName  = $_SESSION['name'];
    $title       = trim($_POST['title']);
    $price       = trim($_POST['price']);
    $category    = trim($_POST['category']);
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("INSERT INTO listings (user_email, seller_name, title, price, category, description) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("sssdss", $userEmail, $sellerName, $title, $price, $category, $description);
    $stmt->execute();
    $listingId = $stmt->insert_id;
    $stmt->close();

    // image uploads (max 10)
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = __DIR__ . '/Images/listings/';
        $allowed   = ['jpg', 'jpeg', 'png', 'webp'];
        $count     = min(count($_FILES['images']['name']), 10);

        for ($i = 0; $i < $count; $i++) {
            if ($_FILES['images']['error'][$i] !== 0) continue;

            $tmpName  = $_FILES['images']['tmp_name'][$i];
            $origName = $_FILES['images']['name'][$i];
            $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) continue;

            $filename = uniqid('listing_', true) . '.' . $ext;
            $dest     = $uploadDir . $filename;

            if (move_uploaded_file($tmpName, $dest)) {
                $imgStmt = $conn->prepare("INSERT INTO listing_images (listing_id, filename) VALUES (?,?)");
                $imgStmt->bind_param("is", $listingId, $filename);
                $imgStmt->execute();
                $imgStmt->close();
            }
        }
    }

    header("Location: user_page.php");
    exit();
}

// report listings
if (isset($_POST['report_listing'])) {
    $listingId = $_POST['listing_id'];
    $fromEmail = $_SESSION['email'];
    $message   = "Listing #$listingId has been reported by $fromEmail.";

    $admins = $conn->query("SELECT email FROM users WHERE role='admin'");
    while ($admin = $admins->fetch_assoc()) {
        $type = 'report';
        $stmt = $conn->prepare("INSERT INTO notifications (to_email, from_email, listing_id, type, message) VALUES (?,?,?,?,?)");
        $stmt->bind_param("ssiss", $admin['email'], $fromEmail, $listingId, $type, $message);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: user_page.php");
    exit();
}

// intersts
if (isset($_POST['interested_listing'])) {
    $listingId  = $_POST['listing_id'];
    $buyerEmail = $_SESSION['email'];

    $check = $conn->prepare("SELECT id FROM interests WHERE listing_id=? AND buyer_email=?");
    $check->bind_param("is", $listingId, $buyerEmail);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO interests (listing_id, buyer_email) VALUES (?,?)");
        $stmt->bind_param("is", $listingId, $buyerEmail);
        $stmt->execute();
        $stmt->close();

        $lr = $conn->prepare("SELECT user_email, title FROM listings WHERE id=?");
        $lr->bind_param("i", $listingId);
        $lr->execute();
        $lr->bind_result($ownerEmail, $listingTitle);
        $lr->fetch();
        $lr->close();

        $msg  = "$buyerEmail is interested in your listing: \"$listingTitle\"";
        $type = 'interest';
        $stmt = $conn->prepare("INSERT INTO notifications (to_email, from_email, listing_id, type, message) VALUES (?,?,?,?,?)");
        $stmt->bind_param("ssiss", $ownerEmail, $buyerEmail, $listingId, $type, $msg);
        $stmt->execute();
        $stmt->close();
    }
    $check->close();

    header("Location: user_page.php");
    exit();
}

// confimr or deny listing
if (isset($_POST['respond_interest'])) {
    $interestId = $_POST['interest_id'];
    $response   = $_POST['response'];

    $stmt = $conn->prepare("SELECT listing_id, buyer_email FROM interests WHERE id=?");
    $stmt->bind_param("i", $interestId);
    $stmt->execute();
    $stmt->bind_result($listingId, $buyerEmail);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE interests SET status=? WHERE id=?");
    $stmt->bind_param("si", $response, $interestId);
    $stmt->execute();
    $stmt->close();

    $ownerEmail = $_SESSION['email'];

    if ($response === 'confirmed') {
        $phone = 'not available';
        $pStmt = $conn->prepare("SELECT phone FROM user_verification WHERE user_email=?");
        $pStmt->bind_param("s", $ownerEmail);
        $pStmt->execute();
        $pStmt->bind_result($phone);
        $pStmt->fetch();
        $pStmt->close();

        $listingStmt = $conn->prepare("SELECT title FROM listings WHERE id=?");
        $listingStmt->bind_param("i", $listingId);
        $listingStmt->execute();
        $listingStmt->bind_result($listingTitle);
        $listingStmt->fetch();
        $listingStmt->close();

        $msg = "🎉 Your interest in \"$listingTitle\" was accepted! You can contact the seller at: 📞 $phone";
    } else {
        $msg = "Your interest in listing #$listingId was declined by the seller.";
    }

    $type = 'response';
    $stmt = $conn->prepare("INSERT INTO notifications (to_email, from_email, listing_id, type, message) VALUES (?,?,?,?,?)");
    $stmt->bind_param("ssiss", $buyerEmail, $ownerEmail, $listingId, $type, $msg);
    $stmt->execute();
    $stmt->close();

    header("Location: notifications.php");
    exit();
}

// remove listing admin
if (isset($_POST['remove_listing'])) {
    $listingId = $_POST['listing_id'];
    $stmt = $conn->prepare("UPDATE listings SET status='removed' WHERE id=?");
    $stmt->bind_param("i", $listingId);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_page.php");
    exit();
}

// remove listing self
if (isset($_POST['delete_own_listing'])) {
    $listingId = $_POST['listing_id'];
    $email     = $_SESSION['email'];

    $stmt = $conn->prepare("UPDATE listings SET status='removed' WHERE id=? AND user_email=?");
    $stmt->bind_param("is", $listingId, $email);
    $stmt->execute();
    $stmt->close();

    header("Location: user_page.php");
    exit();
}

// edit listings
if (isset($_POST['edit_listing'])) {
    $listingId   = $_POST['listing_id'];
    $email       = $_SESSION['email'];
    $title       = trim($_POST['title']);
    $price       = trim($_POST['price']);
    $category    = trim($_POST['category']);
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("UPDATE listings SET title=?, price=?, category=?, description=? WHERE id=? AND user_email=?");
    $stmt->bind_param("sdsssi", $title, $price, $category, $description, $listingId, $email);
    $stmt->execute();
    $stmt->close();

    // new image uploads
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = __DIR__ . '/Images/listings/';
        $allowed   = ['jpg', 'jpeg', 'png', 'webp'];
        $count     = min(count($_FILES['images']['name']), 10);

        for ($i = 0; $i < $count; $i++) {
            if ($_FILES['images']['error'][$i] !== 0) continue;
            $ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) continue;
            $filename = uniqid('listing_', true) . '.' . $ext;
            if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $uploadDir . $filename)) {
                $imgStmt = $conn->prepare("INSERT INTO listing_images (listing_id, filename) VALUES (?,?)");
                $imgStmt->bind_param("is", $listingId, $filename);
                $imgStmt->execute();
                $imgStmt->close();
            }
        }
    }

    header("Location: user_page.php");
    exit();
}

// remove user
if (isset($_POST['remove_user'])) {
    $targetEmail = $_POST['user_email'];

    // Remove their listings
    $conn->query("UPDATE listings SET status='removed' WHERE user_email='$targetEmail'");

    // Remove the user
    $stmt = $conn->prepare("DELETE FROM users WHERE email=? AND role != 'admin'");
    $stmt->bind_param("s", $targetEmail);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_page.php?view=users");
    exit();
}

// mark notifications as read
if (isset($_POST['mark_read'])) {
    $notifId = $_POST['notif_id'];
    $stmt = $conn->prepare("UPDATE notifications SET is_read=1 WHERE id=?");
    $stmt->bind_param("i", $notifId);
    $stmt->execute();
    $stmt->close();

    header("Location: notifications.php");
    exit();
}

function getListings($conn, $search = '', $category = '', $minPrice = '', $maxPrice = '', $area = '', $page = 1) {
    $perPage = 10;
    $offset  = ($page - 1) * $perPage;

    $sql = "SELECT l.*, v.phone, v.area 
            FROM listings l
            LEFT JOIN user_verification v ON l.user_email = v.user_email
            WHERE l.status = 'active'";

    $params = [];
    $types  = "";

    if (!empty($search)) {
        $sql .= " AND (l.title LIKE ? OR l.description LIKE ?)";
        $s = "%$search%";
        $params[] = $s;
        $params[] = $s;
        $types .= "ss";
    }
    if (!empty($category)) {
        $sql .= " AND l.category = ?";
        $params[] = $category;
        $types .= "s";
    }
    if ($minPrice !== '') {
        $sql .= " AND l.price >= ?";
        $params[] = $minPrice;
        $types .= "d";
    }
    if ($maxPrice !== '') {
        $sql .= " AND l.price <= ?";
        $params[] = $maxPrice;
        $types .= "d";
    }
    if (!empty($area)) {
        $sql .= " AND v.area LIKE ?";
        $params[] = "%$area%";
        $types .= "s";
    }

    // Count total
    $countSql  = str_replace("SELECT l.*, v.phone, v.area", "SELECT COUNT(*)", $sql);
    $countStmt = $conn->prepare($countSql);
    $total = 0;
    if (!empty($params)) $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $countStmt->bind_result($total);
    $countStmt->fetch();
    $countStmt->close();

    $sql .= " ORDER BY l.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result   = $stmt->get_result();
    $listings = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    //attaches image
    foreach ($listings as &$l) {
        $imgStmt = $conn->prepare("SELECT filename FROM listing_images WHERE listing_id=? LIMIT 1");
        $imgStmt->bind_param("i", $l['id']);
        $imgStmt->execute();
        $filename = null;
        $imgStmt->bind_result($filename);
        $imgStmt->fetch();
        $imgStmt->close();
        $l['thumbnail'] = $filename ?? null;
    }

    return ['listings' => $listings, 'total' => $total, 'perPage' => $perPage];
}

// count unread messages
function countUnread($conn, $email) {
    $count = 0;
    $stmt  = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE to_email=? AND is_read=0");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return (int)($count ?? 0);
}