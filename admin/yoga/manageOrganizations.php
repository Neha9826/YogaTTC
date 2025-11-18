<?php
// Use BASE_PATH for server-side includes
// include_once __DIR__ . '../config.php';

include  '../session.php';
include  '../db.php';

// Fetch organizations with creator name
$res = $conn->query("
  SELECT o.*, y_users.name AS creator_name 
  FROM organizations o 
  LEFT JOIN y_users ON o.created_by = y_users.id 
  ORDER BY created_at DESC
");

// For URLs in HTML (like links, hrefs)
// $adminURL = $baseURL;
?>
<!DOCTYPE html>
<html lang="en">
<?php include  '../includes/head.php'; ?>
<link href="../css/styles.css" rel="stylesheet">
<body class="sb-nav-fixed">
<?php include  '../includes/navbar.php'; ?>
<div id="layoutSidenav">
    <?php include  '../includes/sidebar.php'; ?>

    <div id="layoutSidenav_content">
      <main>
        <div class="container-fluid px-4 mt-4">
          <h1 class="mt-4">Organizations</h1>
          <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="<?= $adminURL ?>dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Organizations</li>
          </ol>

          <div class="card mb-4">
            <div class="card-header"><i class="fas fa-table me-1"></i> Organizations List</div>
            <div class="card-body">
              <table id="datatablesSimple" class="table table-bordered">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Location</th>
                    <th>Created By</th>
                    <th>Created At</th>
                    <th>Status</th>
                    <th>Documents</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                <?php while ($row = $res->fetch_assoc()): 
                    $status = isset($row['status']) ? $row['status'] : 'pending';
                ?>
                  <tr>
                    <td><?= $row['id']; ?></td>
                    <td><?= htmlspecialchars($row['name']); ?></td>
                    <td><?= htmlspecialchars($row['contact_email'] . ' / ' . $row['contact_phone']); ?></td>
                    <td><?= htmlspecialchars($row['continent'].' / '.$row['country'].' / '.$row['state'].' / '.$row['city']); ?></td>
                    <td><?= $row['creator_name'] ?: 'N/A'; ?></td>
                    <td><?= $row['created_at']; ?></td>
                    <td><?= ucfirst($status); ?></td>
                    <td>
                      <?php
                        // GST doc
                        if (!empty($row['gst_doc'])) {
                            $gstPath = htmlspecialchars($row['gst_doc']);
                            echo '<a href="' . $gstPath . '" target="_blank" class="btn btn-sm btn-outline-primary mb-1">';
                            echo '<i class="fas fa-file-invoice"></i> GST</a><br>';
                        }

                        // MSME doc
                        if (!empty($row['msme_doc'])) {
                            $msmePath = htmlspecialchars($row['msme_doc']);
                            echo '<a href="' . $msmePath . '" target="_blank" class="btn btn-sm btn-outline-secondary">';
                            echo '<i class="fas fa-industry"></i> MSME</a>';
                        }

                        // No documents
                        if (empty($row['gst_doc']) && empty($row['msme_doc'])) {
                            echo '<span class="text-muted small">No documents</span>';
                        }
                      ?>
                    </td>
                    <td>
                      <form method="POST" action="yoga/organization/updateOrganizationStatus.php" style="display:flex;">
    <input type="hidden" name="org_id" value="<?= $row['id']; ?>">
    <select name="status" class="form-select form-select-sm me-2" required>
        <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="approved" <?= $status == 'approved' ? 'selected' : '' ?>>Approved</option>
        <option value="denied" <?= $status == 'denied' ? 'selected' : '' ?>>Rejected</option>
    </select>
    <button type="submit" class="btn btn-sm btn-success">Update</button>
</form>

                    </td>
                  </tr>
                <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </main>
      <?php include  '../includes/footer.php'; ?>
    </div>
</div>
<?php include  '../includes/script.php'; ?>
</body>
</html>