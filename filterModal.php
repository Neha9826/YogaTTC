<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="filterModalLabel">Filters</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            
            <div class="filter-sidebar" id="filter-sidebar-modal">
                <form id="filterFormModal" action="list.php" method="GET">
                    <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">

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
                    <div class="accordion accordion-flush" id="destinationAccordionModal">
                        <?php if (empty($all_locations_hierarchical)): ?>
                            <small class="text-muted p-2">No destinations found.</small>
                        <?php endif; ?>

                        <?php foreach ($all_locations_hierarchical as $continent => $countries): ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-cont-modal-<?= md5($continent) ?>">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-cont-modal-<?= md5($continent) ?>">
                                        <input class="form-check-input me-2" type="checkbox" name="continent[]" value="<?= htmlspecialchars($continent) ?>" id="cb_cont_modal_<?= md5($continent) ?>" <?= in_array($continent, $filter_continents) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="cb_cont_modal_<?= md5($continent) ?>"><?= htmlspecialchars($continent) ?></label>
                                    </button>
                                </h2>
                                <div id="collapse-cont-modal-<?= md5($continent) ?>" class="accordion-collapse collapse" data-bs-parent="#destinationAccordionModal">
                                    <div class="accordion-body">
                                        <?php if (empty($countries)): ?>
                                            <small class="text-muted">No countries listed.</small>
                                        <?php endif; ?>
                                        <?php foreach ($countries as $country => $states): ?>
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading-country-modal-<?= md5($country) ?>">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-country-modal-<?= md5($country) ?>">
                                                        <input class="form-check-input me-2" type="checkbox" name="country[]" value="<?= htmlspecialchars($country) ?>" id="cb_country_modal_<?= md5($country) ?>" <?= in_array($country, $filter_countries) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="cb_country_modal_<?= md5($country) ?>"><?= htmlspecialchars($country) ?></label>
                                                    </button>
                                                </h2>
                                                <div id="collapse-country-modal-<?= md5($country) ?>" class="accordion-collapse collapse" data-bs-parent="#collapse-cont-modal-<?= md5($continent) ?>">
                                                    <div class="accordion-body">
                                                        <?php foreach ($states as $state => $cities): ?>
                                                            <?php if(empty($state)) continue; ?>
                                                            <div class="accordion-item">
                                                                <h2 class="accordion-header" id="heading-state-modal-<?= md5($state) ?>">
                                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-state-modal-<?= md5($state) ?>">
                                                                        <input class="form-check-input me-2" type="checkbox" name="state[]" value="<?= htmlspecialchars($state) ?>" id="cb_state_modal_<?= md5($state) ?>" <?= in_array($state, $filter_states) ? 'checked' : '' ?>>
                                                                        <label class="form-check-label" for="cb_state_modal_<?= md5($state) ?>"><?= htmlspecialchars($state) ?></label>
                                                                    </button>
                                                                </h2>
                                                                <div id="collapse-state-modal-<?= md5($state) ?>" class="accordion-collapse collapse" data-bs-parent="#collapse-country-modal-<?= md5($country) ?>">
                                                                    <div class="accordion-body">
                                                                        <?php foreach ($cities as $city => $val): ?>
                                                                            <?php if(empty($city)) continue; ?>
                                                                            <div class="form-check">
                                                                                <input class="form-check-input" type="checkbox" name="city[]" value="<?= htmlspecialchars($city) ?>" id="cb_city_modal_<?= md5($city) ?>" <?= in_array($city, $filter_cities) ? 'checked' : '' ?>>
                                                                                <label class="form-check-label" for="cb_city_modal_<?= md5($city) ?>"><?= htmlspecialchars($city) ?></label>
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
                                                                echo '<input class="form-check-input" type="checkbox" name="city[]" value="'.htmlspecialchars($city).'" id="cb_city_modal_'.md5($city).'" '.(in_array($city, $filter_cities) ? 'checked' : '').'>';
                                                                echo '<label class="form-check-label" for="cb_city_modal_'.md5($city).'">'.htmlspecialchars($city).'</label>';
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
                            <input class='form-check-input' type='checkbox' name='private[]' value='private' id='cb_private_modal' <?= in_array('private', $filter_private) ? 'checked' : '' ?>>
                            <label class='form-check-label' for='cb_private_modal'>Private Room</label>
                        </div>
                        <div class='form-check'>
                            <input class='form-check-input' type='checkbox' name='private[]' value='group' id='cb_group_modal' <?= in_array('group', $filter_private) ? 'checked' : '' ?>>
                            <label class='form-check-label' for='cb_group_modal'>Group (Shared)</label>
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
            </div>
        <div class="modal-footer">
            <a href="list.php" class="btn btn-clear-filters btn-sm">Clear All</a>
            <button type="button" class="btn btn-apply-filters btn-sm w-50" onclick="document.getElementById('filterFormModal').submit();">Apply Filters</button>
        </div>
    </div>
    </div>
</div>