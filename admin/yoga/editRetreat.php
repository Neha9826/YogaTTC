<?php
// /admin/yoga/editRetreat.php
// include '../../session.php';
include 'db.php';

$current_page = 'admin/yoga/editRetreat.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid retreat ID.';
    header('Location: allRetreats.php');
    exit;
}

$errors = [];

// Fetch organizations for dropdown
$org_result = $conn->query("SELECT id, name FROM organizations ORDER BY name ASC");

// Fetch existing retreat
$stmt = $conn->prepare("SELECT * FROM yoga_retreats WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    $_SESSION['flash_error'] = 'Retreat not found.';
    header('Location: allRetreats.php');
    exit;
}
$retreat = $res->fetch_assoc();

$title = $retreat['title'];
$organization_id = $retreat['organization_id'];
$short_description = $retreat['short_description'];
$full_description = $retreat['full_description'];
$style = $retreat['style'];

$min_price = $retreat['min_price'];
$max_price = $retreat['max_price'];
$is_published = $retreat['is_published'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $organization_id = intval($_POST['organization_id'] ?? 0);
    $short_description = trim($_POST['short_description'] ?? '');
    $full_description = trim($_POST['full_description'] ?? '');
    $style = trim($_POST['style'] ?? '');
    $min_price = floatval($_POST['min_price'] ?? 0);
    $max_price = floatval($_POST['max_price'] ?? 0);
    $is_published = isset($_POST['is_published']) ? 1 : 0;

    if ($title === '') $errors[] = 'Title is required.';
    if ($organization_id <= 0) $errors[] = 'Organization is required.';

    if (empty($errors)) {
        // Generate slug again (safe update)
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
        $slug = trim($slug, '-');

        $stmt = $conn->prepare("UPDATE yoga_retreats 
            SET organization_id = ?, title = ?, slug = ?, short_description = ?, full_description = ?, style = ?, min_price = ?, max_price = ?, is_published = ?, updated_at = NOW()
            WHERE id = ?");
        $stmt->bind_param(
            'isssssddii',
            $organization_id,
            $title,
            $slug,
            $short_description,
            $full_description,
            $style,
            $min_price,
            $max_price,
            $is_published,
            $id
        );

        if ($stmt->execute()) {
            $_SESSION['flash_success'] = 'Retreat updated successfully.';
            header('Location: allRetreats.php');
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
                <h2>Edit Yoga Retreat</h2>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="editRetreat.php?id=<?= $id; ?>">
                    <div class="card mb-4">
                        <div class="card-header">Basic Information</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($title); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Organization <span class="text-danger">*</span></label>
                                <select name="organization_id" class="form-select" required>
                                    <option value="">-- Select Organization --</option>
                                    <?php if ($org_result && $org_result->num_rows > 0): ?>
                                        <?php while ($org = $org_result->fetch_assoc()): ?>
                                            <option value="<?= (int)$org['id']; ?>" <?= ($organization_id == $org['id']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($org['name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Short Description</label>
                                <textarea name="short_description" class="form-control" rows="2"><?= htmlspecialchars($short_description); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Full Description</label>
                                <textarea name="full_description" class="form-control" rows="4"><?= htmlspecialchars($full_description); ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Style</label>
                                    <input type="text" name="style" class="form-control" value="<?= htmlspecialchars($style); ?>">
                                </div>
                                
                                
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Min Price</label>
                                    <input type="number" step="0.01" name="min_price" class="form-control" value="<?= htmlspecialchars($min_price); ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Max Price</label>
                                    <input type="number" step="0.01" name="max_price" class="form-control" value="<?= htmlspecialchars($max_price); ?>">
                                </div>
                                <div class="col-md-3 d-flex align-items-center">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" name="is_published" class="form-check-input" value="1" <?= $is_published ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Published</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Retreat</button>
                    <a href="allRetreats.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
</div>
</body>
</html>
