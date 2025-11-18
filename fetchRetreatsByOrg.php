<?php
session_start();
include __DIR__ . '/db.php';

if(!isset($_SESSION['yoga_host_id'])) exit;

$host_id = $_SESSION['yoga_host_id'];
$org_id = intval($_GET['org_id'] ?? 0);

if(!$org_id) exit(json_encode([]));

// Fetch retreats owned by this host under selected organization
$res = $conn->query("
    SELECT r.id, r.title 
    FROM yoga_retreats r 
    JOIN organizations o ON r.organization_id=o.id 
    WHERE r.organization_id=$org_id AND o.created_by=$host_id
    ORDER BY r.title ASC
");

$retreats = $res->fetch_all(MYSQLI_ASSOC);
echo json_encode($retreats);
