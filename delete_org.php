<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['yoga_host_id'])) {
    header("Location: login.php");
    exit;
}

$host_id = $_SESSION['yoga_host_id'];
$id = intval($_GET['id'] ?? 0);

// Verify org belongs to host
$res = mysqli_query($conn, "SELECT * FROM organizations WHERE id=$id AND created_by=$host_id");
if (!$res || mysqli_num_rows($res) == 0) {
    die("❌ Unauthorized or organization not found.");
}

// Delete docs if exist
$org = mysqli_fetch_assoc($res);
if ($org['gst_doc'] && file_exists(__DIR__ . '/../' . $org['gst_doc'])) {
    unlink(__DIR__ . '/../' . $org['gst_doc']);
}
if ($org['msme_doc'] && file_exists(__DIR__ . '/../' . $org['msme_doc'])) {
    unlink(__DIR__ . '/../' . $org['msme_doc']);
}

// Delete record
mysqli_query($conn, "DELETE FROM organizations WHERE id=$id AND created_by=$host_id");

// Redirect back
header("Location: all_org.php");
exit;
