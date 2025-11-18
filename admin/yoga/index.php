<?php 
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<?php include '../includes/head.php'; ?>
<link href="../css/styles.css" rel="stylesheet">

<body class="sb-nav-fixed">
<?php include '../includes/navbar.php'; ?>

<div id="layoutSidenav">
    <?php include '../includes/sidebar.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4 mt-4">
                <h2>Yoga Module Dashboard</h2>
                <p class="mb-4 text-muted">Hover over a module to see available actions.</p>

                <div class="row g-4">
                    <?php
                    // ✅ Correct mapping of modules to actual DB tables
                    $modules = [
                        'Organizations' => [
                            'icon' => '🏢',
                            'color' => '#150dff',
                            'table' => 'organizations',
                            'actions' => [
                                ['label' => 'All Organizations', 'link' => 'manageOrganizations.php'],
                                ['label' => 'Create Organization', 'link' => 'createOrganization.php']
                            ]
                        ],
                        'Retreats' => [
                            'icon' => '🏕️',
                            'color' => '#f28b82',
                            'table' => 'yoga_retreats',
                            'actions' => [
                                ['label' => 'All Retreats', 'link' => 'allRetreats.php'],
                                ['label' => 'Create Retreat', 'link' => 'createRetreat.php']
                            ]
                        ],
                        'Packages' => [
                            'icon' => '🎁',
                            'color' => '#fbbc04',
                            'table' => 'yoga_packages',
                            'actions' => [
                                ['label' => 'All Packages', 'link' => 'allPackages.php']
                            ]
                        ],
                        'Instructors' => [
                            'icon' => '🧘‍♂️',
                            'color' => '#34a853',
                            'table' => 'yoga_instructors',
                            'actions' => [
                                ['label' => 'Manage Instructors', 'link' => 'manageInstructors.php']
                            ]
                        ],
                        'Amenities' => [
                            'icon' => '🏠',
                            'color' => '#4285f4',
                            'table' => 'yoga_amenities',
                            'actions' => [
                                ['label' => 'Manage Amenities', 'link' => 'manageAmenities.php']
                            ]
                        ],
                        'Batches' => [
                            'icon' => '📅',
                            'color' => '#aa00ff',
                            'table' => 'yoga_batches',
                            'actions' => [
                                ['label' => 'All Batches', 'link' => 'allBatches.php'],
                                ['label' => 'Create Batch', 'link' => 'createBatch.php']
                            ]
                        ],
                        'Bookings' => [
                            'icon' => '📝',
                            'color' => '#00c0ff',
                            'table' => 'y_bookings', // ✅ Corrected table name
                            'actions' => [
                                ['label' => 'All Bookings', 'link' => 'bookings/allBookings.php']
                            ]
                        ],
                        'Users' => [
                            'icon' => '👤',
                            'color' => '#ff6d01',
                            'table' => 'y_users', // ✅ Corrected table name
                            'actions' => [
                                ['label' => 'All Users', 'link' => 'users/allUsers.php'],
                                ['label' => 'Create User', 'link' => 'users/createUser.php']
                            ]
                        ]
                    ];

                    // ✅ Loop and count each module accurately
                    foreach ($modules as $name => $module):
                        $count = 0;
                        $table = $module['table'];

                        if (preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
                            $query = "SELECT COUNT(*) AS cnt FROM `$table`";
                            $result = $conn->query($query);
                            if ($result && $row = $result->fetch_assoc()) {
                                $count = (int)$row['cnt'];
                            }
                        }
                    ?>
                        <div class="col-md-4">
                            <div class="card shadow-sm module-card"
                                 style="position: relative; border-left: 6px solid <?= htmlspecialchars($module['color']) ?>;">
                                <div class="card-body text-center">
                                    <div style="font-size: 2rem;"><?= htmlspecialchars($module['icon']) ?></div>
                                    <h5 class="card-title mb-1"><?= htmlspecialchars($name) ?></h5>
                                    <p class="text-muted mb-0"><?= $count ?> item<?= $count != 1 ? 's' : '' ?></p>
                                </div>
                                <ul class="list-group list-group-flush action-menu" style="
                                    display: none;
                                    position: absolute;
                                    top: 0; left: 0;
                                    width: 100%;
                                    background: rgba(255,255,255,0.95);
                                    border-radius: .5rem;
                                    z-index: 10;
                                    padding: .5rem 0;">
                                    <?php foreach ($module['actions'] as $a): ?>
                                        <li class="list-group-item text-center">
                                            <a href="<?= htmlspecialchars($a['link']) ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($a['label']) ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>

        <?php include '../includes/footer.php'; ?>
    </div>
</div>

<?php include '../includes/script.php'; ?>

<script>
document.querySelectorAll('.module-card').forEach(card => {
    card.addEventListener('mouseenter', () => {
        card.querySelector('.action-menu').style.display = 'block';
    });
    card.addEventListener('mouseleave', () => {
        card.querySelector('.action-menu').style.display = 'none';
    });
});
</script>

</body>
</html>
