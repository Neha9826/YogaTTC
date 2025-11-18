<?php
session_start();
include __DIR__.'/db.php';

if(!isset($_SESSION['yoga_host_id'])) header("Location: login.php");
$host_id = $_SESSION['yoga_host_id'];

$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("
SELECT 
    b.*, 
    p.title AS package_title, 
    p.price_per_person, 
    r.title AS retreat_title 
FROM yoga_batches b
JOIN yoga_packages p ON b.package_id = p.id
JOIN yoga_retreats r ON p.retreat_id = r.id
JOIN organizations o ON r.organization_id = o.id
WHERE b.id = ? AND o.created_by = ?
");

$stmt->bind_param("ii",$id,$host_id);
$stmt->execute();
$batch = $stmt->get_result()->fetch_assoc();
$stmt->close();
if(!$batch) die("Batch not found.");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include __DIR__.'/includes/head.php'; ?>
<title>View Batch</title>
<link rel="stylesheet" href="yoga.css">
</head>
<body class="yoga-page">
<?php include __DIR__.'/includes/fixed_social_bar.php'; ?>
<?php include __DIR__.'/yoga_navbar.php'; ?>

<div class="container-fluid">
<div class="row">
<?php include 'host_sidebar.php'; ?>
<div class="col-md-9 col-lg-10 p-4">
<h2>Batch Details</h2>
<p><strong>Retreat:</strong> <?= htmlspecialchars($batch['retreat_title']) ?></p>
<p><strong>Package:</strong> <?= htmlspecialchars($batch['package_title']) ?></p>
<p><strong>Start Date:</strong> <?= date('d M Y', strtotime($batch['start_date'])) ?></p>
<p><strong>End Date:</strong> <?= date('d M Y', strtotime($batch['end_date'])) ?></p>
<p><strong>Capacity:</strong> <?= $batch['capacity'] ?></p>
<p><strong>Available Slots:</strong> <?= $batch['available_slots'] ?></p>
<p><strong>Price:</strong> ₹<?= number_format($batch['price_override'] ?: $batch['price_per_person'], 2) ?>
<?= $batch['price_override'] ? '<small class="text-muted">(custom override)</small>' : '' ?></p>
<p><strong>Notes:</strong><br><?= nl2br(htmlspecialchars($batch['notes'])) ?></p>
<a href="editHostBatch.php?id=<?= $batch['id'] ?>" class="btn btn-warning">Edit</a>
<a href="allHostBatches.php" class="btn btn-secondary">Back</a>
</div>
</div>
</div>
<?php include __DIR__.'/includes/footer.php'; ?>
</body>
</html>
