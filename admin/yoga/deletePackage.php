<?php
// /admin/yoga/deletePackage.php
include '../../session.php';
include '../../db.php';

$retreat_id = isset($_GET['retreat_id']) ? intval($_GET['retreat_id']) : 0;
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($retreat_id <= 0 || $id <= 0) {
    $_SESSION['flash_error'] = 'Invalid parameters.';
    header('Location: allRetreats.php');
    exit;
}

$stmt = $conn->prepare("DELETE FROM yoga_packages WHERE id = ? AND retreat_id = ?");
$stmt->bind_param('ii', $id, $retreat_id);
if ($stmt->execute()) {
    $_SESSION['flash_success'] = 'Package deleted.';
} else {
    $_SESSION['flash_error'] = 'Error deleting package: ' . $conn->error;
}

header('Location: managePackages.php?retreat_id=' . $retreat_id);
exit;
