<?php
// /admin/yoga/manageInstructors.php
// include '../../session.php';
include 'db.php';

// Fetch all instructors
$instructors = $conn->query("SELECT * FROM yoga_instructors ORDER BY created_at DESC");
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
                <h2>Manage Instructors</h2>

                <?php if (isset($_SESSION['flash_success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['flash_error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
                <?php endif; ?>

                <a href="createInstructor.php" class="btn btn-primary mb-3">Add New Instructor</a>

                <div class="card mb-4">
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Photo</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($instructors && $instructors->num_rows > 0): ?>
                                    <?php while ($ins = $instructors->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($ins['name']); ?></td>
                                            <td>
                                                <?php if ($ins['photo']): ?>
                                                    <img src="../../<?= htmlspecialchars($ins['photo']); ?>" alt="<?= htmlspecialchars($ins['name']); ?>" style="height:50px;">
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($ins['created_at']); ?></td>
                                            <td>
                                                <a href="editInstructor.php?id=<?= $ins['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="deleteInstructor.php?id=<?= $ins['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this instructor?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4">No instructors found.</td></tr>
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
