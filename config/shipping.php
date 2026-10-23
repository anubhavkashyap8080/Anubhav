<?php
/**
 * GaunGaliHat Automatic Shipping Price & Delivery ETA Calculator
 * Accurate Indian Pincode & Weight-based dispatch fee engine
 */

require_once __DIR__ . '/settings.php';

/**
 * Calculate accurate delivery charges based on destination pincode, order subtotal, and total weight.
 *
 * @param string $pincode 6-digit Indian PIN code
 * @param float $subtotal Cart subtotal in INR
 * @param float $total_weight_kg Total parcel weight in kilograms (default 0.5kg)
 * @return array Calculated details [shipping_fee, is_free, zone_name, eta_text, carrier, remaining_for_free]
 */
function calculate_shipping_rate($pincode, $subtotal, $total_weight_kg = 0.5) {
    $clean_pin = preg_replace('/\D/', '', $pincode);
    $subtotal = (float)$subtotal;
    $total_weight_kg = max(0.5, (float)$total_weight_kg);
    
    // Default fallback zone if pincode is empty or invalid
    $zone = 'Zone 2 (North India)';
    $base_fee = 60.0;
    $eta_days = '3 to 5 business days';

    if (strlen($clean_pin) === 6) {
        $prefix2 = (int)substr($clean_pin, 0, 2);
        $first_digit = (int)substr($clean_pin, 0, 1);

        // Zone 1: Uttar Pradesh (Hardoi Origin / Local)
        if ($prefix2 >= 20 && $prefix2 <= 28) {
            $zone = 'Uttar Pradesh (Local Artisan Cluster Zone)';
            $base_fee = (float)get_setting('shipping_base_up', 40);
            $eta_days = '2 to 3 business days';
        }
        // Zone 2: North India (Delhi, Haryana, Punjab, Himachal, J&K, Rajasthan, Bihar)
        elseif (($prefix2 >= 11 && $prefix2 <= 19) || ($prefix2 >= 30 && $prefix2 <= 34) || ($prefix2 >= 80 && $prefix2 <= 85)) {
            $zone = 'North India Region';
            $base_fee = (float)get_setting('shipping_base_north', 60);
            $eta_days = '3 to 5 business days';
        }
        // Zone 3: West & Central India (Maharashtra, Gujarat, MP, Chhattisgarh)
        elseif (($prefix2 >= 36 && $prefix2 <= 49)) {
            $zone = 'West & Central India';
            $base_fee = 70.0;
            $eta_days = '4 to 6 business days';
        }
        // Zone 4: South & East India (Karnataka, TN, Kerala, AP, Telangana, Bengal, Odisha)
        elseif ($first_digit === 5 || $first_digit === 6 || ($prefix2 >= 70 && $prefix2 <= 77)) {
            $zone = 'South & East India';
            $base_fee = (float)get_setting('shipping_base_other', 80);
            $eta_days = '5 to 7 business days';
        }
        // Zone 5: North-East & Special Regions (Assam, Meghalaya, Tripura, etc.)
        elseif ($prefix2 >= 78 && $prefix2 <= 79) {
            $zone = 'North-East & Special Terrain';
            $base_fee = 100.0;
            $eta_days = '6 to 8 business days';
        }
    }

    // Heavy Parcel Surcharge: Extra ₹15 per 0.5kg above 2kg
    $weight_surcharge = 0.0;
    if ($total_weight_kg > 2.0) {
        $extra_half_kgs = ceil(($total_weight_kg - 2.0) / 0.5);
        $weight_surcharge = $extra_half_kgs * (float)get_setting('shipping_heavy_per_kg', 15);
    }

    $threshold = (float)get_setting('free_shipping_threshold', 499);
    $is_free = ($subtotal >= $threshold);
    $remaining_for_free = max(0, $threshold - $subtotal);

    $final_shipping_fee = $is_free ? 0.0 : round($base_fee + $weight_surcharge, 2);

    return [
        'pincode' => $clean_pin,
        'shipping_fee' => $final_shipping_fee,
        'base_fee' => $base_fee,
        'weight_surcharge' => $weight_surcharge,
        'total_weight_kg' => round($total_weight_kg, 2),
        'is_free' => $is_free,
        'threshold' => $threshold,
        'remaining_for_free' => round($remaining_for_free, 2),
        'zone_name' => $zone,
        'eta_text' => $eta_days,
        'carrier' => 'India Post Registered Express'
    ];
}
