<?php
require_once __DIR__ . '/db.php'; // Ensure database connection

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'msg' => 'Invalid request method']);
    exit;
}

$package_id = isset($_POST['package_id']) ? (int)$_POST['package_id'] : 0;
$retreat_id = isset($_POST['retreat_id']) ? (int)$_POST['retreat_id'] : 0;
$name       = trim($_POST['user_name'] ?? '');
$email      = trim($_POST['user_email'] ?? '');
$rating     = (int)($_POST['rating'] ?? 5);
$text       = trim($_POST['review_text'] ?? '');

if (!$package_id || empty($name) || empty($text)) {
    echo json_encode(['success' => false, 'msg' => 'Please fill in all required fields.']);
    exit;
}

// Prepare SQL
$stmt = $conn->prepare("INSERT INTO y_reviews (package_id, retreat_id, user_name, user_email, rating, review_text, is_approved) VALUES (?, ?, ?, ?, ?, ?, 1)");

if ($stmt) {
    $stmt->bind_param("iissss", $package_id, $retreat_id, $name, $email, $rating, $text);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'msg' => 'Database error']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'msg' => 'Database preparation error']);
}
?>