<?php
include '../db.php';
$errors = [];
$success = '';
$user = null;

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid user ID.");
}

$user_id = intval($_GET['id']);

// Fetch existing user
$stmt = $conn->prepare("SELECT * FROM y_users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    die("User not found.");
}
$stmt->close();

// Handle update
// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $phone    = mysqli_real_escape_string($conn, $_POST['phone']);
    $role     = mysqli_real_escape_string($conn, $_POST['role']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $verification_type   = !empty($_POST['verification_type']) ? $_POST['verification_type'] : $user['verification_type'];
    $verification_number = !empty($_POST['verification_number']) ? $_POST['verification_number'] : $user['verification_number'];
    $verification_status = !empty($_POST['verification_status']) ? $_POST['verification_status'] : $user['verification_status'];

    // Validations
    if (empty($name) || empty($email) || empty($role)) {
        $errors[] = "Name, Email and Role are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif (!empty($password) && $password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        // Update password only if entered
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        } else {
            $hashed_password = $user['password']; // keep old password
        }

        $stmt = $conn->prepare("UPDATE y_users 
    SET name=?, email=?, phone=?, role=?, password=?, verification_type=?, verification_number=?, verification_status=?, updated_at=NOW() 
    WHERE id=?");
$stmt->bind_param("ssssssssi", 
    $name, $email, $phone, $role, $hashed_password, $verification_type, $verification_number, $verification_status, $user_id
);


        if ($stmt->execute()) {
            $success = "User updated successfully!";
            // refresh user details
            $stmt->close();
            $stmt = $conn->prepare("SELECT * FROM y_users WHERE id=?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
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
                <h2>Edit User / Host</h2>

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
                        <form method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                    value="<?= htmlspecialchars($user['name']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                    value="<?= htmlspecialchars($user['email']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" 
                                    value="<?= htmlspecialchars($user['phone']); ?>">
                            </div>

                            <div class="mb-3 position-relative">
                                <label for="password" class="form-label">Password (leave blank to keep current)</label>
                                <input type="password" class="form-control" id="password" name="password">
                                <span toggle="#password" class="toggle-password" style="position:absolute; top:38px; right:10px; cursor:pointer;">👁️</span>
                            </div>

                            <div class="mb-3 position-relative">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                <span toggle="#confirm_password" class="toggle-password" style="position:absolute; top:38px; right:10px; cursor:pointer;">👁️</span>
                            </div>

                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role" required onchange="toggleVerification()">
                                    <option value="user" <?= ($user['role']=='user')?'selected':''; ?>>User</option>
                                    <option value="host" <?= ($user['role']=='host')?'selected':''; ?>>Host</option>
                                </select>
                            </div>

                            <div class="mb-3" id="verificationOptions" style="display: <?= ($user['role']=='host')?'block':'none'; ?>;">
                                <label class="form-label">Verification Method</label>
                                <select class="form-select" id="verification_type" name="verification_type" onchange="showVerificationInput()">
                                    <option value="">Select Verification Type</option>
                                    <option value="aadhaar" <?= ($user['verification_type']=='aadhaar')?'selected':''; ?>>Aadhaar</option>
                                    <option value="pan" <?= ($user['verification_type']=='pan')?'selected':''; ?>>PAN</option>
                                </select>
                            </div>

                            <div class="mb-3" id="verificationInput" style="display: <?= ($user['verification_type'])?'block':'none'; ?>;">
                                <label id="verificationLabel" class="form-label">
                                    <?= $user['verification_type']=='aadhaar'?'Enter Aadhaar Number':'Enter PAN Number'; ?>
                                </label>
                                <input type="text" class="form-control" id="verification_number" name="verification_number"
                                    value="<?= htmlspecialchars($user['verification_number']); ?>">
                                <button type="button" class="btn btn-success mt-2" onclick="verifyDocument()">Verify</button>
                                <div id="verificationResult" class="mt-2"></div>
                            </div>

                            <div class="mb-3" id="verificationStatusBox" style="display: <?= ($user['role']=='host')?'block':'none'; ?>;">
    <label for="verification_status" class="form-label">Verification Status</label>
    <select class="form-select" id="verification_status" name="verification_status">
        <option value="pending" <?= ($user['verification_status']=='pending')?'selected':''; ?>>Pending</option>
        <option value="verified" <?= ($user['verification_status']=='verified')?'selected':''; ?>>Verified</option>
        <option value="failed" <?= ($user['verification_status']=='failed')?'selected':''; ?>>Failed</option>
    </select>
</div>

                            <button type="submit" class="btn btn-primary">Update User</button>
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

// function toggleVerification() {
//     const role = document.getElementById('role').value;
//     document.getElementById('verificationOptions').style.display = (role === 'host') ? 'block' : 'none';
// }

function toggleVerification() {
    const role = document.getElementById('role').value;
    document.getElementById('verificationOptions').style.display = (role === 'host') ? 'block' : 'none';
    document.getElementById('verificationInput').style.display = (role === 'host') ? 'block' : 'none';
    document.getElementById('verificationStatusBox').style.display = (role === 'host') ? 'block' : 'none';
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
    }
    document.getElementById('verificationInput').style.display = (type) ? 'block' : 'none';
}

function verifyDocument() {
    const type = document.getElementById('verification_type').value;
    const number = document.getElementById('verification_number').value;
    const resultBox = document.getElementById('verificationResult');

    if (!number) {
        resultBox.innerHTML = '<span class="text-danger">Please enter a valid number.</span>';
        return;
    }

    fetch('verifyDocument.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ type, number })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            resultBox.innerHTML = '<span class="text-success">Verification Successful ✅</span>';
        } else {
            resultBox.innerHTML = '<span class="text-danger">Verification Failed ❌</span>';
        }
    })
    .catch(() => {
        resultBox.innerHTML = '<span class="text-danger">Error connecting to verification API.</span>';
    });
}
</script>
</body>
</html>
