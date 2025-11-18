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

// Fetch retreat and verify ownership
$stmt = $conn->prepare("SELECT r.*, o.name AS org_name FROM yoga_retreats r JOIN organizations o ON r.organization_id=o.id WHERE r.id=? AND o.created_by=?");
$stmt->bind_param("ii", $retreat_id, $host_id);
$stmt->execute();
$retreat = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$retreat) die("Retreat not found.");

$success = $error = '';

// Handle POST update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $short_desc = $_POST['short_description'] ?? '';
    $full_desc = $_POST['full_description'] ?? '';
    $style = $_POST['style'] ?? '';
    $min_price = $_POST['min_price'] ?? 0;
    $max_price = $_POST['max_price'] ?? 0;
    $slug = strtolower(str_replace(' ', '-', $title));

    // Prevent duplicate slug
    $stmt = $conn->prepare("SELECT id FROM yoga_retreats WHERE id!=? AND slug=? LIMIT 1");
    $stmt->bind_param("is", $retreat_id, $slug);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $error = "Another retreat with this title already exists.";
    } else {
        $stmt = $conn->prepare("
            UPDATE yoga_retreats 
            SET title=?, slug=?, short_description=?, full_description=?, style=?, min_price=?, max_price=?, updated_at=NOW() 
            WHERE id=?
        ");
        $stmt->bind_param("sssssdid", $title, $slug, $short_desc, $full_desc, $style, $min_price, $max_price, $retreat_id);
        if ($stmt->execute()) $success = "Retreat updated successfully!";
        else $error = "Update failed: ".$conn->error;
        $stmt->close();

        // Handle Images Upload
        if (!empty($_FILES['images']['name'][0])) {
            $upload_dir_rel = 'uploads/retreats/';
            $upload_dir_abs = __DIR__ . '/' . $upload_dir_rel;
            if (!is_dir($upload_dir_abs)) mkdir($upload_dir_abs, 0755, true);

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) continue;
                $original_name = $_FILES['images']['name'][$key];
                $safe_name = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($original_name));
                $file_name = time().'_'.mt_rand(1000,9999).'_'.$safe_name;
                $target_abs = $upload_dir_abs . $file_name;
                $db_path = $upload_dir_rel . $file_name;

                if (move_uploaded_file($tmp_name, $target_abs)) {
                    $conn->query("INSERT INTO yoga_retreat_images (retreat_id, image_path) VALUES ($retreat_id, '".$conn->real_escape_string($db_path)."')");
                }
            }
        }

        // Handle Videos Upload
        if (!empty($_POST['videos'])) {
            foreach ($_POST['videos'] as $video_url) {
                $video_url_trim = trim($video_url);
                if ($video_url_trim != '') {
                    $conn->query("INSERT INTO yoga_retreat_media (retreat_id, type, media_path) VALUES ($retreat_id, 'video', '".$conn->real_escape_string($video_url_trim)."')");
                }
            }
        }

        // --- ADD THIS SNIPPET (Handle Video FILE Uploads) ---
        if (!empty($_FILES['video_files']['name'][0])) {
            $video_upload_dir_rel = 'uploads/retreats/videos/';
            $video_upload_dir_abs = __DIR__ . '/' . $video_upload_dir_rel;
            if (!is_dir($video_upload_dir_abs)) mkdir($video_upload_dir_abs, 0755, true);

            foreach ($_FILES['video_files']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['video_files']['error'][$key] !== UPLOAD_ERR_OK) continue;

                $original_name = $_FILES['video_files']['name'][$key];
                $safe_name = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($original_name));
                $file_name = time().'_'.mt_rand(1000,9999).'_'.$safe_name;
                $target_abs = $video_upload_dir_abs . $file_name;
                $db_path = $video_upload_dir_rel . $file_name;

                if (move_uploaded_file($tmp_name, $target_abs)) {
                    $conn->query("INSERT INTO yoga_retreat_media (retreat_id, type, media_path) 
                        VALUES ($retreat_id, 'video_file', '".$conn->real_escape_string($db_path)."')");
                }
            }
        }
        // --- END SNIPPET ---

        // Delete selected images
        if (!empty($_POST['delete_images'])) {
            foreach ($_POST['delete_images'] as $img_id) {
                $img_id = intval($img_id);
                $res = $conn->query("SELECT image_path FROM yoga_retreat_images WHERE id=$img_id AND retreat_id=$retreat_id");
                if ($row = $res->fetch_assoc()) {
                    if (file_exists(__DIR__.'/'.$row['image_path'])) unlink(__DIR__.'/'.$row['image_path']);
                    $conn->query("DELETE FROM yoga_retreat_images WHERE id=$img_id");
                }
            }
        }

        // --- REPLACE THIS BLOCK ---
        // Delete selected videos (Links OR Files)
        if (!empty($_POST['delete_videos'])) {
            foreach ($_POST['delete_videos'] as $vid_id) {
                $vid_id = intval($vid_id);
                
                // First, get the video info to check its type
                $vidRes = $conn->query("SELECT type, media_path FROM yoga_retreat_media WHERE id=$vid_id AND retreat_id=$retreat_id");
                if ($row = $vidRes->fetch_assoc()) {
                    
                    // If it's an uploaded file, delete the file from the server
                    if ($row['type'] == 'video_file') {
                        $file_to_delete = __DIR__ . '/' . $row['media_path'];
                        if (file_exists($file_to_delete)) {
                            unlink($file_to_delete);
                        }
                    }
                    
                    // Now, delete the record from the database
                    $conn->query("DELETE FROM yoga_retreat_media WHERE id=$vid_id");
                }
            }
        }
        // --- END REPLACEMENT ---
    }
}

// Fetch current media
$images_res = $conn->query("SELECT * FROM yoga_retreat_images WHERE retreat_id=$retreat_id ORDER BY is_primary DESC, sort_order ASC");
$images = $images_res->fetch_all(MYSQLI_ASSOC);

$videos_res = $conn->query("SELECT * FROM yoga_retreat_media WHERE retreat_id=$retreat_id");
$videos = $videos_res->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__.'/includes/head.php'; ?>
    <title>Edit Retreat | <?= htmlspecialchars($retreat['title']) ?></title>
    <link rel="stylesheet" href="yoga.css">
</head>
<body class="yoga-page">

<?php include __DIR__.'/includes/fixed_social_bar.php'; ?>
<?php include __DIR__.'/yoga_navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'host_sidebar.php'; ?>
        <div class="col-md-9 col-lg-10 p-4">
            <h2>Edit Retreat</h2>

            <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
            <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($retreat['title']) ?>" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Style</label>
                    <input type="text" name="style" class="form-control" value="<?= htmlspecialchars($retreat['style']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Short Description</label>
                    <textarea name="short_description" class="form-control"><?= htmlspecialchars($retreat['short_description']) ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Full Description</label>
                    <textarea name="full_description" class="form-control"><?= htmlspecialchars($retreat['full_description']) ?></textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Min Price</label>
                    <input type="number" step="0.01" name="min_price" class="form-control" value="<?= $retreat['min_price'] ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Max Price</label>
                    <input type="number" step="0.01" name="max_price" class="form-control" value="<?= $retreat['max_price'] ?>">
                </div>

                <!-- Existing Images -->
                <div class="col-12">
                    <label class="form-label">Existing Images</label><br>
                    <?php foreach($images as $img): ?>
                        <div class="d-inline-block text-center me-2 mb-2">
                            <img src="<?= file_exists(__DIR__.'/'.$img['image_path']) ? $img['image_path'] : '../assets/default_retreat.png' ?>" width="100" class="rounded mb-1" alt="Retreat Image"><br>
                            <input type="checkbox" name="delete_images[]" value="<?= $img['id'] ?>"> Delete
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Upload New Images -->
                <div class="col-12">
                    <label class="form-label">Upload New Images</label>
                    <input type="file" name="images[]" class="form-control" multiple>
                </div>

                <!-- Existing Videos -->
                <div class="col-12">
                    <label class="form-label">Existing Videos</label><br>
                    <?php if(empty($videos)): ?>
                        <p class="text-muted">No videos have been added yet.</p>
                    <?php endif; ?>

                    <?php foreach($videos as $vid): ?>
                        <div class="mb-3 p-2 border rounded">
                            <?php if ($vid['type'] == 'video'): // It's a URL Link ?>
                                <strong>Link:</strong> <a href="<?= htmlspecialchars($vid['media_path']) ?>" target="_blank"><?= htmlspecialchars($vid['media_path']) ?></a>
                            
                            <?php else: // It's an uploaded 'video_file' ?>
                                <strong>File:</strong> <a href="<?= htmlspecialchars($vid['media_path']) ?>" target="_blank"><?= htmlspecialchars(basename($vid['media_path'])) ?></a>
                                <div class="mt-2">
                                    <video width="200" controls preload="metadata">
                                        <source src="<?= htmlspecialchars($vid['media_path']) ?>">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-1">
                                <input type="checkbox" name="delete_videos[]" value="<?= $vid['id'] ?>" id="del_vid_<?= $vid['id'] ?>">
                                <label for="del_vid_<?= $vid['id'] ?>" class="text-danger">Delete</label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Add New Videos -->
                <div class="col-md-6">
                    <label class="form-label">Add New Video URLs</label>
                    <input type="text" name="videos[]" class="form-control mb-2" placeholder="YouTube / Vimeo URL">
                    <div id="video-container"></div>
                    <button type="button" class="btn btn-sm btn-secondary mt-1" onclick="addVideo()">+ Add More URLs</button>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Upload New Video Files</label>
                    <input type="file" name="video_files[]" class="form-control mb-2" accept="video/mp4,video/webm,video/ogg">
                    <div id="video-file-container"></div>
                    <button type="button" class="btn btn-sm btn-secondary mt-1" onclick="addVideoFile()">+ Add More Files</button>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Update Retreat</button>
                    <a href="viewHostRetreat.php?id=<?= $retreat_id ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__.'/includes/footer.php'; ?>

<script>
// Function for adding more URL fields
function addVideo() {
  let container = document.getElementById("video-container");
  let input = document.createElement("input");
  input.type = "text";
  input.name = "videos[]";
  input.classList.add("form-control", "mb-2");
  input.placeholder = "YouTube / Vimeo URL";
  container.appendChild(input);
}

// Function for adding more File fields
function addVideoFile() {
  let container = document.getElementById("video-file-container");
  let input = document.createElement("input");
  input.type = "file";
  input.name = "video_files[]";
  input.classList.add("form-control", "mb-2");
  input.accept = "video/mp4,video/webm,video/ogg";
  container.appendChild(input);
}
</script>
</body>
</html>
