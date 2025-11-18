<?php
include '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$type = $data['type'] ?? '';
$number = $data['number'] ?? '';

$response = ['success' => false];

// ---------------------------
// MOCK VALIDATION (Safe for demo)
// Replace this with real API call later
// ---------------------------
if ($type === 'aadhaar') {
    // Aadhaar: must be 12 digits
    if (preg_match('/^[0-9]{12}$/', $number)) {
        $response['success'] = true;
    }
} elseif ($type === 'pan') {
    // PAN: 5 letters + 4 digits + 1 letter
    if (preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', strtoupper($number))) {
        $response['success'] = true;
    }
}

// ---------------------------
// REAL API INTEGRATION EXAMPLE (future)
// ---------------------------
/*
$apiUrl = "https://api.karza.in/v2/verify";
$apiKey = "YOUR_API_KEY";

$payload = [
    "id_number" => $number,
    "id_type"   => $type
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "x-api-key: $apiKey"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$apiResponse = curl_exec($ch);
if ($apiResponse !== false) {
    $decoded = json_decode($apiResponse, true);
    if (!empty($decoded['result']) && $decoded['result'] === 'success') {
        $response['success'] = true;
    }
}
curl_close($ch);
*/
// ---------------------------

echo json_encode($response);
