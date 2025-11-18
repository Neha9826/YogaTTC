<?php
// /yoga/register.php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name  = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'client';
    $verification_file = null;

    // Hash password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // If role=host → require PAN/Aadhaar
    if ($role === 'host' && !empty($_FILES['verification_file']['name'])) {
        $targetDir = "../uploads/hosts/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $verification_file = $targetDir . time() . "_" . basename($_FILES['verification_file']['name']);
        move_uploaded_file($_FILES['verification_file']['tmp_name'], $verification_file);
    }

    $stmt = $conn->prepare("INSERT INTO users (name,email,password,role,verification_file,status,created_at) VALUES (?,?,?,?,?,'pending',NOW())");
    $stmt->bind_param("sssss", $name, $email, $password_hash, $role, $verification_file);

    if ($stmt->execute()) {
        $_SESSION['flash_success'] = "Registration successful! Awaiting admin approval.";
    } else {
        $_SESSION['flash_error'] = "Error: " . $stmt->error;
    }
    $stmt->close();

    header("Location: register.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include 'includes/head.php'; ?>
<body>


<div class="container mt-5 mb-5">
    <h2>User Registration</h2>
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
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Role</label>
            <select name="role" id="role" class="form-control" required onchange="toggleVerification()">
                <option value="client">Client</option>
                <option value="host">Host</option>
            </select>
        </div>
        <div class="mb-3" id="verificationField" style="display:none;">
            <label>PAN/Aadhaar (Host only)</label>
            <input type="file" name="verification_file" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>

<script>
function toggleVerification() {
    const role = document.getElementById('role').value;
    document.getElementById('verificationField').style.display = (role === 'host') ? 'block' : 'none';
}
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
