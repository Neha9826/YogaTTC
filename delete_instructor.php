<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['yoga_host_id'])) {
    header("Location: login.php");
    exit;
}

$host_id = $_SESSION['yoga_host_id'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: all_instructors.php");
    exit;
}

$instructor_id = intval($_GET['id']);

// Ensure this instructor belongs to this host
$sql = "
    SELECT i.*, o.id AS org_id
    FROM yoga_instructors i
    JOIN organizations o ON i.organization_id = o.id
    WHERE i.id = $instructor_id AND o.created_by = $host_id
    LIMIT 1
";
$res = $conn->query($sql);
if ($res->num_rows === 0) {
    header("Location: all_instructors.php");
    exit;
}

// Optionally delete photo file
$instructor = $res->fetch_assoc();
if (!empty($instructor['photo']) && file_exists('../'.$instructor['photo'])) {
    unlink('../'.$instructor['photo']);
}

// Delete instructor
$conn->query("DELETE FROM yoga_instructors WHERE id = $instructor_id");

header("Location: all_instructors.php");
exit;
