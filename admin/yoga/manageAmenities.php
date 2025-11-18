<?php
// /admin/yoga/manageAmenities.php
include 'db.php';

// Fetch amenities
$amenities = $conn->query("SELECT id, name, icon_class, created_at FROM yoga_amenities ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<?php include '../includes/head.php'; ?>

<!-- ✅ Ensure Bootstrap Icons CSS is loaded -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link href="../css/styles.css" rel="stylesheet">

<body class="sb-nav-fixed">
<?php include '../includes/navbar.php'; ?>
<div id="layoutSidenav">
    <?php include '../includes/sidebar.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4 mt-4">
                <h2>Manage Amenities</h2>

                <?php if (isset($_SESSION['flash_success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['flash_error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
                <?php endif; ?>

                <a href="createAmenity.php" class="btn btn-primary mb-3">Add New Amenity</a>

                <div class="card mb-4">
                    <div class="card-body">
                        <table class="table table-bordered align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 35%">Name</th>
                                    <th style="width: 25%">Icon</th>
                                    <th style="width: 25%">Created</th>
                                    <th style="width: 15%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($amenities && $amenities->num_rows > 0): ?>
                                    <?php while ($a = $amenities->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($a['name']); ?></td>
                                            <td>
                                                <?php if (!empty($a['icon_class'])): ?>
                                                    <!-- ✅ Display icon itself -->
                                                    <i class="<?= htmlspecialchars($a['icon_class']); ?>" 
                                                       style="font-size: 1.8em; color: #007bff;"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-question-circle text-muted" style="font-size: 1.8em;"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars(date('d M Y, h:i A', strtotime($a['created_at']))); ?></td>
                                            <td>
                                                <a href="editAmenity.php?id=<?= $a['id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="deleteAmenity.php?id=<?= $a['id']; ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this amenity?');">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted">No amenities found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
</div>
</body>
</html>
