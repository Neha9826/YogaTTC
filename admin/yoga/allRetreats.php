<?php
// /admin/yoga/allRetreats.php
include 'db.php';
$current_page = 'admin/yoga/allRetreats.php';

// Fetch all retreats with organization details
$sql = "SELECT yr.*, 
               o.name AS organization_name, 
               o.address, 
               o.city, 
               o.state, 
               o.country
        FROM yoga_retreats yr
        LEFT JOIN organizations o ON yr.organization_id = o.id
        ORDER BY yr.created_at DESC";
$result = $conn->query($sql);
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
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 class="m-0">Yoga Retreats</h2>
                    <a href="createRetreat.php" class="btn btn-primary">Create New Retreat</a>
                </div>

                <!-- Flash messages -->
                <?php if (!empty($_SESSION['flash_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($_SESSION['flash_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:60px;">#</th>
                                    <th>Title</th>
                                    <th>Organization</th>
                                    <th>Location</th>
                                    <th style="width:150px;">Price Range</th>
                                    <th style="width:110px;">Status</th>
                                    <th style="width:240px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
                                        <?php
                                            // Build full location from organization
                                            $parts = array_filter([
                                                $row['address'] ?? '',
                                                $row['city'] ?? '',
                                                $row['state'] ?? '',
                                                $row['country'] ?? ''
                                            ]);
                                            $fullLocation = !empty($parts) ? implode(', ', $parts) : '—';
                                        ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td><?= htmlspecialchars($row['title']); ?></td>
                                            <td><?= htmlspecialchars($row['organization_name'] ?: '—'); ?></td>
                                            <td><?= htmlspecialchars($fullLocation); ?></td>
                                            <td>
                                                <?= number_format((float)$row['min_price'], 2); ?> -
                                                <?= number_format((float)$row['max_price'], 2); ?>
                                            </td>
                                            <td>
                                                <?php if ($row['is_published']): ?>
                                                    <span class="badge bg-success">Published</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Draft</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="editRetreat.php?id=<?= (int)$row['id']; ?>" class="btn btn-sm btn-outline-primary me-1">Edit</a>
                                                <a href="deleteRetreat.php?id=<?= (int)$row['id']; ?>" class="btn btn-sm btn-outline-danger me-1" onclick="return confirm('Are you sure you want to delete this retreat?');">Delete</a>
                                                <a href="managePackages.php?retreat_id=<?= (int)$row['id']; ?>" class="btn btn-sm btn-outline-warning me-1">Packages</a>
                                                <a href="bookings/allBookings.php?retreat_id=<?= (int)$row['id']; ?>" class="btn btn-sm btn-outline-info">Bookings</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No retreats found.</td>
                                    </tr>
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
