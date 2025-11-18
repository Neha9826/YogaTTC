<?php
session_start();
include '../db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid instructor ID.';
    header('Location: manageInstructors.php');
    exit;
}

// Fetch instructor
$stmt = $conn->prepare("SELECT * FROM yoga_instructors WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    $_SESSION['flash_error'] = 'Instructor not found.';
    header('Location: manageInstructors.php');
    exit;
}
$instructor = $res->fetch_assoc();

// Initialize variables safely
$name = $instructor['name'] ?? '';
$bio = $instructor['bio'] ?? '';
$photo = $instructor['photo'] ?? '';
$social_links = $instructor['social_links'] ?? '';
$specialization = $instructor['specialization'] ?? '';
$experience_years = $instructor['experience_years'] ?? '';
$certifications = $instructor['certifications'] ?? '';
$availability = $instructor['availability'] ?? '';
$status = $instructor['status'] ?? 'pending';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $social_links = trim($_POST['social_links'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $experience_years = intval($_POST['experience_years'] ?? 0);
    $availability = trim($_POST['availability'] ?? '');
    $status = $_POST['status'] ?? 'pending';

    if ($name === '') $errors[] = 'Name is required.';

    // Upload folder
    $uploadDir = "../../uploads/instructors/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    // Photo upload
    if (!empty($_FILES['photo']['name'])) {
        $filename = time() . '_' . basename($_FILES['photo']['name']);
        $targetFile = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
            $photo = "uploads/instructors/" . $filename;
        } else {
            $errors[] = 'Failed to upload photo.';
        }
    }

    // Multiple certificate uploads
    $certPaths = [];
    if (!empty($_FILES['certifications']['name'][0])) {
        foreach ($_FILES['certifications']['tmp_name'] as $key => $tmpName) {
            $certName = time() . "_cert_" . basename($_FILES['certifications']['name'][$key]);
            $targetFile = $uploadDir . $certName;
            if (move_uploaded_file($tmpName, $targetFile)) {
                $certPaths[] = "uploads/instructors/" . $certName;
            }
        }
    }
    if (!empty($certPaths)) {
        $certifications = implode(',', $certPaths); // store comma-separated
    }

    if (empty($errors)) {
    $stmt = $conn->prepare("UPDATE yoga_instructors 
        SET name=?, bio=?, photo=?, social_links=?, specialization=?, experience_years=?, certifications=?, availability=?, status=? 
        WHERE id=?");

    // 9 fields + 1 ID = 10 total → 10 type placeholders
    // s = string, i = integer
    $stmt->bind_param(
        'sssssisssi',
        $name,
        $bio,
        $photo,
        $social_links,
        $specialization,
        $experience_years,
        $certifications,
        $availability,
        $status,
        $id
    );

    if ($stmt->execute()) {
        $_SESSION['flash_success'] = 'Instructor updated successfully.';
        header('Location: manageInstructors.php');
        exit;
    } else {
        $errors[] = 'DB error: ' . $conn->error;
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
                <h2>Edit Instructor</h2>

                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="card mb-4">
                        <div class="card-header">Instructor Info</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label>Bio</label>
                                <textarea name="bio" class="form-control" rows="3"><?= htmlspecialchars($bio) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label>Photo</label>
                                <input type="file" name="photo" class="form-control">
                                <?php if ($photo): ?><img src="../../<?= htmlspecialchars($photo) ?>" style="height:60px" class="mt-2"><?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label>Specialization</label>
                                <input type="text" name="specialization" class="form-control" value="<?= htmlspecialchars($specialization) ?>">
                            </div>

                            <div class="mb-3">
                                <label>Experience (Years)</label>
                                <input type="number" name="experience_years" class="form-control" value="<?= htmlspecialchars($experience_years) ?>">
                            </div>

                            <div class="mb-3">
                                <label>Upload Certificates (Multiple)</label>
                                <input type="file" name="certifications[]" class="form-control" multiple>
                                <?php if (!empty($certifications)): ?>
                                    <p class="mt-2"><strong>Existing Files:</strong><br><?= htmlspecialchars($certifications) ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label>Social Links</label>
                                <textarea name="social_links" class="form-control"><?= htmlspecialchars($social_links) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label>Availability</label>
                                <input type="text" name="availability" class="form-control" value="<?= htmlspecialchars($availability) ?>">
                            </div>

                            <div class="mb-3">
                                <label>Status</label>
                                <select name="status" class="form-select">
                                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Instructor</button>
                    <a href="manageInstructors.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
</div>
</body>
</html>
