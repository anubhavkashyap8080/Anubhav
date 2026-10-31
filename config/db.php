<?php
/**
 * GaunGaliHat Enterprise E-Commerce Platform
 * Database Connection & Platform Configuration Layer (PDO MySQL)
 */

// Enable global output buffering to prevent "headers already sent"
if (ob_get_level() === 0) {
    ob_start();
}

// Item 13: Secure Session Cookie Parameters before session_start
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $is_https,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

/**
 * Item 4: Load Platform Configuration outside public_html or from environment variables
 */
function load_platform_config() {
    static $config = null;
    if ($config !== null) {
        return $config;
    }

    $locations = [
        getenv('GGH_CONFIG_PATH'),
        dirname(__DIR__, 2) . '/gaungalihat_config.php', // Outside web root on cPanel (/home/user/gaungalihat_config.php)
        dirname(__DIR__) . '/config/config.local.php',    // Git-ignored local config file
    ];

    foreach ($locations as $loc) {
        if (!empty($loc) && file_exists($loc) && is_readable($loc)) {
            $loaded = include $loc;
            if (is_array($loaded)) {
                $config = $loaded;
                break;
            }
        }
    }

    if (!is_array($config)) {
        $config = [];
    }

    return $config;
}

function get_platform_config($section = null, $default = null) {
    $cfg = load_platform_config();
    if ($section === null) return $cfg;
    return $cfg[$section] ?? $default;
}

$platform_cfg = load_platform_config();
$db_cfg = $platform_cfg['db'] ?? [];

// Resolve DB credentials from config or environment variables (no hardcoded credentials)
$db_host = $db_cfg['host'] ?? getenv('DB_HOST') ?: 'localhost';
$db_port = $db_cfg['port'] ?? getenv('DB_PORT') ?: '3306';
$db_name = $db_cfg['name'] ?? getenv('DB_NAME') ?: '';
$db_user = $db_cfg['user'] ?? getenv('DB_USER') ?: '';
$db_pass = $db_cfg['pass'] ?? getenv('DB_PASS') ?: '';

if (!defined('DB_HOST')) define('DB_HOST', $db_host);
if (!defined('DB_PORT')) define('DB_PORT', $db_port);
if (!defined('DB_NAME')) define('DB_NAME', $db_name);
if (!defined('DB_USER')) define('DB_USER', $db_user);
if (!defined('DB_PASS')) define('DB_PASS', $db_pass);

$pdo = null;
$db_driver = 'mysql';
$last_mysql_error = null;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_TIMEOUT            => 3,
];

// Set MySQL UTF-8 collation command if constant exists
if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
    $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci";
}

if (!empty($db_name) && !empty($db_user)) {
    try {
        $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass, $options);
        $db_driver = 'mysql';
    } catch (Throwable $e) {
        $last_mysql_error = $e->getMessage();
        error_log("[GaunGaliHat DB Error] Connection failed: " . $e->getMessage());
    }
}

if ($pdo) {
    // MySQL Connected Successfully! Ensure UTF-8 & Verify platform schema
    try {
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    } catch (Throwable $e_utf8) {}

    try {
        // Item 5: Stop auto-running schema.sql on production requests. Database provisioning is handled via migrations.
        // Auto-heal Hindi UTF-8 mojibake in products & categories if present
        try {
            $probe = $pdo->query("SELECT COUNT(*) FROM products WHERE hindi_name LIKE '%à¤%'")->fetchColumn();
            if ((int)$probe > 0) {
                $heal_p = [
                    'GGH-TL-101' => 'देसी हंसिया (मजबूत धारदार)',
                    'GGH-TL-102' => 'मजबूत लोहे की खुरपी (निराई-गुड़ाई)',
                    'GGH-TL-103' => 'लोहे का मजबूत फावड़ा / कुदाल',
                    'GGH-TL-104' => 'लोहे की भारी कुल्हाड़ी (लकड़ी कटाई)',
                    'GGH-TL-105' => 'खेत व तालाब सुरक्षा जाल (नेट)',
                    'GGH-TL-106' => 'लोहार हथौड़ा (3 किलो भारी)',
                    'GGH-PL-201' => 'देसी अनपॉलिश अरहर दाल (1 किलो)',
                    'GGH-PL-202' => 'देसी चना दाल (पत्थर चक्की दलिया)',
                    'GGH-PL-203' => 'बुंदेलखंडी देसी काला चना (1 किलो)',
                    'GGH-PL-204' => 'धुली मूंग दाल (आर्गेनिक व सुपाच्य)',
                    'GGH-PL-205' => 'देसी छिलका उड़द दाल (दाल मखनी व तड़का)',
                    'GGH-PK-301' => 'बनारसी कच्ची घानी आम का देसी अचार',
                    'GGH-PK-302' => 'अवधी बनारसी भरवां लाल मिर्च का अचार',
                    'GGH-PK-303' => 'गांव का देसी कटहल का अचार (सरसों तेल)',
                    'GGH-PK-304' => 'जंगली आंवला मुरब्बा (देसी खांड में निर्मित)',
                    'GGH-PK-305' => 'पाचक नींबू अदरक हरी मिर्च अचार (बिना तेल)',
                    'GGH-JT-401' => 'हाथ से बना जूट झोला (2 का सेट)',
                    'GGH-WM-001' => 'हाथ से बुना शुद्ध खादी कुर्ता',
                    'GGH-WM-002' => 'पारंपरिक सूती गमछा (3 का सेट)',
                    'GGH-DC-501' => 'हाथ से बुनी प्राकृतिक चटाई (फ्लोर मैट)',
                    'GGH-DC-502' => 'चन्नापटना लकड़ी के पारंपरिक खिलौने'
                ];
                $st_hp = $pdo->prepare("UPDATE products SET hindi_name = ? WHERE code = ?");
                foreach ($heal_p as $c_code => $c_hname) {
                    $st_hp->execute([$c_hname, $c_code]);
                }
                $heal_c = [
                    'farming-traditional-tools' => 'कृषि एवं खेती औजार',
                    'pulses-organic-grains' => 'दालें एवं देसी अनाज',
                    'pickles-village-murabba' => 'देसी अचार व मुरब्बा',
                    'hardware-cottage-implements' => 'लोहार एवं वर्कशॉप उपकरण',
                    'jute-bags-cloth-jholas' => 'इको जूट बैग एवं झोले',
                    'village-home-decor-toys' => 'ग्रामीण सजावट व खिलौने',
                    'handmade-apparel-textiles' => 'हथकरघा वस्त्र व खादी',
                    'rural-utility-machinery' => 'ग्रामीण उपयोगिता व मशीनरी',
                    'handmade-by-rural-women' => 'ग्रामीण महिलाओं द्वारा निर्मित'
                ];
                $st_hc = $pdo->prepare("UPDATE categories SET hindi_name = ? WHERE slug = ?");
                foreach ($heal_c as $c_slug => $c_hname) {
                    $st_hc->execute([$c_hname, $c_slug]);
                }
            }
        } catch (Throwable $e_heal) {}

        // Ensure all application tables (wishlists, coupons, roles, etc.) exist
        ensure_platform_schema($pdo);
    } catch (Throwable $e_tbl) {}
} else {
    // Item 4 Deploy Cleanup: Fail securely without SQLite fallback
    if (defined('TESTING_MOCK_DB') && TESTING_MOCK_DB === true) {
        return;
    }
    // Generic error page for visitors; no sensitive connection parameters or raw errors displayed.
    http_response_code(500);
    error_log("[GaunGaliHat DB Fatal] Database connection could not be established: " . ($last_mysql_error ?: 'No driver response'));
    die("<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>Service Unavailable — GaunGaliHat</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #fdfbf7; color: #1f2937; padding: 40px 20px; display: flex; align-items: center; justify-content: center; min-height: 80vh; margin: 0; }
    .card { max-width: 500px; width: 100%; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 32px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); text-align: center; }
    h1 { color: #b91c1c; font-size: 22px; margin-top: 0; margin-bottom: 12px; }
    p { font-size: 14px; line-height: 1.6; color: #4b5563; margin-bottom: 20px; }
    .ref { font-size: 12px; color: #9ca3af; font-family: monospace; }
  </style>
</head>
<body>
  <div class='card'>
    <h1>Service Temporarily Unavailable</h1>
    <p>We are currently experiencing a technical interruption with our database connectivity. Our engineering team has been notified. Please try again shortly.</p>
    <div class='ref'>Reference: GGH-DB-ERR-500</div>
  </div>
</body>
</html>");
}

/**
 * Ensure all platform tables (site_settings, blogs, tickets, etc.) are always present and healthy
 */
function ensure_platform_schema($pdo) {
    if (!$pdo) return;
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $is_sqlite = ($driver === 'sqlite');

    // Auto-migrate SEO columns in products table (seo_title, seo_description, seo_keywords)
    try {
        if ($is_sqlite) {
            $cols = $pdo->query("PRAGMA table_info(products)")->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array('seo_title', $cols)) {
                $pdo->exec("ALTER TABLE products ADD COLUMN seo_title TEXT DEFAULT NULL");
            }
            if (!in_array('seo_description', $cols)) {
                $pdo->exec("ALTER TABLE products ADD COLUMN seo_description TEXT DEFAULT NULL");
            }
            if (!in_array('seo_keywords', $cols)) {
                $pdo->exec("ALTER TABLE products ADD COLUMN seo_keywords TEXT DEFAULT NULL");
            }
        } else {
            $col_t = $pdo->query("SHOW COLUMNS FROM `products` LIKE 'seo_title'")->fetch();
            if (!$col_t) {
                $pdo->exec("ALTER TABLE `products` ADD COLUMN `seo_title` VARCHAR(255) DEFAULT NULL");
            }
            $col_d = $pdo->query("SHOW COLUMNS FROM `products` LIKE 'seo_description'")->fetch();
            if (!$col_d) {
                $pdo->exec("ALTER TABLE `products` ADD COLUMN `seo_description` TEXT DEFAULT NULL");
            }
            $col_k = $pdo->query("SHOW COLUMNS FROM `products` LIKE 'seo_keywords'")->fetch();
            if (!$col_k) {
                $pdo->exec("ALTER TABLE `products` ADD COLUMN `seo_keywords` VARCHAR(255) DEFAULT NULL");
            }
        }
    } catch (Throwable $e_seo_mig) {}

    // Security Hardening Schema Migration (Item 10, 11, 12, 15, 25)
    try {
        if ($is_sqlite) {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS password_resets (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER DEFAULT 0,
                    email TEXT NOT NULL,
                    token_hash TEXT NOT NULL,
                    expires_at DATETIME NOT NULL,
                    used INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
                CREATE TABLE IF NOT EXISTS login_attempts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    identifier TEXT NOT NULL,
                    ip_address TEXT NOT NULL,
                    success INTEGER DEFAULT 0,
                    attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
                CREATE TABLE IF NOT EXISTS coupon_usages (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    coupon_id INTEGER NOT NULL,
                    user_id INTEGER DEFAULT NULL,
                    order_id INTEGER DEFAULT NULL,
                    used_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
            ");
            $u_cols = $pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_COLUMN, 1);
            $mig_u = [
                'email_verified_at' => 'DATETIME DEFAULT NULL',
                'email_verification_token' => 'TEXT DEFAULT NULL',
                'email_verification_expires' => 'DATETIME DEFAULT NULL',
                'force_password_change' => 'INTEGER DEFAULT 0',
                'two_factor_secret' => 'TEXT DEFAULT NULL',
                'two_factor_enabled' => 'INTEGER DEFAULT 0',
                'failed_login_attempts' => 'INTEGER DEFAULT 0',
                'lockout_until' => 'DATETIME DEFAULT NULL'
            ];
            foreach ($mig_u as $col => $type) {
                if (!in_array($col, $u_cols)) {
                    $pdo->exec("ALTER TABLE users ADD COLUMN {$col} {$type}");
                }
            }
            $o_cols = $pdo->query("PRAGMA table_info(orders)")->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array('order_access_token', $o_cols)) {
                $pdo->exec("ALTER TABLE orders ADD COLUMN order_access_token TEXT DEFAULT NULL");
            }
            if (!in_array('gateway_order_id', $o_cols)) {
                $pdo->exec("ALTER TABLE orders ADD COLUMN gateway_order_id TEXT DEFAULT NULL");
            }
            $oi_cols = $pdo->query("PRAGMA table_info(order_items)")->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array('item_status', $oi_cols)) {
                $pdo->exec("ALTER TABLE order_items ADD COLUMN item_status TEXT DEFAULT 'Confirmed'");
            }
        } else {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `password_resets` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT DEFAULT 0,
                    `email` VARCHAR(150) NOT NULL,
                    `token_hash` VARCHAR(64) NOT NULL,
                    `expires_at` DATETIME NOT NULL,
                    `used` TINYINT(1) DEFAULT 0,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_pr_token` (`token_hash`),
                    INDEX `idx_pr_email` (`email`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS `login_attempts` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `identifier` VARCHAR(150) NOT NULL,
                    `ip_address` VARCHAR(45) NOT NULL,
                    `success` TINYINT(1) DEFAULT 0,
                    `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_la_ip` (`ip_address`, `attempted_at`),
                    INDEX `idx_la_ident` (`identifier`, `attempted_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS `coupon_usages` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `coupon_id` INT NOT NULL,
                    `user_id` INT DEFAULT NULL,
                    `order_id` INT DEFAULT NULL,
                    `used_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_cu_coupon_user` (`coupon_id`, `user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS `admin_audit_logs` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT DEFAULT NULL,
                    `user_name` VARCHAR(150) DEFAULT 'System',
                    `user_role` VARCHAR(50) DEFAULT 'guest',
                    `action` VARCHAR(100) NOT NULL,
                    `module` VARCHAR(100) NOT NULL,
                    `entity_type` VARCHAR(100) DEFAULT NULL,
                    `entity_id` VARCHAR(100) DEFAULT NULL,
                    `details` TEXT DEFAULT NULL,
                    `ip_address` VARCHAR(45) NOT NULL,
                    `user_agent` TEXT DEFAULT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_aal_user` (`user_id`),
                    INDEX `idx_aal_module` (`module`),
                    INDEX `idx_aal_action` (`action`),
                    INDEX `idx_aal_created` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS `security_logs` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `event_type` VARCHAR(100) NOT NULL,
                    `details` TEXT DEFAULT NULL,
                    `description` TEXT DEFAULT NULL,
                    `user_id` INT DEFAULT NULL,
                    `ip_address` VARCHAR(45) NOT NULL,
                    `user_agent` TEXT DEFAULT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_sl_event` (`event_type`),
                    INDEX `idx_sl_created` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS `rate_limits` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `action` VARCHAR(50) NOT NULL,
                    `identifier` VARCHAR(100) NOT NULL,
                    `hit_count` INT NOT NULL DEFAULT 1,
                    `first_hit_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `last_hit_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY `uk_action_identifier` (`action`, `identifier`),
                    INDEX `idx_rate_limit_action` (`action`, `last_hit_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS `hero_campaigns` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(150) NOT NULL,
                    `slug` VARCHAR(100) NOT NULL,
                    `campaign_type` VARCHAR(50) DEFAULT 'festival',
                    `theme_preset` VARCHAR(50) DEFAULT 'diwali',
                    `title_line1` VARCHAR(255) DEFAULT '',
                    `badge_text` VARCHAR(100) DEFAULT '',
                    `status` VARCHAR(50) DEFAULT 'active',
                    `start_datetime` DATETIME DEFAULT NULL,
                    `end_datetime` DATETIME DEFAULT NULL,
                    `priority` INT DEFAULT 0,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS `hero_campaign_versions` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `campaign_id` INT DEFAULT 1,
                    `version_num` INT NOT NULL,
                    `snapshot_data` LONGTEXT,
                    `reason` VARCHAR(255) DEFAULT NULL,
                    `created_by` VARCHAR(100) DEFAULT 'Admin',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            $check_and_add_col = function($table, $col, $def) use ($pdo) {
                try {
                    $chk = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE '{$col}'")->fetch();
                    if (!$chk) {
                        $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$col}` {$def}");
                    }
                } catch (Throwable $t) {}
            };

            $check_and_add_col('security_logs', 'details', 'TEXT DEFAULT NULL');
            $check_and_add_col('security_logs', 'description', 'TEXT DEFAULT NULL');

            $check_and_add_col('users', 'email_verified_at', 'DATETIME DEFAULT NULL');
            $check_and_add_col('users', 'email_verification_token', 'VARCHAR(100) DEFAULT NULL');
            $check_and_add_col('users', 'email_verification_expires', 'DATETIME DEFAULT NULL');
            $check_and_add_col('users', 'force_password_change', 'TINYINT(1) DEFAULT 0');
            $check_and_add_col('users', 'two_factor_secret', 'VARCHAR(100) DEFAULT NULL');
            $check_and_add_col('users', 'two_factor_enabled', 'TINYINT(1) DEFAULT 0');
            $check_and_add_col('users', 'failed_login_attempts', 'INT DEFAULT 0');
            $check_and_add_col('users', 'lockout_until', 'DATETIME DEFAULT NULL');
            $check_and_add_col('users', 'password_changed_at', 'DATETIME DEFAULT NULL');

            $check_and_add_col('orders', 'order_access_token', 'VARCHAR(64) DEFAULT NULL');
            $check_and_add_col('orders', 'gateway_order_id', 'VARCHAR(100) DEFAULT NULL');
            $check_and_add_col('order_items', 'item_status', "VARCHAR(50) DEFAULT 'Confirmed'");

            // Advanced Security Log columns (severity, user agent, request URI, details)
            $check_and_add_col('security_logs', 'details', 'TEXT DEFAULT NULL');
            $check_and_add_col('security_logs', 'severity', "VARCHAR(20) DEFAULT 'info'");
            $check_and_add_col('security_logs', 'user_agent', 'VARCHAR(255) DEFAULT NULL');
            $check_and_add_col('security_logs', 'request_uri', 'VARCHAR(500) DEFAULT NULL');
            $check_and_add_col('security_logs', 'request_method', 'VARCHAR(10) DEFAULT NULL');
            try {
                // Legacy schema had `description TEXT NOT NULL` with no default -> make it optional
                $desc_col = $pdo->query("SHOW COLUMNS FROM `security_logs` LIKE 'description'")->fetch();
                if ($desc_col && strtoupper((string)($desc_col['Null'] ?? '')) === 'NO') {
                    $pdo->exec("ALTER TABLE `security_logs` MODIFY `description` TEXT NULL");
                }
            } catch (Throwable $t) {}

            // Blocked IPs table for IP blacklist manager in Admin > Security Logs
            try {
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS `blocked_ips` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `ip_address` VARCHAR(45) NOT NULL,
                        `reason` VARCHAR(255) DEFAULT 'Suspicious activity',
                        `blocked_by` INT DEFAULT NULL,
                        `blocked_until` DATETIME DEFAULT NULL,
                        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        UNIQUE KEY `uk_ip_address` (`ip_address`),
                        INDEX `idx_blocked_until` (`blocked_until`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");
            } catch (Throwable $t) {}
        }
    } catch (Throwable $e_sec_schema) {}

    // 1. site_settings
    if ($is_sqlite) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS site_settings (
                setting_key TEXT PRIMARY KEY,
                setting_value TEXT,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    } else {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `site_settings` (
                `setting_key` VARCHAR(100) PRIMARY KEY,
                `setting_value` TEXT,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    // Auto-heal corrupted '0' brand_name, brand_tagline, or '0 —' / '0 -' / '0 __' titles and legacy company branding
    try {
        $pdo->exec("UPDATE site_settings SET setting_value = 'GaunGaliHat' WHERE setting_key = 'brand_name' AND (setting_value = '0' OR setting_value = '' OR setting_value IS NULL)");
        $pdo->exec("UPDATE site_settings SET setting_value = 'Hunar desh ka, ganv se ghar tak' WHERE setting_key = 'brand_tagline' AND (setting_value = '0' OR setting_value = '' OR setting_value IS NULL OR setting_value LIKE '%S S Industries%' OR setting_value = 'Desh ki mitti ka swad | Ganv se ghar tak')");
        $pdo->exec("UPDATE site_settings SET setting_value = 'Kalyani agrofood pvt ltd' WHERE setting_key = 'company_name' AND (setting_value = '0' OR setting_value = '' OR setting_value IS NULL OR setting_value = 'S S Industries')");
        $pdo->exec("UPDATE site_settings SET setting_value = '© 2026 GaunGaliHat. A Unit of Kalyani agrofood pvt ltd. All Rights Reserved.' WHERE setting_key = 'footer_copyright' AND setting_value LIKE '%S S Industries%'");
        $pdo->exec("UPDATE site_settings SET setting_value = '🌾 Kalyani agrofood pvt ltd • Hunar desh ka, ganv se ghar tak' WHERE setting_key = 'hero_badge_text' AND setting_value LIKE '%S S Industries%'");
        $pdo->exec("UPDATE site_settings SET setting_value = 'G145 Phase 2 Industrial Area, UPSIDA Sandila, Hardoi, UP' WHERE setting_key = 'head_office_address' AND (setting_value = '0' OR setting_value = '' OR setting_value IS NULL)");
        $pdo->exec("UPDATE site_settings SET setting_value = 'GaunGaliHat — Desh ki mitti ka swad | Rural Tools & Farm Staples' WHERE setting_key IN ('homepage_title', 'seo_meta_title') AND (setting_value = '0' OR setting_value LIKE '0 %' OR setting_value LIKE '0-%' OR setting_value LIKE '0—%' OR setting_value LIKE '0_%')");
    } catch (Throwable $e_heal0) {}

    // Default Settings
    try {
        $chk = $pdo->query("SELECT COUNT(*) FROM site_settings")->fetchColumn();
        if ($chk == 0) {
            $defaults = [
                'brand_name' => 'गाँव गली हाट (GaunGaliHat)',
                'company_name' => 'Kalyani agrofood pvt ltd',
                'brand_tagline' => 'Hunar desh ka, ganv se ghar tak',
                'announcement_text' => 'Direct from Rural Village Artisans • Free All-India Delivery on Orders ₹499+!',
                'contact_phone' => '+91 7897492100',
                'contact_email' => 'namaste@ganvgalihat.com',
                'factory_address' => 'Madhoganj Baghauli Road, Newada, Hardoi, Uttar Pradesh - 241126',
                'head_office_address' => 'G145 Phase 2 Industrial Area, UPSIDA Sandila, Hardoi, UP',
                'free_shipping_threshold' => '499',
                'shipping_base_up' => '40',
                'shipping_base_north' => '60',
                'shipping_base_other' => '85',
                'shipping_heavy_per_kg' => '25',
                'razorpay_enabled' => !empty($platform_cfg['payments']['razorpay']['enabled']) ? '1' : '0',
                'razorpay_key_id' => $platform_cfg['payments']['razorpay']['key_id'] ?? '',
                'razorpay_key_secret' => $platform_cfg['payments']['razorpay']['key_secret'] ?? '',
                'payu_enabled' => !empty($platform_cfg['payments']['payu']['enabled']) ? '1' : '0',
                'payu_merchant_key' => $platform_cfg['payments']['payu']['merchant_key'] ?? '',
                'payu_merchant_salt' => $platform_cfg['payments']['payu']['merchant_salt'] ?? '',
                'payu_mode' => $platform_cfg['payments']['payu']['mode'] ?? 'test',
                'cod_enabled' => '1',
                'commission_rate_default' => '4',
                'default_language' => 'en',
                'site_default_language' => 'en',
                // SEO Settings
                'seo_meta_title' => 'GaunGaliHat — Desh ki mitti ka swad | Rural Tools & Farm Staples',
                'seo_meta_description' => 'Direct from Indian village foundries & self-help groups. Authentic hand-forged agricultural sickles, khurpis, unpolished Awadh pulses, and Banarasi achar.',
                'seo_meta_keywords' => 'farming tools, sickle, hansiya, khurpi, unpolished dal, banarasi achar, rural artisans, Hardoi foundries, organic pulses',
                'seo_canonical_url' => 'https://ganvgalihat.com',
                'seo_google_analytics' => 'G-GGH9087TEST',
                'seo_google_console' => 'google-site-verification=ggh_rural_artisan_2026_verified',
                'seo_og_image' => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=1200',
                // Social Auth Settings
                'google_auth_enabled' => '1',
                'google_client_id' => '9876543210-gghruralartisanapps.apps.googleusercontent.com',
                'facebook_auth_enabled' => '0',
                'facebook_app_id' => '',
                // Site Customizer
                'show_announcement' => '1',
                'logo_url' => '/logo.png',
                'favicon_url' => '/favicon.png',
                'footer_copyright' => '© 2026 GaunGaliHat. A Unit of Kalyani agrofood pvt ltd. All Rights Reserved.',
                'social_instagram' => 'https://instagram.com/ganvgalihat',
                'social_youtube' => 'https://youtube.com/@ganvgalihat',
                'social_facebook' => 'https://facebook.com/ganvgalihat',
                'social_whatsapp' => 'https://wa.me/917897492100',
                // Hero Section Customizer Defaults (Festivals, Sales, Seasonal)
                'hero_festive_preset' => 'general',
                'hero_theme_color' => 'emerald',
                'hero_badge_text' => '🌾 Kalyani agrofood pvt ltd • Hunar desh ka, ganv se ghar tak',
                'hero_title_line1' => 'Gaon ke hunar,',
                'hero_title_line2' => 'ab har ghar tak',
                'hero_subtitle' => 'Discover high-durability hand-forged agricultural sickles, garden khurpis, pure unpolished Awadh pulses, Banarasi mustard oil pickles, eco golden jute bags, and handlooms direct from rural Bharat.',
                'hero_image_url' => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=800&q=80',
                'hero_btn1_text' => 'Explore Marketplace',
                'hero_btn1_link' => '/products.php',
                'hero_btn2_text' => 'Farming & Garden Tools',
                'hero_btn2_link' => '/products.php?category=farming-traditional-tools',
                'hero_stat1_num' => '100%',
                'hero_stat1_label' => 'Forged Iron & Pure Dal',
                'hero_stat2_num' => '40+',
                'hero_stat2_label' => 'Village Artisan Hubs',
                'hero_stat3_num' => '₹499+',
                'hero_stat3_label' => 'Free India Delivery',
                'hero_tag1_title' => 'High-Carbon Tempered Steel',
                'hero_tag1_subtitle' => 'Forged Desi Hansiya',
                'hero_tag2_title' => 'Handcrafted Rating',
                'hero_tag2_subtitle' => '★ 4.96 Verified'
            ];
            $ins = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
            foreach ($defaults as $k => $v) {
                $ins->execute([$k, $v]);
            }
        } else {
            // Ensure any missing hero settings are added
            $hero_defs = [
                'hero_festive_preset' => 'general',
                'hero_theme_color' => 'emerald',
                'hero_badge_text' => '🌾 Kalyani agrofood pvt ltd • Hunar desh ka, ganv se ghar tak',
                'hero_title_line1' => 'Gaon ke hunar,',
                'hero_title_line2' => 'ab har ghar tak',
                'hero_subtitle' => 'Discover high-durability hand-forged agricultural sickles, garden khurpis, pure unpolished Awadh pulses, Banarasi mustard oil pickles, eco golden jute bags, and handlooms direct from rural Bharat.',
                'hero_image_url' => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=800&q=80',
                'hero_btn1_text' => 'Explore Marketplace',
                'hero_btn1_link' => '/products.php',
                'hero_btn2_text' => 'Farming & Garden Tools',
                'hero_btn2_link' => '/products.php?category=farming-traditional-tools',
                'hero_stat1_num' => '100%',
                'hero_stat1_label' => 'Forged Iron & Pure Dal',
                'hero_stat2_num' => '40+',
                'hero_stat2_label' => 'Village Artisan Hubs',
                'hero_stat3_num' => '₹499+',
                'hero_stat3_label' => 'Free India Delivery',
                'hero_tag1_title' => 'High-Carbon Tempered Steel',
                'hero_tag1_subtitle' => 'Forged Desi Hansiya',
                'hero_tag2_title' => 'Handcrafted Rating',
                'hero_tag2_subtitle' => '★ 4.96 Verified'
            ];
            $chk_stmt = $pdo->prepare("SELECT setting_key FROM site_settings WHERE setting_key = ?");
            $ins_stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
            foreach ($hero_defs as $k => $v) {
                $chk_stmt->execute([$k]);
                if (!$chk_stmt->fetch()) {
                    $ins_stmt->execute([$k, $v]);
                }
            }
        }
    } catch (Exception $e) {}

    // 2. wishlists
    try {
        if ($is_sqlite) {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS wishlists (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    product_id INTEGER NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE(user_id, product_id)
                )
            ");
        } else {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `wishlists` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT NOT NULL,
                    `product_id` INT NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY `user_product` (`user_id`, `product_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
    } catch (Exception $e) {}

    // 3. blogs
    try {
        if ($is_sqlite) {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS blogs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    hindi_title TEXT,
                    slug TEXT UNIQUE NOT NULL,
                    excerpt TEXT,
                    content TEXT,
                    category TEXT DEFAULT 'Rural Craftsmanship',
                    author_name TEXT DEFAULT 'Anubhav Kashyap',
                    image TEXT,
                    status TEXT DEFAULT 'published',
                    views INTEGER DEFAULT 120,
                    tags TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } else {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `blogs` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `title` VARCHAR(255) NOT NULL,
                    `hindi_title` VARCHAR(255) DEFAULT NULL,
                    `slug` VARCHAR(255) UNIQUE NOT NULL,
                    `excerpt` TEXT,
                    `content` LONGTEXT,
                    `category` VARCHAR(100) DEFAULT 'Rural Craftsmanship',
                    `author_name` VARCHAR(100) DEFAULT 'Anubhav Kashyap',
                    `image` TEXT,
                    `status` ENUM('published', 'draft') DEFAULT 'published',
                    `views` INT DEFAULT 120,
                    `tags` VARCHAR(255) DEFAULT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
    } catch (Exception $e) {}

    // 4. tickets & ticket_replies
    try {
        if ($is_sqlite) {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS tickets (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    ticket_number TEXT UNIQUE NOT NULL,
                    user_id INTEGER DEFAULT NULL,
                    name TEXT NOT NULL,
                    email TEXT NOT NULL,
                    phone TEXT DEFAULT NULL,
                    role TEXT DEFAULT 'customer',
                    subject TEXT NOT NULL,
                    category TEXT DEFAULT 'Order Tracking',
                    priority TEXT DEFAULT 'Medium',
                    status TEXT DEFAULT 'Open',
                    message TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS ticket_replies (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    ticket_id INTEGER NOT NULL,
                    user_id INTEGER DEFAULT NULL,
                    sender_name TEXT NOT NULL,
                    sender_role TEXT DEFAULT 'admin',
                    message TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } else {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `tickets` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `ticket_number` VARCHAR(50) UNIQUE NOT NULL,
                    `user_id` INT DEFAULT NULL,
                    `name` VARCHAR(100) NOT NULL,
                    `email` VARCHAR(150) NOT NULL,
                    `phone` VARCHAR(50) DEFAULT NULL,
                    `role` ENUM('customer', 'vendor', 'visitor') DEFAULT 'customer',
                    `subject` VARCHAR(255) NOT NULL,
                    `category` VARCHAR(100) DEFAULT 'Order Tracking',
                    `priority` ENUM('Low', 'Medium', 'High', 'Urgent') DEFAULT 'Medium',
                    `status` ENUM('Open', 'In Progress', 'Resolved', 'Closed') DEFAULT 'Open',
                    `message` TEXT NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `ticket_replies` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `ticket_id` INT NOT NULL,
                    `user_id` INT DEFAULT NULL,
                    `sender_name` VARCHAR(100) NOT NULL,
                    `sender_role` VARCHAR(50) DEFAULT 'admin',
                    `message` TEXT NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
    } catch (Exception $e) {}

    // 5. Ensure avatar column in users & vendors
    try {
        if ($is_sqlite) {
            $cols = $pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array('avatar', $cols)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN avatar TEXT DEFAULT NULL");
            }
            $vcols = $pdo->query("PRAGMA table_info(vendors)")->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array('avatar', $vcols)) {
                $pdo->exec("ALTER TABLE vendors ADD COLUMN avatar TEXT DEFAULT NULL");
            }
        } else {
            $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'avatar'")->fetchAll();
            if (empty($cols)) {
                $pdo->exec("ALTER TABLE `users` ADD COLUMN `avatar` VARCHAR(255) DEFAULT NULL");
            }
            $vcols = $pdo->query("SHOW COLUMNS FROM vendors LIKE 'avatar'")->fetchAll();
            if (empty($vcols)) {
                $pdo->exec("ALTER TABLE `vendors` ADD COLUMN `avatar` VARCHAR(255) DEFAULT NULL");
            }
        }
    } catch (Exception $e) {}

    // 6. Coupons & Promo Codes
    try {
        if ($is_sqlite) {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS coupons (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    code TEXT UNIQUE NOT NULL,
                    title TEXT NOT NULL,
                    description TEXT,
                    discount_type TEXT DEFAULT 'percentage',
                    discount_value REAL NOT NULL,
                    min_order_amount REAL DEFAULT 0,
                    max_discount REAL DEFAULT 0,
                    coupon_type TEXT DEFAULT 'sale_discount',
                    usage_limit INTEGER DEFAULT 1000,
                    used_count INTEGER DEFAULT 0,
                    start_date DATE,
                    expiry_date DATE,
                    status TEXT DEFAULT 'active',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
                CREATE TABLE IF NOT EXISTS coupon_usages (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    coupon_id INTEGER NOT NULL,
                    user_id INTEGER,
                    order_id INTEGER,
                    customer_email TEXT,
                    customer_phone TEXT,
                    discount_amount REAL,
                    used_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
            ");
        } else {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `coupons` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `code` VARCHAR(50) UNIQUE NOT NULL,
                    `title` VARCHAR(150) NOT NULL,
                    `description` TEXT,
                    `discount_type` ENUM('percentage', 'fixed', 'free_shipping') DEFAULT 'percentage',
                    `discount_value` DECIMAL(10,2) NOT NULL,
                    `min_order_amount` DECIMAL(10,2) DEFAULT 0,
                    `max_discount` DECIMAL(10,2) DEFAULT 0,
                    `coupon_type` ENUM('one_per_user', 'new_user_only', 'sale_discount') DEFAULT 'sale_discount',
                    `usage_limit` INT DEFAULT 1000,
                    `used_count` INT DEFAULT 0,
                    `start_date` DATE DEFAULT NULL,
                    `expiry_date` DATE DEFAULT NULL,
                    `status` ENUM('active', 'inactive') DEFAULT 'active',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS `coupon_usages` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `coupon_id` INT NOT NULL,
                    `user_id` INT DEFAULT NULL,
                    `order_id` INT DEFAULT NULL,
                    `customer_email` VARCHAR(150) DEFAULT NULL,
                    `customer_phone` VARCHAR(50) DEFAULT NULL,
                    `discount_amount` DECIMAL(10,2) DEFAULT 0,
                    `used_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // Seed initial coupons if none exist
        $chk_c = $pdo->query("SELECT COUNT(*) FROM coupons")->fetchColumn();
        if ($chk_c == 0) {
            $init_coupons = [
                ['WELCOME50', 'नया ग्राहक विशेष छूट (New User Special)', 'नए ग्राहकों के लिए पहले आर्डर पर फ्लैट ₹50 की छूट', 'fixed', 50, 299, 50, 'new_user_only', 5000, '2027-12-31'],
                ['FESTIVAL20', 'त्योहार महाबचत सेल (Festive Sale 20% Off)', 'सभी हथकरघा, औजार व दालों पर 20% तक की भारी छूट', 'percentage', 20, 499, 300, 'sale_discount', 2000, '2027-12-31'],
                ['SPECIAL100', 'विशेष डिस्काउंट (One Code Per User)', 'प्रति ग्राहक 1 बार उपयोग योग्य फ्लैट ₹100 की भारी छूट', 'fixed', 100, 699, 100, 'one_per_user', 1000, '2027-12-31'],
                ['DESIKISAN', 'देसी किसान औजार छूट (Artisan Forge 15% Off)', 'कृषि उपकरणों और हंसिया/खुरपी पर 15% की विशेष छूट', 'percentage', 15, 399, 250, 'sale_discount', 3000, '2027-12-31']
            ];
            $ins_c = $pdo->prepare("
                INSERT INTO coupons (code, title, description, discount_type, discount_value, min_order_amount, max_discount, coupon_type, usage_limit, expiry_date, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
            ");
            foreach ($init_coupons as $ic) {
                $ins_c->execute($ic);
            }
        }
    } catch (Exception $e) {}

    // 7. Live Chat System Tables
    try {
        if ($is_sqlite) {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS chat_sessions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    session_token TEXT UNIQUE NOT NULL,
                    user_id INTEGER DEFAULT NULL,
                    user_name TEXT NOT NULL,
                    user_email TEXT DEFAULT NULL,
                    user_phone TEXT DEFAULT NULL,
                    status TEXT DEFAULT 'active',
                    last_message TEXT,
                    unread_admin INTEGER DEFAULT 0,
                    unread_user INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
                CREATE TABLE IF NOT EXISTS chat_messages (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    session_id INTEGER NOT NULL,
                    sender_type TEXT NOT NULL,
                    sender_name TEXT NOT NULL,
                    message TEXT NOT NULL,
                    is_read INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
            ");
        } else {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `chat_sessions` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `session_token` VARCHAR(100) UNIQUE NOT NULL,
                    `user_id` INT DEFAULT NULL,
                    `user_name` VARCHAR(100) NOT NULL,
                    `user_email` VARCHAR(150) DEFAULT NULL,
                    `user_phone` VARCHAR(50) DEFAULT NULL,
                    `status` ENUM('active', 'resolved', 'closed') DEFAULT 'active',
                    `last_message` TEXT,
                    `unread_admin` INT DEFAULT 0,
                    `unread_user` INT DEFAULT 0,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS `chat_messages` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `session_id` INT NOT NULL,
                    `sender_type` ENUM('user', 'admin', 'support') NOT NULL,
                    `sender_name` VARCHAR(100) NOT NULL,
                    `message` TEXT NOT NULL,
                    `is_read` TINYINT(1) DEFAULT 0,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }
    } catch (Exception $e) {}

    // 8. Run Enterprise Version-Controlled Migrations
    run_enterprise_migrations($pdo);
}

/**
 * Enterprise Version-Controlled Migration Runner (Dual Engine: MySQL & SQLite)
 */
function run_enterprise_migrations($pdo) {
    if (!$pdo) return;
    $is_sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');

    // Ensure migrations table exists
    try {
        if ($is_sqlite) {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS migrations (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    migration TEXT UNIQUE NOT NULL,
                    executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } else {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `migrations` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `migration` VARCHAR(255) UNIQUE NOT NULL,
                    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
    } catch (Exception $e) {
        return;
    }

    $migrationsDir = __DIR__ . '/../database/migrations';
    if (!is_dir($migrationsDir)) {
        return;
    }

    $files = glob($migrationsDir . '/*.sql');
    sort($files);

    foreach ($files as $file) {
        $migName = basename($file);
        $chk = $pdo->prepare("SELECT id FROM migrations WHERE migration = ? LIMIT 1");
        $chk->execute([$migName]);
        if ($chk->fetch()) {
            continue; // Already migrated
        }

        $rawSql = file_get_contents($file);
        if ($is_sqlite) {
            $sql = preg_replace('/ENGINE=InnoDB.*?;/i', ';', $rawSql);
            $sql = preg_replace('/AUTO_INCREMENT/i', 'AUTOINCREMENT', $sql);
            $sql = preg_replace('/INT AUTOINCREMENT PRIMARY KEY/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sql);
            $sql = preg_replace('/ENUM\(.*?\)/i', 'TEXT', $sql);
            $sql = preg_replace('/TINYINT\(1\)/i', 'INTEGER', $sql);
            $sql = preg_replace('/COLLATE utf8mb4_unicode_ci/i', '', $sql);
            $sql = preg_replace('/DEFAULT CHARACTER SET utf8mb4/i', '', $sql);
            $sql = preg_replace('/DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP/i', 'DATETIME DEFAULT CURRENT_TIMESTAMP', $sql);
            $sql = preg_replace('/TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP/i', 'DATETIME DEFAULT CURRENT_TIMESTAMP', $sql);
            $sql = preg_replace('/ON UPDATE CURRENT_TIMESTAMP/i', '', $sql);
        } else {
            $sql = $rawSql;
        }

        $queries = explode(';', $sql);
        foreach ($queries as $q) {
            $trimmed = trim($q);
            if (!empty($trimmed)) {
                try {
                    $pdo->exec($trimmed);
                } catch (Exception $ex) {
                    // ignore non-fatal schema warnings
                }
            }
        }

        // Record migration
        $ins = $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
        $ins->execute([$migName]);
    }

    // Seed Initial RBAC Roles & System Permissions if empty
    seed_rbac_and_templates($pdo);
}

/**
 * Seed Default RBAC Roles, Permissions, and Notification Templates
 */
function seed_rbac_and_templates($pdo) {
    if (!$pdo) return;
    try {
        $cnt = $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
        if ($cnt == 0) {
            $roles = [
                ['Super Administrator', 'super_admin', 'Full platform access and root governance', 1],
                ['Marketplace Administrator', 'marketplace_admin', 'Moderation of products, vendors, categories and site operations', 1],
                ['Seller Support Executive', 'seller_support', 'Vendor onboarding, verification and support ticket handling', 1],
                ['Order Manager', 'order_manager', 'Order tracking, packaging dispatch, logistics and cancellation handling', 1],
                ['Finance & Settlement Manager', 'finance_manager', 'Vendor payouts, refunds, banking settlements and financial ledgers', 1],
                ['SEO Manager', 'seo_manager', 'Search engine optimization, sitemaps, robots.txt and metadata', 1],
                ['Content Editor', 'content_editor', 'Blog management, articles, editorial and rich media', 1],
                ['Marketing Manager', 'marketing_manager', 'Coupons, promotions, festival campaigns and hero customizer', 1],
                ['Technical Administrator', 'technical_admin', 'API integrations, security center, backups and system settings', 1],
                ['Read-Only Analyst', 'readonly_analyst', 'Analytics, performance reports and view-only operational visibility', 1],
            ];
            $ins = $pdo->prepare("INSERT INTO roles (name, slug, description, is_system) VALUES (?, ?, ?, ?)");
            foreach ($roles as $r) {
                $ins->execute($r);
            }
        }

        // Seed Core Permissions
        $p_cnt = $pdo->query("SELECT COUNT(*) FROM permissions")->fetchColumn();
        if ($p_cnt == 0) {
            $perms = [
                ['orders', 'View Orders', 'orders.view', 'View customer orders and shipment status'],
                ['orders', 'Modify Orders', 'orders.edit', 'Update order and fulfillment statuses'],
                ['orders', 'Cancel Orders', 'orders.cancel', 'Process customer order cancellations'],
                ['products', 'View Products', 'products.view', 'Browse product catalog'],
                ['products', 'Moderate Products', 'products.moderate', 'Approve or reject vendor products'],
                ['products', 'Edit Products', 'products.edit', 'Create or edit product specifications'],
                ['products', 'Delete Products', 'products.delete', 'Remove products from catalog'],
                ['sellers', 'View Sellers', 'sellers.view', 'Inspect vendor profiles and KYC'],
                ['sellers', 'Approve Sellers', 'sellers.approve', 'Approve vendor registration and KYC documents'],
                ['sellers', 'Suspend Sellers', 'sellers.suspend', 'Suspend or ban non-compliant sellers'],
                ['sellers', 'Commission Rules', 'sellers.commission', 'Configure custom vendor commission rates'],
                ['payouts', 'View Settlements', 'payouts.view', 'Inspect vendor earnings and balances'],
                ['payouts', 'Approve Payouts', 'payouts.approve', 'Authorize bank transfers and UTR generation'],
                ['coupons', 'Manage Coupons', 'coupons.manage', 'Create, edit, and deactivate discount codes'],
                ['campaigns', 'Manage Hero Campaigns', 'campaigns.manage', 'Build festival themes and scheduled banners'],
                ['campaigns', 'Custom HTML Hero', 'campaigns.html_custom', 'Edit and deploy sandboxed custom HTML/CSS hero markup'],
                ['blogs', 'Manage Articles', 'blogs.manage', 'Draft, edit, and publish blog content'],
                ['seo', 'Manage SEO', 'seo.manage', 'Configure metadata, sitemaps, and 301 redirects'],
                ['integrations', 'Manage Integrations', 'integrations.manage', 'Configure GA4, GSC, Firebase, and Gateways'],
                ['security', 'Security Center Access', 'security.view', 'Inspect security audit logs and rate-limiting'],
                ['users', 'Manage Admin Users', 'users.manage', 'Create staff accounts and assign roles'],
            ];
            $ins_p = $pdo->prepare("INSERT INTO permissions (module, name, slug, description) VALUES (?, ?, ?, ?)");
            foreach ($perms as $p) {
                $ins_p->execute($p);
            }
        }

        // Seed Core Notification Templates
        $n_cnt = $pdo->query("SELECT COUNT(*) FROM notification_templates")->fetchColumn();
        if ($n_cnt == 0) {
            $ntemplates = [
                ['order_confirmed', 'Order Confirmation', 'Your GaunGaliHat Order #{order_number} is Confirmed!', 'Namaste {customer_name}, your order of {total_amount} has been received and sent to village workshops for dispatch.'],
                ['order_dispatched', 'Order Dispatched', 'Your Order #{order_number} has been Dispatched via India Post', 'Your parcel tracking number is {tracking_consignment_no}. Estimated delivery in 2-4 business days.'],
                ['vendor_kyc_approved', 'Artisan Store Approved', 'Welcome to GaunGaliHat Artisan Network!', 'Congratulations! Your workshop profile has been verified. You can now list products and receive nationwide orders.'],
                ['payout_settled', 'Bank Settlement Completed', 'Payout Ref #{payout_ref} Settled to Your Bank Account', 'Amount of {net_amount} has been credited to your bank account with UTR: {utr_number}.'],
            ];
            $ins_n = $pdo->prepare("INSERT INTO notification_templates (slug, name, subject, body_template) VALUES (?, ?, ?, ?)");
            foreach ($ntemplates as $nt) {
                $ins_n->execute($nt);
            }
        }
    } catch (Exception $e) {}
}

/**
 * Initialize SQLite database with exact same tables & data if MySQL is temporarily offline
 */
function init_sqlite_schema($pdo) {
    $schemaFile = __DIR__ . '/../database/schema.sql';
    if (file_exists($schemaFile)) {
        $rawSql = file_get_contents($schemaFile);
        // Convert MySQL syntax to SQLite compatible syntax
        $converted = preg_replace('/ENGINE=InnoDB.*?;/i', ';', $rawSql);
        $converted = preg_replace('/AUTO_INCREMENT/i', 'AUTOINCREMENT', $converted);
        $converted = preg_replace('/INT AUTOINCREMENT PRIMARY KEY/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $converted);
        $converted = preg_replace('/ENUM\(.*?\)/i', 'TEXT', $converted);
        $converted = preg_replace('/TINYINT\(1\)/i', 'INTEGER', $converted);
        $converted = preg_replace('/COLLATE utf8mb4_unicode_ci/i', '', $converted);
        $converted = preg_replace('/DEFAULT CHARACTER SET utf8mb4/i', '', $converted);
        $converted = preg_replace('/CREATE DATABASE.*?;/i', '', $converted);
        $converted = preg_replace('/USE `gaungalihat`;/i', '', $converted);
        
        $queries = explode(';', $converted);
        foreach ($queries as $q) {
            $trimmed = trim($q);
            if (!empty($trimmed)) {
                try {
                    $pdo->exec($trimmed);
                } catch (Exception $ex) {
                    // ignore non-fatal conversion notices
                }
            }
        }
    }
}
