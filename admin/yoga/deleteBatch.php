<?php
include '../../../db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid batch ID.");
}

$id = (int)$_GET['id'];

// Delete batch
$stmt = $conn->prepare("DELETE FROM y_batches WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: allBatches.php?msg=deleted");
    exit;
} else {
    echo "Error deleting batch.";
}
