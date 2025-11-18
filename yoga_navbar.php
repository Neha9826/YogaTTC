<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container">
    <!-- Logo -->
    <a class="navbar-brand fw-bold text-teal" href="#">YogBhawnaMission</a>
    <!-- <br><p class="text-muted">by CareMyTrip.com</p> -->
    <!-- Toggler for mobile -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Center Links -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav mx-auto">
        <li class="nav-item"><a class="nav-link" href="#">Destinations</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Type of Retreat</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Yoga Teacher Training</a></li>
      </ul>

      <!-- Right Icons -->
      <div class="d-flex align-items-center gap-3">
        <span>INR</span>
        <a href="#"><i class="bi bi-gift"></i></a>
        <a href="#"><i class="bi bi-heart"></i></a>

        <!-- User Dropdown -->
        <div class="dropdown">
          <a href="#" class="d-flex align-items-center text-dark text-decoration-none" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person fs-4"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow p-2" aria-labelledby="userDropdown">
            <?php if (isset($_SESSION['yoga_user_id'])): ?>
              <li><span class="dropdown-item-text">Hello, <?= htmlspecialchars($_SESSION['yoga_user_name']) ?></span></li>
              <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            <?php elseif (isset($_SESSION['yoga_host_id'])): ?>
              <li><span class="dropdown-item-text">Host: <?= htmlspecialchars($_SESSION['yoga_host_name']) ?></span></li>
              <li><a class="dropdown-item" href="hostDashboard.php">Dashboard</a></li>
              <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            <?php else: ?>
              <li><a class="dropdown-item" href="login.php">Login</a></li>
              <li><a class="dropdown-item" href="createUser.php">Create Account</a></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="register_instructor.php">Become a Partner</a></li>
            <li><a class="dropdown-item" href="tel:9917003456">Contact Support</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</nav>