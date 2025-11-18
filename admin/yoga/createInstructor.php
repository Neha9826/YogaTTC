<?php
include '../db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'host') {
    die("Unauthorized");
}
$host_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name  = $_POST['name'] ?? '';
    $bio   = $_POST['bio'] ?? '';
    $specialization = $_POST['specialization'] ?? '';
    $experience_years = $_POST['experience_years'] ?? '';
    $certifications = $_POST['certifications'] ?? '';
    $social_links = $_POST['social_links'] ?? '';
    $availability = $_POST['availability'] ?? '';
    $org_id = $_POST['organization_id'] ?? null;

    $photo = null;
    $verification_file = null;

    $uploadDir = "../../uploads/instructors/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    if (!empty($_FILES['photo']['name'])) {
        $photo = "uploads/instructors/" . time() . "_" . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], "../../" . $photo);
    }
    if (!empty($_FILES['verification_file']['name'])) {
        $verification_file = "uploads/instructors/" . time() . "_" . basename($_FILES['verification_file']['name']);
        move_uploaded_file($_FILES['verification_file']['tmp_name'], "../../" . $verification_file);
    }

    $stmt = $conn->prepare("INSERT INTO yoga_instructors 
        (name, bio, photo, specialization, experience_years, certifications, social_links, availability, type, organization_id, verification_file, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'full-time', ?, ?, 'pending', NOW())");
    $stmt->bind_param("ssssisssis", $name, $bio, $photo, $specialization, $experience_years, $certifications, $social_links, $availability, $org_id, $verification_file);

    if ($stmt->execute()) {
        $_SESSION['flash_success'] = "Instructor created successfully! Awaiting admin approval.";
    } else {
        $_SESSION['flash_error'] = "Error: " . $stmt->error;
    }
    header("Location: manageInstructors.php");
    exit;
}

$orgs = $conn->query("SELECT id,name FROM organizations WHERE created_by='$host_id'");
?>
<!DOCTYPE html>
<html lang="en">
<?php include '../includes/head.php'; ?>
<body class="sb-nav-fixed">
<?php include '../includes/navbar.php'; ?>
<div id="layoutSidenav">
    <?php include '../includes/sidebar.php'; ?>
    <div id="layoutSidenav_content">
        <main class="container mt-4">
            <h2>Create Instructor</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3"><label>Name</label><input type="text" name="name" class="form-control" required></div>
                <div class="mb-3"><label>Bio</label><textarea name="bio" class="form-control" rows="3"></textarea></div>
                <div class="mb-3"><label>Specialization</label><input type="text" name="specialization" class="form-control"></div>
                <div class="mb-3"><label>Experience (Years)</label><input type="number" name="experience_years" class="form-control"></div>
                <div class="mb-3"><label>Certifications</label><textarea name="certifications" class="form-control"></textarea></div>
                <div class="mb-3"><label>Social Links</label><textarea name="social_links" class="form-control"></textarea></div>
                <div class="mb-3"><label>Availability</label><input type="text" name="availability" class="form-control"></div>
                <div class="mb-3"><label>Photo</label><input type="file" name="photo" class="form-control"></div>
                <div class="mb-3">
                    <label>Organization</label>
                    <select name="organization_id" class="form-control" required>
                        <option value="">-- Select Organization --</option>
                        <?php while ($row = $orgs->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3"><label>Verification File</label><input type="file" name="verification_file" class="form-control"></div>
                <button type="submit" class="btn btn-primary">Create Instructor</button>
            </form>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
</div>
</body>
</html>
