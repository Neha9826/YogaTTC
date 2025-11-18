<?php
include '../../session.php';
include 'db.php';

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("DELETE FROM organizations WHERE id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['flash_success'] = "Organization deleted successfully!";
} else {
    $_SESSION['flash_error'] = "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
header("Location: manageOrganizations.php");
exit;
?>
