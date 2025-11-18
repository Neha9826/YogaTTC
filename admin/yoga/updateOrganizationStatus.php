<?php
// admin/organization/updateOrganizationStatus.php
include __DIR__ . '/../db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $org_id = intval($_POST['org_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    if (in_array($status, ['pending','approved','denied'])) {
        $query = "UPDATE organizations SET status='$status' WHERE id=$org_id";
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Organization status updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating status: " . mysqli_error($conn);
        }
    }
}

header("Location: yoga/organization/manageOrganizations.php");
exit;
