<?php
// /admin/yoga/bookings/allBookings.php
// include '../../session.php';
include '../db.php';

// Fetch all yoga bookings with retreat & user info
$sql = "SELECT b.id, b.booking_reference, b.status, b.total_amount, b.created_at,
               r.title AS retreat_title,
               u.name AS user_name, u.email AS user_email
        FROM y_bookings b
        LEFT JOIN yoga_retreats r ON b.retreat_id = r.id
        LEFT JOIN y_users u ON b.user_id = u.id
        ORDER BY b.created_at DESC";
$result = $conn->query($sql);
?>
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
                <h2>Yoga Bookings</h2>

                <?php if (isset($_SESSION['flash_success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['flash_error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Booking Ref</th>
                                    <th>User</th>
                                    <th>Retreat</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['id']; ?></td>
                                            <td><?= htmlspecialchars($row['booking_reference']); ?></td>
                                            <td>
                                                <?= htmlspecialchars($row['user_name']); ?><br>
                                                <small><?= htmlspecialchars($row['user_email']); ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($row['retreat_title']); ?></td>
                                            <td><?= ucfirst($row['status']); ?></td>
                                            <td><?= number_format($row['total_amount'], 2); ?></td>
                                            <td><?= htmlspecialchars($row['created_at']); ?></td>
                                            <td>
                                                <a href="viewBooking.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-info">View</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="8">No bookings found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
        <?php include '../../includes/footer.php'; ?>
    </div>
</div>
</body>
</html>
