<?php
// We no longer include db.php here, as it should be included by the main page.
$waHref = $waHref ?? '#';
$telHref = $telHref ?? '#';
$mailHref = $mailHref ?? '#';
?>

<style>
.fixed_social_bar {
    bottom: 40px !important; /* Adjust this value as needed */
    z-index: 999;
}
</style>

<div class="social-sidebar">
    <ul>
        <li class="extra-icon hidden-item"><a href="#" target="_blank"><i class="fa fa-youtube"></i></a></li>
        <li class="extra-icon hidden-item"><a href="https://www.instagram.com/retreatshivoham?igsh=MWd1MTg1emRqOHE3Ng==" target="_blank"><i class="fa fa-instagram"></i></a></li>
        <li class="extra-icon hidden-item"><a href="#" target="_blank"><i class="fa fa-facebook-square"></i></a></li>
        
        <li><a href="javascript:void(0)" class="toggle-btn"><i class="fa fa-plus"></i></a></li>
        <li><a href="<?= htmlspecialchars($mailHref) ?>" target="_blank"><i class="fa fa-envelope"></i></a></li>
        <li><a href="<?= htmlspecialchars($telHref) ?>" target="_blank"><i class="fa fa-phone"></i></a></li>
        <li><a href="<?= htmlspecialchars($waHref) ?>" target="_blank"><i class="fa fa-whatsapp"></i></a></li>
    </ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const socialSidebar = document.querySelector('.social-sidebar');
    const toggleButton = document.querySelector('.social-sidebar .toggle-btn');

    if (toggleButton && socialSidebar) {
        toggleButton.addEventListener('click', function (e) {
            // Prevent the click from immediately propagating to the document listener
            e.stopPropagation(); 
            socialSidebar.classList.toggle('active');
        });
    }

    // New event listener for clicks anywhere on the document
    document.addEventListener('click', function (e) {
        // Check if the click occurred outside the social sidebar
        if (!socialSidebar.contains(e.target) && socialSidebar.classList.contains('active')) {
            socialSidebar.classList.remove('active');
        }
    });
});
</script>
