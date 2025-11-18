<?php
session_start();
include __DIR__ . '/db.php';
if(!isset($_SESSION['yoga_host_id'])) header("Location: login.php");

$host_id = $_SESSION['yoga_host_id'];

$sql = "
SELECT p.*, r.title AS retreat_name, o.name AS org_name
FROM yoga_packages p
JOIN yoga_retreats r ON p.retreat_id = r.id
JOIN organizations o ON r.organization_id = o.id
WHERE o.created_by = $host_id
ORDER BY p.created_at DESC
";
$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__.'/includes/head.php'; ?>
    <title>My Packages</title>
    <link rel="stylesheet" href="yoga.css">
</head>
<body class="yoga-page">
<?php include __DIR__.'/includes/fixed_social_bar.php'; ?>
<?php include __DIR__.'/yoga_navbar.php'; ?>

<div class="container-fluid">
  <div class="row">
    <?php include 'host_sidebar.php'; ?>
    <div class="col-md-9 col-lg-9 p-4">
      <h2>My Packages</h2>
      <a href="createPackage.php" class="btn btn-primary mb-3">➕ Add Package</a>

      <table class="table table-bordered align-middle">
        <thead>
          <tr>
            <th>Title</th>
            <th>Retreat</th>
            <th>Organization</th>
            <th>Price/Person</th>
            <th>Persons</th>
            <th>Nights</th>
            <th>Meals</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row=$res->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['retreat_name']) ?></td>
            <td><?= htmlspecialchars($row['org_name']) ?></td>
            <td>₹<?= number_format($row['price_per_person'],2) ?></td>
            <td><?= $row['min_persons'] ?> - <?= $row['max_persons'] ?></td>
            <td><?= $row['nights'] ?></td>
            <td><?= $row['meals_included'] ? 'Yes' : 'No' ?></td>
            <td>
              <a href="viewHostPackage.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">View</a>
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
