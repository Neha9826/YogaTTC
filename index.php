<?php
require_once __DIR__ . '/yoga_session.php';
include __DIR__ . '/db.php'; // Assumes db.php is in this directory

// === 1. DEFINE DYNAMIC FILTER CONFIG ===
$dynamic_filter_config = [
    'deal' => ['table' => 'yoga_package_deals', 'link_table' => 'yoga_package_selected_deals', 'col' => 'deal_id', 'title' => 'Deals'],
    'dayonline' => ['table' => 'yoga_package_dayonline', 'link_table' => 'yoga_package_selected_dayonline', 'col' => 'dayonline_id', 'title' => 'Day or Online'],
    'language' => ['table' => 'yoga_package_languages', 'link_table' => 'yoga_package_selected_languages', 'col' => 'language_id', 'title' => 'Language of instruction'],
    'meal' => ['table' => 'yoga_package_meals', 'link_table' => 'yoga_package_selected_meals', 'col' => 'meal_id', 'title' => 'Meals'],
    'food' => ['table' => 'yoga_package_food', 'link_table' => 'yoga_package_selected_food', 'col' => 'food_id', 'title' => 'Food'],
    'airport_transfer' => ['table' => 'yoga_package_airport_transfers', 'link_table' => 'yoga_package_selected_airport_transfers', 'col' => 'transfer_id', 'title' => 'Airport transfer'],
    'type' => ['table' => 'yoga_package_types', 'link_table' => 'yoga_package_selected_types', 'col' => 'type_id', 'title' => 'Types'],
    'category' => ['table' => 'yoga_package_categories', 'link_table' => 'yoga_package_selected_categories', 'col' => 'category_id', 'title' => 'Categories'],
];

// === 2. PRICE RANGE FILTER CONFIG ===
$price_range_config = [
    '0-10000' => ['min' => 0, 'max' => 10000, 'label' => 'Below ₹10,000'],
    '10000-25000' => ['min' => 10000, 'max' => 25000, 'label' => '₹10,000 - ₹25,000'],
    '25000-50000' => ['min' => 25000, 'max' => 50000, 'label' => '₹25,000 - ₹50,000'],
    '50000-100000' => ['min' => 50000, 'max' => 100000, 'label' => '₹50,000 - ₹100,000'],
    '100000' => ['min' => 100000, 'max' => 9999999, 'label' => 'Over ₹100,000'],
];

// === 3. DEFINE STATIC LOCATION HIERARCHY ===
$all_locations_static = [
    'Asia' => [
        'India' => [
            'Odisha' => ['Bhubaneswar' => true, 'Puri' => true, 'Cuttack' => true],
            'Goa' => ['Panaji' => true, 'Margao' => true],
            'Uttarakhand' => ['Rishikesh' => true, 'Dehradun' => true],
            'Himachal Pradesh' => ['Dharamshala' => true, 'Shimla' => true],
        ],
        'Thailand' => [
            'Chiang Mai' => ['Chiang Mai City' => true],
            'Phuket' => ['Phuket Town' => true],
        ],
        'Indonesia' => [
            'Bali' => ['Ubud' => true, 'Kuta' => true],
        ],
        'Japan' => [
            'Kanto' => ['Tokyo' => true],
            'Kansai' => ['Kyoto' => true, 'Osaka' => true],
        ],
    ],
    'Europe' => [
        'Spain' => [
            'Andalusia' => ['Seville' => true, 'Malaga' => true],
            'Catalonia' => ['Barcelona' => true],
        ],
        'Portugal' => [
            'Lisbon' => ['Lisbon' => true],
            'Algarve' => ['Faro' => true],
        ],
        'Germany' => [
            'Berlin' => ['Berlin' => true],
            'Bavaria' => ['Munich' => true],
        ],
        'United Kingdom' => [
            'England' => ['London' => true, 'Manchester' => true],
            'Scotland' => ['Edinburgh' => true],
        ],
    ],
    'North America' => [
        'USA' => [
            'California' => ['Los Angeles' => true, 'San Francisco' => true],
            'New York' => ['New York City' => true],
            'Florida' => ['Miami' => true],
        ],
        'Canada' => [
            'British Columbia' => ['Vancouver' => true],
            'Ontario' => ['Toronto' => true],
        ],
        'Mexico' => [
            'Quintana Roo' => ['Tulum' => true, 'Cancun' => true],
        ],
    ],
    'South America' => [
        'Brazil' => [
            'Rio de Janeiro' => ['Rio de Janeiro' => true],
            'Bahia' => ['Salvador' => true],
        ],
        'Peru' => [
            'Cusco' => ['Cusco' => true, 'Sacred Valley' => true],
        ],
    ],
    'Africa' => [
        'Morocco' => [
            'Marrakech-Safi' => ['Marrakech' => true],
        ],
        'South Africa' => [
            'Western Cape' => ['Cape Town' => true],
        ],
    ],
    'Australia' => [
        'New South Wales' => [
            'Sydney' => true,
            'Byron Bay' => true,
        ],
        'Victoria' => [
            'Melbourne' => true,
        ],
    ],
    'Antarctica' => []
];


// === 4. READ ALL FILTERS FROM GET ===
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

$filter_durations = isset($_GET['duration']) ? (array)$_GET['duration'] : [];
$filter_continents = isset($_GET['continent']) ? (array)$_GET['continent'] : [];
$filter_countries = isset($_GET['country']) ? (array)$_GET['country'] : [];
$filter_states = isset($_GET['state']) ? (array)$_GET['state'] : [];
$filter_cities = isset($_GET['city']) ? (array)$_GET['city'] : [];
$filter_price_ranges = isset($_GET['price_range']) ? (array)$_GET['price_range'] : [];
$filter_private = isset($_GET['private']) ? (array)$_GET['private'] : []; // For "Private" filter

$dynamic_filters = [];
foreach ($dynamic_filter_config as $key => $config) {
    $dynamic_filters[$key] = isset($_GET[$key]) ? (array)$_GET[$key] : [];
}

// === 5. FETCH DYNAMIC DATA FOR SIDEBAR ===

function get_filter_items($conn, $table, $link_table, $col) {
    $sql = "
        SELECT T1.id, T1.name, COUNT(DISTINCT p.id) as count
        FROM $table T1
        JOIN $link_table T2 ON T1.id = T2.$col
        JOIN yoga_packages p ON T2.package_id = p.id
        JOIN yoga_retreats r ON p.retreat_id = r.id
        WHERE p.is_published = 1 AND r.is_published = 1
        GROUP BY T1.id, T1.name
        HAVING count > 0
        ORDER BY T1.name ASC
    ";
    $res = $conn->query($sql);
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

$sidebar_data = [];
foreach ($dynamic_filter_config as $key => $config) {
    $sidebar_data[$key] = get_filter_items($conn, $config['table'], $config['link_table'], $config['col']);
}

$all_locations_hierarchical = $all_locations_static;

$dur_res = $conn->query("
    SELECT p.nights, COUNT(p.id) as count
    FROM yoga_packages p
    JOIN yoga_retreats r ON p.retreat_id = r.id
    WHERE p.nights > 0 AND p.is_published = 1 AND r.is_published = 1
    GROUP BY p.nights
    ORDER BY p.nights ASC
");
$all_durations = $dur_res ? $dur_res->fetch_all(MYSQLI_ASSOC) : [];


// === 6. BUILD THE DYNAMIC WHERE CLAUSE ===
$where = [];
$where[] = "p.is_published = 1 AND r.is_published = 1";

if ($q !== '') {
    $q_esc = $conn->real_escape_string($q);
    $where[] = "(p.title LIKE '%$q_esc%' OR p.description LIKE '%$q_esc%' OR r.title LIKE '%$q_esc%' OR o.name LIKE '%$q_esc%' OR o.country LIKE '%$q_esc%')";
}

// Location Filter
$location_where = [];
if (!empty($filter_continents)) {
    $escaped = array_map(fn($v) => "'" . $conn->real_escape_string($v) . "'", $filter_continents);
    $location_where[] = "o.continent IN (" . implode(',', $escaped) . ")";
}
if (!empty($filter_countries)) {
    $escaped = array_map(fn($v) => "'" . $conn->real_escape_string($v) . "'", $filter_countries);
    $location_where[] = "o.country IN (" . implode(',', $escaped) . ")";
}
if (!empty($filter_states)) {
    $escaped = array_map(fn($v) => "'" . $conn->real_escape_string($v) . "'", $filter_states);
    $location_where[] = "o.state IN (" . implode(',', $escaped) . ")";
}
if (!empty($filter_cities)) {
    $escaped = array_map(fn($v) => "'" . $conn->real_escape_string($v) . "'", $filter_cities);
    $location_where[] = "o.city IN (" . implode(',', $escaped) . ")";
}
if (!empty($location_where)) {
    $where[] = "(" . implode(' OR ', $location_where) . ")";
}

// Duration (Nights) Filter
if (!empty($filter_durations)) {
    $escaped = array_map('intval', $filter_durations);
    $where[] = "p.nights IN (" . implode(',', $escaped) . ")";
}

// Price Range Filter
if (!empty($filter_price_ranges)) {
    $price_where = [];
    foreach ($filter_price_ranges as $range_key) {
        if (isset($price_range_config[$range_key])) {
            $min = $price_range_config[$range_key]['min'];
            $max = $price_range_config[$range_key]['max'];
            $price_where[] = "(p.price_per_person >= $min AND p.price_per_person <= $max)";
        }
    }
    if (!empty($price_where)) {
        $where[] = "(" . implode(' OR ', $price_where) . ")";
    }
}

// "Private" Filter (Placeholder logic)
// When ready, you'll need to add `is_private` column to yoga_packages
if (!empty($filter_private)) {
    $private_where = [];
    if (in_array('private', $filter_private)) {
        // $private_where[] = "p.is_private = 1";
    }
    if (in_array('group', $filter_private)) {
        // $private_where[] = "p.is_private = 0";
    }
    if (!empty($private_where)) {
        // $where[] = "(" . implode(' OR ', $private_where) . ")";
    }
    // As this isn't implemented, we add no WHERE clause, but the filter shows
}


// Add Dynamic Filters (Deals, Languages, etc.)
foreach ($dynamic_filter_config as $key => $config) {
    if (!empty($dynamic_filters[$key])) {
        $escaped_ids = implode(',', array_map('intval', $dynamic_filters[$key]));
        $where[] = "EXISTS (
            SELECT 1 FROM {$config['link_table']}
            WHERE package_id = p.id AND {$config['col']} IN ($escaped_ids)
        )";
    }
}

$where_sql = (count($where) > 0) ? " WHERE " . implode(' AND ', $where) : '';

// === 7. BUILD PAGINATION & SORTING ===
$perPage = 9;
$offset = ($page - 1) * $perPage;

$countSql = "
  SELECT COUNT(DISTINCT p.id) AS cnt
  FROM yoga_packages p
  JOIN yoga_retreats r ON p.retreat_id = r.id
  JOIN organizations o ON r.organization_id = o.id
  $where_sql
";
$countRes = $conn->query($countSql);
$total = ($countRes && $countRes->num_rows) ? (int)$countRes->fetch_assoc()['cnt'] : 0;
$totalPages = max(1, ceil($total / $perPage));

$orderBy_sql = 'ORDER BY p.created_at DESC';
if ($sort === 'price_asc') {
    $orderBy_sql = 'ORDER BY p.price_per_person ASC';
} elseif ($sort === 'price_desc') {
    $orderBy_sql = 'ORDER BY p.price_per_person DESC';
}

// === 8. BUILD AND RUN FINAL QUERY ===
$sql = "
  SELECT
    p.id,
    p.title AS package_title,
    p.description,
    p.price_per_person,
    p.nights,
    p.meals_included,
    p.max_persons,
    p.view_count,
    r.id AS retreat_id,
    r.title AS retreat_title,
    o.id AS org_id,
    o.name AS org_name,
    o.country,
    (SELECT image_path FROM yoga_retreat_images WHERE retreat_id = r.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) AS image_path
  FROM yoga_packages p
  JOIN yoga_retreats r ON p.retreat_id = r.id
  JOIN organizations o ON r.organization_id = o.id
  $where_sql
  GROUP BY p.id
  $orderBy_sql
  LIMIT $offset, $perPage
";

$res = $conn->query($sql);
if (!$res) {
    echo "Error: " . $conn->error;
    $res = null;
}

// === 9. HELPER FUNCTIONS FOR HTML ===
function build_query_string($exclude_key = null) {
    $params = $_GET;
    if ($exclude_key && isset($params[$exclude_key])) {
        unset($params[$exclude_key]);
    }
    return http_build_query($params);
}

function render_filter_group($title, $input_name, $items, $selected_values, $item_key = 'id', $item_label = 'name') {
    if (empty($items)) return;

    $html = "<h5 class='filter-title'>$title</h5>";
    $html .= "<div style='padding-bottom: 1rem;' class='filter-content filter-scroll-box'>";
    
    foreach ($items as $item) {
        $value = htmlspecialchars($item[$item_key]);
        $label = htmlspecialchars($item[$item_label]);
        $count = (int)$item['count'];
        $id = "cb_{$input_name}_{$value}";
        $checked = in_array($item[$item_key], $selected_values) ? 'checked' : '';

        $html .= "<div class='form-check'>";
        $html .= "<input class='form-check-input' type='checkbox' name='{$input_name}[]' value='$value' id='$id' $checked>";
        $html .= "<label class='form-check-label' for='$id'>$label ($count)</label>";
        $html .= "</div>";
    }
    
    $html .= "</div>";
    return $html;
}

function render_price_filter($config, $selected_ranges) {
    $html = "<h5 class='filter-title'>Price per Course (₹)</h5>";
    $html .= "<div style='padding-bottom: 1rem;' class='filter-content filter-scroll-box'>";
    
    foreach ($config as $key => $range) {
        $value = htmlspecialchars($key);
        $label = htmlspecialchars($range['label']);
        $id = "cb_price_range_{$value}";
        $checked = in_array($key, $selected_ranges) ? 'checked' : '';

        $html .= "<div class='form-check'>";
        $html .= "<input class='form-check-input' type='checkbox' name='price_range[]' value='$value' id='$id' $checked>";
        $html .= "<label class='form-check-label' for='$id'>$label</label>";
        $html .= "</div>";
    }
    
    $html .= "</div>";
    return $html;
}

$pageTitle = "Find & Book Yoga Retreats";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="yoga.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    .filter-sidebar .accordion-button {
        padding: 0.5rem 0.5rem;
        font-size: 0.9rem;
        font-weight: 600;
        color: #333;
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
    }
    .filter-sidebar .accordion-button:not(.collapsed) {
        background-color: #e9ecef;
    }
    .filter-sidebar .accordion-button:focus {
        box-shadow: none;
    }
    .filter-sidebar .accordion-body {
        padding: 0.5rem 0.5rem 0.5rem 1.5rem;
        background-color: #fff;
    }
    .filter-sidebar .accordion-item {
        border: none;
        border-bottom: 1px solid #dee2e6;
    }
    .filter-sidebar .accordion-item:last-of-type {
        border-bottom: 1px solid #dee2e6;
    }
    .filter-sidebar .form-check {
        font-size: 0.85rem;
    }
    .filter-sidebar .form-check-label {
        cursor: pointer;
    }
    .filter-sidebar .filter-scroll-box {
        max-height: 250px;
        overflow-y: auto;
    }
  </style>
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
      
      <aside class="col-lg-3">
        <div class="filter-sidebar" id="filter-sidebar">
          <form id="filterForm" action="index.php" method="GET">
            <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">

            <h4 style="padding-bottom: 1rem;" class="filter-title-bold">Filters</h4>
            <div class="d-flex gap-2">
              <button class="btn btn-apply-filters btn-sm w-50" type="submit">Apply</button>
              <a href="index.php" class="btn btn-clear-filters btn-sm">Clear All</a>
            </div>

            <?php echo render_filter_group(
                $dynamic_filter_config['deal']['title'],
                'deal',
                $sidebar_data['deal'],
                $dynamic_filters['deal']
            ); ?>
            
            <?php echo render_filter_group(
                $dynamic_filter_config['dayonline']['title'],
                'dayonline',
                $sidebar_data['dayonline'],
                $dynamic_filters['dayonline']
            ); ?>

            <h5 class="filter-title">Destinations</h5>
            <div class="accordion accordion-flush" id="destinationAccordion">
                <?php if (empty($all_locations_hierarchical)): ?>
                    <small class="text-muted p-2">No destinations found.</small>
                <?php endif; ?>

                <?php foreach ($all_locations_hierarchical as $continent => $countries): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-cont-<?= md5($continent) ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-cont-<?= md5($continent) ?>">
                                <input class="form-check-input me-2" type="checkbox" name="continent[]" value="<?= htmlspecialchars($continent) ?>" id="cb_cont_<?= md5($continent) ?>" <?= in_array($continent, $filter_continents) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="cb_cont_<?= md5($continent) ?>"><?= htmlspecialchars($continent) ?></label>
                            </button>
                        </h2>
                        <div id="collapse-cont-<?= md5($continent) ?>" class="accordion-collapse collapse" data-bs-parent="#destinationAccordion">
                            <div class="accordion-body">
                                <?php if (empty($countries)): ?>
                                    <small class="text-muted">No countries listed.</small>
                                <?php endif; ?>
                                <?php foreach ($countries as $country => $states): ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading-country-<?= md5($country) ?>">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-country-<?= md5($country) ?>">
                                                <input class="form-check-input me-2" type="checkbox" name="country[]" value="<?= htmlspecialchars($country) ?>" id="cb_country_<?= md5($country) ?>" <?= in_array($country, $filter_countries) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="cb_country_<?= md5($country) ?>"><?= htmlspecialchars($country) ?></label>
                                            </button>
                                        </h2>
                                        <div id="collapse-country-<?= md5($country) ?>" class="accordion-collapse collapse" data-bs-parent="#collapse-cont-<?= md5($continent) ?>">
                                            <div class="accordion-body">
                                                <?php foreach ($states as $state => $cities): ?>
                                                    <?php if(empty($state)) continue; ?>
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header" id="heading-state-<?= md5($state) ?>">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-state-<?= md5($state) ?>">
                                                                <input class="form-check-input me-2" type="checkbox" name="state[]" value="<?= htmlspecialchars($state) ?>" id="cb_state_<?= md5($state) ?>" <?= in_array($state, $filter_states) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="cb_state_<?= md5($state) ?>"><?= htmlspecialchars($state) ?></label>
                                                            </button>
                                                        </h2>
                                                        <div id="collapse-state-<?= md5($state) ?>" class="accordion-collapse collapse" data-bs-parent="#collapse-country-<?= md5($country) ?>">
                                                            <div class="accordion-body">
                                                                <?php foreach ($cities as $city => $val): ?>
                                                                    <?php if(empty($city)) continue; ?>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" name="city[]" value="<?= htmlspecialchars($city) ?>" id="cb_city_<?= md5($city) ?>" <?= in_array($city, $filter_cities) ? 'checked' : '' ?>>
                                                                        <label class="form-check-label" for="cb_city_<?= md5($city) ?>"><?= htmlspecialchars($city) ?></label>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                                
                                                <?php if(isset($states[''])) {
                                                    foreach($states[''] as $city => $val) {
                                                        if(empty($city)) continue;
                                                        echo '<div class="form-check ms-2">';
                                                        echo '<input class="form-check-input" type="checkbox" name="city[]" value="'.htmlspecialchars($city).'" id="cb_city_'.md5($city).'" '.(in_array($city, $filter_cities) ? 'checked' : '').'>';
                                                        echo '<label class="form-check-label" for="cb_city_'.md5($city).'">'.htmlspecialchars($city).'</label>';
                                                        echo '</div>';
                                                    }
                                                } ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php echo render_filter_group(
                'Duration (Nights)', 
                'duration', 
                $all_durations, 
                $filter_durations, 
                'nights',
                'nights'
            ); ?>
            
            <?php echo render_price_filter($price_range_config, $filter_price_ranges); ?>
            
            <h5 class='filter-title'>Private</h5>
            <div style='padding-bottom: 1rem;' class='filter-content filter-scroll-box'>
                <div class='form-check'>
                    <input class='form-check-input' type='checkbox' name='private[]' value='private' id='cb_private' <?= in_array('private', $filter_private) ? 'checked' : '' ?>>
                    <label class='form-check-label' for='cb_private'>Private Room</label>
                </div>
                <div class='form-check'>
                    <input class='form-check-input' type='checkbox' name='private[]' value='group' id='cb_group' <?= in_array('group', $filter_private) ? 'checked' : '' ?>>
                    <label class='form-check-label' for='cb_group'>Group (Shared)</label>
                </div>
            </div>
            
            <?php echo render_filter_group(
                $dynamic_filter_config['language']['title'],
                'language',
                $sidebar_data['language'],
                $dynamic_filters['language']
            ); ?>
            
            <?php echo render_filter_group(
                $dynamic_filter_config['meal']['title'],
                'meal',
                $sidebar_data['meal'],
                $dynamic_filters['meal']
            ); ?>
            
            <?php echo render_filter_group(
                $dynamic_filter_config['food']['title'],
                'food',
                $sidebar_data['food'],
                $dynamic_filters['food']
            ); ?>
            
            <?php echo render_filter_group(
                $dynamic_filter_config['airport_transfer']['title'],
                'airport_transfer',
                $sidebar_data['airport_transfer'],
                $dynamic_filters['airport_transfer']
            ); ?>

            <?php echo render_filter_group(
                $dynamic_filter_config['category']['title'],
                'category',
                $sidebar_data['category'],
                $dynamic_filters['category']
            ); ?>
            
            <?php echo render_filter_group(
                $dynamic_filter_config['type']['title'],
                'type',
                $sidebar_data['type'],
                $dynamic_filters['type']
            ); ?>

            

          </form>
        </div>
      </aside>

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
              $query_string_for_sort = build_query_string('sort');
              parse_str($query_string_for_sort, $params);
              foreach ($params as $key => $value) {
                  if (is_array($value)) {
                      foreach ($value as $val) {
                          echo '<input type="hidden" name="' . htmlspecialchars($key) . '[]" value="' . htmlspecialchars($val) . '">';
                      }
                  } else {
                      echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                  }
              }
              ?>
              <label for="sort-select" class="sort-by-label">Sort by:</label>
              <select name="sort" id="sort-select" class="form-select" onchange="this.form.submit()">
                  <option value="" <?= $sort == '' ? 'selected' : '' ?>>Newest</option>
                  <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Price low→high</option>
                  <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Price high→low</option>
              </select>
          </form>
        </div>

          <div class="package-list-wrapper">
            
            <?php if ($res && $res->num_rows > 0): ?>
              <?php while ($pkg = $res->fetch_assoc()): ?>
                <?php
                  $img = $pkg['image_path'] ? $pkg['image_path'] : 'https://placehold.co/400x300/E0E0E0/777?text=Yoga+Retreat';
                ?>
                <div class="package-card-horizontal">
                  <div class="row g-0">
                    <div class="col-md-4">
                      <div class="horizontal-image-wrapper">
                        <a href="packageDetails.php?id=<?= $pkg['id'] ?>" class="horizontal-image-link">
                          <div class="horizontal-image" style="background-image: url('<?= $img ?>');"></div>
                        </a>
                        <button class="btn-wishlist"><i class="bi bi-heart"></i></button>
                      </div>
                    </div>
                    <div class="col-md-8 d-flex flex-column"> 
                      <div class="horizontal-body flex-grow-1">
                        <div class="card-header-top">
                          <div class="card-top-left">
                            <i class="bi bi-geo-alt-fill"></i> <span><?= htmlspecialchars($pkg['country']) ?></span>
                          </div>
                          <div class="card-top-right">
                            <!-- <span><i class="bi bi-eye"></i> 132</span> -->
                            <span><i class="bi bi-eye"></i> <?= (int)$pkg['view_count'] ?></span>
                            <span><i class="bi bi-star-fill"></i> 4.5 (120)</span>
                          </div>
                        </div>
                        <h3 class="package-card-title">
                          <a href="packageDetails.php?id=<?= $pkg['id'] ?>"><?= htmlspecialchars($pkg['package_title']) ?></a>
                        </h3>
                        <div class="package-card-facilities">
                          <span><i class="bi bi-airplane"></i> Airport Transfer</span>
                          <span><i class="bi bi-cup-straw"></i> <?= $pkg['meals_included'] ? 'Meals Included' : 'Meals Not Included' ?></span>
                          <span><i class="bi bi-translate"></i> Instructed in English</span>
                        </div>
                      </div>
                      <div class="horizontal-footer">
                        <div class="footer-left-info">
                            <?php $persons = (int)$pkg['max_persons']; ?>
                            <span>
                                <i class="bi bi-person"></i> <?= $persons ?> <?= ($persons == 1 ? 'person' : 'persons') ?>
                            </span>
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
              <?php endwhile; ?>
            <?php else: ?>
              <div class="col-12">
                <div class="alert alert-info" role="alert">
                  No yoga retreats found matching your criteria. Please try different filters.
                </div>
              </div>
            <?php endif; ?>
          </div>

        <?php if ($totalPages > 1): ?>
          <nav aria-label="Page navigation" class="pagination-wrapper">
            <ul class="pagination justify-content-center">
              
              <?php $pagination_query = build_query_string('page'); ?>

              <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?= $page - 1 ?>&<?= $pagination_query ?>" aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
                </a>
              </li>
              
              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                  <a class="page-link" href="?page=<?= $i ?>&<?= $pagination_query ?>"><?= $i ?></a>
                </li>
              <?php endfor; ?>
              
              <li class="page-item <?php if ($page >= $totalPages) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?= $page + 1 ?>&<?= $pagination_query ?>" aria-label="Next">
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
  <?php include 'includes/footer.php'; ?>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    window.addEventListener('scroll', function() {
      const nav = document.querySelector('.navbar');
      if (window.scrollY > 50) {
        nav.classList.add('sticky'); 
      } else {
        nav.classList.remove('sticky');
      }
    });

    document.addEventListener("scroll", function () {
      const sidebar = document.getElementById('filter-sidebar');
      if (sidebar && window.innerWidth >= 992) {
        const nav = document.querySelector('.navbar.sticky');
        let navHeight = nav ? nav.offsetHeight : 0;
        const mainContentTop = document.querySelector('.main-content').offsetTop;
        const sidebarTopOffset = 20;

        if (window.scrollY > (mainContentTop - navHeight - sidebarTopOffset)) {
           sidebar.style.top = (navHeight + sidebarTopOffset) + 'px';
           sidebar.classList.add('sticky-sidebar');
        } else {
           sidebar.style.top = '0';
           sidebar.classList.remove('sticky-sidebar');
        }
      }
    });
    
    document.querySelectorAll('#destinationAccordion .form-check-input, #destinationAccordion .form-check-label').forEach(el => {
        el.addEventListener('click', e => {
            e.stopPropagation();
        });
    });
  </script>
</body>
</html>