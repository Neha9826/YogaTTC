<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['yoga_host_id'])) header("Location: login.php");

$host_id = $_SESSION['yoga_host_id'];
$success = $error = '';

// Prevent undefined variable warnings
$batch = [
    'organization_id' => '',
    'retreat_id' => '',
    'retreat_title' => '',
    'package_id' => '',
    'package_title' => ''
];

// Fetch organizations for dropdown
$org_res = $conn->query("SELECT * FROM organizations WHERE created_by=$host_id ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $package_id = intval($_POST['package_id'] ?? 0);
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $capacity = intval($_POST['capacity'] ?? 0);
    $available_slots = isset($_POST['available_slots']) && $_POST['available_slots'] !== ''
    ? intval($_POST['available_slots'])
    : $capacity;
    $status = $_POST['status'] ?? 'open';
    $price_override = $_POST['price_override'] ?: null;
    $notes = $_POST['notes'] ?? '';

    if (!$package_id || !$start_date || !$end_date || $capacity <= 0) {
        $error = "Please fill all required fields correctly.";
    } else {
        // Check overlapping batches
        $stmt = $conn->prepare("
            SELECT id FROM yoga_batches 
            WHERE package_id=? 
              AND ((start_date<=? AND end_date>=?) OR (start_date<=? AND end_date>=?))
            LIMIT 1
        ");
        $stmt->bind_param("issss", $package_id, $start_date, $start_date, $end_date, $end_date);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $error = "Another batch for this package overlaps with selected dates.";
        } else {
            $stmt->close();

            // Insert new batch
            $stmt = $conn->prepare("
                INSERT INTO yoga_batches 
                (package_id, start_date, end_date, capacity, available_slots, status, price_override, notes, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param(
                "issiisss",
                $package_id, $start_date, $end_date, $capacity, $available_slots,
                $status, $price_override, $notes
            );

            if ($stmt->execute()) $success = "Batch created successfully!";
            else $error = "Error: " . $stmt->error;

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__.'/includes/head.php'; ?>
    <title>Create Batch</title>
    <link rel="stylesheet" href="yoga.css">
</head>
<body class="yoga-page">
<?php include __DIR__.'/includes/fixed_social_bar.php'; ?>
<?php include __DIR__.'/yoga_navbar.php'; ?>

<div class="container-fluid">
<div class="row">
<?php include 'host_sidebar.php'; ?>
<div class="col-md-9 col-lg-10 p-4">
<h2>Create Batch</h2>
<?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<form method="post" class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Organization</label>
        <select id="org_select" class="form-select" required>
            <option value="">Select Organization</option>
            <?php while($org = $org_res->fetch_assoc()): ?>
                <option value="<?= $org['id'] ?>" <?= $batch['organization_id']==$org['id']?'selected':'' ?>>
                    <?= htmlspecialchars($org['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Retreat</label>
        <select id="retreat_select" class="form-select" required>
            <option value="<?= $batch['retreat_id'] ?>"><?= htmlspecialchars($batch['retreat_title']??'Select Retreat') ?></option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Package</label>
        <select name="package_id" id="package_select" class="form-select" required>
            <option value="<?= $batch['package_id'] ?>"><?= htmlspecialchars($batch['package_title']) ?></option>
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Start Date</label>
        <input type="date" name="start_date" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">End Date</label>
        <input type="date" name="end_date" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Capacity</label>
        <input type="number" name="capacity" class="form-control" min="1" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Price Override (optional)</label>
        <input type="number" name="price_override" class="form-control" step="0.01" placeholder="Leave empty for package price">
    </div>
    <div class="col-md-3">
    <label class="form-label">Available Slots</label>
    <input type="number" name="available_slots" class="form-control" min="0" placeholder="Defaults to capacity">
</div>

<div class="col-md-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select" required>
        <option value="open" selected>Open</option>
        <option value="full">Full</option>
        <option value="closed">Closed</option>
    </select>
</div>

    <div class="col-5">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control"></textarea>
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary">Create Batch</button>
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

$(document).ready(function(){

    // Step 2a: When organization changes, load retreats
    $('#org_select').change(function(){
        let org_id = $(this).val();
        $('#retreat_select').html('<option>Loading...</option>');
        $('#package_select').html('<option value="">Select Package</option>');

        if(!org_id) return $('#retreat_select').html('<option value="">Select Retreat</option>');

        $.post('getRetreats.php', {org_id: org_id}, function(res){
            $('#retreat_select').html(res);
        });
    });

    // Step 2b: When retreat changes, load packages
    $('#retreat_select').change(function(){
        let retreat_id = $(this).val();
        $('#package_select').html('<option>Loading...</option>');

        if(!retreat_id) return $('#package_select').html('<option value="">Select Package</option>');

        $.post('getPackages.php', {retreat_id: retreat_id}, function(res){
            $('#package_select').html(res);
        });
    });

});
</script>


<?php include __DIR__.'/includes/footer.php'; ?>
</body>
</html>
