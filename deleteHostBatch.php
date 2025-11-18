<?php
session_start();
include __DIR__.'/db.php';

if(!isset($_SESSION['yoga_host_id'])) header("Location: login.php");
$host_id = $_SESSION['yoga_host_id'];

$id = intval($_GET['id'] ?? 0);
if(!$id) die("Invalid batch ID.");

// Ensure host owns this batch
$stmt = $conn->prepare("
SELECT b.id FROM yoga_batches b
JOIN yoga_packages p ON b.package_id=p.id
JOIN yoga_retreats r ON p.retreat_id=r.id
JOIN organizations o ON r.organization_id=o.id
WHERE b.id=? AND o.created_by=?
");
$stmt->bind_param("ii",$id,$host_id);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows == 0) die("Unauthorized or batch not found.");
$stmt->close();

// Optional: check if bookings exist for this batch
$conn->query("DELETE FROM yoga_batches WHERE id=$id");
header("Location: allHostBatches.php");
exit;
