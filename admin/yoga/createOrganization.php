<?php
// admin/organization/createOrganization.php
include __DIR__ . '/../db.php';
session_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = $_POST['description'] ?? null;
    $website = trim($_POST['website'] ?? null);
    $contact_email = trim($_POST['contact_email'] ?? null);
    $contact_phone = trim($_POST['contact_phone'] ?? null);
    $address = trim($_POST['address'] ?? null);
    $continent = trim($_POST['continent'] ?? null);
    $country = trim($_POST['country'] ?? null);
    $state = trim($_POST['state'] ?? null);
    $city = trim($_POST['city'] ?? null);
    $location_lat = $_POST['location_lat'] ?: null;
    $location_lng = $_POST['location_lng'] ?: null;
    $created_by = $_SESSION['y_user_id'] ?? null; // host user ID

    if ($name === '') $errors[] = 'Name required';
    if ($slug === '') $slug = preg_replace('/[^a-z0-9\-]/','-',strtolower($name));

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO organizations (name, slug, description, website, contact_email, contact_phone, address, continent, country, state, city, location_lat, location_lng, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssssssssddi',
            $name, $slug, $description, $website, $contact_email, $contact_phone, $address,
            $continent, $country, $state, $city, $location_lat, $location_lng, $created_by
        );
        if ($stmt->execute()) {
            $_SESSION['flash_success'] = 'Organization saved and pending admin approval.';
            header("Location: manageOrganizations.php");
            exit;
        } else {
            $errors[] = 'DB error: ' . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include  '../includes/head.php'; ?>
<link href="../css/styles.css" rel="stylesheet">
<body class="sb-nav-fixed">
<?php include  '../includes/navbar.php'; ?>
<div id="layoutSidenav">
    <?php include  '../includes/sidebar.php'; ?>

<div id="layoutSidenav_content">
  <main class="container-fluid px-4 mt-4">
    <h2>Create Organization</h2>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><?php foreach ($errors as $e) echo htmlspecialchars($e).'<br>'; ?></div>
    <?php endif; ?>

    <form method="post" id="orgForm">
      <div class="mb-3">
        <label>Name</label>
        <input name="name" class="form-control" value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required>
      </div>

      <div class="mb-3">
        <label>Slug (optional)</label>
        <input name="slug" class="form-control" value="<?= isset($slug) ? htmlspecialchars($slug) : '' ?>">
      </div>

      <div class="mb-3">
        <label>Description</label>
        <textarea name="description" class="form-control"><?= isset($description) ? htmlspecialchars($description) : '' ?></textarea>
      </div>

      <div class="row">
        <div class="col-md-4 mb-3">
          <label>Continent</label>
          <select id="continent" name="continent" class="form-control"></select>
        </div>
        <div class="col-md-4 mb-3">
          <label>Country</label>
          <select id="country" name="country" class="form-control" disabled></select>
        </div>
        <div class="col-md-4 mb-3">
          <label>State</label>
          <select id="state" name="state" class="form-control" disabled></select>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label>City</label>
          <select id="city" name="city" class="form-control" disabled></select>
        </div>
        <div class="col-md-6 mb-3">
          <label>Address</label>
          <input name="address" class="form-control" value="<?= isset($address) ? htmlspecialchars($address) : '' ?>">
        </div>
      </div>

      <div class="mb-3">
        <label>Website</label>
        <input name="website" class="form-control" value="<?= isset($website) ? htmlspecialchars($website) : '' ?>">
      </div>

      <div class="row">
        <div class="col-md-4 mb-3">
          <label>Contact Email</label>
          <input name="contact_email" class="form-control" value="<?= isset($contact_email) ? htmlspecialchars($contact_email) : '' ?>">
        </div>
        <div class="col-md-4 mb-3">
          <label>Contact Phone</label>
          <input name="contact_phone" class="form-control" value="<?= isset($contact_phone) ? htmlspecialchars($contact_phone) : '' ?>">
        </div>
        <div class="col-md-4 mb-3">
          <label>Location (lat,lng)</label>
          <div class="input-group">
            <input name="location_lat" id="location_lat" class="form-control" placeholder="lat" value="<?= isset($location_lat) ? htmlspecialchars($location_lat) : '' ?>">
            <input name="location_lng" id="location_lng" class="form-control" placeholder="lng" value="<?= isset($location_lng) ? htmlspecialchars($location_lng) : '' ?>">
            <button id="searchLocationBtn" type="button" class="btn btn-outline-secondary">Search</button>
          </div>
        </div>
      </div>

      <button class="btn btn-primary">Save Organization</button>
    </form>
  </main>
  <?php include  '../includes/footer.php'; ?>
</div>

</div>

<script>
/*
  Uses admin/organization/api/getLocations.php and addLocation.php
  - cascading selects: continent -> country -> state -> city
  - option "Other" opens a prompt to add a custom entry (POST to addLocation.php)
*/
document.addEventListener('DOMContentLoaded', () => {
  const el = (id) => document.getElementById(id);
  const endpoint = '<?= $baseURL ?>organization/api/getLocations.php';

  function fetchAndFill(level, parent = '') {
    fetch(endpoint + '?level=' + level + '&parent=' + encodeURIComponent(parent))
      .then(r => r.json())
      .then(data => {
        const sel = el(level);
        sel.innerHTML = '';
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.text = '-- select --';
        sel.appendChild(defaultOption);

        data.forEach(item => {
          const opt = document.createElement('option');
          opt.value = item.name;
          opt.text = item.name;
          sel.appendChild(opt);
        });
        const other = document.createElement('option');
        other.value = '__other__';
        other.text = 'Other...';
        sel.appendChild(other);
        sel.disabled = false;
      });
  }

  // load continents
  fetchAndFill('continent');

  el('continent').addEventListener('change', function(){
    const val = this.value;
    if (!val) { el('country').innerHTML=''; el('country').disabled=true; el('state').disabled=true; el('city').disabled=true; return; }
    if (val === '__other__') { addOther('continent',''); return; }
    fetchAndFill('country', val);
    el('state').innerHTML=''; el('state').disabled=true;
    el('city').innerHTML=''; el('city').disabled=true;
  });
  el('country').addEventListener('change', function(){
    const val = this.value;
    if (!val) { el('state').innerHTML=''; el('state').disabled=true; return; }
    if (val === '__other__') { addOther('country', el('continent').value); return; }
    fetchAndFill('state', el('country').value);
    el('city').innerHTML=''; el('city').disabled=true;
  });
  el('state').addEventListener('change', function(){
    const val = this.value;
    if (!val) { el('city').innerHTML=''; el('city').disabled=true; return; }
    if (val === '__other__') { addOther('state', el('country').value); return; }
    fetchAndFill('city', el('state').value);
  });
  el('city').addEventListener('change', function(){
    if (this.value === '__other__') { addOther('city', el('state').value); }
  });

  function addOther(level, parent) {
    const name = prompt('Enter new ' + level + ':');
    if (!name) return;
    fetch('<?= $baseURL ?>organization/api/addLocation.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({level: level, parent: parent, name: name})
    })
    .then(r=>r.json())
    .then(res=>{
      if (res.success) {
        alert('Added: ' + name);
        // refresh the level
        fetchAndFill(level, parent);
      } else {
        alert('Error: ' + res.error);
      }
    });
  }

  // Basic geocode search: open input, call geocodeSearch.php
  document.getElementById('searchLocationBtn').addEventListener('click', () => {
    const q = prompt('Enter place name to search (e.g., Dehradun, Uttarakhand):');
    if (!q) return;
    fetch('<?= $baseURL ?>organization/api/geocodeSearch.php?q='+encodeURIComponent(q))
      .then(r=>r.json())
      .then(res=>{
        if (res.lat && res.lng) {
          el('location_lat').value = res.lat;
          el('location_lng').value = res.lng;
          alert('Location found: ' + res.lat + ',' + res.lng);
        } else {
          alert('No location found.');
        }
      });
  });
});
</script>
</body>
</html>