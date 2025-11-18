<?php
session_start();
include __DIR__ . '/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM y_users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['role'] === 'host') {
            if ($user['verification_status'] !== 'verified') {
                $errors[] = "Your account is pending verification by admin. Please wait until it's approved.";
            } else {
                $_SESSION['yoga_host_id']   = $user['id'];
                $_SESSION['yoga_host_name'] = $user['name'];
                header("Location: hostDashboard.php");
                exit;
            }
        } else { // Normal user
            $_SESSION['yoga_user_id']   = $user['id'];
            $_SESSION['yoga_user_name'] = $user['name'];
            $_SESSION['yoga_user_email'] = $user['email'];
            $_SESSION['yoga_user_phone'] = $user['phone'];

            header("Location: index.php");
            exit;
        }
    } else {
        $errors[] = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'includes/head.php'; ?>
  <title>Login | Yoga Bhawna Mission</title>
</head>
<body>

<?php include __DIR__ . '/yoga_navbar.php'; ?>

<div class="container my-5" style="max-width: 500px;">
  <h3 class="mb-4">Login</h3>
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><?php echo implode('<br>', $errors); ?></div>
  <?php endif; ?>
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Login</button>
  </form>
  <p class="mt-3">Don’t have an account? <a href="createUser.php">Create one</a></p>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
