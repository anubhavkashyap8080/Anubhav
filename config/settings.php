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
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
        while ($row = $stmt->fetch()) {
            $site_settings[$row['setting_key']] = fix_utf8_mojibake($row['setting_value']);
        }
    } catch (Exception $e) {
        // defaults if not yet seeded
    }
}

function get_setting($key, $default = '') {
    global $site_settings;
    $val = isset($site_settings[$key]) ? $site_settings[$key] : $default;
    return fix_utf8_mojibake($val);
}

// Item 26: Canonical Platform URL Definition (no trailing slash)
$cfg_app = function_exists('get_platform_config') ? get_platform_config('app') : [];
$resolved_site_url = rtrim((string)(getenv('SITE_URL') ?: ($cfg_app['site_url'] ?? 'https://gaungalihat.womenvission.in')), '/');
if (!defined('SITE_URL')) {
    define('SITE_URL', $resolved_site_url);
}

// Item 26: Enforce Canonical Domain Redirect
if (PHP_SAPI !== 'cli' && !empty($_SERVER['HTTP_HOST'])) {
    $canonical_parts = parse_url(SITE_URL);
    $canonical_host = strtolower($canonical_parts['host'] ?? '');
    $req_host = strtolower(explode(':', $_SERVER['HTTP_HOST'])[0]);

    if (!empty($canonical_host) && !empty($req_host) && $req_host !== $canonical_host && !in_array($req_host, ['localhost', '127.0.0.1'], true)) {
        $canonical_scheme = $canonical_parts['scheme'] ?? 'https';
        $canonical_port = (!empty($canonical_parts['port']) && !in_array($canonical_parts['port'], [80, 443])) ? ':' . $canonical_parts['port'] : '';
        $redirect_target = $canonical_scheme . '://' . $canonical_host . $canonical_port . ($_SERVER['REQUEST_URI'] ?? '/');
        header('Location: ' . $redirect_target, true, 301);
        exit;
    }
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
$canonical_host_fallback = parse_url(SITE_URL, PHP_URL_HOST) ?: 'gaungalihat.womenvission.in';
define('APP_PHONE', get_setting('contact_phone', '+91 7897492100'));
define('APP_EMAIL', get_setting('contact_email', 'namaste@' . $canonical_host_fallback));
define('APP_CURRENCY', '₹');

// Payment Gateway Constants (Config/Env takes priority over site_settings DB table, Item 10)
$cfg_payments = function_exists('get_platform_config') ? get_platform_config('payments') : [];

define('RAZORPAY_ENABLED', (bool)(getenv('RAZORPAY_ENABLED') !== false ? getenv('RAZORPAY_ENABLED') : ($cfg_payments['razorpay']['enabled'] ?? get_setting('razorpay_enabled', 0))));
define('RAZORPAY_KEY_ID', (string)(getenv('RAZORPAY_KEY_ID') ?: ($cfg_payments['razorpay']['key_id'] ?? get_setting('razorpay_key_id', ''))));
$raw_rp_secret = getenv('RAZORPAY_KEY_SECRET') ?: ($cfg_payments['razorpay']['key_secret'] ?? '');
define('RAZORPAY_KEY_SECRET', (string)(!empty($raw_rp_secret) ? $raw_rp_secret : get_setting('razorpay_key_secret', '')));

define('PAYU_ENABLED', (bool)(getenv('PAYU_ENABLED') !== false ? getenv('PAYU_ENABLED') : ($cfg_payments['payu']['enabled'] ?? get_setting('payu_enabled', 0))));
define('PAYU_MERCHANT_KEY', (string)(getenv('PAYU_MERCHANT_KEY') ?: ($cfg_payments['payu']['merchant_key'] ?? get_setting('payu_merchant_key', ''))));
$raw_payu_salt = getenv('PAYU_MERCHANT_SALT') ?: ($cfg_payments['payu']['merchant_salt'] ?? '');
define('PAYU_MERCHANT_SALT', (string)(!empty($raw_payu_salt) ? $raw_payu_salt : get_setting('payu_merchant_salt', '')));
define('PAYU_MODE', (string)(getenv('PAYU_MODE') ?: ($cfg_payments['payu']['mode'] ?? get_setting('payu_mode', 'test')))); // 'test' or 'live'
define('PAYU_URL', PAYU_MODE === 'live' ? 'https://secure.payu.in/_payment' : 'https://test.payu.in/_payment');

// Social Auth Constants (Item 10: config/env priority)
$cfg_social = function_exists('get_platform_config') ? get_platform_config('social') : [];
define('GOOGLE_AUTH_ENABLED', (bool)(getenv('GOOGLE_AUTH_ENABLED') !== false ? getenv('GOOGLE_AUTH_ENABLED') : ($cfg_social['google']['enabled'] ?? get_setting('google_auth_enabled', 1))));
define('GOOGLE_CLIENT_ID', (string)(getenv('GOOGLE_CLIENT_ID') ?: ($cfg_social['google']['client_id'] ?? get_setting('google_client_id', ''))));
$raw_google_secret = getenv('GOOGLE_CLIENT_SECRET') ?: ($cfg_social['google']['client_secret'] ?? '');
define('GOOGLE_CLIENT_SECRET', (string)(!empty($raw_google_secret) ? $raw_google_secret : get_setting('google_client_secret', '')));

define('COD_ENABLED', (bool)get_setting('cod_enabled', 1));

// Shipping Constants
define('FREE_SHIPPING_THRESHOLD', (float)get_setting('free_shipping_threshold', 499));
