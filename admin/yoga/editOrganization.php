<?php
// admin/yoga/editOrganization.php
include '../includes/session.php';
include 'db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    $_SESSION['flash_error'] = 'No organization ID.';
    header('Location: manageOrganizations.php');
    exit;
}

$res = $conn->prepare("SELECT * FROM organizations WHERE id=?");
$res->bind_param('i', $id);
$res->execute();
$organization = $res->get_result()->fetch_assoc();
if (!$organization) {
    $_SESSION['flash_error'] = 'Organization not found.';
    header('Location: manageOrganizations.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name']);
    $slug          = trim($_POST['slug']);
    $description   = trim($_POST['description']);
    $website       = trim($_POST['website']);
    $contact_email = trim($_POST['contact_email']);
    $contact_phone = trim($_POST['contact_phone']);
    $address       = trim($_POST['address']);
    $continent     = trim($_POST['continent']);
    $country       = trim($_POST['country']);
    $state         = trim($_POST['state']);
    $city          = trim($_POST['city']);
    $location_lat  = trim($_POST['location_lat']);
    $location_lng  = trim($_POST['location_lng']);

    $stmt = $conn->prepare(
        "UPDATE organizations
         SET name=?, slug=?, description=?, website=?, contact_email=?, contact_phone=?,
             address=?, continent=?, country=?, state=?, city=?, location_lat=?, location_lng=?
         WHERE id=?"
    );
    $stmt->bind_param(
        'sssssssssssssi',
        $name, $slug, $description, $website, $contact_email, $contact_phone,
        $address, $continent, $country, $state, $city, $location_lat, $location_lng, $id
    );
    if ($stmt->execute()) {
        $_SESSION['flash_success'] = 'Organization updated successfully!';
        header('Location: manageOrganizations.php');
        exit;
    } else {
        $_SESSION['flash_error'] = 'Error: ' . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include '../includes/head.php'; ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<style>
#map { height: 300px; width: 100%; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 6px; }
</style>
</head>
<body class="sb-nav-fixed">
<?php include '../includes/navbar.php'; ?>
<div id="layoutSidenav">
<?php include '../includes/sidebar.php'; ?>
<div id="layoutSidenav_content">
<main>
<div class="container-fluid px-4 mt-4">
<h2>Edit Organization</h2>
<?php if (isset($_SESSION['flash_error'])): ?>
<div class="alert alert-danger"><?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>
<form method="post">
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($organization['name']); ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Slug</label>
        <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($organization['slug']); ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control"><?= htmlspecialchars($organization['description']); ?></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Website</label>
        <input type="text" name="website" class="form-control" value="<?= htmlspecialchars($organization['website']); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Contact Email</label>
        <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars($organization['contact_email']); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Contact Phone</label>
        <input type="text" name="contact_phone" class="form-control" value="<?= htmlspecialchars($organization['contact_phone']); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control"><?= htmlspecialchars($organization['address']); ?></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Continent</label>
        <input type="text" name="continent" class="form-control" value="<?= htmlspecialchars($organization['continent']); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Country</label>
        <input type="text" name="country" class="form-control" value="<?= htmlspecialchars($organization['country']); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">State</label>
        <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($organization['state']); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">City</label>
        <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($organization['city']); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Select Location on Map</label>
        <div id="map"></div>
    </div>
    <div class="mb-3">
        <label class="form-label">Latitude</label>
        <input type="text" id="location_lat" name="location_lat" class="form-control"
               value="<?= htmlspecialchars($organization['location_lat']); ?>" readonly>
    </div>
    <div class="mb-3">
        <label class="form-label">Longitude</label>
        <input type="text" id="location_lng" name="location_lng" class="form-control"
               value="<?= htmlspecialchars($organization['location_lng']); ?>" readonly>
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
</form>
</div>
</main>
<?php include '../includes/footer.php'; ?>
</div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    var lat = parseFloat(document.getElementById('location_lat').value) || 20.5937;
    var lng = parseFloat(document.getElementById('location_lng').value) || 78.9629;
    var zoom = (document.getElementById('location_lat').value && document.getElementById('location_lng').value) ? 12 : 4;
    var map = L.map('map').setView([lat, lng], zoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    var marker = L.marker([lat, lng], {draggable: true}).addTo(map);
    marker.on('dragend', function (e) {
        var pos = marker.getLatLng();
        document.getElementById('location_lat').value = pos.lat.toFixed(7);
        document.getElementById('location_lng').value = pos.lng.toFixed(7);
    });
    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        document.getElementById('location_lat').value = e.latlng.lat.toFixed(7);
        document.getElementById('location_lng').value = e.latlng.lng.toFixed(7);
    });
});
</script>
</body>
</html>
