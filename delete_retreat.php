<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['yoga_host_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: all_retreats.php");
    exit;
}

$retreat_id = intval($_GET['id']);
$host_id = $_SESSION['yoga_host_id'];

// Fetch retreat to delete image
$sql = "SELECT r.image FROM retreats r JOIN organizations o ON r.organization_id=o.id WHERE r.id=$retreat_id AND o.created_by=$host_id";
$res = $conn->query($sql);
if ($res->num_rows > 0) {
    $retreat = $res->fetch_assoc();
    if (!empty($retreat['image']) && file_exists('../'.$retreat['image'])) {
        unlink('../'.$retreat['image']);
    }
    $conn->query("DELETE FROM retreats WHERE id=$retreat_id");
}

header("Location: all_retreats.php");
exit;
