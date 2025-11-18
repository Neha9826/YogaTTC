<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['yoga_host_id'])) header("Location: login.php");
$host_id = $_SESSION['yoga_host_id'];

// --- Fetch all bookings for this host ---
$sql = "
SELECT b.*, p.title AS package_title, r.title AS retreat_title, 
       u.name AS user_name, u.email, u.phone
FROM y_bookings b
JOIN yoga_packages p ON b.package_id = p.id
JOIN yoga_retreats r ON b.retreat_id = r.id
JOIN organizations o ON r.organization_id = o.id
JOIN y_users u ON b.user_id = u.id
WHERE o.created_by = ?
ORDER BY b.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $host_id);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__.'/includes/head.php'; ?>
  <title>Host Booking Requests</title>
  <link rel="stylesheet" href="yoga.css">
  <!-- Simple-DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css">
</head>
<body class="yoga-page">

<?php include __DIR__.'/yoga_navbar.php'; ?>

<div class="container-fluid">
  <div class="row">
    <?php include 'host_sidebar.php'; ?>

    <div class="col-md-9 col-lg-10 p-4">
      <h2 class="mb-3">Booking Requests</h2>

      <!-- Live Search Box -->
      <!-- <div class="mb-3">
        <input type="text" id="searchInput" class="form-control" placeholder="Search by guest, email, phone, package, retreat, status, or date...">
      </div> -->

      <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle" id="bookingsTable">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Package</th>
              <th>Guest</th>
              <th>Total</th>
              <th>Paid</th>
              <th>Status</th>
              <th>Extras</th>
              <th>Created</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($bookings)): $i = 1; foreach ($bookings as $b): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($b['package_title']) ?><br>
                  <small class="text-muted"><?= htmlspecialchars($b['retreat_title']) ?></small>
              </td>
              <td>
                <?= htmlspecialchars($b['user_name']) ?><br>
                <small><?= htmlspecialchars($b['email']) ?></small><br>
                <small><?= htmlspecialchars($b['phone']) ?></small>
              </td>
              <td>₹<?= number_format((float)$b['total_amount'], 2) ?></td>
              <td>₹<?= number_format((float)$b['paid_amount'], 2) ?></td>
              <td>
                <span class="badge bg-<?= $b['status']=='confirmed'?'success':($b['status']=='cancelled'?'danger':'warning') ?>">
                  <?= htmlspecialchars($b['status']) ?>
                </span>
              </td>
              <td><?= nl2br(htmlspecialchars($b['extras'] ?? '')) ?></td>
              <td><?= htmlspecialchars(date('Y-m-d', strtotime($b['created_at']))) ?></td>
              <td><a href="editHostBookings.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-primary">Edit</a></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="9" class="text-center text-muted">No booking requests yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__.'/includes/footer.php'; ?>

<!-- Simple-DataTables JS -->
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" ></script>
<script>
// Initialize Simple-DataTable
const dataTable = new simpleDatatables.DataTable("#bookingsTable", {
  searchable: true,
  perPage: 10,
  fixedHeight: true,
  labels: {
    placeholder: "Search...",
    noRows: "No booking requests found",
  },
});

// Live search (works on all columns including dates)
document.getElementById("searchInput").addEventListener("input", function() {
  const query = this.value.trim().toLowerCase();
  dataTable.search(query);
});
</script>
</body>
</html>
