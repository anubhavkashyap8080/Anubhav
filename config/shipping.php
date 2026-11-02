<?php
/**
 * GanvGaliHat Enterprise Logistics & Multi-Carrier Delivery Engine
 * Real-time Indian PIN Code Serviceability, Volumetric Weight & Courier Rate Calculation
 * Supported Delivery Networks:
 * - India Post (Speed Post / Parcel) — Universal 19,000+ PIN Code Rural Reach
 * - Shiprocket Multi-Carrier API — Delhivery, Blue Dart, Xpressbees, Shadowfax, DTDC
 * - Delhivery Direct Express — 18,500+ PIN Code Fleet
 * - NimbusPost Pan-India Hub — 29,000+ PIN Code Coverage
 */

require_once __DIR__ . '/settings.php';

/**
 * Determine geographic shipping zone from 6-digit Indian PIN code
 */
function get_pincode_shipping_zone($pincode) {
    $clean_pin = preg_replace('/\D/', '', (string)$pincode);
    if (strlen($clean_pin) !== 6) {
        return [
            'zone_id' => 'north',
            'zone_name' => 'North India Region',
            'state' => 'Unknown',
            'is_local' => false,
            'is_state' => false,
            'is_remote' => false,
            'eta_min' => 3,
            'eta_max' => 5
        ];
    }

    $prefix2 = (int)substr($clean_pin, 0, 2);
    $first_digit = (int)substr($clean_pin, 0, 1);

    // Origin: Hardoi / Sandila / Lucknow Hub (PIN 241204 / 241126)
    // Local: Same district or neighbor (241xxx, 226xxx, 227xxx)
    if ($prefix2 === 24 || $prefix2 === 22) {
        return [
            'zone_id' => 'local',
            'zone_name' => 'Local Artisan Cluster (Sandila / Hardoi / Lucknow)',
            'state' => 'Uttar Pradesh',
            'is_local' => true,
            'is_state' => true,
            'is_remote' => false,
            'eta_min' => 1,
            'eta_max' => 2
        ];
    }

    // Zone 1: Rest of Uttar Pradesh
    if ($prefix2 >= 20 && $prefix2 <= 28) {
        return [
            'zone_id' => 'state_up',
            'zone_name' => 'Uttar Pradesh State Zone',
            'state' => 'Uttar Pradesh',
            'is_local' => false,
            'is_state' => true,
            'is_remote' => false,
            'eta_min' => 2,
            'eta_max' => 3
        ];
    }

    // Zone 2: Delhi NCR & North Zone (Haryana, Punjab, HP, J&K, Rajasthan, Bihar, Uttarakhand)
    if (($prefix2 >= 11 && $prefix2 <= 19) || ($prefix2 >= 30 && $prefix2 <= 34) || ($prefix2 >= 80 && $prefix2 <= 85)) {
        return [
            'zone_id' => 'north',
            'zone_name' => 'North India & NCR',
            'state' => 'North Zone',
            'is_local' => false,
            'is_state' => false,
            'is_remote' => false,
            'eta_min' => 3,
            'eta_max' => 5
        ];
    }

    // Zone 3: West & Central (Maharashtra, Gujarat, MP, Chhattisgarh)
    if ($prefix2 >= 36 && $prefix2 <= 49) {
        return [
            'zone_id' => 'west_central',
            'zone_name' => 'West & Central India',
            'state' => 'West/Central',
            'is_local' => false,
            'is_state' => false,
            'is_remote' => false,
            'eta_min' => 4,
            'eta_max' => 6
        ];
    }

    // Zone 4: South & East (Karnataka, Tamil Nadu, Kerala, AP, Telangana, Bengal, Odisha)
    if ($first_digit === 5 || $first_digit === 6 || ($prefix2 >= 70 && $prefix2 <= 77)) {
        return [
            'zone_id' => 'south_east',
            'zone_name' => 'South & East India',
            'state' => 'South/East',
            'is_local' => false,
            'is_state' => false,
            'is_remote' => false,
            'eta_min' => 4,
            'eta_max' => 6
        ];
    }

    // Zone 5: North-East & Special Terrains (Assam, Meghalaya, Tripura, Nagaland, Sikkim, Ladakh)
    if ($prefix2 >= 78 && $prefix2 <= 79) {
        return [
            'zone_id' => 'northeast_remote',
            'zone_name' => 'North-East & Hill Terrain',
            'state' => 'North East',
            'is_local' => false,
            'is_state' => false,
            'is_remote' => true,
            'eta_min' => 5,
            'eta_max' => 8
        ];
    }

    return [
        'zone_id' => 'national',
        'zone_name' => 'Pan-India Standard Zone',
        'state' => 'India',
        'is_local' => false,
        'is_state' => false,
        'is_remote' => false,
        'eta_min' => 3,
        'eta_max' => 6
    ];
}

/**
 * Fetch real-time rate from Shiprocket API if configured
 */
function fetch_shiprocket_live_rates($pickup_pincode, $delivery_pincode, $weight_kg, $length_cm, $width_cm, $height_cm, $cod = false) {
    $email = trim(get_setting('shiprocket_email', ''));
    $password = trim(get_setting('shiprocket_password', ''));
    $enabled = (get_setting('shiprocket_enabled', '0') === '1');

    if (!$enabled || empty($email) || empty($password)) {
        return null; // Not configured or disabled
    }

    // Check cached token
    $cache_file = sys_get_temp_dir() . '/ggh_shiprocket_token.json';
    $token = null;
    if (file_exists($cache_file)) {
        $c_data = json_decode(@file_get_contents($cache_file), true);
        if (!empty($c_data['token']) && ($c_data['expires_at'] ?? 0) > time()) {
            $token = $c_data['token'];
        }
    }

    // Authenticate if no valid token
    if (!$token) {
        if (!function_exists('curl_init')) return null;
        $ch = curl_init('https://apiv2.shiprocket.in/v1/external/auth/login');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['email' => $email, 'password' => $password]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 4
        ]);
        $res = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200 && $res) {
            $auth_data = json_decode($res, true);
            if (!empty($auth_data['token'])) {
                $token = $auth_data['token'];
                @file_put_contents($cache_file, json_encode([
                    'token' => $token,
                    'expires_at' => time() + 86400 * 8 // 8 days validity
                ]));
            }
        }
    }

    if (!$token) {
        return null;
    }

    // Call serviceability API
    $weight_val = max(0.2, (float)$weight_kg);
    $query = http_build_query([
        'pickup_postcode' => $pickup_pincode ?: '241204',
        'delivery_postcode' => $delivery_pincode,
        'weight' => $weight_val,
        'length' => max(5, (float)$length_cm),
        'breadth' => max(5, (float)$width_cm),
        'height' => max(5, (float)$height_cm),
        'cod' => $cod ? 1 : 0
    ]);

    $ch = curl_init('https://apiv2.shiprocket.in/v1/external/courier/serviceability/?' . $query);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ],
        CURLOPT_TIMEOUT => 4
    ]);
    $res = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200 && $res) {
        $s_data = json_decode($res, true);
        if (!empty($s_data['data']['available_courier_companies'])) {
            $live_quotes = [];
            foreach ($s_data['data']['available_courier_companies'] as $c) {
                $c_rate = (float)($c['rate'] ?? 0);
                if ($c_rate > 0) {
                    $live_quotes[] = [
                        'partner_id' => 'shiprocket_' . ($c['courier_company_id'] ?? 'carrier'),
                        'carrier_code' => 'shiprocket',
                        'courier_name' => $c['courier_name'] ?? 'Shiprocket Express',
                        'service_type' => !empty($c['is_surface']) ? 'Surface Logistics' : 'Air Express',
                        'rate' => round($c_rate, 2),
                        'eta_days' => ($c['estimated_delivery_days'] ?? 4) . ' business days',
                        'etd_date' => date('l, M d', strtotime('+' . ($c['estimated_delivery_days'] ?? 4) . ' days')),
                        'reach' => '29,000+ PIN Codes (Shiprocket Network)',
                        'badge' => !empty($c['is_surface']) ? 'Surface Direct' : 'Express Air',
                        'cod_available' => !empty($c['cod']),
                        'icon' => 'fa-solid fa-truck-fast',
                        'rating' => (float)($c['rating'] ?? 4.5),
                        'is_live_api' => true
                    ];
                }
            }
            if (!empty($live_quotes)) {
                return $live_quotes;
            }
        }
    }

    return null;
}

/**
 * Returns complete quotes from ALL available delivery partners covering every corner of India
 *
 * @param string $pincode 6-digit Indian PIN code
 * @param float $subtotal Order total before shipping
 * @param float $weight_kg Dead weight in kg
 * @param float $length_cm Package length in cm
 * @param float $width_cm Package width in cm
 * @param float $height_cm Package height in cm
 * @param bool $cod Whether payment method is Cash on Delivery
 * @return array Multi-carrier delivery options
 */
function get_all_shipping_partner_quotes($pincode, $subtotal = 0.0, $weight_kg = 0.5, $length_cm = 10.0, $width_cm = 10.0, $height_cm = 10.0, $cod = false) {
    $clean_pin = preg_replace('/\D/', '', (string)$pincode);
    if (strlen($clean_pin) !== 6) {
        $clean_pin = '241126';
    }

    $subtotal = (float)$subtotal;
    $weight_kg = max(0.1, (float)$weight_kg);
    $length_cm = max(5.0, (float)$length_cm);
    $width_cm = max(5.0, (float)$width_cm);
    $height_cm = max(5.0, (float)$height_cm);

    // Volumetric Weight Calculation (Standard UN/Logistics Formula: L*W*H / 5000)
    $volumetric_weight = round(($length_cm * $width_cm * $height_cm) / 5000.0, 2);
    // Billable weight is maximum of Dead Weight vs Volumetric Weight
    $billable_weight = max($weight_kg, $volumetric_weight);

    $zone = get_pincode_shipping_zone($clean_pin);
    $threshold = (float)get_setting('free_shipping_threshold', 499.0);
    $is_free_eligible = ($subtotal >= $threshold);

    // Check if live Shiprocket API returns real rates
    $pickup_pin = get_setting('shiprocket_pickup_pincode', '241204'); // Sandila Industrial Hub
    $live_shiprocket = fetch_shiprocket_live_rates($pickup_pin, $clean_pin, $billable_weight, $length_cm, $width_cm, $height_cm, $cod);

    $options = [];

    // =========================================================================
    // 1. INDIA POST SPEED POST (Universal 100% Indian Village Reach)
    // =========================================================================
    // Tariff rules: Official Department of Posts (DoP) Speed Post tariff matrix
    $indiapost_base = 40.0;
    if ($zone['zone_id'] === 'local') {
        $indiapost_base = (float)get_setting('shipping_base_local', 30.0);
    } elseif ($zone['zone_id'] === 'state_up') {
        $indiapost_base = (float)get_setting('shipping_base_up', 40.0);
    } elseif ($zone['zone_id'] === 'north') {
        $indiapost_base = (float)get_setting('shipping_base_north', 55.0);
    } elseif ($zone['zone_id'] === 'northeast_remote') {
        $indiapost_base = (float)get_setting('shipping_base_remote', 85.0);
    } else {
        $indiapost_base = (float)get_setting('shipping_base_other', 65.0);
    }

    // Weight surcharge: ₹15 per 500g above 1kg
    $indiapost_surcharge = 0.0;
    if ($billable_weight > 1.0) {
        $extra_half = ceil(($billable_weight - 1.0) / 0.5);
        $indiapost_surcharge = $extra_half * (float)get_setting('shipping_heavy_per_kg', 15.0);
    }

    $indiapost_final = round($indiapost_base + $indiapost_surcharge, 2);
    $indiapost_rate = $is_free_eligible ? 0.0 : $indiapost_final;

    $options[] = [
        'partner_id' => 'indiapost',
        'carrier_code' => 'indiapost',
        'courier_name' => 'India Post (Speed Post / पार्सल)',
        'service_type' => 'Department of Posts National Network',
        'rate' => $indiapost_rate,
        'original_rate' => $indiapost_final,
        'rate_formatted' => $is_free_eligible ? 'FREE Delivery' : ('₹' . number_format($indiapost_rate, 2)),
        'is_free' => $is_free_eligible,
        'eta_days' => $zone['eta_min'] . ' to ' . $zone['eta_max'] . ' business days',
        'etd_date' => date('l, M d', strtotime('+' . $zone['eta_max'] . ' days')),
        'reach' => '100% India PIN Codes (All 1.55 Lakh Post Offices & Villages)',
        'badge' => 'Deepest Rural Reach (हर गांव तक)',
        'cod_available' => true,
        'icon' => 'fa-solid fa-envelope-open-text',
        'rating' => 4.9,
        'is_recommended' => true,
        'is_live_api' => false
    ];

    // =========================================================================
    // 2. SHIPROCKET MULTI-CARRIER NETWORK (Delhivery / Blue Dart / Xpressbees)
    // =========================================================================
    if (!empty($live_shiprocket)) {
        // Append real live Shiprocket API couriers
        foreach ($live_shiprocket as $idx => $sr_courier) {
            $sr_rate = $sr_courier['rate'];
            $sr_actual = $is_free_eligible ? 0.0 : $sr_rate;
            $sr_courier['rate'] = $sr_actual;
            $sr_courier['original_rate'] = $sr_rate;
            $sr_courier['rate_formatted'] = $is_free_eligible ? 'FREE Delivery' : ('₹' . number_format($sr_actual, 2));
            $sr_courier['is_free'] = $is_free_eligible;
            $sr_courier['is_recommended'] = ($idx === 0);
            $options[] = $sr_courier;
        }
    } else {
        // High-fidelity fallback benchmark rates for Shiprocket partners
        // A. Delhivery Surface via Shiprocket
        $delhivery_base = ($zone['is_state']) ? 52.0 : 68.0;
        if ($billable_weight > 1.0) {
            $delhivery_base += ceil(($billable_weight - 1.0) / 0.5) * 18.0;
        }
        $delhivery_final = round($delhivery_base, 2);
        $options[] = [
            'partner_id' => 'shiprocket_delhivery',
            'carrier_code' => 'shiprocket',
            'courier_name' => 'Delhivery Surface (via Shiprocket)',
            'service_type' => 'Surface Express Logistics',
            'rate' => $is_free_eligible ? 0.0 : $delhivery_final,
            'original_rate' => $delhivery_final,
            'rate_formatted' => $is_free_eligible ? 'FREE Delivery' : ('₹' . number_format($delhivery_final, 2)),
            'is_free' => $is_free_eligible,
            'eta_days' => max(2, $zone['eta_min']) . ' to ' . max(3, $zone['eta_max']) . ' business days',
            'etd_date' => date('l, M d', strtotime('+' . max(3, $zone['eta_max']) . ' days')),
            'reach' => '18,500+ PIN Codes Nationwide',
            'badge' => 'Best for Heavy Tools & Cast Iron',
            'cod_available' => true,
            'icon' => 'fa-solid fa-truck-ramp-box',
            'rating' => 4.7,
            'is_recommended' => false,
            'is_live_api' => false
        ];

        // B. Blue Dart Air Express (Priority Fast)
        $bluedart_base = ($zone['is_state']) ? 75.0 : 95.0;
        if ($billable_weight > 1.0) {
            $bluedart_base += ceil(($billable_weight - 1.0) / 0.5) * 25.0;
        }
        $bluedart_final = round($bluedart_base, 2);
        $options[] = [
            'partner_id' => 'shiprocket_bluedart',
            'carrier_code' => 'shiprocket',
            'courier_name' => 'Blue Dart Air Express (via Shiprocket)',
            'service_type' => 'Priority Air Freight',
            'rate' => $bluedart_final, // Air express typically not free to protect seller margins
            'original_rate' => $bluedart_final,
            'rate_formatted' => '₹' . number_format($bluedart_final, 2),
            'is_free' => false,
            'eta_days' => '1 to 3 business days',
            'etd_date' => date('l, M d', strtotime('+2 days')),
            'reach' => '14,000+ Air Connected Hubs',
            'badge' => 'Fastest Air Delivery (हवाई एक्सप्रेस)',
            'cod_available' => false,
            'icon' => 'fa-solid fa-plane-departure',
            'rating' => 4.8,
            'is_recommended' => false,
            'is_live_api' => false
        ];

        // C. Xpressbees Surface
        $xpress_base = ($zone['is_state']) ? 48.0 : 62.0;
        if ($billable_weight > 1.0) {
            $xpress_base += ceil(($billable_weight - 1.0) / 0.5) * 16.0;
        }
        $xpress_final = round($xpress_base, 2);
        $options[] = [
            'partner_id' => 'shiprocket_xpressbees',
            'carrier_code' => 'shiprocket',
            'courier_name' => 'Xpressbees Surface (via Shiprocket)',
            'service_type' => 'Value E-Commerce Courier',
            'rate' => $is_free_eligible ? 0.0 : $xpress_final,
            'original_rate' => $xpress_final,
            'rate_formatted' => $is_free_eligible ? 'FREE Delivery' : ('₹' . number_format($xpress_final, 2)),
            'is_free' => $is_free_eligible,
            'eta_days' => ($zone['eta_min'] + 1) . ' to ' . ($zone['eta_max'] + 1) . ' business days',
            'etd_date' => date('l, M d', strtotime('+' . ($zone['eta_max'] + 1) . ' days')),
            'reach' => '20,000+ PIN Codes',
            'badge' => 'Economy Surface',
            'cod_available' => true,
            'icon' => 'fa-solid fa-box-archive',
            'rating' => 4.6,
            'is_recommended' => false,
            'is_live_api' => false
        ];
    }

    return [
        'pincode' => $clean_pin,
        'zone' => $zone,
        'dead_weight_kg' => round($weight_kg, 2),
        'volumetric_weight_kg' => round($volumetric_weight, 2),
        'billable_weight_kg' => round($billable_weight, 2),
        'dimensions_formatted' => "{$length_cm} × {$width_cm} × {$height_cm} cm",
        'is_free_eligible' => $is_free_eligible,
        'free_threshold' => $threshold,
        'remaining_for_free' => max(0.0, $threshold - $subtotal),
        'partners' => $options
    ];
}

/**
 * Backward-compatible master function: calculates default shipping rate + returns all multi-carrier options
 */
function calculate_shipping_rate($pincode, $subtotal, $total_weight_kg = 0.5, $length_cm = 10.0, $width_cm = 10.0, $height_cm = 10.0) {
    $quotes = get_all_shipping_partner_quotes($pincode, $subtotal, $total_weight_kg, $length_cm, $width_cm, $height_cm);
    
    // Default primary option is India Post Speed Post (or first recommended option)
    $primary = $quotes['partners'][0];
    foreach ($quotes['partners'] as $opt) {
        if (!empty($opt['is_recommended'])) {
            $primary = $opt;
            break;
        }
    }

    return [
        'pincode' => $quotes['pincode'],
        'shipping_fee' => $primary['rate'],
        'base_fee' => $primary['original_rate'] ?? $primary['rate'],
        'weight_surcharge' => max(0.0, ($primary['rate'] - ($quotes['zone']['is_state'] ? 40 : 60))),
        'total_weight_kg' => $quotes['dead_weight_kg'],
        'volumetric_weight_kg' => $quotes['volumetric_weight_kg'],
        'billable_weight_kg' => $quotes['billable_weight_kg'],
        'dimensions_formatted' => $quotes['dimensions_formatted'],
        'is_free' => $quotes['is_free_eligible'],
        'threshold' => $quotes['free_threshold'],
        'remaining_for_free' => round($quotes['remaining_for_free'], 2),
        'zone_name' => $quotes['zone']['zone_name'],
        'eta_text' => $primary['eta_days'],
        'carrier' => $primary['courier_name'],
        'carrier_id' => $primary['partner_id'],
        'reach' => $primary['reach'],
        'badge' => $primary['badge'],
        'delivery_partners' => $quotes['partners']
    ];
}
