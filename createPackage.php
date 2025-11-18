<?php
session_start();
include __DIR__ . '/db.php';

if(!isset($_SESSION['yoga_host_id'])) {
    header("Location: login.php");
    exit;
}

$host_id = $_SESSION['yoga_host_id'];

$success = $error = "";

// Fetch host's organizations
$org_res = $conn->query("SELECT id, name FROM organizations WHERE created_by=$host_id ORDER BY name ASC");
$organizations = $org_res->fetch_all(MYSQLI_ASSOC);

// ✅ NEW: Fetch all dynamic field options
$all_deals = $conn->query("SELECT * FROM yoga_package_deals ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$all_dayonline = $conn->query("SELECT * FROM yoga_package_dayonline ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$all_languages = $conn->query("SELECT * FROM yoga_package_languages ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$all_meals = $conn->query("SELECT * FROM yoga_package_meals ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$all_food = $conn->query("SELECT * FROM yoga_package_food ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$all_airport_transfers = $conn->query("SELECT * FROM yoga_package_airport_transfers ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$all_types = $conn->query("SELECT * FROM yoga_package_types ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$all_categories = $conn->query("SELECT * FROM yoga_package_categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

// ✅ NEW: Helper function to render the form blocks
function render_dynamic_field_block($title, $key, $items) {
    $html = "<div class='col-md-6 mb-3 border p-3'>"; // Added border and padding for clarity
    $html .= "<label class='form-label fw-bold'>$title</label><br>";
    
    // Checkboxes for existing items
    if (empty($items)) {
        $html .= "<small class='text-muted'>No " . strtolower($title) . " found.</small><br>";
    } else {
        foreach ($items as $item) {
            $html .= "<div class='form-check form-check-inline'>";
            $html .= "<input type='checkbox' name='{$key}s[]' value='{$item['id']}' class='form-check-input' id='{$key}_{$item['id']}'>";
            $html .= "<label for='{$key}_{$item['id']}' class='form-check-label'>" . htmlspecialchars($item['name']) . "</label>";
            $html .= "</div>";
        }
    }

    // "Add New" container and button
    $html .= "<div id='new-{$key}-container' class='mt-2'></div>";
    $html .= "<button type='button' class='btn btn-sm btn-secondary mt-2' onclick=\"addDynamicInput('{$key}', '$title')\">+ Add New</button>";
    
    $html .= "</div>";
    return $html;
}


// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $retreat_id = intval($_POST['retreat_id']);
    $title = trim($_POST['title']);
    $slug = strtolower(str_replace(' ', '-', $title));
    $description = trim($_POST['description']);
    // --- ADD THIS SNIPPET ---
    $highlights = trim($_POST['highlights']);
    $accommodation_overview = trim($_POST['accommodation_overview']);
    // --- END SNIPPET ---
    $program = trim($_POST['program']); 
    // --- ADD THIS SNIPPET ---
    $whats_included = trim($_POST['whats_included']);
    $whats_excluded = trim($_POST['whats_excluded']);
    $cancellation_policy = trim($_POST['cancellation_policy']);
    // --- END SNIPPET ---
    $price_per_person = floatval($_POST['price_per_person']);
    $min_persons = intval($_POST['min_persons']);
    $max_persons = intval($_POST['max_persons']);
    $nights = intval($_POST['nights']);
    $meals_included = isset($_POST['meals_included']) ? 1 : 0;

    // Check duplicate slug
    $stmt = $conn->prepare("SELECT id FROM yoga_packages WHERE slug=? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Package with this title already exists.";
        }
        $stmt->close();
    }

    if (empty($error)) {
        // --- REPLACE THIS QUERY ---
        $stmt = $conn->prepare("
            INSERT INTO yoga_packages 
            (retreat_id, title, slug, description, 
             highlights, accommodation_overview, 
             program, whats_included, whats_excluded, cancellation_policy, 
             price_per_person, min_persons, max_persons, nights, meals_included, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        // --- END REPLACEMENT ---
        if ($stmt) {
            $stmt->bind_param(
                "issssssssssiiiii", 
                $retreat_id,
                $title,
                $slug,
                $description,
                // --- ADD THIS SNIPPET ---
                $highlights,
                $accommodation_overview,
                // --- END SNIPPET ---
                $program,
                // --- ADD THIS SNIPPET ---
                $whats_included,
                $whats_excluded,
                $cancellation_policy,
                // --- END SNIPPET ---
                $price_per_person,
                $min_persons,
                $max_persons,
                $nights,
                $meals_included
            );

            if ($stmt->execute()) {
                $package_id = $stmt->insert_id;

                // Insert Daily Schedule (existing logic)
                if (!empty($_POST['schedule_time']) && !empty($_POST['schedule_activity'])) {
                    // ... (your existing schedule logic) ...
                }

                // Insert Accommodation (existing logic)
                // Insert Accommodation options if provided
                if (!empty($_POST['accommodation_type'])) {
                    $uploadDir = "uploads/accommodations/"; // Make sure this path is correct and writable
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                    $types = $_POST['accommodation_type'];
                    $prices = $_POST['accommodation_price'];
                    $persons_list = $_POST['persons'];
                    $details_list = $_POST['more_detail'];

                    // Updated INSERT statement
                    $accStmt = $conn->prepare("
                        INSERT INTO yoga_package_accommodations 
                        (package_id, accommodation_type, price_per_person, persons, more_detail, created_at)
                        VALUES (?, ?, ?, ?, ?, NOW())
                    ");
                    
                    // Prepare image insert
                    $imgStmt = $conn->prepare("INSERT INTO yoga_accommodation_images (accommodation_id, image_path) VALUES (?, ?)");

                    foreach ($types as $i => $type) {
                        $type = trim($type);
                        $price = floatval($prices[$i]);
                        $persons = intval($persons_list[$i]);
                        $detail = trim($details_list[$i]);

                        if ($type && $price > 0) {
                            // Bind new params and execute
                            $accStmt->bind_param("isdis", $package_id, $type, $price, $persons, $detail);
                            $accStmt->execute();
                            $acc_id = $accStmt->insert_id; // Get the new accommodation ID

                            // --- Handle Image Uploads for this accommodation ---
                            $fieldName = "accommodation_images_new_$i";
                            if (!empty($_FILES[$fieldName]['name'][0])) {
                                foreach ($_FILES[$fieldName]['tmp_name'] as $k => $tmp) {
                                    if (is_uploaded_file($tmp)) {
                                        $filename = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES[$fieldName]['name'][$k]);
                                        $targetPath = $uploadDir . $filename;
                                        
                                        if (move_uploaded_file($tmp, $targetPath)) {
                                            $relPath = $uploadDir . $filename; // Store relative path
                                            $imgStmt->bind_param("is", $acc_id, $relPath);
                                            $imgStmt->execute();
                                        }
                                    }
                                }
                            }
                            // --- End Image Upload Logic ---
                        }
                    }
                    $accStmt->close();
                    $imgStmt->close();
                }

                // ✅ NEW: Save all dynamic fields
                $dynamic_fields = [
                    'deal' => ['table' => 'yoga_package_deals', 'link_table' => 'yoga_package_selected_deals', 'col' => 'deal_id'],
                    'dayonline' => ['table' => 'yoga_package_dayonline', 'link_table' => 'yoga_package_selected_dayonline', 'col' => 'dayonline_id'],
                    'language' => ['table' => 'yoga_package_languages', 'link_table' => 'yoga_package_selected_languages', 'col' => 'language_id'],
                    'meal' => ['table' => 'yoga_package_meals', 'link_table' => 'yoga_package_selected_meals', 'col' => 'meal_id'],
                    'food' => ['table' => 'yoga_package_food', 'link_table' => 'yoga_package_selected_food', 'col' => 'food_id'],
                    'airport_transfer' => ['table' => 'yoga_package_airport_transfers', 'link_table' => 'yoga_package_selected_airport_transfers', 'col' => 'transfer_id'],
                    'type' => ['table' => 'yoga_package_types', 'link_table' => 'yoga_package_selected_types', 'col' => 'type_id'],
                    'category' => ['table' => 'yoga_package_categories', 'link_table' => 'yoga_package_selected_categories', 'col' => 'category_id'],
                ];

                foreach ($dynamic_fields as $key => $config) {
                    $item_ids_to_link = [];

                    // 1. Handle existing items (checkboxes)
                    if (!empty($_POST[$key . 's'])) { // e.g., $_POST['deals']
                        foreach ($_POST[$key . 's'] as $item_id) {
                            $item_ids_to_link[] = intval($item_id);
                        }
                    }

                    // 2. Handle new items (text inputs)
                    if (!empty($_POST['new_' . $key])) { // e.g., $_POST['new_deal']
                        $stmt_master = $conn->prepare("INSERT INTO {$config['table']} (name) VALUES (?)");
                        $stmt_select = $conn->prepare("SELECT id FROM {$config['table']} WHERE name = ?");

                        foreach ($_POST['new_' . $key] as $item_name) {
                            $item_name_trimmed = trim($item_name);
                            if ($item_name_trimmed) {
                                $new_item_id = 0;

                                // Check if it already exists
                                $stmt_select->bind_param("s", $item_name_trimmed);
                                $stmt_select->execute();
                                $result = $stmt_select->get_result();
                                
                                if ($result->num_rows > 0) {
                                    $new_item_id = $result->fetch_assoc()['id'];
                                } else {
                                    // If not, insert it
                                    $stmt_master->bind_param("s", $item_name_trimmed);
                                    $stmt_master->execute();
                                    $new_item_id = $stmt_master->insert_id;
                                }
                                
                                if ($new_item_id > 0) {
                                    $item_ids_to_link[] = $new_item_id;
                                }
                            }
                        }
                        $stmt_master->close();
                        $stmt_select->close();
                    }

                    // 3. Link all unique items to the package
                    $item_ids_to_link = array_unique($item_ids_to_link);
                    if (!empty($item_ids_to_link)) {
                        $stmt_link = $conn->prepare("INSERT IGNORE INTO {$config['link_table']} (package_id, {$config['col']}) VALUES (?, ?)");
                        foreach ($item_ids_to_link as $item_id) {
                            $stmt_link->bind_param("ii", $package_id, $item_id);
                            $stmt_link->execute();
                        }
                        $stmt_link->close();
                    }
                }
                // ✅ END NEW: Save dynamic fields

                // --- ADD THIS SNIPPET ---
                // Save "Add More" extra sections
                if (!empty($_POST['extra_title']) && !empty($_POST['extra_description'])) {
                    $extra_titles = $_POST['extra_title'];
                    $extra_descriptions = $_POST['extra_description'];
                    
                    $extraStmt = $conn->prepare("
                        INSERT INTO yoga_package_extra_sections (package_id, title, description, sort_order) 
                        VALUES (?, ?, ?, ?)
                    ");
                    
                    foreach ($extra_titles as $i => $title) {
                        $title = trim($title);
                        $desc = trim($extra_descriptions[$i]);
                        $sort_order = $i + 1;
                        
                        if ($title && $desc) {
                            $extraStmt->bind_param("issi", $package_id, $title, $desc, $sort_order);
                            $extraStmt->execute();
                        }
                    }
                    $extraStmt->close();
                }
                // --- END SNIPPET ---

                $success = "Package created successfully!";
            } else {
                $error = "Failed to create package: " . $conn->error;
            }
            $stmt->close();
        } else {
            $error = "Failed to prepare insert statement: " . $conn->error;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__.'/includes/head.php'; ?>
    <title>Create Package</title>
    <link rel="stylesheet" href="yoga.css">
    
    <script src="https://cdn.tiny.cloud/1/urrcm7wdpcb1a3ecik6nzieh9flmjgccnw43mlrf2grgze9x/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    
</head>
<body class="yoga-page">
<?php include __DIR__.'/includes/fixed_social_bar.php'; ?>
<?php include __DIR__.'/yoga_navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'host_sidebar.php'; ?>
        <div class="col-md-9 col-lg-10 p-4">
            <h2>Create Package</h2>

            <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
            <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Organization</label>
                    <select name="organization_id" id="organization_id" class="form-control" required>
                        <option value="">Select Organization</option>
                        <?php foreach($organizations as $org): ?>
                            <option value="<?= $org['id'] ?>"><?= htmlspecialchars($org['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Retreat</label>
                    <select name="retreat_id" id="retreat_id" class="form-control" required>
                        <option value="">Select an Organization first</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Package Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Price (₹)</label>
                    <input type="number" step="0.01" name="price_per_person" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Min Persons</label>
                    <input type="number" name="min_persons" class="form-control" value="1" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Max Persons</label>
                    <input type="number" name="max_persons" class="form-control" value="1" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Nights</label>
                    <input type="number" name="nights" class="form-control" value="0">
                </div>

                <div class="col-md-3 d-flex align-items-center">
                    <div class="form-check mt-2">
                        <input type="checkbox" name="meals_included" class="form-check-input" id="meals_included" checked>
                        <label class="form-check-label" for="meals_included">Meals Included</label>
                    </div>
                </div>

                <!-- ========== Daily Schedule ========== -->
                <!-- <div class="col-12">
                    <label class="form-label">Daily Schedule</label>
                    <div id="scheduleContainer">
                        <div class="row mb-2 schedule-row">
                        <div class="col-md-3"><input type="time" name="schedule_time[]" class="form-control" required></div>
                        <div class="col-md-7"><input type="text" name="schedule_activity[]" class="form-control" placeholder="Activity" required></div>
                        <div class="col-md-2"><button type="button" class="btn btn-danger removeRow">Remove</button></div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary mt-2" id="addRowBtn">+ Add Another</button>
                </div> -->

                <div class="col-12">
                    <label class="form-label">Highlights</label>
                    <textarea name="highlights" id="highlights_editor" class="form-control tinymce-editor" rows="10"></textarea>
                </div>
                
                <!-- ========== Program ========== -->
                <div class="col-12">
                    <label class="form-label">Program</label>
                    <textarea name="program" id="program_editor" class="form-control tinymce-editor" rows="10"></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">Accommodation Overview</label>
                    <textarea name="accommodation_overview" id="accommodation_overview_editor" class="form-control tinymce-editor" rows="10" placeholder="A general description of the accommodation. Specific room types are added below."></textarea>
                </div>

                <!-- ========== Accommodation Options ========== -->
                <div class="col-12 mt-4">
                    <label class="form-label fw-bold">Accommodation Options</label>
                    <div id="accommodationContainer">
                        
                        <div class="accommodation-block border rounded p-3 mb-3">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label">Accommodation Type</label>
                                    <input type="text" name="accommodation_type[]" class="form-control" placeholder="e.g. Shared Room, Deluxe Cottage" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Price (₹)</label>
                                    <input type="number" step="0.01" name="accommodation_price[]" class="form-control" placeholder="Price (₹)" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Max Persons</label>
                                    <input type="number" name="persons[]" class="form-control" value="1" required>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger w-100 removeAccRow">Remove</button>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">More Detail (Optional)</label>
                                    <textarea name="more_detail[]" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Upload Images</label>
                                    <input type="file" name="accommodation_images_new_0[]" class="form-control" multiple accept="image/*">
                                </div>
                            </div>
                        </div>
                        </div>
                    <button type="button" class="btn btn-secondary mt-2" id="addAccRowBtn">+ Add Another</button>
                </div>

                <!-- <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control"></textarea>
                </div> -->
               
                <hr class="my-4">
                
                <div class="col-12">
                    <h3>Package Details & Options</h3>
                </div>

                <div class="row g-3">
                    <?php echo render_dynamic_field_block('Deals', 'deal', $all_deals); ?>
                    <?php echo render_dynamic_field_block('Day or Online', 'dayonline', $all_dayonline); ?>
                </div>
                <div class="row g-3 mt-1">
                     <?php echo render_dynamic_field_block('Languages', 'language', $all_languages); ?>
                     <?php echo render_dynamic_field_block('Meals', 'meal', $all_meals); ?>
                </div>
                 <div class="row g-3 mt-1">
                     <?php echo render_dynamic_field_block('Food', 'food', $all_food); ?>
                     <?php echo render_dynamic_field_block('Airport Transfer', 'airport_transfer', $all_airport_transfers); ?>
                </div>
                 <div class="row g-3 mt-1">
                     <?php echo render_dynamic_field_block('Type', 'type', $all_types); ?>
                     <?php echo render_dynamic_field_block('Category', 'category', $all_categories); ?>
                </div>

                <div class="col-12">
                    <label class="form-label">What's Included</label>
                    <textarea name="whats_included" id="whats_included_editor" class="form-control tinymce-editor" rows="10"></textarea>
                </div>
                
                <div class="col-12">
                    <label class="form-label">What's Excluded</label>
                    <textarea name="whats_excluded" id="whats_excluded_editor" class="form-control tinymce-editor" rows="10"></textarea>
                </div>
                
                <div class="col-12">
                    <label class="form-label">Cancellation Policy</label>
                    <textarea name="cancellation_policy" id="cancellation_policy_editor" class="form-control tinymce-editor" rows="10"></textarea>
                </div>
                
                <hr class="my-4">
                
                <div class="col-12">
                    <h3 class="h5">Extra Information Sections (Optional)</h3>
                    <p class="text-muted small">Add extra sections like "FAQs", "Location Details", or "Things to Bring".</p>
                    <div id="extra_sections_container">
                        </div>
                    <button type="button" class="btn btn-secondary mt-2" id="add_extra_section_btn">+ Add Section</button>
                </div>

                <hr class="my-4">

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Create Package</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Retreat Fetcher (Existing)
document.getElementById('organization_id').addEventListener('change', function() {
    const orgId = this.value;
    const retreatSelect = document.getElementById('retreat_id');
    retreatSelect.innerHTML = '<option value="">Loading...</option>';

    if(orgId === '') {
        retreatSelect.innerHTML = '<option value="">Select Retreat</option>';
        return;
    }

    fetch('fetchRetreatsByOrg.php?org_id='+orgId)
        .then(res => res.json())
        .then(data => {
            let options = '<option value="">Select Retreat</option>';
            data.forEach(r => {
                options += `<option value="${r.id}">${r.title}</option>`;
            });
            retreatSelect.innerHTML = options;
        })
        .catch(err => {
            console.error(err);
            retreatSelect.innerHTML = '<option value="">Error loading retreats</option>';
        });
});
</script>

<?php include __DIR__.'/includes/footer.php'; ?>

<script>
// --- ADD THIS NEW SCRIPT BLOCK ---

let accCounter = 1; // Start counter at 1 since 0 is already on the page
document.getElementById('addAccRowBtn').addEventListener('click', function() {
  const container = document.getElementById('accommodationContainer');
  const newRow = document.createElement('div');
  // Use the correct class from your HTML
  newRow.classList.add('accommodation-block', 'border', 'rounded', 'p-3', 'mb-3'); 
  
  // Use the unique counter for the file input name
  newRow.innerHTML = `
    <div class="row g-3">
        <div class="col-md-5">
            <label class="form-label">Accommodation Type</label>
            <input type="text" name="accommodation_type[]" class="form-control" placeholder="e.g. Shared Room, Deluxe Cottage" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Price (₹)</label>
            <input type="number" step="0.01" name="accommodation_price[]" class="form-control" placeholder="Price (₹)" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Max Persons</label>
            <input type="number" name="persons[]" class="form-control" value="1" required>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="button" class="btn btn-danger w-100 removeAccRow">Remove</button>
        </div>
        <div class="col-md-12">
            <label class="form-label">More Detail (Optional)</label>
            <textarea name="more_detail[]" class="form-control" rows="2"></textarea>
        </div>
        <div class="col-md-12">
            <label class="form-label">Upload Images</label>
            <input type="file" name="accommodation_images_new_${accCounter}[]" class="form-control" multiple accept="image/*">
        </div>
    </div>`;
  container.appendChild(newRow);
  accCounter++; // Increment the counter
});

// This is the correct remove logic for the new block
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('removeAccRow')) {
    e.target.closest('.accommodation-block').remove();
  }
});

// --- END OF NEW SCRIPT BLOCK ---

</script>

<script>
  function initTinyMCE(selector) {
    tinymce.init({
      selector: selector,
      plugins: 'lists link image table code help wordcount bold italic underline',
      toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code'
    });
  }
  
  // Initialize all editors that are already on the page
  initTinyMCE('textarea.tinymce-editor');

  let extraSectionCounter = 0;

document.getElementById('add_extra_section_btn').addEventListener('click', function() {
    extraSectionCounter++;
    const container = document.getElementById('extra_sections_container');
    
    const newSection = document.createElement('div');
    newSection.classList.add('extra-section-item', 'border', 'p-3', 'mb-3', 'bg-light');
    
    const newEditorId = 'extra_editor_' + extraSectionCounter;
    
    newSection.innerHTML = `
        <div class="row">
            <div class="col-10">
                <label class="form-label fw-bold">Section Title</label>
                <input type="text" name="extra_title[]" class="form-control mb-2" placeholder="e.g., FAQ" required>
            </div>
            <div class="col-2 text-end">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeExtraSection(this)">Remove</button>
            </div>
        </div>
        <label class="form-label">Section Description</label>
        <textarea name="extra_description[]" id="${newEditorId}" class="form-control" rows="5"></textarea>
    `;
    
    container.appendChild(newSection);
    
    // IMPORTANT: Initialize TinyMCE on the new textarea
    initTinyMCE('textarea#' + newEditorId);
});

function removeExtraSection(button) {
    const sectionItem = button.closest('.extra-section-item');
    // We need to properly remove the TinyMCE instance before removing the element
    const textareaId = sectionItem.querySelector('textarea').id;
    if (tinymce.get(textareaId)) {
        tinymce.get(textareaId).remove();
    }
    sectionItem.remove();
}


function addDynamicInput(type, title) {
    // type = 'deal', title = 'Deals'
    // type = 'airport_transfer', title = 'Airport Transfer'
    
    const container = document.getElementById(`new-${type}-container`);
    const row = document.createElement('div');
    row.classList.add('input-group', 'mb-2');
    
    // Create a placeholder title
    let placeholder = title.replace(/s$/, ''); // Remove trailing 's' (e.g., Deals -> Deal)

    row.innerHTML = `
        <input type="text" name="new_${type}[]" class="form-control" placeholder="New ${placeholder} name">
        <button type="button" class="btn btn-outline-danger" onclick="this.closest('.input-group').remove()" title="Remove">&times;</button>
    `;
    container.appendChild(row);
    row.querySelector('input').focus();
}
</script>
</body>
</html>