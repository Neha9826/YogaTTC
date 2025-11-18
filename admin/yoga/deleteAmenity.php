<?php
// /admin/yoga/deleteAmenity.php
include '../../session.php';
include '../../db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid amenity ID.';
    header('Location: manageAmenities.php');
    exit;
}

$stmt = $conn->prepare("DELETE FROM yoga_amenities WHERE id = ?");
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    $_SESSION['flash_success'] = 'Amenity deleted.';
} else {
    $_SESSION['flash_error'] = 'Error deleting amenity: ' . $conn->error;
}

header('Location: manageAmenities.php');
exit;
