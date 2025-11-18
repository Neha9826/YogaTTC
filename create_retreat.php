<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['yoga_host_id'])) {
    header("Location: login.php");
    exit;
}

$created_by = $_SESSION['yoga_host_id'];

// Fetch organizations of the logged-in host
$org_result = $conn->prepare("SELECT id, name FROM organizations WHERE created_by = ?");
$org_result->bind_param("i", $created_by);
$org_result->execute();
$organizations = $org_result->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch amenities
$amenities_result = $conn->query("SELECT * FROM yoga_amenities ORDER BY name ASC");
$amenities = $amenities_result->fetch_all(MYSQLI_ASSOC);

// Fetch instructors (with type)
$instructors_result = $conn->query("SELECT id, name, type FROM yoga_instructors ORDER BY name ASC");
$instructors = $instructors_result->fetch_all(MYSQLI_ASSOC);

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $organization_id = $_POST['organization_id'];
    $title = $_POST['title'];
    $slug = strtolower(str_replace(' ', '-', $title));
    $short_description = $_POST['short_description'];
    $full_description = $_POST['full_description'];
    $style = $_POST['style'];
    $min_price = $_POST['min_price'];
    $max_price = $_POST['max_price'];

    // Check if title or slug already exists for this organization
$check_stmt = $conn->prepare("SELECT id FROM yoga_retreats WHERE organization_id = ? AND (title = ? OR slug = ?)");
$check_stmt->bind_param("iss", $organization_id, $title, $slug);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $error = "A retreat with the same title or slug already exists for this organization.";
} else {
    // Proceed with insert


    // Insert retreat
    $stmt = $conn->prepare("INSERT INTO yoga_retreats 
        (organization_id, title, slug, short_description, full_description, style, min_price, max_price, created_at) 
        VALUES (?,?,?,?,?,?,?,?,NOW())");
    $stmt->bind_param("isssssdd", $organization_id, $title, $slug, $short_description, $full_description, $style, $min_price, $max_price);
    $stmt->execute();
    $retreat_id = $stmt->insert_id;
    $stmt->close();

    // Save levels
    if (!empty($_POST['levels'])) {
        foreach ($_POST['levels'] as $level) {
            $conn->query("INSERT INTO yoga_retreat_levels (retreat_id, level) VALUES ($retreat_id, '".$conn->real_escape_string($level)."')");
        }
    }

    // Save amenities
    if (!empty($_POST['amenities'])) {
        foreach ($_POST['amenities'] as $amenity_id) {
            $conn->query("INSERT INTO yoga_retreat_amenities (retreat_id, amenity_id) VALUES ($retreat_id, ".intval($amenity_id).")");
        }
    }

    // Save new amenities
    if (!empty($_POST['new_amenity'])) {
        foreach ($_POST['new_amenity'] as $new_am) {
            if (trim($new_am) != '') {
                $na = $conn->real_escape_string($new_am);
                $conn->query("INSERT INTO yoga_amenities (name) VALUES ('$na')");
                $new_id = $conn->insert_id;
                $conn->query("INSERT INTO yoga_retreat_amenities (retreat_id, amenity_id) VALUES ($retreat_id, $new_id)");
            }
        }
    }

    // Save instructors
    if (!empty($_POST['instructors'])) {
        foreach ($_POST['instructors'] as $instructor_id) {
            $iid = intval($instructor_id);
            if ($iid) {
                $conn->query("INSERT INTO yoga_retreat_instructors (retreat_id, instructor_id) VALUES ($retreat_id, $iid)");
            }
        }
    }
    // --- Save images (ensure upload directory exists, sanitize filenames) ---
    if (!empty($_FILES['images']['name'][0])) {
        $upload_dir_rel = 'uploads/retreats/';
        $upload_dir_abs = __DIR__ . '/' . $upload_dir_rel;

        if (!is_dir($upload_dir_abs)) {
            mkdir($upload_dir_abs, 0755, true);
        }

        $sort_order = 1; // initialize sort order
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if (!isset($_FILES['images']['error'][$key]) || $_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
                continue;
            }

            $original_name = $_FILES['images']['name'][$key];
            $safe_name = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($original_name));
            $file_name = time() . '_' . mt_rand(1000, 9999) . '_' . $safe_name;
            $target_abs = $upload_dir_abs . $file_name;
            $db_path = $upload_dir_rel . $file_name;

            if (move_uploaded_file($tmp_name, $target_abs)) {
                $alt_text = $conn->real_escape_string(pathinfo($original_name, PATHINFO_FILENAME));
                $conn->query("INSERT INTO yoga_retreat_images (retreat_id, image_path, alt_text, is_primary, sort_order) 
                    VALUES ($retreat_id, '".$conn->real_escape_string($db_path)."', '$alt_text', ".($sort_order==1?1:0).", $sort_order)");
                $sort_order++;
            }
        }
    }

    // --- End Save images ---

    // Save videos (URLs)
    if (!empty($_POST['videos'])) {
        foreach ($_POST['videos'] as $video_url) {
            $video_url_trim = trim($video_url);
            if ($video_url_trim != '') {
                $conn->query("INSERT INTO yoga_retreat_media (retreat_id, type, media_path) 
                    VALUES ($retreat_id, 'video', '".$conn->real_escape_string($video_url_trim)."')");
            }
        }
    }

    // --- ADD THIS NEW SNIPPET ---
    // --- Save uploaded videos ---
    if (!empty($_FILES['video_files']['name'][0])) {
        // Create a separate directory for video files
        $video_upload_dir_rel = 'uploads/retreats/videos/';
        $video_upload_dir_abs = __DIR__ . '/' . $video_upload_dir_rel;

        if (!is_dir($video_upload_dir_abs)) {
            mkdir($video_upload_dir_abs, 0755, true);
        }

        foreach ($_FILES['video_files']['tmp_name'] as $key => $tmp_name) {
            if (!isset($_FILES['video_files']['error'][$key]) || $_FILES['video_files']['error'][$key] !== UPLOAD_ERR_OK) {
                // Skip files with errors
                continue;
            }

            $original_name = $_FILES['video_files']['name'][$key];
            $safe_name = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($original_name));
            $file_name = time() . '_' . mt_rand(1000, 9999) . '_' . $safe_name;
            $target_abs = $video_upload_dir_abs . $file_name;
            $db_path = $video_upload_dir_rel . $file_name;

            // Move the uploaded file
            if (move_uploaded_file($tmp_name, $target_abs)) {
                // Insert into the media table, using 'video_file' to distinguish from a URL
                $conn->query("INSERT INTO yoga_retreat_media (retreat_id, type, media_path) 
                    VALUES ($retreat_id, 'video_file', '".$conn->real_escape_string($db_path)."')");
            }
        }
    }
    // --- End Save uploaded videos ---
    // --- END SNIPPET ---
}
$check_stmt->close();
    // --- End Save videos ---
    $success = "Retreat created successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
    <title>Create Retreat | Yoga Bhawna Mission</title>
    <link rel="stylesheet" href="yoga.css">
</head>
<body class="yoga-page">
<?php include __DIR__ . '/includes/fixed_social_bar.php'; ?>
<?php include __DIR__ . '/yoga_navbar.php'; ?>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <?php include 'host_sidebar.php'; ?> 

    <!-- Main Content -->
    <div class="col-md-9 col-lg-10 p-4">
      <h2>Create Retreat</h2>
      <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
      <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

      <form method="post" enctype="multipart/form-data" class="row g-3">

        <!-- Organization -->
        <div class="col-md-5">
          <label class="form-label">Organization</label>
          <select name="organization_id" class="form-select" required>
            <option value="">Select Organization</option>
            <?php foreach ($organizations as $org): ?>
              <option value="<?= $org['id'] ?>"><?= htmlspecialchars($org['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Title -->
        <div class="col-md-5">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" required>
        </div>

        <!-- Short Description -->
        <div class="col-md-5">
          <label class="form-label">Short Description</label>
          <textarea name="short_description" class="form-control" rows="2"></textarea>
        </div>

        <!-- Full Description -->
        <div class="col-md-5">
          <label class="form-label">Full Description</label>
          <textarea name="full_description" class="form-control" rows="4"></textarea>
        </div>

        <!-- Style -->
        <div class="col-md-5">
          <label class="form-label">Style</label>
          <input type="text" name="style" class="form-control">
        </div>

        <!-- Levels -->
        <div class="col-md-5">
          <label class="form-label">Levels</label><br>
          <?php foreach (['Beginner','Intermediate','Advanced','All'] as $level): ?>
            <div class="form-check form-check-inline">
              <input type="checkbox" name="levels[]" value="<?= $level ?>" class="form-check-input" id="level_<?= $level ?>">
              <label for="level_<?= $level ?>" class="form-check-label"><?= $level ?></label>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Prices -->
        <div class="col-md-5">
          <label class="form-label">Min Price</label>
          <input type="number" name="min_price" class="form-control">
        </div>
        <div class="col-md-5">
          <label class="form-label">Max Price</label>
          <input type="number" name="max_price" class="form-control">
        </div>

        <!-- Amenities -->
        <div class="col-md-5">
          <label class="form-label">Amenities</label><br>
          <?php foreach ($amenities as $am): ?>
            <div class="form-check form-check-inline mb-1">
              <input type="checkbox" name="amenities[]" value="<?= $am['id'] ?>" class="form-check-input" id="am_<?= $am['id'] ?>">
              <label for="am_<?= $am['id'] ?>" class="form-check-label">
                <i class="<?= $am['icon_class'] ?? 'bi-question-circle' ?>"></i>
                <?= htmlspecialchars($am['name']) ?>
              </label>
            </div>
          <?php endforeach; ?>

          <div id="new-amenities-container" class="mt-2"></div>
          <button type="button" class="btn btn-sm btn-secondary mt-1" onclick="addAmenityField()">+ Add New Amenity</button>
        </div>


        <!-- Instructors -->
        <div class="col-md-5">
          <label class="form-label">Instructors</label>
          <div id="instructors-container">
            <select name="instructors[]" class="form-select mb-2">
              <option value="">Select Instructor</option>
              <?php foreach ($instructors as $ins): ?>
                <option value="<?= $ins['id'] ?>">
                  <?= htmlspecialchars($ins['name']) ?> (<?= ucfirst($ins['type']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="button" class="btn btn-sm btn-secondary mt-1" onclick="addInstructor()">Add More</button>
        </div>

        <!-- Images -->
        <div class="col-md-5">
          <label class="form-label">Images</label>
          <input type="file" name="images[]" class="form-control" multiple>
        </div>

        <!-- Videos -->
        <div class="col-md-5">
          <label class="form-label">Videos (URLs)</label>
          <input type="text" name="videos[]" class="form-control mb-2" placeholder="YouTube / Vimeo URL">
          <div id="video-container"></div>
          <button type="button" class="btn btn-sm btn-secondary mt-1" onclick="addVideo()">Add More</button>
        </div>

        <div class="col-md-5">
          <label class="form-label">Upload Videos (Files)</label>
          <input type="file" name="video_files[]" class="form-control mb-2" accept="video/mp4,video/webm,video/ogg">
          <div id="video-file-container"></div>
          <button type="button" class="btn btn-sm btn-secondary mt-1" onclick="addVideoFile()">Add More Video Files</button>
        </div>
        <div class="col-md-12">

        <!-- Submit -->
        <div class="col-md-12">
          <button type="submit" class="btn btn-primary">Create Retreat</button>
        </div>

        <script>
document.querySelector("form").addEventListener("submit", function(e) {
  e.preventDefault(); // temporarily prevent form from submitting

  // Submit new amenities first, then submit full form
  submitNewAmenities(() => {
    e.target.submit(); // now submit the full retreat form
  });
});
</script>

      </form>
    </div>
  </div>
</div>

<script>
function addInstructor() {
  let container = document.getElementById("instructors-container");
  let select = container.querySelector("select").cloneNode(true);
  container.appendChild(select);
}

function addVideo() {
  let container = document.getElementById("video-container");
  let input = document.createElement("input");
  input.type = "text";
  input.name = "videos[]";
  input.classList.add("form-control", "mb-2");
  input.placeholder = "YouTube / Vimeo URL";
  container.appendChild(input);
}

// --- ADD THIS NEW SNIPPET ---
function addVideoFile() {
  let container = document.getElementById("video-file-container");
  let input = document.createElement("input");
  input.type = "file";
  input.name = "video_files[]";
  input.classList.add("form-control", "mb-2");
  input.accept = "video/mp4,video/webm,video/ogg";
  container.appendChild(input);
}
// --- END SNIPPET ---

// === Add new amenity fields dynamically ===
function addInstructor() {
  let container = document.getElementById("instructors-container");
  let select = container.querySelector("select").cloneNode(true);
  container.appendChild(select);
}

function addVideo() {
  let container = document.getElementById("video-container");
  let input = document.createElement("input");
  input.type = "text";
  input.name = "videos[]";
  input.classList.add("form-control", "mb-2");
  input.placeholder = "YouTube / Vimeo URL";
  container.appendChild(input);
}

// === Add new amenity fields dynamically ===
function addAmenityField() {
  const container = document.getElementById("new-amenities-container");
  const row = document.createElement("div");
  row.classList.add("input-group", "mb-2");

  row.innerHTML = `
    <span class="input-group-text"><i class="bi bi-question-circle" style="font-size:1.2em;"></i></span>
    <input type="text" name="new_amenity[]" class="form-control" placeholder="Enter amenity name">
  `;

  const input = row.querySelector("input");
  const icon = row.querySelector("i");

  // Auto-update icon suggestion via AJAX
  input.addEventListener("input", function() {
    const value = this.value.trim();
    if (!value) return;
    fetch("ajax/getAmenityIcon.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "name=" + encodeURIComponent(value)
    })
    .then(res => res.json())
    .then(data => { icon.className = "bi " + data.icon; })
    .catch(err => console.error("Icon fetch error:", err));
  });

  container.appendChild(row);
}

// === Submit new amenities instantly ===
function submitNewAmenities(callback) {
  const newAmenityRows = document.querySelectorAll("#new-amenities-container .input-group");
  if (!newAmenityRows.length) {
    if (callback) callback();
    return;
  }

  let pending = newAmenityRows.length;
  newAmenityRows.forEach(row => {
    const input = row.querySelector("input");
    const icon = row.querySelector("i").className.replace("bi ", "").trim();
    const name = input.value.trim();
    if (!name) {
      pending--;
      if (pending === 0 && callback) callback();
      return;
    }

    fetch("ajax/addAmenity.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "name=" + encodeURIComponent(name) + "&icon=" + encodeURIComponent(icon)
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === "success" || data.status === "exists") {
        // Dynamically add new amenity checkbox to main list
        const container = document.querySelector("#new-amenities-container").parentElement;
        const div = document.createElement("div");
        div.classList.add("form-check", "form-check-inline", "mb-1");
        div.innerHTML = `
          <input type="checkbox" class="form-check-input" name="amenities[]" value="${data.id}" id="am_${data.id}" checked>
          <label for="am_${data.id}" class="form-check-label">
            <i class="${data.icon}"></i> ${data.name}
          </label>
        `;
        container.insertBefore(div, document.getElementById("new-amenities-container"));
        row.remove();
      }
    })
    .finally(() => {
      pending--;
      if (pending === 0 && callback) callback();
    });
  });
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
