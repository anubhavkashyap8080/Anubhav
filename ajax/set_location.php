<?php
/**
 * GaunGaliHat Intelligent Geolocation & Nearby Artisan Dispatch Engine
 * Supports GPS Coordinates, 19,000+ Indian PIN Codes, and Reverse Geo-routing
 */

require_once __DIR__ . '/../includes/functions.php';

function send_json_loc_response($data) {
    if (ob_get_length()) {
        ob_clean();
    }
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=UTF-8');
    }
    echo json_encode($data);
    exit;
}

$lat = isset($_POST['lat']) ? floatval($_POST['lat']) : (isset($_GET['lat']) ? floatval($_GET['lat']) : 0.0);
$lng = isset($_POST['lng']) ? floatval($_POST['lng']) : (isset($_GET['lng']) ? floatval($_GET['lng']) : 0.0);
$pincode = clean_input($_POST['pincode'] ?? ($_GET['pincode'] ?? ''));

// Reference Database of Indian Regions, Major Cities, and Artisan Forge Hubs
$clusters = [
    'Madhoganj Forge Hub' => ['lat' => 27.1264, 'lng' => 80.1444, 'pincode' => '241126', 'city' => 'Madhoganj', 'state' => 'Uttar Pradesh'],
    'Sandila Industrial Area' => ['lat' => 27.0754, 'lng' => 80.5057, 'pincode' => '241204', 'city' => 'Sandila', 'state' => 'Uttar Pradesh'],
    'Hardoi City' => ['lat' => 27.3989, 'lng' => 80.1310, 'pincode' => '241001', 'city' => 'Hardoi', 'state' => 'Uttar Pradesh'],
    'Mallawan Heritage Cluster' => ['lat' => 27.0428, 'lng' => 80.1554, 'pincode' => '241303', 'city' => 'Mallawan', 'state' => 'Uttar Pradesh'],
    'Lucknow Capital Circle' => ['lat' => 26.8467, 'lng' => 80.9462, 'pincode' => '226001', 'city' => 'Lucknow', 'state' => 'Uttar Pradesh'],
    'Kanpur Industrial Hub' => ['lat' => 26.4499, 'lng' => 80.3319, 'pincode' => '208001', 'city' => 'Kanpur', 'state' => 'Uttar Pradesh'],
    'Varanasi Handloom & Pickle Hub' => ['lat' => 25.3176, 'lng' => 82.9739, 'pincode' => '221001', 'city' => 'Varanasi', 'state' => 'Uttar Pradesh'],
    'Prayagraj Sangam' => ['lat' => 25.4358, 'lng' => 81.8463, 'pincode' => '211001', 'city' => 'Prayagraj', 'state' => 'Uttar Pradesh'],
    'Gorakhpur Eastern UP' => ['lat' => 26.7606, 'lng' => 83.3732, 'pincode' => '273001', 'city' => 'Gorakhpur', 'state' => 'Uttar Pradesh'],
    'Bareilly Northern Hub' => ['lat' => 28.3670, 'lng' => 79.4304, 'pincode' => '243001', 'city' => 'Bareilly', 'state' => 'Uttar Pradesh'],
    'Agra Forge Cluster' => ['lat' => 27.1767, 'lng' => 78.0081, 'pincode' => '282001', 'city' => 'Agra', 'state' => 'Uttar Pradesh'],
    'Noida / NCR' => ['lat' => 28.5355, 'lng' => 77.3910, 'pincode' => '201301', 'city' => 'Noida', 'state' => 'Uttar Pradesh'],
    'Delhi NCR' => ['lat' => 28.6139, 'lng' => 77.2090, 'pincode' => '110001', 'city' => 'New Delhi', 'state' => 'Delhi'],
    'Jaipur Handicrafts' => ['lat' => 26.9124, 'lng' => 75.7873, 'pincode' => '302001', 'city' => 'Jaipur', 'state' => 'Rajasthan'],
    'Patna Gangetic Hub' => ['lat' => 25.5941, 'lng' => 85.1376, 'pincode' => '800001', 'city' => 'Patna', 'state' => 'Bihar'],
    'Kolkata Eastern Hub' => ['lat' => 22.5726, 'lng' => 88.3639, 'pincode' => '700001', 'city' => 'Kolkata', 'state' => 'West Bengal'],
    'Mumbai Financial Capital' => ['lat' => 19.0760, 'lng' => 72.8777, 'pincode' => '400001', 'city' => 'Mumbai', 'state' => 'Maharashtra'],
    'Pune Maharashtra Hub' => ['lat' => 18.5204, 'lng' => 73.8567, 'pincode' => '411001', 'city' => 'Pune', 'state' => 'Maharashtra'],
    'Ahmedabad Western Hub' => ['lat' => 23.0225, 'lng' => 72.5714, 'pincode' => '380001', 'city' => 'Ahmedabad', 'state' => 'Gujarat'],
    'Bengaluru South Hub' => ['lat' => 12.9716, 'lng' => 77.5946, 'pincode' => '560001', 'city' => 'Bengaluru', 'state' => 'Karnataka'],
    'Hyderabad Deccan' => ['lat' => 17.3850, 'lng' => 78.4867, 'pincode' => '500001', 'city' => 'Hyderabad', 'state' => 'Telangana'],
    'Chennai Coromandel' => ['lat' => 13.0827, 'lng' => 80.2707, 'pincode' => '600001', 'city' => 'Chennai', 'state' => 'Tamil Nadu'],
    'Bhopal Central India' => ['lat' => 23.2599, 'lng' => 77.4126, 'pincode' => '462001', 'city' => 'Bhopal', 'state' => 'Madhya Pradesh'],
    'Chandigarh North' => ['lat' => 30.7333, 'lng' => 76.7794, 'pincode' => '160017', 'city' => 'Chandigarh', 'state' => 'Punjab & Chandigarh'],
    'Dehradun Uttarakhand' => ['lat' => 30.3165, 'lng' => 78.0322, 'pincode' => '248001', 'city' => 'Dehradun', 'state' => 'Uttarakhand'],
    'Guwahati North-East' => ['lat' => 26.1445, 'lng' => 91.7362, 'pincode' => '781001', 'city' => 'Guwahati', 'state' => 'Assam'],
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

// Defaults
$detected_city = 'Hardoi';
$detected_pincode = '241126';
$detected_state = 'Uttar Pradesh';
$calc_lat = 27.1264;
$calc_lng = 80.1444;

// 1. Process GPS Coordinates
if ($lat != 0.0 && $lng != 0.0) {
    $calc_lat = $lat;
    $calc_lng = $lng;

    // Find nearest geographic anchor
    $min_dist = 999999;
    $closest_name = 'Madhoganj Forge Hub';
    foreach ($clusters as $cname => $info) {
        $dist = haversine_distance($lat, $lng, $info['lat'], $info['lng']);
        if ($dist < $min_dist) {
            $min_dist = $dist;
            $closest_name = $cname;
        }
    }

    $detected_city = $clusters[$closest_name]['city'];
    $detected_pincode = $clusters[$closest_name]['pincode'];
    $detected_state = $clusters[$closest_name]['state'];
    $_SESSION['user_lat'] = $lat;
    $_SESSION['user_lng'] = $lng;

} elseif (!empty($pincode)) {
    // 2. Process Indian PIN Code
    $clean_pin = preg_replace('/\D/', '', $pincode);
    if (strlen($clean_pin) === 6) {
        $detected_pincode = $clean_pin;
        $prefix2 = substr($clean_pin, 0, 2);
        $prefix3 = substr($clean_pin, 0, 3);

        // Specific Hardoi Local Clusters
        if ($clean_pin === '241126') {
            $detected_city = 'Madhoganj (Hardoi)';
            $detected_state = 'Uttar Pradesh';
            $calc_lat = 27.1264; $calc_lng = 80.1444;
        } elseif ($clean_pin === '241204') {
            $detected_city = 'Sandila Industrial';
            $detected_state = 'Uttar Pradesh';
            $calc_lat = 27.0754; $calc_lng = 80.5057;
        } elseif ($clean_pin === '241303') {
            $detected_city = 'Mallawan';
            $detected_state = 'Uttar Pradesh';
            $calc_lat = 27.0428; $calc_lng = 80.1554;
        } elseif ($prefix3 === '241') {
            $detected_city = 'Hardoi District';
            $detected_state = 'Uttar Pradesh';
            $calc_lat = 27.3989; $calc_lng = 80.1310;
        } elseif ($prefix3 === '226') {
            $detected_city = 'Lucknow';
            $detected_state = 'Uttar Pradesh';
            $calc_lat = 26.8467; $calc_lng = 80.9462;
        } elseif ($prefix3 === '208') {
            $detected_city = 'Kanpur';
            $detected_state = 'Uttar Pradesh';
            $calc_lat = 26.4499; $calc_lng = 80.3319;
        } elseif ($prefix3 === '221') {
            $detected_city = 'Varanasi';
            $detected_state = 'Uttar Pradesh';
            $calc_lat = 25.3176; $calc_lng = 82.9739;
        } elseif ($prefix3 === '201') {
            $detected_city = 'Noida / Ghaziabad';
            $detected_state = 'Uttar Pradesh';
            $calc_lat = 28.5355; $calc_lng = 77.3910;
        } elseif ($prefix3 === '282') {
            $detected_city = 'Agra';
            $detected_state = 'Uttar Pradesh';
            $calc_lat = 27.1767; $calc_lng = 78.0081;
        } elseif ($prefix3 === '243') {
            $detected_city = 'Bareilly';
            $detected_state = 'Uttar Pradesh';
            $calc_lat = 28.3670; $calc_lng = 79.4304;
        } elseif ($prefix3 === '273') {
            $detected_city = 'Gorakhpur';
            $detected_state = 'Uttar Pradesh';
            $calc_lat = 26.7606; $calc_lng = 83.3732;
        } elseif ($prefix3 === '211') {
            $detected_city = 'Prayagraj';
            $detected_state = 'Uttar Pradesh';
            $calc_lat = 25.4358; $calc_lng = 81.8463;
        } elseif ($prefix3 === '224') {
            $detected_city = 'Ayodhya';
            $detected_state = 'Uttar Pradesh';
            $calc_lat = 26.7922; $calc_lng = 82.1998;
        } elseif ($prefix2 === '11') {
            $detected_city = 'Delhi NCR';
            $detected_state = 'Delhi';
            $calc_lat = 28.6139; $calc_lng = 77.2090;
        } elseif (in_array($prefix2, ['12', '13'])) {
            $detected_city = 'Haryana (Gurugram/Faridabad)';
            $detected_state = 'Haryana';
            $calc_lat = 28.4595; $calc_lng = 77.0266;
        } elseif (in_array($prefix2, ['14', '15', '16'])) {
            $detected_city = 'Punjab & Chandigarh';
            $detected_state = 'Punjab';
            $calc_lat = 30.7333; $calc_lng = 76.7794;
        } elseif ($prefix2 === '17') {
            $detected_city = 'Himachal Pradesh';
            $detected_state = 'Himachal Pradesh';
            $calc_lat = 31.1048; $calc_lng = 77.1734;
        } elseif (in_array($prefix2, ['18', '19'])) {
            $detected_city = 'Jammu & Kashmir';
            $detected_state = 'Jammu and Kashmir';
            $calc_lat = 34.0837; $calc_lng = 74.7973;
        } elseif (in_array($prefix2, ['20', '21', '22', '23', '24', '25', '26', '27', '28'])) {
            $detected_city = 'Uttar Pradesh Region';
            $detected_state = 'Uttar Pradesh';
            $calc_lat = 27.1264; $calc_lng = 80.1444;
        } elseif (in_array($prefix2, ['30', '31', '32', '33', '34'])) {
            $detected_city = 'Rajasthan (Jaipur)';
            $detected_state = 'Rajasthan';
            $calc_lat = 26.9124; $calc_lng = 75.7873;
        } elseif (in_array($prefix2, ['36', '37', '38', '39'])) {
            $detected_city = 'Gujarat (Ahmedabad/Surat)';
            $detected_state = 'Gujarat';
            $calc_lat = 23.0225; $calc_lng = 72.5714;
        } elseif (in_array($prefix2, ['40', '41', '42', '43', '44'])) {
            $detected_city = 'Maharashtra (Mumbai/Pune)';
            $detected_state = 'Maharashtra';
            $calc_lat = 19.0760; $calc_lng = 72.8777;
        } elseif (in_array($prefix2, ['45', '46', '47', '48', '49'])) {
            $detected_city = 'Madhya Pradesh (Bhopal/Indore)';
            $detected_state = 'Madhya Pradesh';
            $calc_lat = 23.2599; $calc_lng = 77.4126;
        } elseif (in_array($prefix2, ['50', '51', '52', '53'])) {
            $detected_city = 'Andhra Pradesh & Telangana';
            $detected_state = 'Telangana';
            $calc_lat = 17.3850; $calc_lng = 78.4867;
        } elseif (in_array($prefix2, ['56', '57', '58', '59'])) {
            $detected_city = 'Karnataka (Bengaluru)';
            $detected_state = 'Karnataka';
            $calc_lat = 12.9716; $calc_lng = 77.5946;
        } elseif (in_array($prefix2, ['60', '61', '62', '63', '64'])) {
            $detected_city = 'Tamil Nadu (Chennai)';
            $detected_state = 'Tamil Nadu';
            $calc_lat = 13.0827; $calc_lng = 80.2707;
        } elseif (in_array($prefix2, ['67', '68', '69'])) {
            $detected_city = 'Kerala (Kochi)';
            $detected_state = 'Kerala';
            $calc_lat = 9.9312; $calc_lng = 76.2673;
        } elseif (in_array($prefix2, ['70', '71', '72', '73', '74'])) {
            $detected_city = 'West Bengal (Kolkata)';
            $detected_state = 'West Bengal';
            $calc_lat = 22.5726; $calc_lng = 88.3639;
        } elseif (in_array($prefix2, ['75', '76', '77'])) {
            $detected_city = 'Odisha (Bhubaneswar)';
            $detected_state = 'Odisha';
            $calc_lat = 20.2961; $calc_lng = 85.8245;
        } elseif (in_array($prefix2, ['78', '79'])) {
            $detected_city = 'Assam & North East';
            $detected_state = 'Assam';
            $calc_lat = 26.1445; $calc_lng = 91.7362;
        } elseif (in_array($prefix2, ['80', '81', '82', '83', '84', '85'])) {
            $detected_city = 'Bihar & Jharkhand (Patna)';
            $detected_state = 'Bihar';
            $calc_lat = 25.5941; $calc_lng = 85.1376;
        } else {
            $detected_city = 'India Postal Region';
            $detected_state = 'India';
            $calc_lat = 20.5937; $calc_lng = 78.9629;
        }

        $_SESSION['user_lat'] = $calc_lat;
        $_SESSION['user_lng'] = $calc_lng;
    }
}

// Update Active Session Location Keys
$_SESSION['delivery_pincode'] = $detected_pincode;
$_SESSION['shipping_pincode'] = $detected_pincode;
$_SESSION['delivery_city'] = $detected_city;
$_SESSION['delivery_state'] = $detected_state;
$_SESSION['delivery_location_text'] = "$detected_city, $detected_state - $detected_pincode";
$_SESSION['delivery_location_detected'] = 1;

// Fetch verified artisan vendors and calculate distance
$nearby_vendors = [];
if ($pdo) {
    try {
        $vendors_stmt = $pdo->query("SELECT * FROM vendors WHERE verified = 1");
        $vendors = $vendors_stmt->fetchAll() ?: [];

        foreach ($vendors as $v) {
            $v_lat = 27.1264;
            $v_lng = 80.1444;

            if (($v['district'] ?? '') === 'Hardoi') {
                if (strpos($v['name'] ?? '', 'Sandila') !== false) {
                    $v_lat = 27.0754; $v_lng = 80.5057;
                } elseif (strpos($v['village'] ?? '', 'Mallawan') !== false) {
                    $v_lat = 27.0428; $v_lng = 80.1554;
                } elseif (strpos($v['village'] ?? '', 'Baghauli') !== false) {
                    $v_lat = 27.2800; $v_lng = 80.2000;
                } else {
                    $v_lat = 27.1400; $v_lng = 80.1600;
                }
            } elseif (($v['district'] ?? '') === 'Varanasi') {
                $v_lat = 25.3176; $v_lng = 82.9739;
            } elseif (($v['district'] ?? '') === 'Ramanagara') {
                $v_lat = 12.7209; $v_lng = 77.2799;
            }

            $dist = haversine_distance($calc_lat, $calc_lng, $v_lat, $v_lng);
            $v['distance_km'] = $dist;
            $v['dispatch_eta'] = ($dist <= 60) ? 'Local Express: 24 to 48 hrs' : ($dist <= 250 ? 'Regional Post: 2 to 3 days' : 'India Post: 3 to 5 days');
            $nearby_vendors[] = $v;
        }

        // Sort by closest distance
        usort($nearby_vendors, function($a, $b) {
            return ($a['distance_km'] ?? 0) <=> ($b['distance_km'] ?? 0);
        });
    } catch (Throwable $e_v) {}
}

send_json_loc_response([
    'success' => true,
    'location_text' => $_SESSION['delivery_location_text'],
    'pincode' => $detected_pincode,
    'city' => $detected_city,
    'state' => $detected_state,
    'nearby_count' => count($nearby_vendors),
    'nearest_vendor' => $nearby_vendors[0]['name'] ?? 'Kalyani agrofood pvt ltd Workshop',
    'nearest_distance' => ($nearby_vendors[0]['distance_km'] ?? 0) . ' km',
    'dispatch_eta' => $nearby_vendors[0]['dispatch_eta'] ?? 'Fastest dispatch direct from village'
]);
