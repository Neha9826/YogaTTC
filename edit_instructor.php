<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['yoga_host_id'])) {
    header("Location: login.php");
    exit;
}

$host_id = $_SESSION['yoga_host_id'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: all_instructors.php");
    exit;
}

$instructor_id = intval($_GET['id']);

// Fetch instructor
$sql = "
    SELECT i.*, o.name AS org_name 
    FROM yoga_instructors i
    JOIN organizations o ON i.organization_id = o.id
    WHERE i.id = $instructor_id AND o.created_by = $host_id
    LIMIT 1
";
$res = $conn->query($sql);
if ($res->num_rows === 0) {
    echo "<div class='container mt-5'>Instructor not found.</div>";
    exit;
}

$instructor = $res->fetch_assoc();

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $type = $conn->real_escape_string($_POST['type']);
    $bio = $conn->real_escape_string($_POST['bio']);
    $social_links = $conn->real_escape_string($_POST['social_links']);

    $photo_sql = "";

    // Delete current photo if requested
    if (isset($_POST['delete_photo']) && $_POST['delete_photo'] == '1' && !empty($instructor['photo']) && file_exists('../'.$instructor['photo'])) {
        unlink('../'.$instructor['photo']);
        $photo_sql = ", photo=NULL";
    }

    // Handle new photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $new_name = 'photo_' . time() . '.' . $ext;
        $upload_path = 'uploads/instructors/' . $new_name; // full relative path
        move_uploaded_file($_FILES['photo']['tmp_name'], '../' . $upload_path);
        $photo_sql = ", photo='$upload_path'";
    }

    $update_sql = "
        UPDATE yoga_instructors SET
            name='$name',
            email='$email',
            phone='$phone',
            type='$type',
            bio='$bio',
            social_links='$social_links'
            $photo_sql
        WHERE id=$instructor_id
    ";

    if ($conn->query($update_sql)) {
        header("Location: view_instructor.php?id=$instructor_id");
        exit;
    } else {
        $errors[] = "Database error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
    <title>Edit Instructor | Yoga Bhawna Mission</title>
    <link rel="stylesheet" href="yoga.css">
</head>
<body class="yoga-page">

<?php include __DIR__ . '/includes/fixed_social_bar.php'; ?>
<?php include __DIR__ . '/yoga_navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'host_sidebar.php'; ?>
        <div class="col-md-9 col-lg-10 p-4">
            <h2>Edit Instructor</h2>
            <a href="view_instructor.php?id=<?= $instructor['id'] ?>" class="btn btn-secondary mb-3">⬅ Back</a>

            <?php if ($errors): ?>
                <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <!-- Profile Image Top -->
                <div class="mb-4 text-center">
                    <?php
                    $photo = trim($instructor['photo']);
                    $photo_path = (!empty($photo) && file_exists('../'.$photo)) 
                                  ? '../'.$photo 
                                  : '../assets/default_profile.png';
                    ?>
                    <img src="<?= $photo_path ?>" class="rounded-circle mb-2" width="120" height="120" alt="Profile">
                    <?php if(!empty($instructor['photo'])): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="delete_photo" value="1" id="delete_photo">
                            <label class="form-check-label" for="delete_photo">Delete current photo</label>
                        </div>
                    <?php endif; ?>
                    <div class="mt-2">
                        <input type="file" name="photo" class="form-control">
                    </div>
                </div>

                <!-- Two-column form -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($instructor['name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($instructor['email']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($instructor['phone']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label>Type</label>
                        <select name="type" class="form-control" required>
                            <option value="full-time" <?= $instructor['type']=='full-time'?'selected':'' ?>>Full-time</option>
                            <option value="part-time" <?= $instructor['type']=='part-time'?'selected':'' ?>>Part-time</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Bio</label>
                        <textarea name="bio" class="form-control"><?= htmlspecialchars($instructor['bio']) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label>Social Links</label>
                        <textarea name="social_links" class="form-control"><?= htmlspecialchars($instructor['social_links']) ?></textarea>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Update Instructor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
