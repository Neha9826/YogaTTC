<?php
require_once __DIR__ . '/yoga_session.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // ✅ Check login
    if (empty($_SESSION['yoga_user_id'])) {
        echo json_encode([
            'success' => false,
            'require_login' => true,
            'msg' => 'Please login before booking.'
        ]);
        exit;
    }

    $user_id    = (int) $_SESSION['yoga_user_id'];
    $package_id = (int) ($_POST['package_id'] ?? 0);
    $batch_id   = isset($_POST['batch_id']) && (int)$_POST['batch_id'] > 0 ? (int)$_POST['batch_id'] : null;
    $accom_id   = (int) ($_POST['accommodation_id'] ?? 0);
    $arrival    = trim($_POST['arrival_date'] ?? '');
    $no_dates   = isset($_POST['no_dates_yet']) ? 1 : 0;
    $message    = trim($_POST['message'] ?? '');
    $guests     = isset($_POST['guests']) ? (int)$_POST['guests'] : 1;

    if ($package_id <= 0) {
        echo json_encode(['success' => false, 'msg' => 'Invalid package selected.']);
        exit;
    }

    // ✅ Get retreat_id for package
    $stmt = $conn->prepare("SELECT retreat_id FROM yoga_packages WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res->num_rows) throw new Exception("Invalid package ID.");
    $retreat_id = (int)$res->fetch_assoc()['retreat_id'];
    $stmt->close();

    // ✅ Determine total amount (use accommodation price if exists)
    $total_amount = 0;
    if ($accom_id > 0) {
        $aQ = $conn->prepare("SELECT price_per_person FROM yoga_package_accommodations WHERE id=? AND package_id=?");
        $aQ->bind_param("ii", $accom_id, $package_id);
        $aQ->execute();
        $aR = $aQ->get_result();
        if ($aR->num_rows) {
            $total_amount = (float)$aR->fetch_assoc()['price_per_person'];
        }
        $aQ->close();
    } else {
        $pQ = $conn->prepare("SELECT price_per_person FROM yoga_packages WHERE id=?");
        $pQ->bind_param("i", $package_id);
        $pQ->execute();
        $pR = $pQ->get_result();
        if ($pR->num_rows) {
            $total_amount = (float)$pR->fetch_assoc()['price_per_person'];
        }
        $pQ->close();
    }

    // ✅ Insert booking (handles nullable batch_id)
    $status = 'pending';

    if ($batch_id === null) {
        // No batch selected
        $insert = $conn->prepare("
            INSERT INTO y_bookings (user_id, retreat_id, package_id, status, total_amount, guests)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $insert->bind_param("iiisdi", 
            $user_id, 
            $retreat_id, 
            $package_id, 
            $status, 
            $total_amount, 
            $guests
        );
    } else {
        // Batch selected
        $insert = $conn->prepare("
            INSERT INTO y_bookings (user_id, retreat_id, package_id, batch_id, status, total_amount, guests)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $insert->bind_param("iiiisdi", 
            $user_id, 
            $retreat_id, 
            $package_id, 
            $batch_id, 
            $status, 
            $total_amount, 
            $guests
        );
    }

    if (!$insert->execute()) {
        throw new Exception($insert->error);
    }

    echo json_encode(['success' => true, 'msg' => 'Booking request submitted successfully!']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}
?>
