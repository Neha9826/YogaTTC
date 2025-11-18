<?php
session_start();

include __DIR__ . '/db.php';

if (!isset($_SESSION['yoga_host_id'])) {
    header("Location: login.php");
    exit;
}

$host_id = $_SESSION['yoga_host_id'];

// Fetch all retreats with first image using LEFT JOIN
$sql = "
    SELECT r.*, o.name AS org_name, ri.image_path
    FROM yoga_retreats r
    JOIN organizations o ON r.organization_id = o.id
    LEFT JOIN (
        SELECT retreat_id, image_path
        FROM yoga_retreat_images
        WHERE is_primary = 1
        UNION ALL
        SELECT retreat_id, image_path
        FROM yoga_retreat_images
        WHERE is_primary = 0
    ) ri ON ri.retreat_id = r.id
    WHERE o.created_by = ?
    GROUP BY r.id
    ORDER BY r.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $host_id);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
    <title>My Retreats</title>
    <link rel="stylesheet" href="yoga.css">
</head>
<body class="yoga-page">

    
    <?php include __DIR__ . '/includes/fixed_social_bar.php'; ?>
    <?php include __DIR__ . '/yoga_navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">

            <?php include 'host_sidebar.php'; ?>

            <div class="col-md-9 col-lg-10 p-4">
                <h2>My Retreats</h2>
                <a href="create_retreat.php" class="btn btn-primary mb-3">➕ Add Retreat</a>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Organization</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $res->fetch_assoc()): ?>
                            <?php
                                $img_path = (!empty($row['image_path']) && file_exists(__DIR__.'/'.$row['image_path'])) 
                                    ? $row['image_path'] 
                                    : '../assets/default_retreat.png';
                            ?>
                            <tr>
                                <td class="text-center">
                                    <img src="<?= $img_path ?>" class="rounded" width="80" height="50" alt="Retreat Image">
                                </td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['org_name']) ?></td>
                                <td><?= date('d M Y', strtotime($row['start_date'] ?? $row['created_at'])) ?></td>
                                <td><?= date('d M Y', strtotime($row['end_date'] ?? $row['created_at'])) ?></td>
                                <td>₹<?= number_format($row['min_price'],2) ?> - ₹<?= number_format($row['max_price'],2) ?></td>
                                <td>
                                    <a href="viewHostRetreat.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">View</a>
                                    <a href="editHostRetreat.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="deleteHostRetreat.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
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
