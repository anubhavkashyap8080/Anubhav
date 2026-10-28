<?php
/**
 * GaunGaliHat Platform Settings & Gateway Configurations
 */

require_once __DIR__ . '/db.php';

// Helper to normalize any mojibake characters safely
if (!function_exists('fix_utf8_mojibake')) {
    function fix_utf8_mojibake($str) {
        if (!is_string($str) || empty($str)) return $str;
        $replacements = [
            'â‚¹' => '₹',
            'ðŸŒ¾' => '🌾',
            'ðŸš€' => '🚀',
            'ðŸ“ ' => '📍',
            'â€¢' => '•',
            'â€”' => '—',
            'â€“' => '–',
            'â€¦' => '…',
            'â„¢' => '™',
            'Â®' => '®',
            'Â©' => '©',
            'â˜…' => '★'
        ];
        return str_replace(array_keys($replacements), array_values($replacements), $str);
    }
}

// Fetch settings from database
$site_settings = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
    while ($row = $stmt->fetch()) {
        $site_settings[$row['setting_key']] = fix_utf8_mojibake($row['setting_value']);
    }
} catch (Exception $e) {
    // defaults if not yet seeded
}

function get_setting($key, $default = '') {
    global $site_settings;
    $val = isset($site_settings[$key]) ? $site_settings[$key] : $default;
    return fix_utf8_mojibake($val);
}

// Global Platform Constants
$raw_brand_name = trim((string)get_setting('brand_name', 'GaunGaliHat'));
if (empty($raw_brand_name) || $raw_brand_name === '0' || preg_match('/^0\b/', $raw_brand_name)) {
    $raw_brand_name = 'GaunGaliHat';
}
define('APP_NAME', $raw_brand_name);

$raw_company_name = trim((string)get_setting('company_name', 'S S Industries'));
if (empty($raw_company_name) || $raw_company_name === '0') {
    $raw_company_name = 'S S Industries';
}
define('APP_COMPANY', $raw_company_name);

$raw_tagline = trim((string)get_setting('brand_tagline', 'S S Industries Enterprise • Ganv se ghar tak'));
if (empty($raw_tagline) || $raw_tagline === '0') {
    $raw_tagline = 'S S Industries Enterprise • Ganv se ghar tak';
}
define('APP_TAGLINE', $raw_tagline);
define('APP_PHONE', get_setting('contact_phone', '+91 7897492100'));
define('APP_EMAIL', get_setting('contact_email', 'namaste@ganvgalihat.com'));
define('APP_CURRENCY', '₹');

// Payment Gateway Constants
define('RAZORPAY_ENABLED', (bool)get_setting('razorpay_enabled', 1));
define('RAZORPAY_KEY_ID', get_setting('razorpay_key_id', 'rzp_test_1DP5mmOlF5G5ag'));
define('RAZORPAY_KEY_SECRET', get_setting('razorpay_key_secret', 's9F2xKlY02jK3991lmxO92aa'));

define('PAYU_ENABLED', (bool)get_setting('payu_enabled', 1));
define('PAYU_MERCHANT_KEY', get_setting('payu_merchant_key', 'gtKFFx'));
define('PAYU_MERCHANT_SALT', get_setting('payu_merchant_salt', 'eCwWELxi'));
define('PAYU_MODE', get_setting('payu_mode', 'test')); // 'test' or 'live'
define('PAYU_URL', PAYU_MODE === 'live' ? 'https://secure.payu.in/_payment' : 'https://test.payu.in/_payment');

define('COD_ENABLED', (bool)get_setting('cod_enabled', 1));

// Shipping Constants
define('FREE_SHIPPING_THRESHOLD', (float)get_setting('free_shipping_threshold', 499));
