<?php
include 'db.php';
session_start();

$errors = [];
$name = '';
$icon_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $icon_class = trim($_POST['icon_class'] ?? 'bi-question-circle');

    if ($name === '') $errors[] = 'Amenity name is required.';

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO yoga_amenities (name, icon_class, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param('ss', $name, $icon_class);

        if ($stmt->execute()) {
            $_SESSION['flash_success'] = 'Amenity created successfully!';
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
<link href="../css/styles.css" rel="stylesheet">
<body class="sb-nav-fixed">
<?php include '../includes/navbar.php'; ?>
<div id="layoutSidenav">
    <?php include '../includes/sidebar.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4 mt-4">
                <h2>Add Amenity</h2>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" id="amenityForm">
                    <div class="card mb-4">
                        <div class="card-header">Amenity Info</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Amenity Name</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i id="iconPreview" class="bi bi-question-circle" style="font-size:1.2em;"></i>
                                    </span>
                                    <input type="text" name="name" id="amenityName" class="form-control"
                                           placeholder="e.g., Yoga Hall, Wi-Fi, Meditation Room"
                                           value="<?= htmlspecialchars($name); ?>" required>
                                </div>
                                <input type="hidden" name="icon_class" id="iconClass" value="<?= htmlspecialchars($icon_class); ?>">
                                <small class="text-muted">Icon updates automatically based on the amenity name.</small>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Create Amenity</button>
                    <a href="manageAmenities.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
</div>

<script>
// === Auto-detect icon when admin types ===
document.getElementById("amenityName").addEventListener("input", function () {
    const name = this.value.trim();
    if (!name) return;

    fetch("ajax/getAmenityIcon.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "name=" + encodeURIComponent(name)
    })
    .then(res => res.json())
    .then(data => {
        const iconPreview = document.getElementById("iconPreview");
        iconPreview.className = "bi " + data.icon;
        document.getElementById("iconClass").value = data.icon;
    })
    .catch(err => console.error("Icon fetch error:", err));
});
</script>
</body>
</html>
