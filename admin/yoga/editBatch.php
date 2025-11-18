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
                <h2>Edit Batch</h2>
                <?php
                include 'db.php';

                if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                    echo '<div class="alert alert-danger">Invalid batch ID.</div>';
                    exit;
                }

                $id = (int)$_GET['id'];

                // Fetch all packages with their retreat titles
                $pkgSql = "SELECT p.id AS package_id, 
                                  p.title AS package_title, 
                                  r.title AS retreat_title 
                           FROM yoga_packages p
                           LEFT JOIN yoga_retreats r ON p.retreat_id = r.id
                           ORDER BY r.title ASC";
                $packages = $conn->query($pkgSql);

                // Fetch batch details
                $stmt = $conn->prepare("SELECT * FROM yoga_batches WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $batch = $stmt->get_result()->fetch_assoc();

                if (!$batch) {
                    echo '<div class="alert alert-danger">Batch not found.</div>';
                    exit;
                }

                if ($_SERVER["REQUEST_METHOD"] === "POST") {
                    $package_id = intval($_POST['package_id']);
                    $start_date = $_POST['start_date'];
                    $end_date = $_POST['end_date'];
                    $capacity = intval($_POST['capacity']);
                    $available_slots = intval($_POST['available_slots']);
                    $status = $_POST['status'];

                    $stmt = $conn->prepare("UPDATE yoga_batches 
                                            SET package_id = ?, start_date = ?, end_date = ?, capacity = ?, 
                                                available_slots = ?, status = ?
                                            WHERE id = ?");
                    $stmt->bind_param("issii si", $package_id, $start_date, $end_date, $capacity, $available_slots, $status, $id);

                    if ($stmt->execute()) {
                        echo '<div class="alert alert-success">Batch updated successfully!</div>';
                        // Update local array for re-display
                        $batch['package_id'] = $package_id;
                        $batch['start_date'] = $start_date;
                        $batch['end_date'] = $end_date;
                        $batch['capacity'] = $capacity;
                        $batch['available_slots'] = $available_slots;
                        $batch['status'] = $status;
                    } else {
                        echo '<div class="alert alert-danger">Error updating batch: ' . $conn->error . '</div>';
                    }
                }
                ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Retreat & Package</label>
                        <select name="package_id" class="form-select" required>
                            <option value="">Select Retreat</option>
                            <?php while ($p = $packages->fetch_assoc()): ?>
                                <option value="<?= $p['package_id']; ?>" 
                                    <?= ($batch['package_id'] == $p['package_id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($p['retreat_title'] . " → " . $p['package_title']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" 
                               value="<?= htmlspecialchars($batch['start_date']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" 
                               value="<?= htmlspecialchars($batch['end_date']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Capacity</label>
                        <input type="number" name="capacity" class="form-control" 
                               value="<?= htmlspecialchars($batch['capacity']); ?>" required>
                    </div>

                    <!-- ✅ New Field: Available Slots -->
                    <div class="mb-3">
                        <label class="form-label">Available Slots</label>
                        <input type="number" name="available_slots" class="form-control"
                               value="<?= htmlspecialchars($batch['available_slots'] ?? 0); ?>" min="0" required>
                    </div>

                    <!-- ✅ New Field: Status -->
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="open" <?= ($batch['status'] === 'open') ? 'selected' : ''; ?>>Open</option>
                            <option value="full" <?= ($batch['status'] === 'full') ? 'selected' : ''; ?>>Full</option>
                            <option value="closed" <?= ($batch['status'] === 'closed') ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Batch</button>
                    <a href="allBatches.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
</div>
</body>
</html>
