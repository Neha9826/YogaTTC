<?php
// /admin/yoga/managePackages.php
// include '../../session.php';
include 'db.php';

$current_page = 'admin/yoga/managePackages.php';

$retreat_id = isset($_GET['retreat_id']) ? intval($_GET['retreat_id']) : 0;
if ($retreat_id <= 0) {
    $_SESSION['flash_error'] = 'Invalid retreat ID.';
    header('Location: allRetreats.php');
    exit;
}

// Fetch retreat info (prepared)
$stmt = $conn->prepare("SELECT id, title FROM yoga_retreats WHERE id = ?");
$stmt->bind_param('i', $retreat_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res === false || $res->num_rows === 0) {
    $_SESSION['flash_error'] = 'Retreat not found.';
    header('Location: allRetreats.php');
    exit;
}
$retreat = $res->fetch_assoc();
$retreat_title = $retreat['title'] ?? 'Untitled Retreat';

// Fetch packages (prepared)
$pstmt = $conn->prepare("SELECT id, title, nights, price_per_person, created_at FROM yoga_packages WHERE retreat_id = ? ORDER BY created_at DESC");
$pstmt->bind_param('i', $retreat_id);
$pstmt->execute();
$packages_res = $pstmt->get_result();
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
                <h2>Manage Packages – <?= htmlspecialchars($retreat_title); ?></h2>

                <?php if (!empty($_SESSION['flash_success'])): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
                <?php endif; ?>
                <?php if (!empty($_SESSION['flash_error'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
                <?php endif; ?>

                <a href="createPackage.php?retreat_id=<?= (int)$retreat_id; ?>" class="btn btn-primary mb-3">Add New Package</a>

                <div class="card mb-4">
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Package Name</th>
                                    <th>Days</th>
                                    <th>Price</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($packages_res && $packages_res->num_rows > 0): ?>
                                    <?php while ($pkg = $packages_res->fetch_assoc()): ?>
                                        <?php
                                            // Defensive access for each field
                                            $pkg_id = isset($pkg['id']) ? (int)$pkg['id'] : 0;
                                            $pkg_title = isset($pkg['title']) ? $pkg['title'] : '—';
                                            $pkg_nights = isset($pkg['nights']) ? (int)$pkg['nights'] : 0;
                                            // price may be null or empty — show 0.00 in that case
                                            $pkg_price_per_person = isset($pkg['price_per_person']) && $pkg['price_per_person'] !== null && $pkg['price_per_person'] !== '' ? (float)$pkg['price_per_person'] : 0.0;
                                            $pkg_created = isset($pkg['created_at']) ? $pkg['created_at'] : '—';
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($pkg_title); ?></td>
                                            <td><?= $pkg_nights; ?></td>
                                            <td><?= number_format($pkg_price_per_person, 2); ?></td>
                                            <td><?= htmlspecialchars($pkg_created); ?></td>
                                            <td>
                                                <a href="editPackage.php?id=<?= $pkg_id; ?>&retreat_id=<?= (int)$retreat_id; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="deletePackage.php?id=<?= $pkg_id; ?>&retreat_id=<?= (int)$retreat_id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this package?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center">No packages found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <a href="allRetreats.php" class="btn btn-secondary">Back to Retreats</a>
            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
</div>
</body>
</html>
