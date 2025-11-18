<?php
// /admin/yoga/deleteInstructor.php
include '../../session.php';
include '../../db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid instructor ID.';
    header('Location: manageInstructors.php');
    exit;
}

$stmt = $conn->prepare("DELETE FROM yoga_instructors WHERE id = ?");
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    $_SESSION['flash_success'] = 'Instructor deleted.';
} else {
    $_SESSION['flash_error'] = 'Error deleting instructor: ' . $conn->error;
}

header('Location: manageInstructors.php');
exit;
