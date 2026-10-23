<?php
/**
 * GaunGaliHat Location & Nearby Artisan Dispatch Engine
 */

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$lat = isset($_POST['lat']) ? floatval($_POST['lat']) : (isset($_GET['lat']) ? floatval($_GET['lat']) : 0.0);
$lng = isset($_POST['lng']) ? floatval($_POST['lng']) : (isset($_GET['lng']) ? floatval($_GET['lng']) : 0.0);
$pincode = clean_input($_POST['pincode'] ?? ($_GET['pincode'] ?? ''));

// Known Rural Artisan Cluster Reference Points (Lat, Lng)
$clusters = [
    'Hardoi' => ['lat' => 27.3989, 'lng' => 80.1310, 'pincode' => '241001', 'state' => 'Uttar Pradesh'],
    'Sandila' => ['lat' => 27.0754, 'lng' => 80.5057, 'pincode' => '241204', 'state' => 'Uttar Pradesh'],
    'Madhoganj' => ['lat' => 27.1264, 'lng' => 80.1444, 'pincode' => '241126', 'state' => 'Uttar Pradesh'],
    'Mallawan' => ['lat' => 27.0428, 'lng' => 80.1554, 'pincode' => '241303', 'state' => 'Uttar Pradesh'],
    'Lucknow' => ['lat' => 26.8467, 'lng' => 80.9462, 'pincode' => '226001', 'state' => 'Uttar Pradesh'],
    'Sitapur' => ['lat' => 27.5746, 'lng' => 80.6789, 'pincode' => '261001', 'state' => 'Uttar Pradesh'],
    'Varanasi' => ['lat' => 25.3176, 'lng' => 82.9739, 'pincode' => '221001', 'state' => 'Uttar Pradesh'],
    'Delhi' => ['lat' => 28.6139, 'lng' => 77.2090, 'pincode' => '110001', 'state' => 'Delhi'],
    'Kanpur' => ['lat' => 26.4499, 'lng' => 80.3319, 'pincode' => '208001', 'state' => 'Uttar Pradesh'],
];

function haversine_distance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371; // km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return round($earth_radius * $c, 1);
}

// 1. If GPS coordinates received
$detected_city = 'Hardoi';
$detected_pincode = '241126';
$detected_state = 'Uttar Pradesh';

if ($lat != 0.0 && $lng != 0.0) {
    // Find closest cluster
    $min_dist = 99999;
    $closest_cluster = 'Hardoi';
    foreach ($clusters as $cname => $coords) {
        $dist = haversine_distance($lat, $lng, $coords['lat'], $coords['lng']);
        if ($dist < $min_dist) {
            $min_dist = $dist;
            $closest_cluster = $cname;
        }
    }

    $detected_city = $closest_cluster;
    $detected_pincode = $clusters[$closest_cluster]['pincode'];
    $detected_state = $clusters[$closest_cluster]['state'];
    $_SESSION['user_lat'] = $lat;
    $_SESSION['user_lng'] = $lng;
} elseif (!empty($pincode)) {
    $clean_pin = preg_replace('/\D/', '', $pincode);
    if (strlen($clean_pin) === 6) {
        $detected_pincode = $clean_pin;
        $prefix = substr($clean_pin, 0, 3);
        if ($prefix === '241') {
            $detected_city = 'Hardoi District';
            $detected_state = 'Uttar Pradesh';
        } elseif ($prefix === '226') {
            $detected_city = 'Lucknow';
            $detected_state = 'Uttar Pradesh';
        } elseif ($prefix === '221') {
            $detected_city = 'Varanasi';
            $detected_state = 'Uttar Pradesh';
        } elseif ($prefix === '208') {
            $detected_city = 'Kanpur';
            $detected_state = 'Uttar Pradesh';
        } elseif ($prefix === '110') {
            $detected_city = 'Delhi NCR';
            $detected_state = 'Delhi';
        } else {
            $detected_city = 'India Postal Region';
            $detected_state = 'India';
        }
    }
}

// Store in session
$_SESSION['delivery_pincode'] = $detected_pincode;
$_SESSION['delivery_city'] = $detected_city;
$_SESSION['delivery_state'] = $detected_state;
$_SESSION['delivery_location_text'] = "$detected_city, $detected_state - $detected_pincode";
$_SESSION['delivery_location_detected'] = 1;

// Fetch vendors and calculate distance
$vendors_stmt = $pdo->query("SELECT * FROM vendors WHERE verified = 1");
$vendors = $vendors_stmt->fetchAll();

$nearby_vendors = [];
$reference_lat = $lat ?: 27.1264;
$reference_lng = $lng ?: 80.1444;

foreach ($vendors as $v) {
    // Coordinate estimation based on village/district
    $v_lat = 27.1264;
    $v_lng = 80.1444;
    if ($v['district'] === 'Hardoi') {
        if (strpos($v['name'], 'Sandila') !== false) {
            $v_lat = 27.0754; $v_lng = 80.5057;
        } elseif (strpos($v['village'], 'Mallawan') !== false) {
            $v_lat = 27.0428; $v_lng = 80.1554;
        } elseif (strpos($v['village'], 'Baghauli') !== false) {
            $v_lat = 27.2800; $v_lng = 80.2000;
        } else {
            $v_lat = 27.1400; $v_lng = 80.1600;
        }
    } elseif ($v['district'] === 'Varanasi') {
        $v_lat = 25.3176; $v_lng = 82.9739;
    } elseif ($v['district'] === 'Ramanagara') {
        $v_lat = 12.7209; $v_lng = 77.2799;
    }

    $dist = haversine_distance($reference_lat, $reference_lng, $v_lat, $v_lng);
    $v['distance_km'] = $dist;
    $v['dispatch_eta'] = ($dist <= 50) ? 'Local Express: 24 to 48 hrs' : 'India Post: 2 to 4 days';
    $nearby_vendors[] = $v;
}

// Sort by closest distance
usort($nearby_vendors, function($a, $b) {
    return $a['distance_km'] <=> $b['distance_km'];
});

echo json_encode([
    'success' => true,
    'location_text' => $_SESSION['delivery_location_text'],
    'pincode' => $detected_pincode,
    'city' => $detected_city,
    'state' => $detected_state,
    'nearby_count' => count($nearby_vendors),
    'nearest_vendor' => $nearby_vendors[0]['name'] ?? '',
    'nearest_distance' => ($nearby_vendors[0]['distance_km'] ?? 0) . ' km'
]);
exit;
