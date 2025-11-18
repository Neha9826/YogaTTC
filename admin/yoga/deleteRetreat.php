<?php
// /admin/yoga/deleteRetreat.php
include '../../session.php';
include '../../db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid retreat ID.';
    header('Location: allRetreats.php');
    exit;
}

// Delete retreat
$stmt = $conn->prepare("DELETE FROM yoga_retreats WHERE id = ?");
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    $_SESSION['flash_success'] = 'Retreat deleted successfully.';
} else {
    $_SESSION['flash_error'] = 'Error deleting retreat: ' . $conn->error;
}

header('Location: allRetreats.php');
exit;
