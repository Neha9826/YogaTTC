<?php
session_start();
include __DIR__.'/db.php';

if(!isset($_SESSION['yoga_host_id'])) exit;
$host_id = $_SESSION['yoga_host_id'];

$org_id = intval($_POST['org_id'] ?? 0);
if(!$org_id) exit;

$res = $conn->query("
    SELECT r.id, r.title 
    FROM yoga_retreats r
    JOIN organizations o ON r.organization_id=o.id
    WHERE r.organization_id=$org_id AND o.created_by=$host_id
    ORDER BY r.title ASC
");

echo '<option value="">Select Retreat</option>';
while($row = $res->fetch_assoc()){
    echo '<option value="'.$row['id'].'">'.htmlspecialchars($row['title']).'</option>';
}
