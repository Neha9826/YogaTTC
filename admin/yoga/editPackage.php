<?php
// /admin/yoga/editPackage.php
include '../db.php';
session_start();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$retreat_id = isset($_GET['retreat_id']) ? intval($_GET['retreat_id']) : 0;

// ✅ If retreat_id is missing, fetch it from the package itself
if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid package ID.';
    header('Location: allPackages.php');
    exit;
}

// Fetch package (and retreat info together)
$stmt = $conn->prepare("
    SELECT p.*, r.title AS retreat_title
    FROM yoga_packages p
    LEFT JOIN yoga_retreats r ON p.retreat_id = r.id
    WHERE p.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    $_SESSION['flash_error'] = 'Package not found.';
    header('Location: allPackages.php');
    exit;
}

$pkg = $res->fetch_assoc();
$retreat_id = $pkg['retreat_id'] ?? 0;
$retreat = ['title' => $pkg['retreat_title'] ?? '—'];

// Default values
$errors = [];
$title = $pkg['title'];
$slug = $pkg['slug'];
$description = $pkg['description'];
$price_per_person = $pkg['price_per_person'];
$min_persons = $pkg['min_persons'];
$max_persons = $pkg['max_persons'];
$nights = $pkg['nights'];
$meals_included = $pkg['meals_included'];
$is_published = $pkg['is_published'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price_per_person = floatval($_POST['price_per_person'] ?? 0);
    $min_persons = intval($_POST['min_persons'] ?? 1);
    $max_persons = intval($_POST['max_persons'] ?? 1);
    $nights = intval($_POST['nights'] ?? 0);
    $meals_included = isset($_POST['meals_included']) ? (int)$_POST['meals_included'] : 1;
    $is_published = isset($_POST['is_published']) ? (int)$_POST['is_published'] : 1;

    if ($title === '') $errors[] = 'Package title is required.';
    if ($slug === '') $errors[] = 'Slug is required.';
    if ($price_per_person <= 0) $errors[] = 'Price must be greater than zero.';

    if (empty($errors)) {
        // ✅ No retreat_id restriction — works for allPackages too
        $stmt = $conn->prepare("UPDATE yoga_packages 
            SET title = ?, slug = ?, description = ?, price_per_person = ?, 
                min_persons = ?, max_persons = ?, nights = ?, meals_included = ?, is_published = ? 
            WHERE id = ?");
        $stmt->bind_param(
            'sssdiidiii',
            $title, $slug, $description, $price_per_person,
            $min_persons, $max_persons, $nights, $meals_included, $is_published,
            $id
        );

        if ($stmt->execute()) {
            $_SESSION['flash_success'] = 'Package updated successfully.';

            // ✅ Redirect back to correct page depending on origin
            if ($retreat_id > 0) {
                header('Location: managePackages.php?retreat_id=' . $retreat_id);
            } else {
                header('Location: allPackages.php');
            }
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
                <h2>Edit Package – <?= htmlspecialchars($retreat['title']); ?></h2>

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
                        <div class="card-header">Package Information</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Package Title</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($title); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($slug); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($description); ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Price (Per Person)</label>
                                    <input type="number" step="0.01" name="price_per_person" class="form-control" value="<?= htmlspecialchars($price_per_person); ?>" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Min Persons</label>
                                    <input type="number" name="min_persons" class="form-control" value="<?= htmlspecialchars($min_persons); ?>" min="1">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Max Persons</label>
                                    <input type="number" name="max_persons" class="form-control" value="<?= htmlspecialchars($max_persons); ?>" min="1">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Nights</label>
                                    <input type="number" name="nights" class="form-control" value="<?= htmlspecialchars($nights); ?>" min="0">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Meals Included</label>
                                    <select name="meals_included" class="form-select">
                                        <option value="1" <?= $meals_included ? 'selected' : ''; ?>>Yes</option>
                                        <option value="0" <?= !$meals_included ? 'selected' : ''; ?>>No</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Published Status</label>
                                    <select name="is_published" class="form-select">
                                        <option value="1" <?= $is_published ? 'selected' : ''; ?>>Published</option>
                                        <option value="0" <?= !$is_published ? 'selected' : ''; ?>>Draft</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Package</button>
                    <a href="allPackages.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
</div>
</body>
</html>
