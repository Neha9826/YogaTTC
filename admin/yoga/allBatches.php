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
                <h2>All Yoga Batches</h2>
                <?php
                include 'db.php';
                $sql = "SELECT 
            b.id,
            b.start_date,
            b.end_date,
            b.capacity,
            p.title AS package_title,
            r.title AS retreat_title
        FROM yoga_batches b
        LEFT JOIN yoga_packages p ON b.package_id = p.id
        LEFT JOIN yoga_retreats r ON p.retreat_id = r.id
        ORDER BY b.start_date DESC";

                $result = $conn->query($sql);
                ?>
                <a href="createBatch.php" class="btn btn-primary mb-3">Create New Batch</a>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Retreat</th>
                            <th>Batch Name</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Capacity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id']; ?></td>
                                    <td><?= htmlspecialchars($row['retreat_title']); ?></td>
                                    <td><?= htmlspecialchars($row['package_title']); ?></td>
                                    <td><?= date('d-m-Y', strtotime($row['start_date'])); ?></td>
                                    <td><?= date('d-m-Y', strtotime($row['end_date'])); ?></td>
                                    <td><?= $row['capacity']; ?></td>
                                    <td>
                                        <a href="editBatch.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="deleteBatch.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this batch?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7">No batches found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
        <?php include '../includes/footer.php'; ?>

    </div>
</div>
</body>
</html>
