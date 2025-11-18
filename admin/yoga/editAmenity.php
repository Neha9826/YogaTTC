<?php
// /admin/yoga/editAmenity.php
include 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid amenity ID.';
    header('Location: manageAmenities.php');
    exit;
}

// Fetch amenity
$stmt = $conn->prepare("SELECT id, name, icon_class FROM yoga_amenities WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    $_SESSION['flash_error'] = 'Amenity not found.';
    header('Location: manageAmenities.php');
    exit;
}
$amenity = $res->fetch_assoc();

$errors = [];
$name = $amenity['name'];
$icon_class = $amenity['icon_class'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $icon_class = trim($_POST['icon_class'] ?? 'bi-question-circle');

    if ($name === '') $errors[] = 'Name is required.';

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE yoga_amenities SET name=?, icon_class=? WHERE id=?");
        $stmt->bind_param('ssi', $name, $icon_class, $id);
        if ($stmt->execute()) {
            $_SESSION['flash_success'] = 'Amenity updated successfully.';
            header('Location: manageAmenities.php');
            exit;
        } else {
            $errors[] = 'Database error: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include '../includes/head.php'; ?>

<!-- ✅ Ensure Bootstrap Icons are loaded -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="../css/styles.css" rel="stylesheet">

<body class="sb-nav-fixed">
<?php include '../includes/navbar.php'; ?>
<div id="layoutSidenav">
    <?php include '../includes/sidebar.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4 mt-4">
                <h2>Edit Amenity</h2>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" id="editAmenityForm">
                    <div class="card mb-4">
                        <div class="card-header">Amenity Info</div>
                        <div class="card-body">

                            <!-- Name -->
                            <div class="mb-3">
                                <label class="form-label">Amenity Name</label>
                                <input type="text" name="name" id="amenity_name"
                                       class="form-control"
                                       value="<?= htmlspecialchars($name); ?>"
                                       required>
                            </div>

                            <!-- Icon -->
                            <div class="mb-3">
                                <label class="form-label">Icon Preview</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i id="iconPreview"
                                           class="<?= htmlspecialchars($icon_class ?: 'bi-question-circle'); ?>"
                                           style="font-size:1.6em;"></i>
                                    </span>
                                    <input type="text" name="icon_class" id="icon_class"
                                           class="form-control"
                                           value="<?= htmlspecialchars($icon_class); ?>"
                                           placeholder="Auto-selected icon">
                                </div>
                                <small class="text-muted">Icon updates automatically as you type the amenity name.</small>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Amenity</button>
                    <a href="manageAmenities.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
</div>

<!-- ✅ AJAX for live icon suggestion -->
<script>
document.getElementById('amenity_name').addEventListener('input', function() {
    const name = this.value.trim();
    if (!name) return;
    fetch('ajax/getAmenityIcon.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'name=' + encodeURIComponent(name)
    })
    .then(res => res.json())
    .then(data => {
        const iconInput = document.getElementById('icon_class');
        const preview = document.getElementById('iconPreview');
        if (data.icon) {
            iconInput.value = data.icon;
            preview.className = data.icon;
        }
    })
    .catch(err => console.error('Icon suggestion error:', err));
});
</script>

</body>
</html>
