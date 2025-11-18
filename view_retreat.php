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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
    <title>View Retreat | Yoga Bhawna Mission</title>
    <link rel="stylesheet" href="yoga.css">
</head>
<body class="yoga-page">

<?php include __DIR__ . '/includes/fixed_social_bar.php'; ?>
<?php include __DIR__ . '/yoga_navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'host_sidebar.php'; ?>
        <div class="col-md-9 col-lg-10 p-4">
            <h2><?= htmlspecialchars($retreat['name']) ?></h2>
            <a href="edit_retreat.php?id=<?= $retreat['id'] ?>" class="btn btn-warning mb-3">✎ Edit Retreat</a>
            <a href="all_retreats.php" class="btn btn-secondary mb-3">⬅ Back to List</a>

            <div class="card mb-3">
                <div class="row g-0">
                    <div class="col-md-4">
                        <?php
                        $img = trim($retreat['image']);
                        $img_path = (!empty($img) && file_exists('../'.$img)) 
                                    ? '../'.$img 
                                    : '../assets/default_retreat.png';
                        ?>
                        <img src="<?= $img_path ?>" class="img-fluid rounded-start" alt="Retreat Image">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <p><strong>Organization:</strong> <?= htmlspecialchars($retreat['org_name']) ?></p>
                            <p><strong>Start Date:</strong> <?= date('d M Y', strtotime($retreat['start_date'])) ?></p>
                            <p><strong>End Date:</strong> <?= date('d M Y', strtotime($retreat['end_date'])) ?></p>
                            <p><strong>Price:</strong> ₹<?= number_format($retreat['price'],2) ?></p>
                            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($retreat['description'])) ?></p>
                            <p><strong>Created At:</strong> <?= date('d M Y, H:i', strtotime($retreat['created_at'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
