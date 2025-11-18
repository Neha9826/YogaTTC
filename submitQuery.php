<?php
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'msg' => 'Invalid request']);
        exit;
    }

    include __DIR__ . '/db.php';

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $arrival_date = $_POST['arrival_date'] ?? null;
    $message = trim($_POST['message'] ?? '');
    $package_id = (int)($_POST['package_id'] ?? 0);
    $no_dates_yet = isset($_POST['no_dates_yet']) ? 1 : 0;

    if ($name === '') {
        echo json_encode(['success' => false, 'msg' => 'Name required']);
        exit;
    }

    // Insert into y_query
    $stmt = $conn->prepare("INSERT INTO y_query (package_id, name, email, phone, arrival_date, no_dates_yet, message, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param('issssis', $package_id, $name, $email, $phone, $arrival_date, $no_dates_yet, $message);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}
