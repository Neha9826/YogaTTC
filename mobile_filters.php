<form id="mobileFilterForm" method="get" action="<?= htmlspecialchars(basename($_SERVER['PHP_SELF'])) ?>">
  <div class="mb-3">
    <label class="form-label">Location</label>
    <?php
    $locs = $conn->query("SELECT DISTINCT country FROM organizations WHERE country<>'' ORDER BY country");
    if ($locs && $locs->num_rows > 0):
      while ($l = $locs->fetch_assoc()):
        $val = $l['country'];
        $checked = in_array($val, $locations) ? 'checked' : '';
    ?>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="location[]" id="mloc_<?= md5($val) ?>" value="<?= htmlspecialchars($val) ?>" <?= $checked ?>>
        <label class="form-check-label small" for="mloc_<?= md5($val) ?>"><?= htmlspecialchars($val) ?></label>
      </div>
    <?php endwhile; endif; ?>
  </div>

  <div class="mb-3">
    <label class="form-label">Duration (nights)</label>
    <?php
    $dres = $conn->query("SELECT DISTINCT nights FROM yoga_packages WHERE nights > 0 ORDER BY nights ASC");
    if ($dres && $dres->num_rows > 0):
      while ($d = $dres->fetch_assoc()):
        $dv = (int)$d['nights'];
        $checked = in_array((string)$dv, $durations) ? 'checked' : '';
    ?>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="duration[]" id="mdur_<?= $dv ?>" value="<?= $dv ?>" <?= $checked ?>>
        <label class="form-check-label small" for="mdur_<?= $dv ?>"><?= $dv ?> nights</label>
      </div>
    <?php endwhile; endif; ?>
  </div>

  <div class="mb-3">
    <label class="form-label">Price Range</label>
    <div class="d-flex gap-2">
      <input type="number" name="price_min" class="form-control" placeholder="Min" value="<?= $price_min ?: '' ?>">
      <input type="number" name="price_max" class="form-control" placeholder="Max" value="<?= $price_max ?: '' ?>">
    </div>
  </div>

  <div class="d-grid">
    <button class="btn btn-primary" type="submit">Apply</button>
    <button type="button" class="btn btn-secondary mt-2" data-bs-dismiss="modal">Close</button>
  </div>
</form>
