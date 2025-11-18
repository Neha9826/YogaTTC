<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['yoga_host_id'])) {
    header("Location: login.php");
    exit;
}

$host_id = $_SESSION['yoga_host_id'];

// Check if instructor ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: all_instructors.php");
    exit;
}

$instructor_id = intval($_GET['id']);

// Fetch instructor details for this host
$sql = "
    SELECT i.*, o.name AS org_name 
    FROM yoga_instructors i
    JOIN organizations o ON i.organization_id = o.id
    WHERE i.id = $instructor_id AND o.created_by = $host_id
    LIMIT 1
";
$res = $conn->query($sql);

if ($res->num_rows === 0) {
    echo "<div class='container mt-5'>Instructor not found.</div>";
    exit;
}

$instructor = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
    <title>View Instructor | Yoga Bhawna Mission</title>
    <link rel="stylesheet" href="yoga.css">
</head>
<body class="yoga-page">

<?php include __DIR__ . '/includes/fixed_social_bar.php'; ?>
<?php include __DIR__ . '/yoga_navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'host_sidebar.php'; ?>
        <div class="col-md-9 col-lg-10 p-4">
            <h2>Instructor Profile</h2>
            <div class="mb-3">
                <a href="all_instructors.php" class="btn btn-secondary">⬅ Back to List</a>
                <a href="edit_instructor.php?id=<?= $instructor['id'] ?>" class="btn btn-primary">✎ Edit</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <!-- Profile Image -->
                        <div class="col-md-3 text-center mb-3">
                            <?php
                            $photo = trim($instructor['photo']);
                            $photo_path = (!empty($photo) && file_exists('../'.$photo)) 
                                          ? '../'.$photo 
                                          : '../assets/default_profile.png';
                            ?>
                            <img src="<?= $photo_path ?>" class="img-fluid rounded-circle" alt="<?= htmlspecialchars($instructor['name']) ?>">
                        </div>

                        <!-- Instructor Details -->
                        <div class="col-md-9">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Name:</th>
                                    <td><?= htmlspecialchars($instructor['name']) ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?= htmlspecialchars($instructor['email']) ?></td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td><?= htmlspecialchars($instructor['phone']) ?></td>
                                </tr>
                                <tr>
                                    <th>Type:</th>
                                    <td><?= ucfirst($instructor['type']) ?></td>
                                </tr>
                                <tr>
                                    <th>Organization:</th>
                                    <td><?= htmlspecialchars($instructor['org_name']) ?></td>
                                </tr>
                                <tr>
                                    <th>Bio:</th>
                                    <td><?= nl2br(htmlspecialchars($instructor['bio'])) ?></td>
                                </tr>
                                <tr>
                                    <th>Social Links:</th>
                                    <td><?= nl2br(htmlspecialchars($instructor['social_links'])) ?></td>
                                </tr>
                                <tr>
                                    <th>Verification File:</th>
                                    <td>
                                        <?php if (!empty($instructor['verification_file']) && file_exists('../'.$instructor['verification_file'])): ?>
                                            <a href="../<?= $instructor['verification_file'] ?>" target="_blank">View File</a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created At:</th>
                                    <td><?= date('d M Y, H:i', strtotime($instructor['created_at'])) ?></td>
                                </tr>
                            </table>
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
