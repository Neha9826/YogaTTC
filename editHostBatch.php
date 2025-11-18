<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['yoga_host_id'])) header("Location: login.php");

$host_id = $_SESSION['yoga_host_id'];
$success = $error = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die("Invalid batch ID.");
$batch_id = intval($_GET['id']);

// Fetch batch with host verification
$stmt = $conn->prepare("
SELECT b.*, p.id AS package_id, p.title AS package_title, r.id AS retreat_id, r.organization_id
FROM yoga_batches b
JOIN yoga_packages p ON b.package_id = p.id
JOIN yoga_retreats r ON p.retreat_id = r.id
JOIN organizations o ON r.organization_id = o.id
WHERE b.id = ? AND o.created_by = ?
");
$stmt->bind_param("ii", $batch_id, $host_id);
$stmt->execute();
$batch = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$batch) die("Batch not found.");

// Fetch organizations for dropdown
$org_res = $conn->query("SELECT * FROM organizations WHERE created_by=$host_id ORDER BY name ASC");

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $package_id = intval($_POST['package_id'] ?? 0);
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $capacity = intval($_POST['capacity'] ?? 0);

    // ✅ Default available slots to capacity if empty
    $available_slots = isset($_POST['available_slots']) && $_POST['available_slots'] !== ''
        ? intval($_POST['available_slots'])
        : $capacity;

    $status = $_POST['status'] ?? $batch['status'];
    $price_override = $_POST['price_override'] ?: null;
    $notes = $_POST['notes'] ?? '';

    if (!$package_id || !$start_date || !$end_date || $capacity <= 0) {
        $error = "Please fill all required fields correctly.";
    } else {
        // ✅ Adjust available slots correctly based on changes
        if ($available_slots < $capacity && $available_slots !== $batch['available_slots']) {
            $slots_diff = $capacity - $available_slots;
            $new_available = $available_slots + $slots_diff;
        } else {
            $new_available = $available_slots ?: $capacity;
        }
        if ($new_available < 0) $new_available = 0;

        // ✅ Perform update safely
        $stmt = $conn->prepare("
            UPDATE yoga_batches
            SET package_id=?, start_date=?, end_date=?, capacity=?, available_slots=?, 
                status=?, price_override=?, notes=?, updated_at=NOW()
            WHERE id=?
        ");
        $stmt->bind_param(
            "issiisssi",
            $package_id, $start_date, $end_date, $capacity, $new_available,
            $status, $price_override, $notes, $batch_id
        );

        if ($stmt->execute()) {
            $success = "Batch updated successfully!";
        } else {
            $error = "Update failed: " . $stmt->error;
        }
        $stmt->close();

        // ✅ Refresh updated batch data with all joins (to include organization_id & package_title)
        $stmt = $conn->prepare("
            SELECT b.*, p.id AS package_id, p.title AS package_title, 
                r.id AS retreat_id, r.organization_id
            FROM yoga_batches b
            JOIN yoga_packages p ON b.package_id = p.id
            JOIN yoga_retreats r ON p.retreat_id = r.id
            JOIN organizations o ON r.organization_id = o.id
            WHERE b.id = ? AND o.created_by = ?
        ");
        $stmt->bind_param("ii", $batch_id, $host_id);
        $stmt->execute();
        $batch = $stmt->get_result()->fetch_assoc();
        $stmt->close();

    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include __DIR__.'/includes/head.php'; ?>
<title>Edit Batch | <?= htmlspecialchars($batch['package_title']) ?></title>
<link rel="stylesheet" href="yoga.css">
</head>
<body class="yoga-page">

<?php include __DIR__.'/includes/fixed_social_bar.php'; ?>
<?php include __DIR__.'/yoga_navbar.php'; ?>

<div class="container-fluid">
<div class="row">
<?php include 'host_sidebar.php'; ?>
<div class="col-md-9 col-lg-10 p-4">
<h2>Edit Batch</h2>

<?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<form method="post" class="row g-3">
    <div class="col-md-5">
        <label class="form-label">Organization</label>
        <select id="org_select" class="form-select" required>
            <option value="">Select Organization</option>
            <?php while($org=$org_res->fetch_assoc()): ?>
                <option value="<?= $org['id'] ?>" <?= $batch['organization_id']==$org['id'] ? 'selected':'' ?>><?= htmlspecialchars($org['name']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-5">
        <label class="form-label">Package</label>
        <select name="package_id" id="package_select" class="form-select" required>
            <option value="<?= $batch['package_id'] ?>"><?= htmlspecialchars($batch['package_title']) ?></option>
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Start Date</label>
        <input type="date" name="start_date" class="form-control" value="<?= $batch['start_date'] ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">End Date</label>
        <input type="date" name="end_date" class="form-control" value="<?= $batch['end_date'] ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Capacity</label>
        <input type="number" name="capacity" class="form-control" min="1" value="<?= $batch['capacity'] ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Price Override (optional)</label>
        <input type="number" name="price_override" class="form-control" step="0.01" value="<?= $batch['price_override'] ?>">
    </div>
    <div class="col-md-3">
    <label class="form-label">Available Slots</label>
    <input type="number" name="available_slots" class="form-control" min="0" value="<?= htmlspecialchars($batch['available_slots']) ?>" required>
</div>

<div class="col-md-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select" required>
        <option value="open" <?= ($batch['status']==='open')?'selected':''; ?>>Open</option>
        <option value="full" <?= ($batch['status']==='full')?'selected':''; ?>>Full</option>
        <option value="closed" <?= ($batch['status']==='closed')?'selected':''; ?>>Closed</option>
    </select>
</div>

    <div class="col-5">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control"><?= htmlspecialchars($batch['notes']) ?></textarea>
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary">Update Batch</button>
        <a href="viewHostBatch.php?id=<?= $batch_id ?>" class="btn btn-secondary">Cancel</a>
    </div>
</form>
</div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#org_select').change(function(){
    let org_id = $(this).val();
    $('#package_select').html('<option>Loading...</option>');
    if(!org_id) return $('#package_select').html('<option value="">Select Package</option>');
    $.post('getPackages.php', {org_id: org_id}, function(res){
        $('#package_select').html(res);
    });
});
</script>

<?php include __DIR__.'/includes/footer.php'; ?>
</body>
</html>
