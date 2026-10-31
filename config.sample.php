<?php
/**
 * GaunGaliHat Enterprise Platform Configuration Sample
 * 
 * Instructions:
 * 1. Copy this file to a location OUTSIDE the web root (recommended on cPanel):
 *    e.g. /home/<your-cpanel-user>/gaungalihat_config.php
 *    OR place as config/config.local.php (which is strictly blocked by .htaccess)
 * 2. Fill in your actual production database credentials and API keys.
 * 3. Never commit the actual config with real passwords to version control.
 */

return [
    // Database Credentials
    'db' => [
        'host'    => getenv('DB_HOST') ?: 'localhost',
        'port'    => (int)(getenv('DB_PORT') ?: 3306),
        'name'    => getenv('DB_NAME') ?: 'YOUR_DB_NAME_HERE',
        'user'    => getenv('DB_USER') ?: 'YOUR_DB_USER_HERE',
        'pass'    => getenv('DB_PASS') ?: 'YOUR_DB_PASSWORD_HERE',
        'charset' => 'utf8mb4',
    ],

    // Platform Canonical Domain & Environment
    'app' => [
        'site_url' => getenv('SITE_URL') ?: 'https://ganvgalihat.com', // No trailing slash
        'env'      => getenv('APP_ENV') ?: 'production', // 'production' or 'development'
        'debug'    => false, // Keep false in production to prevent displaying raw errors
    ],

    // Payment Gateway API Credentials
    'payments' => [
        'razorpay' => [
            'enabled'    => (bool)(getenv('RAZORPAY_ENABLED') ?: false),
            'key_id'     => getenv('RAZORPAY_KEY_ID') ?: '',
            'key_secret' => getenv('RAZORPAY_KEY_SECRET') ?: '',
        ],
        'payu' => [
            'enabled'       => (bool)(getenv('PAYU_ENABLED') ?: false),
            'merchant_key'  => getenv('PAYU_MERCHANT_KEY') ?: '',
            'merchant_salt' => getenv('PAYU_MERCHANT_SALT') ?: '',
            'mode'          => getenv('PAYU_MODE') ?: 'test', // 'test' or 'live'
        ],
        'cod' => [
            'enabled' => true,
        ],
    ],

    // Google Identity & OAuth 2.0 Credentials
    'google' => [
        'enabled'       => (bool)(getenv('GOOGLE_AUTH_ENABLED') ?: false),
        'client_id'     => getenv('GOOGLE_CLIENT_ID') ?: '',
        'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
    ],

    // Optional: Cloudflare cache purge from Admin > Cache Manager (token needs only "Zone > Cache Purge")
    'cloudflare' => [
        'zone_id'   => getenv('CF_ZONE_ID') ?: '',
        'api_token' => getenv('CF_API_TOKEN') ?: '',
    ],

    // Security Governance & Administrative Alerts
    'security' => [
        'owner_alert_email' => getenv('OWNER_ALERT_EMAIL') ?: '',
        'session_lifetime'  => 1800, // 30 minutes admin idle timeout
        'admin_require_2fa' => false,
        'setup_secret'      => getenv('SETUP_SECRET') ?: 'ChangeThisToRandomSecretKey32Chars',
    ],
];
