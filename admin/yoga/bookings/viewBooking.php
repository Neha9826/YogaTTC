<?php
// /admin/yoga/bookings/viewBooking.php
include '../../../session.php';
include '../../../db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid booking ID.';
    header('Location: allBookings.php');
    exit;
}

// Fetch booking with related info
$sql = "SELECT b.*, 
               r.title AS retreat_title,
               r.city AS retreat_city,
               p.title AS package_title,
               u.name AS user_name,
               u.email AS user_email,
               u.phone AS user_phone
        FROM y_bookings b
        LEFT JOIN yoga_retreats r ON b.retreat_id = r.id
        LEFT JOIN yoga_packages p ON b.package_id = p.id
        LEFT JOIN y_users u ON b.user_id = u.id
        WHERE b.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    $_SESSION['flash_error'] = 'Booking not found.';
    header('Location: allBookings.php');
    exit;
}
$booking = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<?php include '../../../includes/head.php'; ?>
<body class="sb-nav-fixed">
<?php include '../../../includes/navbar.php'; ?>
<div id="layoutSidenav">
    <?php include '../../../includes/sidebar.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4 mt-4">
                <h2>Booking Details</h2>

                <div class="card mb-4">
                    <div class="card-header">Booking Info</div>
                    <div class="card-body">
                        <p><strong>Booking Reference:</strong> <?= htmlspecialchars($booking['booking_reference']); ?></p>
                        <p><strong>Status:</strong> <?= ucfirst($booking['status']); ?></p>
                        <p><strong>Total Amount:</strong> <?= number_format($booking['total_amount'], 2) . ' ' . htmlspecialchars($booking['currency']); ?></p>
                        <p><strong>Guests:</strong> <?= $booking['guests']; ?></p>
                        <p><strong>Created At:</strong> <?= htmlspecialchars($booking['created_at']); ?></p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">User Info</div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?= htmlspecialchars($booking['user_name']); ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($booking['user_email']); ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($booking['user_phone']); ?></p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">Retreat & Package</div>
                    <div class="card-body">
                        <p><strong>Retreat:</strong> <?= htmlspecialchars($booking['retreat_title']); ?> (<?= htmlspecialchars($booking['retreat_city']); ?>)</p>
                        <p><strong>Package:</strong> <?= htmlspecialchars($booking['package_title']); ?></p>
                        <?php if (!empty($booking['batch_id'])): ?>
                            <p><strong>Batch ID:</strong> <?= $booking['batch_id']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($booking['extras'])): ?>
                    <div class="card mb-4">
                        <div class="card-header">Extras</div>
                        <div class="card-body">
                            <pre><?= htmlspecialchars($booking['extras']); ?></pre>
                        </div>
                    </div>
                <?php endif; ?>

                <a href="allBookings.php" class="btn btn-secondary">Back to Bookings</a>
            </div>
        </main>
        <?php include '../../../includes/footer.php'; ?>
    </div>
</div>
</body>
</html>
