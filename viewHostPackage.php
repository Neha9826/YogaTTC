<?php
session_start();
include __DIR__ . '/db.php';

if(!isset($_SESSION['yoga_host_id'])) header("Location: login.php");

$host_id = $_SESSION['yoga_host_id'];
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) die("Invalid ID");

$id = intval($_GET['id']);

$sql = "
SELECT p.*, r.title AS retreat_name, o.name AS org_name
FROM yoga_packages p
JOIN yoga_retreats r ON p.retreat_id = r.id
JOIN organizations o ON r.organization_id = o.id
WHERE p.id=$id AND o.created_by=$host_id
LIMIT 1
";
$res = $conn->query($sql);
$package = $res->fetch_assoc();
if(!$package) die("Package not found");

// ✅ Fetch extra sections
$extraSecRes = $conn->query("SELECT title, description FROM yoga_package_extra_sections WHERE package_id=$id ORDER BY sort_order ASC");
$extra_sections = $extraSecRes ? $extraSecRes->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__.'/includes/head.php'; ?>
  <title>View Package | <?= htmlspecialchars($package['title']) ?></title>
  <link rel="stylesheet" href="yoga.css">
  <style>
    .schedule-table {
      border-collapse: collapse;
      width: 100%;
      margin-top: 20px;
    }
    .schedule-table th, .schedule-table td {
      border: 1px solid #ddd;
      padding: 8px 12px;
    }
    .schedule-table th {
      background: #f8f9fa;
      text-align: left;
    }
    .schedule-empty {
      color: #888;
      font-style: italic;
      margin-top: 8px;
    }

    /* --- ADD THIS --- */
    .rich-content-display {
      border: 1px solid #eee;
      padding: 1rem;
      border-radius: 4px;
      background: #fdfdfd;
      margin-bottom: 1rem;
    }
    .rich-content-display h1,
    .rich-content-display h2,
    .rich-content-display h3 {
      margin-top: 1rem;
      margin-bottom: 0.5rem;
    }
    .rich-content-display ul,
    .rich-content-display ol {
      padding-left: 2rem;
    }
    /* --- END ADD --- */
    }
  </style>
</head>
<body class="yoga-page">

<?php include __DIR__.'/includes/fixed_social_bar.php'; ?>
<?php include __DIR__.'/yoga_navbar.php'; ?>

<div class="container-fluid">
  <div class="row">
    <?php include 'host_sidebar.php'; ?>
    <div class="col-md-9 col-lg-10 p-4">
      <h2><?= htmlspecialchars($package['title']) ?></h2>
      <p><strong>Organization:</strong> <?= htmlspecialchars($package['org_name']) ?></p>
      <p><strong>Retreat:</strong> <?= htmlspecialchars($package['retreat_name']) ?></p>
      <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($package['description'])) ?></p>
      <p><strong>Price per person:</strong> ₹<?= number_format($package['price_per_person'],2) ?></p>
      <p><strong>Persons:</strong> <?= $package['min_persons'] ?> - <?= $package['max_persons'] ?></p>
      <p><strong>Nights:</strong> <?= $package['nights'] ?></p>
      <p><strong>Meals included:</strong> <?= $package['meals_included'] ? 'Yes' : 'No' ?></p>
      <p><strong>Created:</strong> <?= htmlspecialchars($package['created_at']) ?></p>
      <p><strong>Last Updated:</strong> <?= htmlspecialchars($package['updated_at']) ?></p>

      <h4 class="mt-4">Program</h4>
      <?php if (!empty($package['program'])): ?>
        <div class="rich-content-display">
          <?= $package['program'] // Outputting raw HTML from editor ?>
        </div>
      <?php else: ?>
        <p class="schedule-empty">No Program details added.</p>
      <?php endif; ?>

      <h4 class="mt-4">What's Included</h4>
      <?php if (!empty($package['whats_included'])): ?>
        <div class="rich-content-display">
          <?= $package['whats_included'] // Outputting raw HTML from editor ?>
        </div>
      <?php else: ?>
        <p class="schedule-empty">No details added.</p>
      <?php endif; ?>

      <h4 class="mt-4">What's Excluded</h4>
      <?php if (!empty($package['whats_excluded'])): ?>
        <div class="rich-content-display">
          <?= $package['whats_excluded'] // Outputting raw HTML from editor ?>
        </div>
      <?php else: ?>
        <p class="schedule-empty">No details added.</p>
      <?php endif; ?>

      <h4 class="mt-4">Cancellation Policy</h4>
      <?php if (!empty($package['cancellation_policy'])): ?>
        <div class="rich-content-display">
          <?= $package['cancellation_policy'] // Outputting raw HTML from editor ?>
        </div>
      <?php else: ?>
        <p class="schedule-empty">No policy added.</p>
      <?php endif; ?>

      <?php if (!empty($extra_sections)): ?>
        <?php foreach($extra_sections as $section): ?>
          <h4 class="mt-4"><?= htmlspecialchars($section['title']) ?></h4>
          <div class="rich-content-display">
            <?= $section['description'] // Outputting raw HTML from editor ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <div class="mt-4">
        <a href="editHostPackage.php?id=<?= $package['id'] ?>" class="btn btn-warning">Edit</a>
        <a href="deleteHostPackage.php?id=<?= $package['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this package?')">Delete</a>
        <a href="allHostPackages.php" class="btn btn-secondary">Back</a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__.'/includes/footer.php'; ?>
</body>
</html>
