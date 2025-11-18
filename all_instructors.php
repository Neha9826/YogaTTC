<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['yoga_host_id'])) {
    header("Location: login.php");
    exit;
}

$host_id = $_SESSION['yoga_host_id'];

// Fetch instructors for host's orgs
$sql = "
  SELECT i.*, o.name AS org_name 
  FROM yoga_instructors i
  JOIN organizations o ON i.organization_id = o.id
  WHERE o.created_by = $host_id
  ORDER BY i.created_at DESC
";
$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__ . '/includes/head.php'; ?>
  <title>My Instructors | Yoga Bhawna Mission</title>
  <link rel="stylesheet" href="yoga.css">
</head>
<body class="yoga-page">

<?php include __DIR__ . '/includes/fixed_social_bar.php'; ?>
<?php include __DIR__ . '/yoga_navbar.php'; ?>

<div class="container-fluid">
  <div class="row">
    <?php include 'host_sidebar.php'; ?>
    <div class="col-md-9 col-lg-10 p-4">
      <h2>My Instructors</h2>
      <a href="org_instructor.php" class="btn btn-primary mb-3">➕ Add Instructor</a>

      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Profile</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Organization</th>
            <th>Type</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $res->fetch_assoc()): ?>
          <tr>
            <td class="text-center">
                <?php
                $photo = trim($row['photo']); // remove extra spaces
                $photo_path = (!empty($photo) && file_exists('../'.$photo)) 
                            ? '../'.$photo 
                            : '../assets/default_profile.png';
                ?>
                <img src="<?= $photo_path ?>" class="rounded-circle" width="50" height="50" alt="<?= htmlspecialchars($row['name'] ?: 'Profile') ?>">
            </td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td><?= htmlspecialchars($row['org_name']) ?></td>
            <td><?= ucfirst($row['type']) ?></td>
            <td>
              <a href="view_instructor.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">View</a>
              <a href="delete_instructor.php?id=<?= $row['id'] ?>" 
                 class="btn btn-sm btn-danger" 
                 onclick="return confirm('Are you sure?')">Delete</a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
