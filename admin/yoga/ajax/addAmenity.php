<?php
include __DIR__ . '/../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $icon = trim($_POST['icon'] ?? 'bi-question-circle');

    if ($name !== '') {
        // Check if already exists
        $check = $conn->prepare("SELECT id, icon_class FROM yoga_amenities WHERE name = ?");
        $check->bind_param("s", $name);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $amenity = $res->fetch_assoc();
            echo json_encode([
                'status' => 'exists',
                'id' => $amenity['id'],
                'name' => $name,
                'icon' => $amenity['icon_class']
            ]);
            exit;
        }

        // Insert new amenity
        $stmt = $conn->prepare("INSERT INTO yoga_amenities (name, icon_class) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $icon);
        $stmt->execute();
        $newId = $conn->insert_id;

        echo json_encode([
            'status' => 'success',
            'id' => $newId,
            'name' => $name,
            'icon' => $icon
        ]);
        exit;
    }
}

echo json_encode(['status' => 'error']);
