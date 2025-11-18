<?php
// Make sure BASE_URL is available

// Fetch contact info
if (isset($conn) && !$conn->connect_error) {
    $result = $conn->query("SELECT phone, email FROM contact_info LIMIT 1");
    $contact = $result->fetch_assoc() ?: ['phone' => '', 'email' => ''];
} else {
    $contact = ['phone' => '', 'email' => ''];
}

$contact_phone = $contact['phone'];
$contact_email = $contact['email'];

$plainPhone = !empty($contact_phone) ? preg_replace('/\D+/', '', $contact_phone) : '';
$waHref     = $plainPhone ? "https://wa.me/{$plainPhone}" : "#";
$telHref    = $plainPhone ? "tel:{$plainPhone}" : "#";
$mailHref   = !empty($contact_email) ? "mailto:{$contact_email}" : "#";

$current_page = basename($_SERVER['PHP_SELF']);
?>
<header>
    <div class="header-area">
        <div id="sticky-header" class="main-header-area">
            <div class="container-fluid p-0">
                <div class="row align-items-center no-gutters">
                    
                    <!-- Navigation -->
                    <div class="col-xl-5 col-lg-6">
                        <div class="main-menu d-none d-lg-block">
                            <nav>
                                <ul id="navigation">
                                    <li><a class="<?= ($current_page == 'index.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>index.php">Home</a></li>
                                    <li><a class="<?= ($current_page == 'allRooms.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>allRooms.php">Rooms</a></li>
                                    <li><a class="<?= ($current_page == 'about.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>about.php">About</a></li>
                                    <li><a class="<?= ($current_page == 'blog.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>blog.php">Blog</a></li>
                                    <li><a class="<?= ($current_page == 'nearbyPlaces.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>nearbyPlaces.php">Nearby</a></li>
                                    <li><a class="<?= ($current_page == 'yoga/index.php') ? 'active' : '' ?>" href="<?= YOGA_URL ?>index.php">Yoga</a></li>
                                    <li><a class="<?= ($current_page == 'contact.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>contact.php">Contact</a></li>

                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <li class="dropdown">
                                            <a href="#"><i style="font-size:20px; vertical-align:middle;" class="fa fa-user"></i> <i class="ti-angle-down"></i></a>
                                            <ul class="submenu">
                                                <li><a class="<?= ($current_page == 'profile.php') ? 'active' : '' ?> text-muted" href="profile.php"><i class="fa fa-user" style="font-size:20px; vertical-align:middle;"></i>Profile</a></li>
                                                <li><a href="logout.php" class="text-muted"><i class="fa fa-sign-out" style="font-size:20px; vertical-align:middle; color: #bd8f03ff;"></i>Logout</a></li>
                                            </ul>
                                        </li>
                                    <?php else: ?>
                                        <li><a class="<?= ($current_page == 'login.php') ? 'active' : '' ?>" href="login.php"><i class="fa fa-user" style="font-size:20px; vertical-align:middle; color: #bd8f03ff;"></i></a></li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>

                    <!-- Logo -->
                    <div class="col-xl-2 col-lg-2">
                        <div class="logo-img">
                            <a href="admin/login.php">
                                <img src="img/Shivoham.png" alt="Yoga Bhawna Mission" style="max-height: 80px; width: auto;">
                            </a>
                        </div>
                    </div>

                    <!-- Social + Book Button -->
                    <div class="col-xl-5 col-lg-4 d-none d-lg-block">
                        <div class="book_room">
                            <div class="socail_links">
                                <ul>
                                    <li><a href="<?= htmlspecialchars($waHref) ?>" target="_blank"><i class="fa fa-whatsapp" style="color: #25D366;"></i></a></li>
                                    <li><a href="#"><i class="fa fa-facebook-square" style="color: #1877F2;"></i></a></li>
                                    <li><a href="https://www.instagram.com/retreatshivoham?igsh=MWd1MTg1emRqOHE3Ng=="><i class="fa fa-instagram" style="color: #C13584;"></i></a></li>
                                    <li><a href="#"><i class="fa fa-youtube" style="color: #FF0000;"></i></a></li>
                                </ul>
                            </div>
                            <div class="book_btn d-none d-lg-block">
                                <a style="background-color: #bd8f03ff;" class="popup-with-form" href="#test-form">Book A Room</a>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Menu -->
                    <div class="col-12">
                        <div class="mobile_menu d-block d-lg-none"></div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</header>
