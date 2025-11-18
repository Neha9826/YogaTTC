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
                <h2>View Yoga User</h2>
                <?php
                include '../../../db.php';

                if (!isset($_GET['id']) || empty($_GET['id'])) {
                    echo '<div class="alert alert-danger">Invalid User ID.</div>';
                } else {
                    $user_id = intval($_GET['id']);
                    $stmt = $conn->prepare("SELECT * FROM y_users WHERE id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $user = $result->fetch_assoc();
                    } else {
                        echo '<div class="alert alert-warning">User not found.</div>';
                    }
                }
                ?>
                <?php if (!empty($user)): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <p><strong>ID:</strong> <?= $user['id']; ?></p>
                            <p><strong>Name:</strong> <?= htmlspecialchars($user['name']); ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
                            <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone']); ?></p>
                            <p><strong>Organization:</strong> <?= htmlspecialchars($user['organization_id']); ?></p>
                            <p><strong>Role:</strong> <?= htmlspecialchars($user['role']); ?></p>
                            <p><strong>Registered:</strong> <?= date('Y-m-d H:i:s', strtotime($user['created_at'])); ?></p>
                            <p><strong>Account Status:</strong> <?= $user['is_active'] == 1 ? 'Active' : 'Inactive'; ?></p>

                            <hr>
                            <h5>Verification Details</h5>
                            <p><strong>Type:</strong> <?= htmlspecialchars($user['verification_type'] ?? 'N/A'); ?></p>
                            <p><strong>Number:</strong> <?= htmlspecialchars($user['verification_number'] ?? 'N/A'); ?></p>
                            <p><strong>Status:</strong> 
                                <?php 
                                if ($user['verification_status'] == 'verified') {
                                    echo '<span class="badge bg-success">Verified</span>';
                                } elseif ($user['verification_status'] == 'failed') {
                                    echo '<span class="badge bg-danger">Failed</span>';
                                } else {
                                    echo '<span class="badge bg-warning text-dark">Pending</span>';
                                }
                                ?>
                            </p>
                            <?php if (!empty($user['verification_file'])): ?>
                                <p><strong>Uploaded File:</strong> 
                                    <a href="<?= htmlspecialchars($user['verification_file']); ?>" target="_blank">
                                        View Document
                                    </a>
                                </p>
                            <?php else: ?>
                                <p><strong>Uploaded File:</strong> N/A</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="allUsers.php" class="btn btn-secondary">Back to Users</a>
                    <a href="editUser.php?id=<?= $user['id']; ?>" class="btn btn-sm btn-warning">Edit User</a>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>
</body>
</html>
