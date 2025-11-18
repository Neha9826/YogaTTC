<!DOCTYPE html>
<html lang="en">
<?php include '../../includes/head.php'; ?>
<body class="sb-nav-fixed">
<?php include '../../includes/navbar.php'; ?>
<div id="layoutSidenav">
    <?php include '../../includes/sidebar.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4 mt-4">
                <h2>Create New Batch</h2>
                <?php
                include '../../../db.php';

                // Fetch retreats for dropdown
                $retreats = $conn->query("SELECT id, title FROM y_retreats ORDER BY title ASC");

                if ($_SERVER["REQUEST_METHOD"] === "POST") {
                    $retreat_id = intval($_POST['retreat_id']);
                    $name = $_POST['name'];
                    $start_date = $_POST['start_date'];
                    $end_date = $_POST['end_date'];
                    $capacity = intval($_POST['capacity']);

                    $stmt = $conn->prepare("INSERT INTO y_batches (retreat_id, name, start_date, end_date, capacity) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("isssi", $retreat_id, $name, $start_date, $end_date, $capacity);

                    if ($stmt->execute()) {
                        echo '<div class="alert alert-success">Batch created successfully!</div>';
                    } else {
                        echo '<div class="alert alert-danger">Error creating batch.</div>';
                    }
                }
                ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Retreat</label>
                        <select name="retreat_id" class="form-select" required>
                            <option value="">Select Retreat</option>
                            <?php while ($r = $retreats->fetch_assoc()): ?>
                                <option value="<?= $r['id']; ?>"><?= htmlspecialchars($r['title']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Batch Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Capacity</label>
                        <input type="number" name="capacity" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success">Create Batch</button>
                </form>
            </div>
        </main>
    </div>
</div>
</body>
</html>
