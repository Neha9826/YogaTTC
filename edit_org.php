<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['yoga_host_id'])) {
    header("Location: login.php");
    exit;
}

$host_id = $_SESSION['yoga_host_id'];
$id = intval($_GET['id'] ?? 0);

$errors = [];
$success = '';

// Fetch org
$sql = "SELECT * FROM organizations WHERE id=$id AND created_by=$host_id";
$res = mysqli_query($conn, $sql);
if (!$res || mysqli_num_rows($res) == 0) {
    die("❌ Organization not found or unauthorized.");
}
$org = mysqli_fetch_assoc($res);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email']);
    $contact_phone = mysqli_real_escape_string($conn, $_POST['contact_phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $gst_doc_path = $org['gst_doc'];
    $msme_doc_path = $org['msme_doc'];

    $uploadDir = __DIR__ . '/uploads/docs/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    // Upload GST only if not already uploaded
    if (empty($org['gst_doc']) && !empty($_FILES['gst_doc']['name'])) {
        $gst_ext = pathinfo($_FILES['gst_doc']['name'], PATHINFO_EXTENSION);
        $gst_name = 'gst_' . time() . '.' . $gst_ext;
        if (move_uploaded_file($_FILES['gst_doc']['tmp_name'], $uploadDir . $gst_name)) {
            $gst_doc_path = 'uploads/docs/' . $gst_name;
        } else {
            $errors[] = "Failed to upload GST document.";
        }
    }

    // Upload MSME only if not already uploaded
    if (empty($org['msme_doc']) && !empty($_FILES['msme_doc']['name'])) {
        $msme_ext = pathinfo($_FILES['msme_doc']['name'], PATHINFO_EXTENSION);
        $msme_name = 'msme_' . time() . '.' . $msme_ext;
        if (move_uploaded_file($_FILES['msme_doc']['tmp_name'], $uploadDir . $msme_name)) {
            $msme_doc_path = 'uploads/docs/' . $msme_name;
        } else {
            $errors[] = "Failed to upload MSME document.";
        }
    }

    if (empty($errors)) {
        $update = "UPDATE organizations SET 
            contact_email='$contact_email',
            contact_phone='$contact_phone',
            address='$address',
            gst_doc='$gst_doc_path',
            msme_doc='$msme_doc_path'
            WHERE id=$id AND created_by=$host_id";

        if (mysqli_query($conn, $update)) {
            $success = "✅ Organization updated successfully.";
            $org = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM organizations WHERE id=$id AND created_by=$host_id"));
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
    <title>Edit Organization | Yoga Bhawna Mission</title>
    <link rel="stylesheet" href="yoga.css">
</head>
<body class="yoga-page">

<?php include __DIR__ . '/includes/fixed_social_bar.php'; ?>
<?php include __DIR__ . '/yoga_navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'host_sidebar.php'; ?>
        <div class="col-md-9 col-lg-10 p-4">
            <h1>Edit Organization</h1>

            <?php if ($errors): ?>
                <div class="alert alert-danger"><?php foreach($errors as $e) echo $e."<br>"; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <!-- Readonly Info -->
                    <div class="col-md-6 mb-3">
                        <label>Name</label>
                        <input type="text" value="<?= htmlspecialchars($org['name']) ?>" class="form-control" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Website</label>
                        <input type="text" value="<?= htmlspecialchars($org['website']) ?>" class="form-control" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Country</label>
                        <input type="text" value="<?= htmlspecialchars($org['country']) ?>" class="form-control" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>City</label>
                        <input type="text" value="<?= htmlspecialchars($org['city']) ?>" class="form-control" disabled>
                    </div>

                    <!-- Editable Fields -->
                    <div class="col-md-6 mb-3">
                        <label>Contact Email</label>
                        <input type="email" name="contact_email" value="<?= htmlspecialchars($org['contact_email']) ?>" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Contact Phone</label>
                        <input type="text" name="contact_phone" value="<?= htmlspecialchars($org['contact_phone']) ?>" class="form-control">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label>Address</label>
                        <input type="text" name="address" value="<?= htmlspecialchars($org['address']) ?>" class="form-control">
                    </div>

                    <!-- Docs -->
                    <div class="col-md-6 mb-3">
                        <label>GST Document</label><br>
                        <?php if($org['gst_doc']): ?>
                            <a href="admin/<?= $org['gst_doc'] ?>" target="_blank">View Uploaded</a><br>
                            <small class="text-muted">Already uploaded – cannot replace</small>
                        <?php else: ?>
                            <input type="file" name="gst_doc" class="form-control">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>MSME Document</label><br>
                        <?php if($org['msme_doc']): ?>
                            <a href="admin/<?= $org['msme_doc'] ?>" target="_blank">View Uploaded</a><br>
                            <small class="text-muted">Already uploaded – cannot replace</small>
                        <?php else: ?>
                            <input type="file" name="msme_doc" class="form-control">
                        <?php endif; ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update Organization</button>
                <a href="all_org.php" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
