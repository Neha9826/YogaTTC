<?php
session_start();
include __DIR__.'/db.php';

if(!isset($_SESSION['yoga_host_id'])) exit;
$host_id = $_SESSION['yoga_host_id'];

$retreat_id = intval($_POST['retreat_id'] ?? 0);
if(!$retreat_id) exit;

// Only packages under retreats belonging to host
$res = $conn->query("
    SELECT p.id, p.title
    FROM yoga_packages p
    JOIN yoga_retreats r ON p.retreat_id=r.id
    JOIN organizations o ON r.organization_id=o.id
    WHERE p.retreat_id=$retreat_id AND o.created_by=$host_id
    ORDER BY p.title ASC
");

echo '<option value="">Select Package</option>';
while($row = $res->fetch_assoc()){
    echo '<option value="'.$row['id'].'">'.htmlspecialchars($row['title']).'</option>';
}
