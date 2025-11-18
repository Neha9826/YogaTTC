<?php
require_once __DIR__ . '/db.php';
session_start();

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['yoga_host_id'])) {
        throw new Exception('Unauthorized access.');
    }

    $id = intval($_POST['id'] ?? 0);
    $status = trim($_POST['status'] ?? '');

    if (!$id || !in_array($status, ['pending','contacted','converted'])) {
        throw new Exception('Invalid parameters.');
    }

    // ✅ Update status first
    $stmt = $conn->prepare("UPDATE y_query SET host_status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();

    // ✅ If converted → auto-create booking
    if ($status === 'converted') {
        // Fetch query details
        $q = $conn->prepare("SELECT * FROM y_query WHERE id=?");
        $q->bind_param("i", $id);
        $q->execute();
        $res = $q->get_result();
        if (!$res->num_rows) throw new Exception('Query not found.');
        $query = $res->fetch_assoc();
        $q->close();

        // Get retreat_id
        $rQ = $conn->prepare("SELECT retreat_id FROM yoga_packages WHERE id=?");
        $rQ->bind_param("i", $query['package_id']);
        $rQ->execute();
        $r = $rQ->get_result();
        $retreat_id = $r->fetch_assoc()['retreat_id'] ?? null;
        $rQ->close();

        if ($retreat_id) {
            // Find or create user
            $u = $conn->prepare("SELECT id FROM y_users WHERE email=? OR phone=? LIMIT 1");
            $u->bind_param("ss", $query['email'], $query['phone']);
            $u->execute();
            $uR = $u->get_result();
            if ($uR->num_rows) {
                $user_id = $uR->fetch_assoc()['id'];
            } else {
                $pass = password_hash($query['phone'], PASSWORD_BCRYPT);
                $insU = $conn->prepare("INSERT INTO y_users (name, email, phone, password) VALUES (?, ?, ?, ?)");
                $insU->bind_param("ssss", $query['name'], $query['email'], $query['phone'], $pass);
                $insU->execute();
                $user_id = $insU->insert_id;
                $insU->close();
            }
            $u->close();

            // Insert placeholder booking
            $statusB = 'pending';
            $total = 0;
            $paid = 0;
            $currency = 'INR';
            $guests = 1;

            $b = $conn->prepare("
                INSERT INTO y_bookings (user_id, retreat_id, package_id, status, total_amount, paid_amount, currency, guests)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $b->bind_param("iiissdsi", $user_id, $retreat_id, $query['package_id'], $statusB, $total, $paid, $currency, $guests);
            $b->execute();
            $booking_id = $b->insert_id;
            $b->close();

            // Store the booking id in y_query
            $upd = $conn->prepare("UPDATE y_query SET converted_booking_id=? WHERE id=?");
            $upd->bind_param("ii", $booking_id, $id);
            $upd->execute();
            $upd->close();
        }
    }

    echo json_encode(['success' => true, 'msg' => 'Status updated successfully.']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}
