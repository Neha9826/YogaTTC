<?php
// yoga/yoga_session.php
if (session_status() === PHP_SESSION_NONE) {
    // Ensure the same session is shared by all /yoga/ pages only
    ini_set('session.cookie_path', '/yoga');
    session_start();
}
?>
