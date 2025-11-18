<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['yoga_host_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch organizations created by this host
$host_id = $_SESSION['yoga_host_id'];
$res = $conn->query("
    SELECT * FROM organizations 
    WHERE created_by = $host_id
    ORDER BY created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
    <meta charset="UTF-8">
    <title>My Organizations | Yoga Bhawna Mission</title>
    <link rel="stylesheet" href="yoga.css">
</head>
<body class="yoga-page">

<?php include __DIR__ . '/includes/fixed_social_bar.php'; ?>
<?php include __DIR__ . '/yoga_navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'host_sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 p-4">
            <h1>My Organizations</h1>
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-building me-1"></i> Registered Organizations</div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Created At</th>
                                <th>Status</th>
                                <th>Documents</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $res->fetch_assoc()): 
                            $status = $row['status'] ?? 'pending';
                        ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['city']) ?></td>
                                <td><?= $row['created_at'] ?></td>
                                <td><?= ucfirst($status) ?></td>
                                <td>
                                    <?php
                                    if (!empty($row['gst_doc'])) {
                                        $gstPath = htmlspecialchars($row['gst_doc']);
                                        echo '<a href="' . $gstPath . '" target="_blank" class="btn btn-sm btn-outline-primary mb-1">';
                                        echo '<i class="fas fa-file-invoice"></i> GST</a><br>';
                                    }
                                    if (!empty($row['msme_doc'])) {
                                        $msmePath = htmlspecialchars($row['msme_doc']);
                                        echo '<a href="' . $msmePath . '" target="_blank" class="btn btn-sm btn-outline-secondary">';
                                        echo '<i class="fas fa-industry"></i> MSME</a>';
                                    }
                                    if (empty($row['gst_doc']) && empty($row['msme_doc'])) {
                                        echo '<span class="text-muted small">No documents</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="edit_org.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning mb-1">Edit</a>
                                    <a href="delete_org.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if ($res->num_rows == 0): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">No organizations found.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
