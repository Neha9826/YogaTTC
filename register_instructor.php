<?php
// /yoga/register_instructor.php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name  = $_POST['name'] ?? '';
    $bio   = $_POST['bio'] ?? '';
    $photo = null;
    $verification_file = null;

    // Handle photo upload
    if (!empty($_FILES['photo']['name'])) {
        $targetDir = "../uploads/instructors/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $photo = $targetDir . time() . "_" . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
    }

    // Optional PAN/Aadhaar upload
    if (!empty($_FILES['verification_file']['name'])) {
        $targetDir = "../uploads/instructors/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $verification_file = $targetDir . time() . "_" . basename($_FILES['verification_file']['name']);
        move_uploaded_file($_FILES['verification_file']['tmp_name'], $verification_file);
    }

    $stmt = $conn->prepare("INSERT INTO yoga_instructors (name, bio, photo, type, organization_id, verification_file, status, created_at) VALUES (?, ?, ?, 'freelancer', NULL, ?, 'pending', NOW())");
    $stmt->bind_param("ssss", $name, $bio, $photo, $verification_file);

    if ($stmt->execute()) {
        $_SESSION['flash_success'] = "Instructor registration submitted! Awaiting admin approval.";
    } else {
        $_SESSION['flash_error'] = "Error: " . $stmt->error;
    }
    $stmt->close();

    header("Location: register_instructor.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register as Instructor</title>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Register as Instructor (Freelancer)</h2>
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Bio</label>
            <textarea name="bio" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label>Photo</label>
            <input type="file" name="photo" class="form-control">
        </div>
        <div class="mb-3">
            <label>PAN/Aadhaar (optional)</label>
            <input type="file" name="verification_file" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>
</body>
</html>
