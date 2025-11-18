<?php
// /admin/yoga/createPackage.php
include '../../session.php';
include '../../db.php';

$retreat_id = isset($_GET['retreat_id']) ? intval($_GET['retreat_id']) : 0;
if ($retreat_id <= 0) {
    $_SESSION['flash_error'] = 'Invalid retreat ID.';
    header('Location: allRetreats.php');
    exit;
}

// Check retreat
$stmt = $conn->prepare("SELECT title FROM yoga_retreats WHERE id = ?");
$stmt->bind_param('i', $retreat_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    $_SESSION['flash_error'] = 'Retreat not found.';
    header('Location: allRetreats.php');
    exit;
}
$retreat = $res->fetch_assoc();

$errors = [];
$name = '';
$days = '';
$price = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $days = intval($_POST['days'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if ($name === '') $errors[] = 'Name is required.';
    if ($days <= 0) $errors[] = 'Days must be positive.';
    if ($price <= 0) $errors[] = 'Price must be positive.';

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO yoga_packages (retreat_id, name, days, price, description, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param('isids', $retreat_id, $name, $days, $price, $description);
        if ($stmt->execute()) {
            $_SESSION['flash_success'] = 'Package created.';
            header('Location: managePackages.php?retreat_id=' . $retreat_id);
            exit;
        } else {
            $errors[] = 'DB error: ' . $conn->error;
        }
    }
}
?>
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
                <h2>Add Package – <?= htmlspecialchars($retreat['title']); ?></h2>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="card mb-4">
                        <div class="card-header">Package Info</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Package Name</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name); ?>" required>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Days</label>
                                    <input type="number" name="days" class="form-control" value="<?= htmlspecialchars($days); ?>" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Price</label>
                                    <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($price); ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($description); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Create Package</button>
                    <a href="managePackages.php?retreat_id=<?= $retreat_id; ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </main>
        <?php include '../../includes/footer.php'; ?>
    </div>
</div>
</body>
</html>
