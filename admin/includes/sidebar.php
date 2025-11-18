<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../config.php';

?>

<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">Core</div>
                <a class="nav-link" href="../dashboard.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>
                <!-- <div class="sb-sidenav-menu-heading">Interface</div> -->
                
                <a class="nav-link" href="yoga/index.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-table"></i></div>
                    Yoga
                </a>
                <!-- <div class="collapse" id="yogaDropdown" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="addYogaDropdown.php">Add Yoga Option</a>
                        <a class="nav-link" href="allYogaDropdown.php">All Yoga Options</a>
                    </nav>
                </div> -->
                
                
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
            <?= isset($_SESSION['emp_name']) ? htmlspecialchars($_SESSION['emp_name']) : 'Unknown User'; ?>
        </div>
    </nav>
</div>