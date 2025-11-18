<?php
// admin/organization/api/getLocations.php
include __DIR__ . '/../../db.php';
header('Content-Type: application/json');

$level = $_GET['level'] ?? 'continent'; // continent|country|state|city
$parent = $_GET['parent'] ?? '';

$allowed = ['continent','country','state','city'];
if (!in_array($level, $allowed)) {
    echo json_encode([]);
    exit;
}

// First: try location_custom entries
if ($level === 'continent') {
    $stmt = $conn->prepare("SELECT DISTINCT name FROM location_custom WHERE level='continent' ORDER BY name");
    $stmt->execute();
    $res = $stmt->get_result();
    $out = [];
    while ($r = $res->fetch_assoc()) $out[] = $r;
    if ($out) { echo json_encode($out); exit; }
}

// Otherwise, fetch by parent
if ($parent) {
    $stmt = $conn->prepare("SELECT DISTINCT name FROM location_custom WHERE level=? AND parent=? ORDER BY name");
    $stmt->bind_param('ss', $level, $parent);
} else {
    $stmt = $conn->prepare("SELECT DISTINCT name FROM location_custom WHERE level=? ORDER BY name");
    $stmt->bind_param('s', $level);
}
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($r = $res->fetch_assoc()) $out[] = $r;

echo json_encode($out);
