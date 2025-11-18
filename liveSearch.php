<?php
// liveSearch.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/yoga_session.php';
include __DIR__ . '/db.php';

// read q safely
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q === '') {
    echo json_encode([]);
    exit;
}

$q_esc = $conn->real_escape_string($q);

// simple relevance: title / retreat title / description / org name / country
$sql = "
  SELECT
    p.id,
    p.title AS package_title,
    p.price_per_person,
    p.nights,
    r.title AS retreat_title,
    o.country,
    (SELECT image_path FROM yoga_retreat_images WHERE retreat_id = r.id LIMIT 1) AS image_path
  FROM yoga_packages p
  JOIN yoga_retreats r ON p.retreat_id = r.id
  JOIN organizations o ON r.organization_id = o.id
  WHERE p.is_published = 1 AND r.is_published = 1
    AND (p.title LIKE '%$q_esc%' OR p.description LIKE '%$q_esc%' OR r.title LIKE '%$q_esc%' OR o.name LIKE '%$q_esc%' OR o.country LIKE '%$q_esc%')
  ORDER BY p.created_at DESC
  LIMIT 10
";

$res = $conn->query($sql);
$out = [];
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $row['url'] = 'packageDetails.php?id=' . (int)$row['id'];
        $out[] = $row;
    }
}

echo json_encode($out);
