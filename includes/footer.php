<?php
// include 'db.php';

// Fetch contact info
$result  = $conn->query("SELECT * FROM contact_info LIMIT 1");
$contact = $result->fetch_assoc() ?: [
    'address'   => 'CMTC House, Kuthalwali, Johrigaon, Dehradun, Uttarakhand-248003', // Example data if DB fails
    'phone'     => '+91-9917003456',          // Example data if DB fails
    'email'     => 'retreatshivoham@gmail.com'           // Example data if DB fails
];

// Make plain phone for tel/WhatsApp links
$plainPhone  = !empty($contact['phone']) ? preg_replace('/\D+/', '', $contact['phone']) : '';
$telHref     = $plainPhone ? "tel:{$plainPhone}" : "#";
$waHref      = $plainPhone ? "https://wa.me/{$plainPhone}" : "#";
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<style>
    .new-footer {
        background-color: #d8dbdcff; /* Dark background from screenshot */
        color: #000000ff; /* Light gray text */
        padding-top: 60px;
        font-size: 15px;
        line-height: 1.7;
    }
    .new-footer .footer-top {
        padding-bottom: 30px;
    }
    .new-footer-col h5 {
        color: #000000ff; /* White headings */
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 25px;
        text-transform: capitalize; /* Matches screenshot "Our Other Themes" */
    }
    .new-footer-col p {
        color: #000000ff;
        margin-bottom: 15px;
    }
    .new-footer-col .line-button {
        color: #018a8aff; /* Kept your brand color for the link */
        text-decoration: none;
        font-weight: 600;
    }
    .new-footer-col .line-button:hover {
        color: #000000ff;
    }
    .new-footer-col ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .new-footer-col ul li {
        margin-bottom: 12px;
    }
    .new-footer-col ul li a {
        color: #000000ff;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    .new-footer-col ul li a:hover {
        color: #000000ff;
        text-decoration: none;
    }
    /* Styles for the "Connect with us" social icons */
    .new-footer-socials {
        margin-top: 25px;
    }
    .new-footer-socials a {
        display: inline-block;
        color: #000000ff;
        font-size: 20px;
        margin-right: 18px;
        transition: color 0.3s ease;
    }
    .new-footer-socials a:hover {
        color: #000000ff;
    }
    /* Bottom Copyright Bar */
    .new-footer-bottom {
        border-top: 1px solid #333; /* Faint line like screenshot */
        padding: 30px 0;
        margin-top: 30px;
    }
    .new-footer-bottom p {
        color: #000000ff;
        font-size: 14px;
        margin: 0;
    }
    .new-footer-bottom p a {
        color: #018a8aff; /* Kept your brand color */
        text-decoration: none;
    }
    .new-footer-bottom p a:hover {
        color: #000000ff;
    }
    /* Ensure links from Reservation block look right */
    .new-footer-col .reservation-links a {
        color: #000000ff;
        text-decoration: none;
    }
    .new-footer-col .reservation-links a:hover {
        color: #000000ff;
    }
</style>
<footer class="new-footer">
    <div class="footer-top">
        <div class="container">
            <div class="row">

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="new-footer-col">
                        <h5>Address</h5>
                        <p>
                            <?= nl2br(htmlspecialchars($contact['address'])) ?>
                        </p>
                        <a href="https://maps.app.goo.gl/WQESzoeQDwMMgBuB9?g_st=ac" target="_blank" class="line-button">Get Direction</a>

                        <h5 style="margin-top: 30px;">Connect with us</h5>
                        <div class="new-footer-socials">
                            <?php if ($plainPhone): ?>
                                <a href="<?= htmlspecialchars($waHref) ?>" target="_blank"><i class="fa fa-whatsapp"></i></a>
                            <?php endif; ?>
                            <a href="#"><i class="fa fa-facebook-square"></i></a>
                            <a href="https://www.instagram.com/retreatshivoham?igsh=MWd1MTg1emRqOHE3Ng==" target="_blank"><i class="fa fa-instagram"></i></a>
                            <a href="#"><i class="fa fa-youtube"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="new-footer-col">
                        <h5>Navigation</h5>
                        <ul>
                            <li><a href="index.php">Home</a></li>
                            <li><a href="rooms.php">Rooms</a></li>
                            <li><a href="about.php">About</a></li>
                            <li><a href="blog.php">Blogs</a></li>
                            <li><a href="contact.php">Contact</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="new-footer-col">
                        <h5>Reservation</h5>
                        <p class="reservation-links">
                            <?php if ($plainPhone): ?>
                                <a href="<?= htmlspecialchars($telHref) ?>"><?= htmlspecialchars($contact['phone']) ?></a><br>
                            <?php else: ?>
                                <?= htmlspecialchars($contact['phone']) ?><br>
                            <?php endif; ?>
                            <a href="mailto:<?= htmlspecialchars($contact['email']) ?>"><?= htmlspecialchars($contact['email']) ?></a>
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="new-footer-col">
                        <h5>Opening Hours</h5>
                        <ul>
                            <li>Mon - Fri: 9:00 AM - 6:00 PM</li>
                            <li>Sat: 10:00 AM - 4:00 PM</li>
                            <li>Sun: Closed</li>
                        </ul>
                        <p style="margin-top: 20px;">Check-in: 12 PM | Check-out: 11 AM</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="new-footer-bottom">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <p class="copy_right text-center"> Copyright ©<script>document.write(new Date().getFullYear());</script>
                        All rights reserved | This website is made with
                        <i class="fa fa-heart-o" aria-hidden="true"></i> by
                        <a href="https://my-portfolio-7bb38.web.app/" target="_blank">Neha Pattnayak</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>

<script>
window.addEventListener('scroll', function() {
  // Check if nav exists before adding class
  const nav = document.querySelector('.navbar');
  if (nav) {
    if (window.scrollY > 50) {
      nav.classList.add('sticky');
    } else {
      nav.classList.remove('sticky');
    }
  }
});
</script>