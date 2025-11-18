<?php
session_start();
include __DIR__ . '/db.php';
if(!isset($_SESSION['yoga_host_id'])) header("Location: login.php");

$host_id = $_SESSION['yoga_host_id'];
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) die("Invalid ID");
$id = intval($_GET['id']);

$stmt = $conn->prepare("
DELETE p FROM yoga_packages p
JOIN yoga_retreats r ON p.retreat_id = r.id
JOIN organizations o ON r.organization_id = o.id
WHERE p.id=? AND o.created_by=?
");
$stmt->bind_param("ii", $id, $host_id);
if($stmt->execute()) {
    header("Location: allHostPackages.php?msg=deleted");
} else {
    die("Error deleting: ".$conn->error);
}
$stmt->close();
?>
