<?php
// admin/organization/api/addLocation.php
include __DIR__ . '/../../db.php';
header('Content-Type: application/json');
$payload = json_decode(file_get_contents('php://input'), true);
$level = $payload['level'] ?? null;
$parent = $payload['parent'] ?? null;
$name = trim($payload['name'] ?? '');

if (!$level || !$name) {
  echo json_encode(['success'=>false,'error'=>'invalid']);
  exit;
}

// check duplicate (unique constraint exists in dump: uniq_level_parent_name)
$stmt = $conn->prepare("SELECT id FROM location_custom WHERE level=? AND parent=? AND name=?");
$stmt->bind_param('sss',$level,$parent,$name);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows) {
  echo json_encode(['success'=>false,'error'=>'Already exists']);
  exit;
}

$stmt = $conn->prepare("INSERT INTO location_custom (level,parent,name,created_by) VALUES (?,?,?,?)");
$created_by = $_SESSION['y_user_id'] ?? null;
$stmt->bind_param('sssi',$level,$parent,$name,$created_by);
if ($stmt->execute()) {
  echo json_encode(['success'=>true,'id'=>$stmt->insert_id]);
} else {
  echo json_encode(['success'=>false,'error'=>$stmt->error]);
}
