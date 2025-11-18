<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['yoga_host_id'])) header("Location: login.php");
$host_id = $_SESSION['yoga_host_id'];
$success = $error = '';

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($booking_id <= 0) die("Invalid booking ID.");

// Verify booking belongs to this host
$stmt = $conn->prepare("
SELECT b.*, p.title AS package_title, r.title AS retreat_title
FROM y_bookings b
JOIN yoga_packages p ON b.package_id = p.id
JOIN yoga_retreats r ON b.retreat_id = r.id
JOIN organizations o ON r.organization_id = o.id
WHERE b.id = ? AND o.created_by = ?
");
$stmt->bind_param("ii", $booking_id, $host_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) die("Unauthorized or invalid booking.");

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paid_amount = floatval($_POST['paid_amount'] ?? 0);
    $extras_raw  = trim($_POST['extras'] ?? '');
    $status      = $_POST['status'] ?? 'pending';

    // Convert empty string to NULL
    $extras = ($extras_raw === '') ? null : $extras_raw;

    // ✅ Use NULL-safe logic for binding
    $stmt = $conn->prepare("UPDATE y_bookings SET paid_amount=?, extras=?, status=?, updated_at=NOW() WHERE id=?");

    if ($extras === null) {
        // Bind with NULL properly
        $null = null;
        $stmt->bind_param("dssi", $paid_amount, $null, $status, $booking_id);
        $stmt->send_long_data(1, ""); // sends empty data for NULL-safe binding
    } else {
        $stmt->bind_param("dssi", $paid_amount, $extras, $status, $booking_id);
    }

    if ($stmt->execute()) {
        $success = "Booking updated successfully!";
    } else {
        $error = "Error updating booking: " . $stmt->error;
    }
    $stmt->close();

    // Refresh updated booking data
    $stmt = $conn->prepare("
    SELECT 
        b.*, 
        p.title AS package_title, 
        r.title AS retreat_title
    FROM y_bookings b
    LEFT JOIN yoga_packages p ON b.package_id = p.id
    LEFT JOIN yoga_retreats r ON b.retreat_id = r.id
    WHERE b.id=?
");

    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$booking) die('Booking not found.');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__.'/includes/head.php'; ?>
  <title>Edit Booking</title>
  <link rel="stylesheet" href="yoga.css">
</head>
<body class="yoga-page">

<?php include __DIR__.'/yoga_navbar.php'; ?>

<div class="container-fluid">
<div class="row">
<?php include 'host_sidebar.php'; ?>
<div class="col-md-9 col-lg-10 p-4">
  <h2>Edit Booking</h2>
  <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
  <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

  <form method="post" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Package</label>
      <input type="text" class="form-control" readonly value="<?= htmlspecialchars($booking['package_title']) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Retreat</label>
      <input type="text" class="form-control" readonly value="<?= htmlspecialchars($booking['retreat_title']) ?>">
    </div>

    <div class="col-md-4">
      <label class="form-label">Total Amount</label>
      <input type="text" class="form-control" readonly value="₹<?= number_format((float)$booking['total_amount'], 2) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Paid Amount</label>
      <input type="number" step="0.01" name="paid_amount" class="form-control" value="<?= htmlspecialchars($booking['paid_amount']) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Status</label>
      <select name="status" class="form-select">
        <option value="pending" <?= $booking['status']=='pending'?'selected':'' ?>>Pending</option>
        <option value="confirmed" <?= $booking['status']=='confirmed'?'selected':'' ?>>Confirmed</option>
        <option value="cancelled" <?= $booking['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
        <option value="completed" <?= $booking['status']=='completed'?'selected':'' ?>>Completed</option>
      </select>
    </div>

    <div class="col-12">
      <label class="form-label">Extras / Notes</label>
      <textarea name="extras" class="form-control" rows="3"><?= htmlspecialchars($booking['extras'] ?? '') ?></textarea>
    </div>

    <div class="col-12">
      <button type="submit" class="btn btn-primary">Save Changes</button>
      <a href="y_HostBookingRequests.php" class="btn btn-secondary">Back</a>
    </div>
  </form>
</div>
</div>
</div>

<?php include __DIR__.'/includes/footer.php'; ?>
</body>
</html>
