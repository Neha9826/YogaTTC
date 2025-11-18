<?php
include __DIR__.'/db.php';

$keyword = $conn->real_escape_string($_GET['keyword'] ?? '');

$sql = "
SELECT 
  p.id, p.title, p.description, p.price_per_person, p.nights,
  r.title AS retreat_title, r.location,
  o.name AS org_name, o.country,
  (SELECT image FROM package_images WHERE package_id = p.id LIMIT 1) AS main_image
FROM yoga_packages p
JOIN yoga_retreats r ON p.retreat_id = r.id
JOIN organizations o ON r.organization_id = o.id
WHERE p.title LIKE '%$keyword%' 
   OR r.title LIKE '%$keyword%' 
   OR o.name LIKE '%$keyword%' 
   OR r.location LIKE '%$keyword%'
ORDER BY p.created_at DESC
";
$res = $conn->query($sql);

if ($res->num_rows > 0) {
  while($pkg = $res->fetch_assoc()) {
    $img = $pkg['main_image'] ? "uploads/packages/".$pkg['main_image'] : "images/default-package.jpg";
    echo '
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <img src="'.$img.'" class="card-img-top" style="height:220px;object-fit:cover;">
        <div class="card-body">
          <h5 class="card-title">'.htmlspecialchars($pkg['title']).'</h5>
          <p class="text-muted small mb-2">'.htmlspecialchars($pkg['retreat_title']).' — '.htmlspecialchars($pkg['country']).'</p>
          <p class="fw-bold text-danger mb-1">₹'.number_format($pkg['price_per_person'], 2).' / person</p>
          <a href="packageDetails.php?id='.$pkg['id'].'" class="btn btn-outline-primary w-100">View Details</a>
        </div>
      </div>
    </div>';
  }
} else {
  echo '<div class="text-center text-muted py-5">No packages found.</div>';
}
?>
