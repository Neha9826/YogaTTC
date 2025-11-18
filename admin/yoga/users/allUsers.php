<!DOCTYPE html>
<html lang="en">
<?php include '../../includes/head.php'; ?>
<link href="../../css/styles.css" rel="stylesheet">
<body class="sb-nav-fixed">
<?php include '../../includes/navbar.php'; ?>
<div id="layoutSidenav">
    <?php include '../../includes/sidebar.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4 mt-4">
                <h2>Yoga Users</h2>
                <?php
                // ✅ keep the old working db path
                include '../../../db.php';

                $sql = "SELECT * FROM y_users ORDER BY created_at DESC";
                $result = $conn->query($sql);
                ?>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Organization</th>
                            <th>Role</th>
                            <th>Registered On</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id']; ?></td>
                                    <td><?= htmlspecialchars($row['name']); ?></td>
                                    <td><?= htmlspecialchars($row['email']); ?></td>
                                    <td><?= htmlspecialchars($row['phone']); ?></td>
                                    <td><?= htmlspecialchars($row['organization_id']); ?></td>
                                    <td><?= htmlspecialchars($row['role']); ?></td>
                                    <td><?= date('Y-m-d', strtotime($row['created_at'])); ?></td>
                                    <td><?= htmlspecialchars($row['verification_status']); ?></td>
                                    <td>
                                        <a href="viewUser.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-info">View</a>
                                        <a href="editUser.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="deleteUser.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8">No yoga users found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
        <?php include '../../includes/footer.php'; ?>
    </div>
</div>
<?php include '../../includes/script.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>
