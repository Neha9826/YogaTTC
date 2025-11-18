<?php
session_start();
include __DIR__.'/db.php';

if(!isset($_SESSION['yoga_host_id'])) header("Location: login.php");
$host_id = $_SESSION['yoga_host_id'];

$sql = "
SELECT 
    b.*, 
    p.title AS package_title, 
    p.price_per_person, 
    r.title AS retreat_title 
FROM yoga_batches b
JOIN yoga_packages p ON b.package_id = p.id
JOIN yoga_retreats r ON p.retreat_id = r.id
JOIN organizations o ON r.organization_id = o.id
WHERE o.created_by = $host_id
ORDER BY b.start_date ASC
";

$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include __DIR__.'/includes/head.php'; ?>
<title>My Batches</title>
<link rel="stylesheet" href="yoga.css">
</head>
<body class="yoga-page">
<?php include __DIR__.'/includes/fixed_social_bar.php'; ?>
<?php include __DIR__.'/yoga_navbar.php'; ?>

<div class="container-fluid">
<div class="row">
<?php include 'host_sidebar.php'; ?>
<div class="col-md-9 col-lg-10 p-4">
<h2>My Batches</h2>
<a href="createBatch.php" class="btn btn-primary mb-3">➕ Add Batch</a>
<table class="table table-bordered">
<thead>
<tr>
<th>Retreat</th>
<th>Package</th>
<th>Start Date</th>
<th>End Date</th>
<th>Capacity</th>
<th>Available Slots</th>
<th>Price</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php while($b=$res->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($b['retreat_title']) ?></td>
<td><?= htmlspecialchars($b['package_title']) ?></td>
<td><?= date('d M Y', strtotime($b['start_date'])) ?></td>
<td><?= date('d M Y', strtotime($b['end_date'])) ?></td>
<td><?= $b['capacity'] ?></td>
<td><?= $b['available_slots'] ?></td>
<td>
  ₹<?= number_format($b['price_override'] ?: $b['price_per_person'], 2) ?>
  <?= $b['price_override'] ? '<small class="text-muted">(custom)</small>' : '' ?>
</td>

<td>
<a href="viewHostBatch.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-primary">View</a>
<a href="editHostBatch.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
<a href="deleteHostBatch.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</div>
</div>
<?php include __DIR__.'/includes/footer.php'; ?>
</body>
</html>
