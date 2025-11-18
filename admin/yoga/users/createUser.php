<?php
include '../db.php';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $phone    = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role     = mysqli_real_escape_string($conn, $_POST['role']);

    $verification_type   = isset($_POST['verification_type']) ? mysqli_real_escape_string($conn, $_POST['verification_type']) : null;
    $verification_number = isset($_POST['verification_number']) ? strtoupper(trim($_POST['verification_number'])) : null;

    $verification_file = null;

    // Basic validations
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        $errors[] = "All fields except phone are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    } else {
        // Check if email already exists
        $check = mysqli_query($conn, "SELECT id FROM y_users WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $errors[] = "Email is already registered.";
        }
    }

    // Extra validation for Host role
    if ($role === 'host') {
        if (empty($verification_type) || empty($verification_number)) {
            $errors[] = "Verification type and number are required for hosts.";
        } else {
            // Manual regex validation
            if ($verification_type === 'aadhaar' && !preg_match('/^[2-9]{1}[0-9]{11}$/', $verification_number)) {
                $errors[] = "Invalid Aadhaar number format.";
            }
            if ($verification_type === 'pan' && !preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $verification_number)) {
                $errors[] = "Invalid PAN number format.";
            }
        }

        // File upload required for host
        if (isset($_FILES['verification_file']) && $_FILES['verification_file']['error'] === UPLOAD_ERR_OK) {
            $targetDir = "../../uploads/verification/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $filename = time() . "_" . basename($_FILES['verification_file']['name']);
            $targetFile = $targetDir . $filename;
            if (move_uploaded_file($_FILES['verification_file']['tmp_name'], $targetFile)) {
                $verification_file = "uploads/verification/" . $filename;
            } else {
                $errors[] = "Failed to upload verification file.";
            }
        } else {
            $errors[] = "Verification document upload is required for hosts.";
        }
    }

    // Insert if no errors
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO y_users 
            (name, email, phone, password, role, verification_type, verification_number, verification_file, verification_status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("ssssssss", $name, $email, $phone, $hashed_password, $role, $verification_type, $verification_number, $verification_file);

        if ($stmt->execute()) {
            $success = "User created successfully! (Awaiting admin approval)";
            $name = $email = $phone = $password = $confirm_password = $role = $verification_type = $verification_number = '';
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include '../../includes/head.php'; ?>
<link href="../../css/styles.css" rel="stylesheet">
<body class="sb-nav-fixed">
<?php include '../../includes/navbar.php'; ?>
<div id="layoutSidenav">
    <?php include '../../includes/sidebar.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4 mt-4">
                <h2>Create User / Host</h2>
                <p class="text-muted mb-4">Fill out the form below to create a new user or host account. Hosts require PAN/Aadhaar verification.</p>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?= isset($phone) ? htmlspecialchars($phone) : '' ?>">
                            </div>

                            <div class="mb-3 position-relative">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <span toggle="#password" class="toggle-password" style="position:absolute; top:38px; right:10px; cursor:pointer;">👁️</span>
                            </div>

                            <div class="mb-3 position-relative">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <span toggle="#confirm_password" class="toggle-password" style="position:absolute; top:38px; right:10px; cursor:pointer;">👁️</span>
                            </div>

                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role" required onchange="toggleVerification()">
                                    <option value="">Select Role</option>
                                    <option value="user" <?= (isset($role) && $role=='user') ? 'selected' : '' ?>>User</option>
                                    <option value="host" <?= (isset($role) && $role=='host') ? 'selected' : '' ?>>Host</option>
                                </select>
                            </div>

                            <!-- Host Verification Fields -->
                            <div id="verificationSection" style="display: <?= (isset($role) && $role=='host') ? 'block' : 'none' ?>;">
                                <div class="mb-3">
                                    <label class="form-label">Verification Method</label>
                                    <select class="form-select" id="verification_type" name="verification_type" onchange="showVerificationInput()">
                                        <option value="">Select Verification Type</option>
                                        <option value="aadhaar" <?= (isset($verification_type) && $verification_type=='aadhaar') ? 'selected' : '' ?>>Aadhaar</option>
                                        <option value="pan" <?= (isset($verification_type) && $verification_type=='pan') ? 'selected' : '' ?>>PAN</option>
                                    </select>
                                </div>

                                <div class="mb-3" id="verificationInput" style="display: <?= (isset($verification_type) && $verification_type) ? 'block' : 'none' ?>;">
                                    <label id="verificationLabel" class="form-label">
                                        <?= (isset($verification_type) && $verification_type=='aadhaar') ? 'Enter Aadhaar Number' : ((isset($verification_type) && $verification_type=='pan') ? 'Enter PAN Number' : '') ?>
                                    </label>
                                    <input type="text" class="form-control" id="verification_number" name="verification_number"
                                        placeholder="<?= (isset($verification_type) && $verification_type=='aadhaar') ? '12-digit Aadhaar Number' : ((isset($verification_type) && $verification_type=='pan') ? '10-digit PAN (e.g., ABCDE1234F)' : '') ?>"
                                        value="<?= isset($verification_number) ? htmlspecialchars($verification_number) : '' ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="verification_file" class="form-label">Upload Document</label>
                                    <input type="file" class="form-control" id="verification_file" name="verification_file">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Create User</button>
                            <a href="allUsers.php" class="btn btn-secondary">Back to Users</a>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Toggle password visibility
document.querySelectorAll('.toggle-password').forEach(span => {
    span.addEventListener('click', function() {
        const input = document.querySelector(this.getAttribute('toggle'));
        input.type = (input.type === 'password') ? 'text' : 'password';
    });
});

function toggleVerification() {
    const role = document.getElementById('role').value;
    document.getElementById('verificationSection').style.display = (role === 'host') ? 'block' : 'none';
}

function showVerificationInput() {
    const type = document.getElementById('verification_type').value;
    const label = document.getElementById('verificationLabel');
    const input = document.getElementById('verification_number');
    if (type === 'aadhaar') {
        label.innerText = "Enter Aadhaar Number";
        input.placeholder = "12-digit Aadhaar Number";
    } else if (type === 'pan') {
        label.innerText = "Enter PAN Number";
        input.placeholder = "10-digit PAN (e.g., ABCDE1234F)";
    } else {
        label.innerText = "";
        input.placeholder = "";
    }
    document.getElementById('verificationInput').style.display = (type) ? 'block' : 'none';
}
</script>
</body>
</html>
