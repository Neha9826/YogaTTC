<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['yoga_host_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
    <meta charset="UTF-8">
    <title>Host Dashboard | Yoga Bhawna Mission</title>
    <link rel="stylesheet" href="yoga.css">
</head>
<body class="yoga-page">

    <!-- optional social bar -->
    <?php include __DIR__ . '/includes/fixed_social_bar.php'; ?>

<?php include __DIR__ . '/yoga_navbar.php'; ?>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar (left) -->
    <!-- <div class="col-md-3 col-lg-2 p-0"> -->
      <?php include 'host_sidebar.php'; ?> 
    <!-- </div> -->

    <!-- Main content (right) -->
    <div class="col-md-9 col-lg-10 p-4">
      <h1>Welcome, Host</h1>
      <p>Select an option from the sidebar to manage your retreats.</p>
    </div>
  </div>
</div>


<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
