<?php
// include 'db.php';

// Fetch contact info
$result  = $conn->query("SELECT * FROM contact_info LIMIT 1");
$contact = $result->fetch_assoc() ?: [
    'address'   => '',
    'phone'     => '',
    'email'     => ''
];

// Make plain phone for tel/WhatsApp links
$plainPhone  = !empty($contact['phone']) ? preg_replace('/\D+/', '', $contact['phone']) : '';
$telHref     = $plainPhone ? "tel:{$plainPhone}" : "#";
$waHref      = $plainPhone ? "https://wa.me/{$plainPhone}" : "#";
?>

<!-- === NEW: Added Font Awesome Link === -->
<!-- This ensures your social icons (fa fa-whatsapp, etc.) will always display -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<footer class="footer_">
    <div class="footer_top">
        <div class="container">
            <div class="row">
                <!-- Address -->
                <div class="col-xl-3 col-md-6 col-lg-3">
                    <div class="footer_widget">
                        <h3 class="footer_title">Address</h3>
                        <p class="footer_text">
                            <?= nl2br(htmlspecialchars($contact['address'])) ?>
                        </p>
                        <a style="color: #85dbdbff;" href="https://maps.app.goo.gl/WQESzoeQDwMMgBuB9?g_st=ac" target="_blank" class="line-button">Get Direction</a>
                    </div>
                </div>

                <!-- Reservation -->
                <div class="col-xl-3 col-md-6 col-lg-3">
                    <div class="footer_widget">
                        <h3 class="footer_title">Reservation</h3>
                        <p class="footer_text">
                            <?php if ($plainPhone): ?>
                                <a href="<?= htmlspecialchars($telHref) ?>" style="color: inherit;"><?= htmlspecialchars($contact['phone']) ?></a><br>
                            <?php else: ?>
                                <?= htmlspecialchars($contact['phone']) ?><br>
                            <?php endif; ?>
                            <a href="mailto:<?= htmlspecialchars($contact['email']) ?>" style="color: inherit;"><?= htmlspecialchars($contact['email']) ?></a>
                        </p>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="col-xl-2 col-md-6 col-lg-2">
                    <div class="footer_widget">
                        <h3 class="footer_title">Navigation</h3>
                        <ul>
                            <li><a href="index.php">Home</a></li>
                            <li><a href="rooms.php">Rooms</a></li>
                            <li><a href="about.php">About</a></li>
                            <li><a href="blog.php">Blogs</a></li>
                            <li><a href="contact.php">Contact</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Newsletter -->
                <div class="col-xl-4 col-md-6 col-lg-4">
                    <!-- Opening Hours -->
                    <div class="footer_widget">
                        <h3 class="footer_title">Opening Hours</h3>
                        <ul class="newsletter_text">
                            <li>Mon - Fri: 9:00 AM - 6:00 PM</li>
                            <li>Sat: 10:00 AM - 4:00 PM</li>
                            <li>Sun: Closed</li>
                        </ul>
                        <p class="newsletter_text">Check-in: 12 PM | Check-out: 11 AM</p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Copy Right -->
    <div class="copy-right_text">
        <div class="container">
            <div class="footer_border"></div>
            <div class="row">
                <div class="col-xl-8 col-md-7 col-lg-9">
                    <p class="copy_right">
                        Copyright &copy;<script>document.write(new Date().getFullYear());</script>
                        All rights reserved | This website is made with
                        <i class="fa fa-heart-o" aria-hidden="true"></i> by
                        <a style="color: #85dbdbff" href="https://my-portfolio-7bb38.web.app/" target="_blank">Neha Pattnayak</a>
                    </p>
                </div>
                <div class="col-xl-4 col-md-5 col-lg-3">
                    <div class="socail_links">
                        <ul>
                            <?php if ($plainPhone): ?>
                                <li><a href="<?= htmlspecialchars($waHref) ?>" target="_blank"><i class="fa fa-whatsapp"></i></a></li>
                            <?php endif; ?>
                            <li><a href="#"><i class="fa fa-facebook-square"></i></a></li>
                            <li><a href="https://www.instagram.com/retreatshivoham?igsh=MWd1MTg1emRqOHE3Ng==" target="_blank"><i class="fa fa-instagram"></i></a></li>
                            <li><a href="#"><i class="fa fa-youtube"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<script>
window.addEventListener('scroll', function() {
  const nav = document.querySelector('.navbar');
  if (window.scrollY > 50) {
    nav.classList.add('sticky');
  } else {
    nav.classList.remove('sticky');
  }
});
</script>

