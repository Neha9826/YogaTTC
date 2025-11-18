<?php
session_start();

// Unset both user and host sessions
unset(
    $_SESSION['yoga_user_id'],
    $_SESSION['yoga_user_name'],
    $_SESSION['yoga_host_id'],
    $_SESSION['yoga_host_name']
);

// Optionally destroy session completely
session_destroy();

// Redirect to yoga home
header("Location: " . dirname($_SERVER['PHP_SELF']) . "/index.php");
exit;
