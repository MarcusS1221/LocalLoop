<?php
require_once 'config.php';

$listingId = intval($_GET['listing_id'] ?? 0);
$images    = [];

$stmt = $conn->prepare("SELECT filename FROM listing_images WHERE listing_id=?");
$stmt->bind_param("i", $listingId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $images[] = $row['filename'];
}
$stmt->close();

header('Content-Type: application/json');
echo json_encode($images);
?>
