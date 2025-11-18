<?php
// === YOUR PHP LOGIC (100% UNCHANGED) ===
require_once __DIR__ . '/yoga_session.php';
include __DIR__ . '/config.php'; // Assumes config.php is in this directory, not /../
include __DIR__ . '/db.php';     // Assumes db.php is in this directory, not /../

// --- Read filters from GET (ensure types and defaults)
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$locations = isset($_GET['location']) ? (array) $_GET['location'] : [];
$durations = isset($_GET['duration']) ? (array) $_GET['duration'] : [];
$price_min = isset($_GET['price_min']) ? (int) $_GET['price_min'] : 0;
$price_max = isset($_GET['price_max']) ? (int) $_GET['price_max'] : 0;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 9;
$offset = ($page - 1) * $perPage;

// --- Build WHERE array (use proper table aliases)
$where = []; // IMPORTANT: initialize to avoid undefined variable errors

if ($q !== '') {
    $q_esc = $conn->real_escape_string($q);
    // search packages, retreats, organization names and country
    $where[] = "(p.title LIKE '%$q_esc%' OR p.description LIKE '%$q_esc%' OR r.title LIKE '%$q_esc%' OR o.name LIKE '%$q_esc%' OR o.country LIKE '%$q_esc%')";
}

// location filter: we use organizations.country as "location"
if (count($locations) > 0) {
    $escaped = array_map(function($v) use ($conn) {
        return "'" . $conn->real_escape_string($v) . "'";
    }, $locations);
    $where[] = "o.country IN (" . implode(',', $escaped) . ")";
}

// duration filter: use p.nights
if (count($durations) > 0) {
    $escaped = array_map(function($v) { return (int)$v; }, $durations);
    $where[] = "p.nights IN (" . implode(',', $escaped) . ")";
}

// price filters: based on package base price (price_per_person)
if ($price_min > 0) {
    $where[] = "p.price_per_person >= " . (int)$price_min;
}
if ($price_max > 0) {
    $where[] = "p.price_per_person <= " . (int)$price_max;
}

// --- Count total matching packages for pagination
$countSql = "
  SELECT COUNT(DISTINCT p.id) AS cnt
  FROM yoga_packages p
  JOIN yoga_retreats r ON p.retreat_id = r.id
  JOIN organizations o ON r.organization_id = o.id
  WHERE p.is_published = 1 AND r.is_published = 1
  " . (count($where) > 0 ? " AND " . implode(' AND ', $where) : '');

$countRes = $conn->query($countSql);
$total = ($countRes && $countRes->num_rows) ? (int)$countRes->fetch_assoc()['cnt'] : 0;
$totalPages = max(1, ceil($total / $perPage)); // Use $total and $perPage

// --- Fetch paginated packages
$sql = "
  SELECT
    p.id,
    p.title AS package_title,
    p.description,
    p.price_per_person,
    p.nights,
    p.meals_included,
    r.id AS retreat_id,
    r.title AS retreat_title,
    o.id AS org_id,
    o.name AS org_name,
    o.country,
    (SELECT image_path FROM yoga_retreat_images WHERE retreat_id = r.id LIMIT 1) AS image_path
  FROM yoga_packages p
  JOIN yoga_retreats r ON p.retreat_id = r.id
  JOIN organizations o ON r.organization_id = o.id
  WHERE p.is_published = 1 AND r.is_published = 1
  " . (count($where) > 0 ? " AND " . implode(' AND ', $where) : '') . "
  ORDER BY p.created_at DESC
  LIMIT $offset, $perPage
";
// ***** FIX: The line above was line 85. I removed "ORDER BY display_order ASC" from the subquery. *****

$res = $conn->query($sql);
if (!$res) {
    // Handle query error gracefully
    echo "Error: " . $conn->error;
    $res = null; // Set $res to null so the rest of the page doesn't fail
}


// --- Prepare data for sidebar filters (distinct countries and nights)
$locs = $conn->query("SELECT DISTINCT country FROM organizations WHERE country<>'' ORDER BY country ASC");
$dres = $conn->query("SELECT DISTINCT nights FROM yoga_packages WHERE nights > 0 ORDER BY nights ASC");

$pageTitle = "Find & Book Yoga Retreats"; // Added for <title>
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  
  <!-- Custom CSS (The one you have) -->
  <link rel="stylesheet" href="yoga.css">
  
  <!-- Google Fonts (from reference site) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&family=Open+Sans:wght@400;600;700&display_swap" rel="stylesheet">
  
  <!-- Font Awesome (for your footer) -->
  <!-- This link supports 'fas', 'far', etc. -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="results-page-body"> <!-- New body class for scoping -->

  <!-- YOUR EXISTING NAVBAR (UNCHANGED) -->
  <?php include 'yoga_navbar.php'; ?>

  <!-- YOUR EXISTING VIDEO BANNER (UNCHANGED) -->
  <?php include 'videoBanner.php'; ?>

  <!-- Main Content Area -->
  <main class="main-content container py-4">
    <div class="row g-4">
      <div class="container-fluid">
        <div class="row">
            <div class="col-12 d-lg-none mb-3">
                <button class="btn btn-outline-secondary w-100" type="button" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fas fa-filter me-2"></i>Filters
                </button>
            </div>
        </div>
      </div>
      <!-- === Sidebar (NEW layout, matches refr.mp4) === -->
      <aside class="col-lg-3">
        <div class="filter-sidebar" id="filter-sidebar">
          <form id="filterForm" action="index.php" method="GET">
            <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">

              <h4 style="padding-bottom: 1rem;" class="filter-title-bold">Filters</h4>

              <h5 class="filter-title">Deals</h5>
              <div style="padding-bottom: 1rem;" class="filter-content filter-scroll-box">
                <div class="form-check"><input class="form-check-input" type="checkbox" name="deal" id="deal_all"><label class="form-check-label" for="deal_all">All deals (1303)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="deal" id="deal_early"><label class="form-check-label" for="deal_early">Early bird (569)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="deal" id="deal_exclusive"><label class="form-check-label" for="deal_exclusive">Exclusive Gifts (443)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="deal" id="deal_special"><label class="form-check-label" for="deal_special">Special offer (174)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="deal" id="deal_last"><label class="form-check-label" for="deal_last">Last minute (117)</label></div>
              </div>
            
              <h5  class="filter-title-new">New</h5>
              <h6 style="text:muted;">Day & Online Experiences</h6>
              <div style="padding-bottom: 1rem;" class="filter-content">
                <div class="form-check"><input class="form-check-input" type="checkbox" id="new_online"><label class="form-check-label" for="new_online">Online experiences (280)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="new_day"><label class="form-check-label" for="new_day">Day Activities (16)</label></div>
              </div>
            
              <h5 class="filter-title">Destinations</h5>
              <div style="padding-bottom: 1rem;" class="filter-content filter-accordion">
                <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseDest" role="button" aria-expanded="false" aria-controls="collapseDest">
                  <div class="sb-sidenav-collapse-arrow">The Americas & Caribbean <i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseDest">
                  <div class="filter-collapse-body">
                    <div class="form-check"><input class="form-check-input" type="radio" id="dest_1"><label class="form-check-label" for="dest_1">Anywhere in Americas & Caribbean</label></div>
                      <div class="form-check">
                        <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseDestsub" role="button" aria-expanded="false" aria-controls="collapseDestsub">
                          <div class="sb-sidenav-collapse-arrow">USA <i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseDestsub">
                          <div class="filter-collapse-body">
                            <div class="form-check"><input class="form-check-input" type="radio" id="dest_1"><label class="form-check-label" for="dest_1">Anywhere in USA</label></div>
                            <div class="form-check"><input class="form-check-input" type="radio" id="dest_2"><label class="form-check-label" for="dest_2">Europe</label></div>
                            <div class="form-check"><input class="form-check-input" type="radio" id="dest_3"><label class="form-check-label" for="dest_3">Asia & Oceania</label></div>
                            <div class="form-check"><input class="form-check-input" type="radio" id="dest_4"><label class="form-check-label" for="dest_4">Africa & the Middle East</label></div>
                          </div>
                        </div>
                      </div>
                      <div class="form-check">
                        <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseDestsub2" role="button" aria-expanded="false" aria-controls="collapseDestsub2">
                          <div class="sb-sidenav-collapse-arrow">Costa Rica <i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseDestsub2">
                          <div class="filter-collapse-body">
                            <div class="form-check"><input class="form-check-input" type="radio" id="dest_1"><label class="form-check-label" for="dest_1">Anywhere in Costa Rica</label></div>
                            <div class="form-check"><input class="form-check-input" type="radio" id="dest_2"><label class="form-check-label" for="dest_2">Europe</label></div>
                            <div class="form-check"><input class="form-check-input" type="radio" id="dest_3"><label class="form-check-label" for="dest_3">Asia & Oceania</label></div>
                            <div class="form-check"><input class="form-check-input" type="radio" id="dest_4"><label class="form-check-label" for="dest_4">Africa & the Middle East</label></div>
                          </div>
                        </div>
                      </div>
                      <div class="form-check">
                        <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseDestsub3" role="button" aria-expanded="false" aria-controls="collapseDestsub3">
                          <div class="sb-sidenav-collapse-arrow">Mexico <i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseDestsub3">
                          <div class="filter-collapse-body">
                            <div class="form-check"><input class="form-check-input" type="radio" id="dest_1"><label class="form-check-label" for="dest_1">Anywhere in Mexico</label></div>
                            <div class="form-check"><input class="form-check-input" type="radio" id="dest_2"><label class="form-check-label" for="dest_2">Europe</label></div>
                            <div class="form-check"><input class="form-check-input" type="radio" id="dest_3"><label class="form-check-label" for="dest_3">Asia & Oceania</label></div>
                            <div class="form-check"><input class="form-check-input" type="radio" id="dest_4"><label class="form-check-label" for="dest_4">Africa & the Middle East</label></div>
                          </div>
                        </div>
                      </div>
                  </div>
                </div>
                <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseDest2" role="button" aria-expanded="false" aria-controls="collapseDest2">
                  <div class="sb-sidenav-collapse-arrow">Europe <i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseDest2">
                  <div class="filter-collapse-body">
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_1"><label class="form-check-label" for="dest_1">The Americas & Caribbean</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_2"><label class="form-check-label" for="dest_2">Europe</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_3"><label class="form-check-label" for="dest_3">Asia & Oceania</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_4"><label class="form-check-label" for="dest_4">Africa & the Middle East</label></div>
                  </div>
                </div>
                <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseDest3" role="button" aria-expanded="false" aria-controls="collapseDest3">
                  <div class="sb-sidenav-collapse-arrow">Asia & Oceania <i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseDest3">
                  <div class="filter-collapse-body">
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_1"><label class="form-check-label" for="dest_1">The Americas & Caribbean</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_2"><label class="form-check-label" for="dest_2">Europe</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_3"><label class="form-check-label" for="dest_3">Asia & Oceania</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_4"><label class="form-check-label" for="dest_4">Africa & the Middle East</label></div>
                  </div>
                </div>
                <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseDest4" role="button" aria-expanded="false" aria-controls="collapseDest4">
                  <div class="sb-sidenav-collapse-arrow">Africa & the Middle East <i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseDest4">
                  <div class="filter-collapse-body">
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_1"><label class="form-check-label" for="dest_1">The Americas & Caribbean</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_2"><label class="form-check-label" for="dest_2">Europe</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_3"><label class="form-check-label" for="dest_3">Asia & Oceania</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_4"><label class="form-check-label" for="dest_4">Africa & the Middle East</label></div>
                  </div>
                </div>
              </div>
            
              <h5 class="filter-title">Arrival date</h5>
              <div style="padding-bottom: 1rem;" class="filter-content filter-scroll-box">
                <div class="form-check"><input class="form-check-input" type="radio" id="date_nov"><label class="form-check-label" for="date_nov">2025 November (4848)</label></div>
                <div class="form-check"><input class="form-check-input" type="radio" id="date_dec"><label class="form-check-label" for="date_dec">2025 December (4340)</label></div>
                <div class="form-check"><input class="form-check-input" type="radio" id="date_jan"><label class="form-check-label" for="date_jan">2026 January (3860)</label></div>
                <div class="form-check"><input class="form-check-input" type="radio" id="date_feb"><label class="form-check-label" for="date_feb">2026 February (3646)</label></div>
                <div class="form-check"><input class="form-check-input" type="radio" id="date_mar"><label class="form-check-label" for="date_mar">2026 March (3813)</label></div>
                <a href="#" class="filter-show-more">Show more</a>
              </div>
            
              <h5 class="filter-title">Duration</h5>
              <div style="padding-bottom: 1rem;" class="filter-content filter-scroll-box">
                <div class="form-check"><input class="form-check-input" type="checkbox" name="duration_static" id="dur_1"><label class="form-check-label" for="dur_1">2 days (93)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="duration_static" id="dur_2"><label class="form-check-label" for="dur_2">From 3 to 7 days (4040)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="duration_static" id="dur_3"><label class="form-check-label" for="dur_3">From 1 to 2 weeks (2786)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="duration_static" id="dur_4"><label class="form-check-label" for="dur_4">More than 2 weeks (1491)</label></div>
              </div>
            
              <h5 class="filter-title">Price per trip</h5>
              <div style="padding-bottom: 1rem;" class="filter-content filter-scroll-box">
                <div class="form-check"><input class="form-check-input" type="checkbox" name="price_range" id="price_1"><label class="form-check-label" for="price_1">Below Rs. 20000 (236)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="price_range" id="price_2"><label class="form-check-label" for="price_2">From Rs. 20000 to Rs. 50000 (1311)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="price_range" id="price_3"><label class="form-check-label" for="price_3">From Rs. 50000 to Rs. 80000 (1479)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="price_range" id="price_4"><label class="form-check-label" for="price_4">From Rs. 80000 to Rs. 150000 (2079)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="price_range" id="price_5"><label class="form-check-label" for="price_5">From Rs. 150000 to Rs. 300000 (1703)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="price_range" id="price_6"><label class="form-check-label" for="price_6">More than Rs. 300000 (587)</label></div>
              </div>
            
              <h5 class="filter-title">Private</h5>
              <div style="padding-bottom: 1rem;" class="filter-content">
                <div class="form-check"><input class="form-check-input" type="checkbox" id="private_1"><label class="form-check-label" for="private_1">Private retreats (676)</label></div>
              </div>
            
              <h5 class="filter-title">Language of instruction</h5>
              <div style="padding-bottom: 1rem;" class="filter-content filter-scroll-box">
                <div class="form-check"><input class="form-check-input" type="checkbox" id="lang_en"><label class="form-check-label" for="lang_en">English (4979)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="lang_de"><label class="form-check-label" for="lang_de">German (397)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="lang_fr"><label class="form-check-label" for="lang_fr">French (290)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="lang_es"><label class="form-check-label" for="lang_es">Spanish (141)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="lang_nl"><label class="form-check-label" for="lang_nl">Dutch (87)</label></div>
              </div>
            
              <h5 class="filter-title">Meals</h5>
              <div style="padding-bottom: 1rem;" class="filter-content filter-scroll-box">
                <div class="form-check"><input class="form-check-input" type="checkbox" id="meal_all"><label class="form-check-label" for="meal_all">All meals included (4238)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="meal_bfast"><label class="form-check-label" for="meal_bfast">Breakfast (5881)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="meal_brunch"><label class="form-check-label" for="meal_brunch">Brunch (693)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="meal_lunch"><label class="form-check-label" for="meal_lunch">Lunch (4654)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="meal_dinner"><label class="form-check-label" for="meal_dinner">Dinner (5614)</label></div>
              </div>
            
              <h5 class="filter-title">Food</h5>
              <div style="padding-bottom: 1rem;" class="filter-content filter-scroll-box">
                <div class="form-check"><input class="form-check-input" type="checkbox" id="food_veg"><label class="form-check-label" for="food_veg">Vegetarian (incl. lacto-ovo) (5599)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="food_vegan"><label class="form-check-label" for="food_vegan">Vegan (3915)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="food_gf"><label class="form-check-label" for="food_gf">Gluten-free (2539)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="food_org"><label class="form-check-label" for="food_org">Organic & whole-foods (2404)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="food_ayu"><label class="form-check-label" for="food_ayu">Ayurvedic & yogic (incl. naturopathic) (2176)</label></div>
                <a href="#" class="filter-show-more">Show more</a>
              </div>
            
              <h5 class="filter-title">Airport transfer</h5>
              <div style="padding-bottom: 1rem;" class="filter-content">
                <div class="form-check"><input class="form-check-input" type="checkbox" id="apt_avail"><label class="form-check-label" for="apt_avail">Airport transfer available (2056)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="apt_incl"><label class="form-check-label" for="apt_incl">Airport transfer included (1766)</label></div>
              </div>
              
            
              <h5 class="filter-title">Categories</h5>
              <div style="padding-bottom: 1rem;" class="filter-content filter-accordion">
                <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseCat" role="button" aria-expanded="false" aria-controls="collapseCat">
                  <div class="sb-sidenav-collapse-arrow">Budget or luxury <i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseCat">
                  <div class="filter-collapse-body">
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_veg"><label class="form-check-label" for="food_veg">Vegetarian (incl. lacto-ovo) (5599)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_vegan"><label class="form-check-label" for="food_vegan">Vegan (3915)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_gf"><label class="form-check-label" for="food_gf">Gluten-free (2539)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_org"><label class="form-check-label" for="food_org">Organic & whole-foods (2404)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_ayu"><label class="form-check-label" for="food_ayu">Ayurvedic & yogic (incl. naturopathic) (2176)</label></div>
                  </div>
                </div>
                <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseCat2" role="button" aria-expanded="false" aria-controls="collapseCat2">
                  <div class="sb-sidenav-collapse-arrow">Skill Level <i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseCat2">
                  <div class="filter-collapse-body">
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_veg"><label class="form-check-label" for="food_veg">Vegetarian (incl. lacto-ovo) (5599)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_vegan"><label class="form-check-label" for="food_vegan">Vegan (3915)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_gf"><label class="form-check-label" for="food_gf">Gluten-free (2539)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_org"><label class="form-check-label" for="food_org">Organic & whole-foods (2404)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_ayu"><label class="form-check-label" for="food_ayu">Ayurvedic & yogic (incl. naturopathic) (2176)</label></div>
                  </div>
                </div>
                <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseCat3" role="button" aria-expanded="false" aria-controls="collapseCat3">
                  <div class="sb-sidenav-collapse-arrow">Teacher Training <i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseCat3">
                  <div class="filter-collapse-body">
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_veg"><label class="form-check-label" for="food_veg">Vegetarian (incl. lacto-ovo) (5599)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_vegan"><label class="form-check-label" for="food_vegan">Vegan (3915)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_gf"><label class="form-check-label" for="food_gf">Gluten-free (2539)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_org"><label class="form-check-label" for="food_org">Organic & whole-foods (2404)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_ayu"><label class="form-check-label" for="food_ayu">Ayurvedic & yogic (incl. naturopathic) (2176)</label></div>
                  </div>
                </div>
                <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseCat4" role="button" aria-expanded="false" aria-controls="collapseCat4">
                  <div class="sb-sidenav-collapse-arrow">Spirituality & Chanting <i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseCat4">
                  <div class="filter-collapse-body">
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_veg"><label class="form-check-label" for="food_veg">Vegetarian (incl. lacto-ovo) (5599)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_vegan"><label class="form-check-label" for="food_vegan">Vegan (3915)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_gf"><label class="form-check-label" for="food_gf">Gluten-free (2539)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_org"><label class="form-check-label" for="food_org">Organic & whole-foods (2404)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_ayu"><label class="form-check-label" for="food_ayu">Ayurvedic & yogic (incl. naturopathic) (2176)</label></div>
                  </div>
                </div>
                <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseCat5" role="button" aria-expanded="false" aria-controls="collapseCat5">
                  <div class="sb-sidenav-collapse-arrow">Health & Wellness <i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseCat5">
                  <div class="filter-collapse-body">
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_veg"><label class="form-check-label" for="food_veg">Vegetarian (incl. lacto-ovo) (5599)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_vegan"><label class="form-check-label" for="food_vegan">Vegan (3915)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_gf"><label class="form-check-label" for="food_gf">Gluten-free (2539)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_org"><label class="form-check-label" for="food_org">Organic & whole-foods (2404)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_ayu"><label class="form-check-label" for="food_ayu">Ayurvedic & yogic (incl. naturopathic) (2176)</label></div>
                  </div>
                </div>
              </div>
            
              <h5 class="filter-title">Types</h5>
              <div style="padding-bottom: 1rem;" class="filter-content filter-accordion">
                <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseCat" role="button" aria-expanded="false" aria-controls="collapseCat">
                  <div class="sb-sidenav-collapse-arrow">Sweat & Flow <i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseCat">
                  <div class="filter-collapse-body">
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_veg"><label class="form-check-label" for="food_veg">Vegetarian (incl. lacto-ovo) (5599)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_vegan"><label class="form-check-label" for="food_vegan">Vegan (3915)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_gf"><label class="form-check-label" for="food_gf">Gluten-free (2539)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_org"><label class="form-check-label" for="food_org">Organic & whole-foods (2404)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_ayu"><label class="form-check-label" for="food_ayu">Ayurvedic & yogic (incl. naturopathic) (2176)</label></div>
                  </div>
                </div>
                <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseCat2" role="button" aria-expanded="false" aria-controls="collapseCat2">
                  <div class="sb-sidenav-collapse-arrow">Mindfulness & Meditation <i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseCat2">
                  <div class="filter-collapse-body">
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_veg"><label class="form-check-label" for="food_veg">Vegetarian (incl. lacto-ovo) (5599)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_vegan"><label class="form-check-label" for="food_vegan">Vegan (3915)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_gf"><label class="form-check-label" for="food_gf">Gluten-free (2539)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_org"><label class="form-check-label" for="food_org">Organic & whole-foods (2404)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_ayu"><label class="form-check-label" for="food_ayu">Ayurvedic & yogic (incl. naturopathic) (2176)</label></div>
                  </div>
                </div>
                <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseCat3" role="button" aria-expanded="false" aria-controls="collapseCat3">
                  <div class="sb-sidenav-collapse-arrow">Restore & Revitalize <i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseCat3">
                  <div class="filter-collapse-body">
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_veg"><label class="form-check-label" for="food_veg">Vegetarian (incl. lacto-ovo) (5599)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_vegan"><label class="form-check-label" for="food_vegan">Vegan (3915)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_gf"><label class="form-check-label" for="food_gf">Gluten-free (2539)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_org"><label class="form-check-label" for="food_org">Organic & whole-foods (2404)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_ayu"><label class="form-check-label" for="food_ayu">Ayurvedic & yogic (incl. naturopathic) (2176)</label></div>
                  </div>
                </div>
                <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseCat4" role="button" aria-expanded="false" aria-controls="collapseCat4">
                  <div class="sb-sidenav-collapse-arrow">Fitness & Strength <i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseCat4">
                  <div class="filter-collapse-body">
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_veg"><label class="form-check-label" for="food_veg">Vegetarian (incl. lacto-ovo) (5599)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_vegan"><label class="form-check-label" for="food_vegan">Vegan (3915)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_gf"><label class="form-check-label" for="food_gf">Gluten-free (2539)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_org"><label class="form-check-label" for="food_org">Organic & whole-foods (2404)</label></div>
                      <div class="form-check"><input class="form-check-input" type="checkbox" id="food_ayu"><label class="form-check-label" for="food_ayu">Ayurvedic & yogic (incl. naturopathic) (2176)</label></div>
                  </div>
                </div>
              </div>
            
            <h5 class="filter-title">Reviews</h5>
              <div style="padding-bottom: 1rem;" class="filter-content">
                <div class="form-check"><input class="form-check-input" type="radio" name="reviews" id="rev_1"><label class="form-check-label" for="rev_1">Excellent (4.5+)</label></div>
                <div class="form-check"><input class="form-check-input" type="radio" name="reviews" id="rev_2"><label class="form-check-label" for="rev_2">Good (4.0+)</label></div>
              </div>
            
            
            <div class="d-grid gap-2">
              <button class="btn btn-apply-filters" type="submit">Apply Filters</button>
              <a href="index.php" class="btn btn-clear-filters">Clear All</a>
            </div>

          </form>
        </div>
      </aside>

      <!-- === Results Column === -->
      <section class="col-lg-9">
        <div class="results-header">
          <p class="results-count">
              <?php if ($total > 0): ?>
              Showing <?php echo $offset + 1; ?> - <?php echo min($offset + $perPage, $total); ?> of <?php echo $total; ?> results
              <?php else: ?>
              No results found
              <?php endif; ?>
          </p>
          
          <form method="get" id="sortForm" class="sort-by-form">
              <?php
              foreach ($locations as $loc) { echo '<input type="hidden" name="location[]" value="' . htmlspecialchars($loc) . '">'; }
              foreach ($durations as $d) { echo '<input type="hidden" name="duration[]" value="' . htmlspecialchars($d) . '">'; }
              if ($price_min) echo '<input type="hidden" name="price_min" value="' . (int)$price_min . '">';
              if ($price_max) echo '<input type="hidden" name="price_max" value="' . (int)$price_max . '">';
              if ($q) echo '<input type="hidden" name="q" value="' . htmlspecialchars($q) . '">';
              ?>
              <label for="sort-select" class="sort-by-label">Sort by:</label>
              <select name="sort" id="sort-select" class="form-select" onchange="this.form.submit()">
              <option value="">Newest</option>
              <option value="price_asc">Price low→high</option>
              <option value="price_desc">Price high→low</option>
              </select>
          </form>
        </div>

          <!-- This is the single-column list wrapper -->
          <div class="package-list-wrapper">
            
            <!-- === Your existing PHP Loop (UNCHANGED LOGIC) === -->
            <?php if ($res && $res->num_rows > 0): ?>
              <?php while ($pkg = $res->fetch_assoc()): ?>
                <?php
                  // === YOUR IMAGE LOGIC (FIXED) ===
                  $img = $pkg['image_path'] ? $pkg['image_path'] : 'https://placehold.co/400x300/E0E0E0/777?text=Yoga+Retreat';
                ?>
                
                <!-- NEW Horizontal Card Layout (matches refr.mp4) -->
                <div class="package-card-horizontal">
                  <div class="row g-0">
                    <div class="col-md-4">
                      <div class="horizontal-image-wrapper">
                        <a href="packageDetails.php?id=<?= $pkg['id'] ?>" class="horizontal-image-link">
                          <div class="horizontal-image" style="background-image: url('<?= $img ?>');"></div>
                        </a>
                        <button class="btn-wishlist"><i class="bi bi-heart"></i></button>
                        <span class="package-card-tag-best">Best Seller</span>
                      </div>
                    </div>

                    <div class="col-md-8 d-flex flex-column"> <div class="horizontal-body flex-grow-1"> <div class="card-header-top">
                          <div class="card-top-left">
                            <i class="bi bi-geo-alt-fill"></i> <span><?= htmlspecialchars($pkg['country']) ?></span>
                          </div>
                          <div class="card-top-right">
                            <span><i class="bi bi-eye"></i> 132</span>
                            <span><i class="bi bi-star-fill"></i> 4.5 (120)</span>
                          </div>
                        </div>

                        <h3 class="package-card-title">
                          <a href="packageDetails.php?id=<?= $pkg['id'] ?>"><?= htmlspecialchars($pkg['package_title']) ?></a>
                        </h3>
                        
                        <div class="package-card-facilities">
                          <span><i class="bi bi-airplane"></i> Airport Transfer available</span>
                          <span><i class="bi bi-cup-straw"></i> <?= htmlspecialchars($pkg['meals_included']) ?></span>
                          <span><i class="bi bi-universal-access"></i> Vegetarian friendly</span>
                          <span><i class="bi bi-translate"></i> Instructed in English</span>
                        </div>
                      </div>

                      <div class="horizontal-footer">
                        <div class="footer-left-info">
                          <span><i class="bi bi-person"></i> 1 person</span>
                          <span><i class="bi bi-calendar-event"></i> <?= (int)$pkg['nights'] ?> nights</span>
                        </div>
                        
                        <div class="footer-price-action">
                          <div class="package-card-price">
                            <span class="package-card-price-label">From</span>
                            <span class="package-card-price-value">
                              ₹<?= number_format($pkg['price_per_person'] ?? 0) ?>
                            </span>
                          </div>
                          <a href="packageDetails.php?id=<?= $pkg['id'] ?>" class="btn btn-view-deal">View Deal</a>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
              <!-- End Horizontal Card -->

            <?php endwhile; ?>
            <?php else: ?>
              <div class="col-12">
                <div class="alert alert-info" role="alert">
                  No yoga retreats found matching your criteria. Please try different filters.
                </div>
              </div>
            <?php endif; ?>
            <!-- === End of PHP Loop === -->
          </div>

        <!-- === Your existing Pagination (UNCHANGED LOGIC) === -->
        <?php if ($totalPages > 1): ?>
          <nav aria-label="Page navigation" class="pagination-wrapper">
            <ul class="pagination justify-content-center">
              
              <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?= $page - 1 ?>&<?= http_build_query($_GET,'','&') ?>" aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
                </a>
              </li>
              
              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                  <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query($_GET,'','&') ?>"><?= $i ?></a>
                </li>
              <?php endfor; ?>
              
              <li class="page-item <?php if ($page >= $totalPages) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?= $page + 1 ?>&<?= http_build_query($_GET,'','&') ?>" aria-label="Next">
                  <span aria-hidden="true">&raquo;</span>
                </a>
              </li>

            </ul>
          </nav>
        <?php endif; ?>

      </section>
    </div>
  </main>

    <?php include 'filterModal.php'; ?>

  <!-- YOUR EXISTING FOOTER (UNCHANGED) -->
  <?php include 'includes/footer.php'; ?>
  

  <!-- Bootstrap JS Bundle -->
  <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <!-- Custom JS for navbar behavior (from your original navbar file) -->
  <script>
    // This is the script from your original yoga_navbar.php
    window.addEventListener('scroll', function() {
      const nav = document.querySelector('.navbar');
      if (window.scrollY > 50) {
        nav.classList.add('sticky'); 
      } else {
        nav.classList.remove('sticky');
      }
    });

    // New script for sticky sidebar (from refr.mp4)
    document.addEventListener("scroll", function () {
      const sidebar = document.getElementById('filter-sidebar');
      if (sidebar && window.innerWidth >= 992) { // Desktop only
        
        // Get height of your sticky navbar
        const nav = document.querySelector('.navbar.sticky');
        let navHeight = nav ? nav.offsetHeight : 0;
        
        // Get height of your search bar (it's not sticky, so this is simpler)
        // We set the top relative to the main content's padding
        const mainContentTop = document.querySelector('.main-content').offsetTop;
        const sidebarTopOffset = 20; // 20px padding from top

        if (window.scrollY > (mainContentTop - navHeight - sidebarTopOffset)) {
           sidebar.style.top = (navHeight + sidebarTopOffset) + 'px';
           sidebar.classList.add('sticky-sidebar');
        } else {
           sidebar.style.top = '0'; // Reset
           sidebar.classList.remove('sticky-sidebar');
        }
      }
    });
  </script>
</body>
</html>

