<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['yoga_host_id'])) header("Location: login.php");
$host_id = $_SESSION['yoga_host_id'];

// Fetch queries belonging to host's organization
$sql = "
SELECT q.*, p.title AS package_title, r.title AS retreat_title
FROM y_query q
JOIN yoga_packages p ON q.package_id = p.id
JOIN yoga_retreats r ON p.retreat_id = r.id
JOIN organizations o ON r.organization_id = o.id
WHERE o.created_by = ?
ORDER BY q.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $host_id);
$stmt->execute();
$queries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__.'/includes/head.php'; ?>
  <title>Host Queries</title>
  <link rel="stylesheet" href="yoga.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css">
  <style>
    .badge-status { text-transform: capitalize; }
    .modal-body small { color: #6c757d; }
  </style>
</head>
<body class="yoga-page">

<?php include __DIR__.'/yoga_navbar.php'; ?>

<div class="container-fluid">
  <div class="row">
    <?php include 'host_sidebar.php'; ?>

    <div class="col-md-9 col-lg-10 p-4">
      <h2 class="mb-3">Guest Queries</h2>

      <!-- Live search -->
      <div class="mb-3">
        <input type="text" id="searchInput" class="form-control" placeholder="Search by guest name, email, phone, package, retreat, or status...">
      </div>

      <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle" id="queriesTable">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Guest</th>
              <th>Package</th>
              <th>Message</th>
              <th>Status</th>
              <th>Submitted</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($queries)): $i=1; foreach($queries as $q): ?>
              <?php
                $status = $q['host_status'] ?? 'pending';
                $badgeClass = [
                  'pending' => 'warning',
                  'contacted' => 'info',
                  'converted' => 'success'
                ][$status] ?? 'secondary';
              ?>
              <tr data-id="<?= $q['id'] ?>">
                <td><?= $i++ ?></td>
                <td>
                  <strong><?= htmlspecialchars($q['name']) ?></strong><br>
                  <small><?= htmlspecialchars($q['email']) ?></small><br>
                  <small><?= htmlspecialchars($q['phone']) ?></small>
                </td>
                <td>
                  <?= htmlspecialchars($q['package_title']) ?><br>
                  <small class="text-muted"><?= htmlspecialchars($q['retreat_title']) ?></small>
                </td>
                <td style="max-width:250px"><?= nl2br(htmlspecialchars($q['message'] ?? '')) ?></td>
                <td class="status-cell">
                  <span class="badge bg-<?= $badgeClass ?> badge-status"><?= ucfirst($status) ?></span>
                </td>
                <td><?= htmlspecialchars(date('Y-m-d', strtotime($q['created_at']))) ?></td>
                <td>
                  <select class="form-select form-select-sm updateStatus d-inline-block w-auto me-2" data-id="<?= $q['id'] ?>">
                    <option value="pending" <?= $status=='pending'?'selected':'' ?>>Pending</option>
                    <option value="contacted" <?= $status=='contacted'?'selected':'' ?>>Contacted</option>
                    <option value="converted" <?= $status=='converted'?'selected':'' ?>>Converted</option>
                  </select>
                  <button type="button" class="btn btn-sm btn-outline-info viewQueryBtn" 
                          data-bs-toggle="modal" 
                          data-bs-target="#viewQueryModal"
                          data-name="<?= htmlspecialchars($q['name']) ?>"
                          data-email="<?= htmlspecialchars($q['email']) ?>"
                          data-phone="<?= htmlspecialchars($q['phone']) ?>"
                          data-message="<?= htmlspecialchars($q['message']) ?>"
                          data-package="<?= htmlspecialchars($q['package_title']) ?>"
                          data-retreat="<?= htmlspecialchars($q['retreat_title']) ?>">
                    View
                  </button>
                </td>
              </tr>
            <?php endforeach; else: ?>
              <tr><td colspan="7" class="text-center text-muted">No queries yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Query Details Modal -->
<div class="modal fade" id="viewQueryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Guest Query</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>Name:</strong> <span id="qName"></span></p>
        <p><strong>Email:</strong> <span id="qEmail"></span></p>
        <p><strong>Phone:</strong> <span id="qPhone"></span></p>
        <p><strong>Package:</strong> <span id="qPackage"></span></p>
        <p><strong>Retreat:</strong> <span id="qRetreat"></span></p>
        <p><strong>Message:</strong><br><span id="qMessage"></span></p>
        <hr>
        <div class="text-center">
          <a id="whatsappLink" class="btn btn-success me-2" target="_blank">
            <i class="bi bi-whatsapp"></i> WhatsApp
          </a>
          <a id="emailLink" class="btn btn-primary" target="_blank">
            <i class="bi bi-envelope"></i> Email
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__.'/includes/footer.php'; ?>

<!-- Simple-DataTables -->
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" ></script>
<script>
const dataTable = new simpleDatatables.DataTable("#queriesTable", {
  searchable: true,
  perPage: 10,
  fixedHeight: true,
  labels: { placeholder: "Search...", noRows: "No queries found" }
});

// live search input
document.getElementById("searchInput").addEventListener("input", function() {
  dataTable.search(this.value.toLowerCase());
});

// handle view modal
document.querySelectorAll(".viewQueryBtn").forEach(btn => {
  btn.addEventListener("click", function() {
    document.getElementById("qName").textContent = this.dataset.name;
    document.getElementById("qEmail").textContent = this.dataset.email;
    document.getElementById("qPhone").textContent = this.dataset.phone;
    document.getElementById("qPackage").textContent = this.dataset.package;
    document.getElementById("qRetreat").textContent = this.dataset.retreat;
    document.getElementById("qMessage").textContent = this.dataset.message;

    const phone = this.dataset.phone.replace(/[^0-9]/g, '');
    const msg = encodeURIComponent(`Hello ${this.dataset.name}, regarding your query for ${this.dataset.package}.`);
    document.getElementById("whatsappLink").href = `https://wa.me/${phone}?text=${msg}`;
    document.getElementById("emailLink").href = `mailto:${this.dataset.email}?subject=Regarding your yoga retreat query`;
  });
});

// update status (AJAX + badge update instantly)
document.querySelectorAll(".updateStatus").forEach(sel => {
  sel.addEventListener("change", async function() {
    const id = this.dataset.id;
    const val = this.value;
    const row = this.closest("tr");
    const badge = row.querySelector(".badge-status");

    try {
      const res = await fetch("updateQueryStatus.php", {
        method: "POST",
        headers: {"Content-Type":"application/x-www-form-urlencoded"},
        body: "id="+id+"&status="+val
      });
      const j = await res.json();
      if (j.success) {
        // change badge instantly
        const map = { pending:"warning", contacted:"info", converted:"success" };
        badge.className = "badge badge-status bg-" + (map[val] || "secondary");
        badge.textContent = val.charAt(0).toUpperCase() + val.slice(1);

        // smooth highlight animation
        row.style.transition = "background 0.5s";
        row.style.background = "#d4edda";
        setTimeout(()=>row.style.background="",800);
      } else {
        alert("Failed to update status: " + j.msg);
      }
    } catch(err) {
      alert("Network error.");
      console.error(err);
    }
  });
});
</script>
</body>
</html>
