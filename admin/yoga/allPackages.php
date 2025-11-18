<?php
// /admin/yoga/allPackages.php
include 'db.php';

// Handle publish/unpublish toggle (AJAX or simple GET)
if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $pkg_id = intval($_GET['id']);
    $new_status = ($_GET['toggle'] === '1') ? 1 : 0;

    $stmt = $conn->prepare("UPDATE yoga_packages SET is_published = ? WHERE id = ?");
    $stmt->bind_param('ii', $new_status, $pkg_id);
    $stmt->execute();

    $_SESSION['flash_success'] = 'Package publication status updated.';
    header('Location: allPackages.php');
    exit;
}

// Fetch all packages with retreat info
$sql = "SELECT p.id, p.title, p.nights, p.price_per_person, p.created_at, p.is_published,
               r.title AS retreat_title
        FROM yoga_packages p
        LEFT JOIN yoga_retreats r ON p.retreat_id = r.id
        ORDER BY p.created_at DESC";
$packages = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<?php include '../includes/head.php'; ?>
<link href="../css/styles.css" rel="stylesheet">

<body class="sb-nav-fixed">
<?php include '../includes/navbar.php'; ?>
<div id="layoutSidenav">
  <?php include '../includes/sidebar.php'; ?>
  <div id="layoutSidenav_content">
    <main>
      <div class="container-fluid px-4 mt-4">
        <h2>All Packages</h2>
        <p class="text-muted">Manage and publish packages across all retreats.</p>

        <?php if (!empty($_SESSION['flash_success'])): ?>
          <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_error'])): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <div class="card mb-4">
          <div class="card-body">
            <table class="table table-bordered align-middle">
              <thead class="table-light">
                <tr>
                  <th>ID</th>
                  <th>Package Name</th>
                  <th>Retreat</th>
                  <th>Days</th>
                  <th>Price</th>
                  <th>Published</th>
                  <!-- <th>Created</th> -->
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($packages && $packages->num_rows > 0): ?>
                  <?php while ($pkg = $packages->fetch_assoc()): ?>
                    <tr>
                      <td><?= htmlspecialchars($pkg['id']); ?></td>
                      <td><?= htmlspecialchars($pkg['title']); ?></td>
                      <td><?= htmlspecialchars($pkg['retreat_title'] ?? '—'); ?></td>
                      <td><?= (int)$pkg['nights']; ?></td>
                      <td><?= number_format((float)$pkg['price_per_person'], 2); ?></td>
                      <td>
                        <?php if ($pkg['is_published']): ?>
                          <span class="badge bg-success">Published</span>
                        <?php else: ?>
                          <span class="badge bg-secondary">Unpublished</span>
                        <?php endif; ?>
                      </td>
                      
                      <td>
                        <a href="editPackage.php?id=<?= $pkg['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
  <a href="deletePackage.php?id=<?= $pkg['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this package?');">Delete</a>

                        <?php if ($pkg['is_published']): ?>
                          <a href="allPackages.php?toggle=0&id=<?= $pkg['id']; ?>" class="btn btn-sm btn-outline-secondary">Unpublish</a>
                        <?php else: ?>
                          <a href="allPackages.php?toggle=1&id=<?= $pkg['id']; ?>" class="btn btn-sm btn-outline-success">Publish</a>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr><td colspan="7" class="text-center">No packages found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
      </div>
    </main>
    <?php include '../includes/footer.php'; ?>
  </div>
</div>
</body>
</html>
