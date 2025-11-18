<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['yoga_host_id'])) {
    header("Location: login.php");
    exit;
}

$host_id = $_SESSION['yoga_host_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid retreat ID.");
}

$retreat_id = intval($_GET['id']);

// Verify host owns this retreat
$stmt = $conn->prepare("
    SELECT r.id 
    FROM yoga_retreats r
    JOIN organizations o ON r.organization_id = o.id
    WHERE r.id=? AND o.created_by=?
");
$stmt->bind_param("ii", $retreat_id, $host_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    die("Retreat not found or access denied.");
}
$stmt->close();

// Delete retreat (related data can also be deleted if needed)
$conn->query("DELETE FROM yoga_retreats WHERE id=$retreat_id");

header("Location: all_host_retreats.php?msg=deleted");
exit;
?>