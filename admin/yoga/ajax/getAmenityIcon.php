<?php
include __DIR__ . '/../../db.php';

/**
 * Yoga Retreat Amenity Icon Mapping
 * Focused on nature, mindfulness, health, and retreat lifestyle.
 */
$map = [
    // 🌿 Yoga & Wellness
    "yoga" => "bi-person-standing",
    "meditation" => "bi-person-arms-up",
    "mindfulness" => "bi-flower3",
    "wellness" => "bi-heart-pulse",
    "therapy" => "bi-heart",
    "massage" => "bi-droplet-half",
    "healing" => "bi-sun",
    "detox" => "bi-droplet",
    "sound bath" => "bi-soundwave",
    "reiki" => "bi-lightning-charge",

    // 🏞️ Nature & Location
    "mountain" => "bi-cloud-drizzle",
    "forest" => "bi-tree",
    "waterfall" => "bi-water",
    "nature" => "bi-flower1",
    "garden" => "bi-flower3",
    "sunrise" => "bi-sunrise",
    "sunset" => "bi-sunset",
    "trek" => "bi-hiking",
    "hike" => "bi-signpost-split",
    "outdoor" => "bi-cloud-sun",

    // 🏡 Accommodation & Stay
    "room" => "bi-house-door",
    "balcony" => "bi-house",
    "hot water" => "bi-droplet-half",
    "fireplace" => "bi-fire",
    "bed" => "bi-bed",
    "toiletries" => "bi-droplet",
    "bathroom" => "bi-bucket",
    "fan" => "bi-wind",
    "heater" => "bi-thermometer-half",

    // ☕ Dining & Food
    "food" => "bi-egg-fried",
    "drink" => "bi-cup-straw",
    "organic" => "bi-cup-hot",
    "vegan" => "bi-leaf",
    "vegetarian" => "bi-leaf",
    "organic" => "bi-leaf",
    "meal" => "bi-cup-hot",
    "breakfast" => "bi-cup-hot",
    "restaurant" => "bi-cup-straw",
    "juice" => "bi-droplet-half",
    "tea" => "bi-cup-hot",
    "coffee" => "bi-cup-hot",

    // 🧘‍♀️ Facilities & Common Areas
    "studio" => "bi-building",
    "hall" => "bi-bank",
    "deck" => "bi-view-list",
    "open area" => "bi-tree",
    "garden area" => "bi-flower2",
    "seating" => "bi-chair",
    "library" => "bi-book",
    "music" => "bi-music-note-beamed",
    "art" => "bi-brush",

    // 🌞 Outdoor & Activities
    "campfire" => "bi-fire",
    "bonfire" => "bi-fire",
    "cycling" => "bi-bicycle",
    "walk" => "bi-person-walking",
    "explore" => "bi-signpost",
    "nature walk" => "bi-tree",
    "bird watching" => "bi-binoculars",
    "waterfall visit" => "bi-water",
    "excursion" => "bi-map",

    // 💬 Misc / Essentials
    "wifi" => "bi-wifi",
    "parking" => "bi-car-front",
    "laundry" => "bi-basket",
    "cleaning" => "bi-broom",
    "security" => "bi-shield-lock",
    "first aid" => "bi-bandage",
    "pets" => "bi-paw"
];

$name = strtolower(trim($_POST['name'] ?? ''));
$icon = "bi-question-circle"; // default if not matched

foreach ($map as $keyword => $class) {
    if (strpos($name, $keyword) !== false) {
        $icon = $class;
        break;
    }
}

echo json_encode(['icon' => $icon]);
