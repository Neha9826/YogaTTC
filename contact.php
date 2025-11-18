<!DOCTYPE html>
<html lang="en">
    <head>
        <?php include 'head.php'; ?>
    </head>

    <body>
        <!-- Top Bar Start -->
        <?php include 'topBar.php'; ?>        
        <!-- Top Bar End -->
        <!-- Nav Bar Start -->
        <?php include 'ybm_navbar.php'; ?>
        <!-- Nav Bar End -->


        <!-- Page Header Start -->
        <div class="page-header contact-header">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <h2>Contact</h2>
                    </div>
                    <div class="col-12">
                        <a href="">Home</a>
                        <a href="">Contact</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Page Header End -->


        <!-- Contact Start -->
        <div class="contact">
            <div class="container">
                <div class="section-header text-center wow zoomIn" data-wow-delay="0.1s">
                    <p>Get In Touch</p>
                    <h2>For Any Query</h2>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-md-4 contact-item wow zoomIn" data-wow-delay="0.2s">
                                <a href="https://maps.app.goo.gl/9ECkvdQ3eDhpAENP9">
                                    <i class="fa fa-map-marker-alt"></i>
                                    <div class="contact-text">
                                        <h2>Location</h2>
                                        <p>Chungi Badethi, Gangotri ByPass Road, Uttarkashi, Uttarakhand-249193</p>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4 contact-item wow zoomIn" data-wow-delay="0.4s">
                                <a href="tel:+919917003456">
                                    <i class="fa fa-phone-alt"></i>
                                    <div class="contact-text">
                                        <h2>Phone</h2>
                                        <p>+91-9917003456</p>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4 contact-item wow zoomIn" data-wow-delay="0.6s">
                                <a href="mailto:info@yogabhawnamission.com">
                                    <i class="far fa-envelope"></i>
                                    <div class="contact-text">
                                        <h2>Email</h2>
                                        <p>info@yogabhawnamission.com</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="query-form" id="query">
                            <div class="container">
                                <div class="section-header text-center wow zoomIn" data-wow-delay="0.1s">
                                    <p>Have any questions? We'd love to hear from you!</p>
                                    <h2>Submit Your Query</h2>
                                </div>
                                <div class="row justify-content-center">
                                    <div class="col-lg-8 col-md-10">
                                        <form class="query-form-inner wow fadeInUp" data-wow-delay="0.2s" action="sendQuery.php" method="post">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="fullname" placeholder="Full Name" required>
                                            </div>
                                            <div class="form-group">
                                                <input type="date" class="form-control" name="dob" placeholder="Date of Birth" required>
                                            </div>
                                            <div class="form-group">
                                                <textarea class="form-control" name="address" rows="2" placeholder="Address" required></textarea>
                                            </div>
                                            <div class="form-group">
                                                <input type="tel" class="form-control" name="phone" placeholder="Phone Number" required>
                                            </div>
                                            <div class="form-group">
                                                <input type="email" class="form-control" name="email" placeholder="Email Address" required>
                                            </div>
                                            <div class="form-group">
                                                <select class="form-control" name="option" required>
                                                    <option value="">Query About</option>
                                                    <option value="YTTC">Yoga Teacher's Training Course</option>
                                                    <option value="Yoga Retreat">Yoga Retreat</option>
                                                </select>
                                            </div>
                                            <div class="text-center">
                                                <button type="submit" class="btn">Submit</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Contact End -->


        <!-- Footer Start -->
        <?php include 'ybm_footer.php'; ?>
        <!-- Footer End -->

        <a href="#" class="back-to-top"><i class="fa fa-chevron-up"></i></a>

        <div class="whatsapp widget-sec">
          <a href="tel:+919917003456" class="cta-btn phone" title="Call Now">
            <i class="fa fa-phone-alt"></i>
          </a>
          <a aria-label="Chat on WhatsApp" href="https://wa.me/+919917003456" target="_blank" class="cta-btn whatsapp" title="Chat on WhatsApp">
            <i class="fab fa-whatsapp"></i>
          </a>
        </div>

        <!-- JavaScript Libraries -->
        <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
        <script src="lib/easing/easing.min.js"></script>
        <script src="lib/wow/wow.min.js"></script>
        <script src="lib/owlcarousel/owl.carousel.min.js"></script>
        <script src="lib/isotope/isotope.pkgd.min.js"></script>
        <script src="lib/lightbox/js/lightbox.min.js"></script>
        
        <!-- Contact Javascript File -->
        <script src="mail/jqBootstrapValidation.min.js"></script>
        <script src="mail/contact.js"></script>

        <!-- Template Javascript -->
        <script src="js/main.js"></script>
    </body>
</html>
