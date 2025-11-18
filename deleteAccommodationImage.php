<?php
include __DIR__ . '/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['img_id'])) {
    echo json_encode(['success'=>false, 'msg'=>'Invalid request']);
    exit;
}

$img_id = intval($_POST['img_id']);
if ($img_id <= 0) {
    echo json_encode(['success'=>false]);
    exit;
}

$res = $conn->query("SELECT image_path FROM yoga_accommodation_images WHERE id=$img_id");
if ($res->num_rows) {
    $row = $res->fetch_assoc();
    $filePath = "../" . $row['image_path'];
    if (file_exists($filePath)) unlink($filePath);
    $conn->query("DELETE FROM yoga_accommodation_images WHERE id=$img_id");
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false]);
}
?>
