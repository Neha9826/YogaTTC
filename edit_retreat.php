<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['yoga_host_id'])) {
    header("Location: login.php");
    exit;
}

$host_id = $_SESSION['yoga_host_id'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: all_retreats.php");
    exit;
}

$retreat_id = intval($_GET['id']);

// Fetch retreat
$sql = "
    SELECT r.*, o.name AS org_name
    FROM retreats r
    JOIN organizations o ON r.organization_id = o.id
    WHERE r.id = $retreat_id AND o.created_by = $host_id
    LIMIT 1
";
$res = $conn->query($sql);
if ($res->num_rows === 0) {
    echo "<div class='container mt-5'>Retreat not found.</div>";
    exit;
}
$retreat = $res->fetch_assoc();

// Fetch organizations
$orgs_res = $conn->query("SELECT id, name FROM organizations WHERE created_by = $host_id ORDER BY name ASC");

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $organization_id = intval($_POST['organization_id']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $price = floatval($_POST['price']);
    $description = $conn->real_escape_string($_POST['description']);

    $img_sql = "";

    // Delete current image
    if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1' && !empty($retreat['image']) && file_exists('../'.$retreat['image'])) {
        unlink('../'.$retreat['image']);
        $img_sql = ", image=NULL";
    }

    // Upload new image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_name = 'retreat_' . time() . '.' . $ext;
        $img_path = 'uploads/retreats/' . $new_name;
        move_uploaded_file($_FILES['image']['tmp_name'], '../'.$img_path);
        $img_sql = ", image='$img_path'";
    }

    $update_sql = "
        UPDATE retreats SET
            name='$name',
            organization_id=$organization_id,
            start_date='$start_date',
            end_date='$end_date',
            price=$price,
            description='$description'
            $img_sql
        WHERE id=$retreat_id
    ";

    if ($conn->query($update_sql)) {
        header("Location: view_retreat.php?id=$retreat_id");
        exit;
    } else {
        $errors[] = "Database error: ".$conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
    <title>Edit Retreat | Yoga Bhawna Mission</title>
    <link rel="stylesheet" href="yoga.css">
</head>
<body class="yoga-page">

<?php include __DIR__ . '/includes/fixed_social_bar.php'; ?>
<?php include __DIR__ . '/yoga_navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'host_sidebar.php'; ?>
        <div class="col-md-9 col-lg-10 p-4">
            <h2>Edit Retreat</h2>
            <a href="view_retreat.php?id=<?= $retreat['id'] ?>" class="btn btn-secondary mb-3">⬅ Back</a>

            <?php if ($errors): ?>
                <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <!-- Top Image -->
                <div class="mb-4 text-center">
                    <?php
                    $img = trim($retreat['image']);
                    $img_path = (!empty($img) && file_exists('../'.$img)) ? '../'.$img : '../assets/default_retreat.png';
                    ?>
                    <img src="<?= $img_path ?>" class="rounded mb-2" width="150" height="100" alt="Retreat Image">
                    <?php if (!empty($retreat['image'])): ?>
                        <div class="form-check">
                            <input type="checkbox" name="delete_image" value="1" class="form-check-input" id="delete_image">
                            <label class="form-check-label" for="delete_image">Delete current image</label>
                        </div>
                    <?php endif; ?>
                    <div class="mt-2">
                        <input type="file" name="image" class="form-control">
                    </div>
                </div>

                <!-- Two-column form -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($retreat['name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label>Organization</label>
                        <select name="organization_id" class="form-control" required>
                            <option value="">Select Organization</option>
                            <?php while($org = $orgs_res->fetch_assoc()): ?>
                                <option value="<?= $org['id'] ?>" <?= $org['id']==$retreat['organization_id']?'selected':'' ?>><?= htmlspecialchars($org['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?= $retreat['start_date'] ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?= $retreat['end_date'] ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label>Price (₹)</label>
                        <input type="number" name="price" class="form-control" step="0.01" value="<?= $retreat['price'] ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label>Description</label>
                        <textarea name="description" class="form-control"><?= htmlspecialchars($retreat['description']) ?></textarea>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Update Retreat</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
