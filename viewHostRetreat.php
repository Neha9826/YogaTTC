<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['yoga_host_id'])) {
    header("Location: login.php");
    exit;
}

$host_id = $_SESSION['yoga_host_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid retreat ID.");
}

$retreat_id = intval($_GET['id']);

// Fetch retreat and verify host
$stmt = $conn->prepare("
    SELECT r.*, o.name AS org_name 
    FROM yoga_retreats r 
    JOIN organizations o ON r.organization_id=o.id 
    WHERE r.id=? AND o.created_by=?
");
$stmt->bind_param("ii", $retreat_id, $host_id);
$stmt->execute();
$retreat = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$retreat) die("Retreat not found.");

// Fetch images
$images_res = $conn->query("SELECT * FROM yoga_retreat_images WHERE retreat_id=$retreat_id ORDER BY is_primary DESC, sort_order ASC");
$images = $images_res->fetch_all(MYSQLI_ASSOC);

// Fetch videos
$videos_res = $conn->query("SELECT * FROM yoga_retreat_media WHERE retreat_id=$retreat_id AND type='video'");
$videos = $videos_res->fetch_all(MYSQLI_ASSOC);

// Fetch levels
$levels_res = $conn->query("SELECT level FROM yoga_retreat_levels WHERE retreat_id=$retreat_id");
$levels = $levels_res->fetch_all(MYSQLI_ASSOC);

// Fetch amenities
$amenities_res = $conn->query("
    SELECT a.name 
    FROM yoga_retreat_amenities ra
    JOIN yoga_amenities a ON ra.amenity_id=a.id
    WHERE ra.retreat_id=$retreat_id
");
$amenities = $amenities_res->fetch_all(MYSQLI_ASSOC);

// Fetch instructors
$instructors_res = $conn->query("
    SELECT i.name, i.type 
    FROM yoga_retreat_instructors ri
    JOIN yoga_instructors i ON ri.instructor_id=i.id
    WHERE ri.retreat_id=$retreat_id
");
$instructors = $instructors_res->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__.'/includes/head.php'; ?>
    <title>View Retreat | <?= htmlspecialchars($retreat['title']) ?></title>
    <link rel="stylesheet" href="yoga.css">
</head>
<body class="yoga-page">
<?php include __DIR__.'/includes/fixed_social_bar.php'; ?>
<?php include __DIR__.'/yoga_navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'host_sidebar.php'; ?>
        <div class="col-md-9 col-lg-10 p-4">

            <h2><?= htmlspecialchars($retreat['title']) ?></h2>
            <p><strong>Organization:</strong> <?= htmlspecialchars($retreat['org_name']) ?></p>
            <p><strong>Style:</strong> <?= htmlspecialchars($retreat['style']) ?></p>
            <p><strong>Price:</strong> ₹<?= number_format($retreat['min_price'],2) ?> - ₹<?= number_format($retreat['max_price'],2) ?></p>
            <p><strong>Short Description:</strong> <?= nl2br(htmlspecialchars($retreat['short_description'])) ?></p>
            <p><strong>Full Description:</strong> <?= nl2br(htmlspecialchars($retreat['full_description'])) ?></p>
            <p><strong>Levels:</strong> <?= !empty($levels) ? implode(", ", array_column($levels, 'level')) : 'N/A' ?></p>
            <p><strong>Amenities:</strong> <?= !empty($amenities) ? implode(", ", array_column($amenities, 'name')) : 'N/A' ?></p>
            <p><strong>Instructors:</strong> 
                <?= !empty($instructors) ? implode(", ", array_map(function($i){ return htmlspecialchars($i['name'])." (".ucfirst($i['type']).")"; }, $instructors)) : 'N/A' ?>
            </p>

            <!-- Display all images -->
            <?php if(!empty($images)): ?>
                <div class="row mb-4">
                    <?php foreach($images as $img): 
                        $img_path = file_exists(__DIR__.'/'.$img['image_path']) ? 'yoga/'.$img['image_path'] : 'assets/default_retreat.png';
                    ?>
                        <div class="col-4 col-md-3 mb-3">
                            <img src="<?= $img_path ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($img['alt_text'] ?? 'Retreat Image') ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <img src="assets/default_retreat.png" class="img-fluid rounded mb-4" alt="Default Retreat">
            <?php endif; ?>

            <!-- Videos -->
            <?php if(!empty($videos)): ?>
                <h4>Videos</h4>
                <ul>
                    <?php foreach($videos as $vid): ?>
                        <li><a href="<?= htmlspecialchars($vid['media_path']) ?>" target="_blank"><?= htmlspecialchars($vid['media_path']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <a href="editHostRetreat.php?id=<?= $retreat_id ?>" class="btn btn-warning">Edit Retreat</a>
            <a href="all_host_retreats.php" class="btn btn-secondary">Back</a>

        </div>
    </div>
</div>

<?php include __DIR__.'/includes/footer.php'; ?>
</body>
</html>
