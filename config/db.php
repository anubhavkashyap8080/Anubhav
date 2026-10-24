<?php
/**
 * GaunGaliHat Enterprise E-Commerce Platform
 * Database Connection & Migration Layer (PDO MySQL with Resilient Fallback)
 */

// Enable global output buffering to prevent "headers already sent"
if (ob_get_level() === 0) {
    ob_start();
}

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Database Credentials (cPanel Production & Local Fallback Configuration)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'womenvission_gaungali');
define('DB_USER', getenv('DB_USER') ?: 'womenvission_admin');
define('DB_PASS', getenv('DB_PASS') ?: 'Anumi@9935');

$pdo = null;
$db_driver = 'mysql';
$last_mysql_error = null;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_TIMEOUT            => 2,
];

// Set MySQL UTF-8 collation command if constant exists
if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
    $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci";
}

// Resilient candidate list: tried strictly in sequence with quick 2s timeout
$candidates = [
    ['host' => DB_HOST, 'user' => DB_USER, 'pass' => DB_PASS, 'db' => DB_NAME],
    ['host' => 'localhost', 'user' => 'admin', 'pass' => 'Anumi@9935', 'db' => 'womenvission_gaungali'],
    ['host' => 'localhost', 'user' => 'womenvission_admin', 'pass' => 'Anumi@9935', 'db' => 'womenvission_gaungali'],
    ['host' => 'localhost', 'user' => 'root', 'pass' => '', 'db' => 'gaungalihat'],
];

$tried = [];
foreach ($candidates as $cand) {
    $sig = $cand['host'] . ':' . $cand['user'] . ':' . $cand['db'];
    if (isset($tried[$sig])) continue;
    $tried[$sig] = true;

    try {
        $dsn = "mysql:host={$cand['host']};port=" . DB_PORT . ";dbname={$cand['db']};charset=utf8mb4";
        $pdo = new PDO($dsn, $cand['user'], $cand['pass'], $options);
        $db_driver = 'mysql';
        break;
    } catch (Throwable $e) {
        $last_mysql_error = $e->getMessage();
    }
}

if ($pdo) {
    // MySQL Connected Successfully! Ensure UTF-8 & Verify/migrate platform schema
    try {
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    } catch (Throwable $e_utf8) {}

    try {
        $table_check = $pdo->query("SHOW TABLES LIKE 'products'");
        if (!$table_check || !$table_check->fetch()) {
            $schemaFile = __DIR__ . '/../database/schema.sql';
            if (file_exists($schemaFile)) {
                $sql = file_get_contents($schemaFile);
                $queries = array_filter(array_map('trim', explode(';', $sql)));
                foreach ($queries as $q) {
                    if (!empty($q)) {
                        try { $pdo->exec($q); } catch (Throwable $e_q) {}
                    }
                }
            }
        }
        // Ensure all application tables (wishlists, coupons, roles, etc.) exist
        ensure_platform_schema($pdo);
    } catch (Throwable $e_tbl) {}
} else {
    // 2. MySQL is not reachable - fallback immediately to local SQLite database
    $sqliteDir = __DIR__ . '/../data';
    $sqliteFile = $sqliteDir . '/gaungalihat.sqlite';

    if (extension_loaded('pdo_sqlite') && is_dir($sqliteDir) && file_exists($sqliteFile)) {
        try {
            $pdo = new PDO("sqlite:" . $sqliteFile, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $db_driver = 'sqlite';
            ensure_platform_schema($pdo);
        } catch (Throwable $e_sqlite) {
            $pdo = null;
        }
    }

    if (!$pdo) {
        // Output clear diagnostics instead of a blank white screen
        http_response_code(500);
        die("<!DOCTYPE html>
        <html lang='en'>
        <head>
          <meta charset='UTF-8'>
          <title>Database Connection Diagnostic — GaunGaliHat</title>
          <style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #fdfbf7; color: #1f2937; padding: 40px 20px; }
            .card { max-width: 600px; margin: 0 auto; background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
            h2 { color: #dc2626; margin-top: 0; font-size: 20px; }
            p { font-size: 14px; line-height: 1.6; color: #4b5563; }
            .code { background: #f3f4f6; padding: 12px; border-radius: 8px; font-family: monospace; font-size: 12px; overflow-x: auto; margin: 15px 0; border-left: 4px solid #dc2626; }
            .tip { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px; font-size: 13px; margin-top: 15px; }
          </style>
        </head>
        <body>
          <div class='card'>
            <h2>GaunGaliHat Database Connection Diagnostic</h2>
            <p>The application could not establish a connection to MySQL with the current configuration.</p>
            <div class='code'><strong>MySQL Error:</strong> " . htmlspecialchars($last_mysql_error ?: 'Unknown error') . "</div>
            <div class='tip'>
              <strong>Quick Fix in cPanel:</strong><br>
              1. Open <strong>cPanel > MySQL Databases</strong>.<br>
              2. Ensure user <code>" . htmlspecialchars(DB_USER) . "</code> or <code>admin</code> has been added to database <code>" . htmlspecialchars(DB_NAME) . "</code>.<br>
              3. Ensure <strong>All Privileges</strong> are granted.
            </div>
          </div>
        </body>
        </html>");
    }
}

/**
 * Ensure all platform tables (site_settings, blogs, tickets, etc.) are always present and healthy
 */
function ensure_platform_schema($pdo) {
    if (!$pdo) return;
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $is_sqlite = ($driver === 'sqlite');

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

    // Default Settings
    try {
        $chk = $pdo->query("SELECT COUNT(*) FROM site_settings")->fetchColumn();
        if ($chk == 0) {
            $defaults = [
                'brand_name' => 'गाँव गली हाट (GaunGaliHat)',
                'company_name' => 'S S Industries',
                'brand_tagline' => 'Desh ki mitti ka swad | Ganv se ghar tak',
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
                'razorpay_enabled' => '1',
                'razorpay_key_id' => 'rzp_test_SSIndustriesLiveKey',
                'razorpay_key_secret' => 'rzp_sec_SSIndustriesLiveSecret',
                'payu_enabled' => '1',
                'payu_merchant_key' => 'SSIndKeyTest108',
                'payu_merchant_salt' => 'SSIndSaltTest7897',
                'payu_mode' => 'test',
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
                'footer_copyright' => '© 2026 GaunGaliHat. A Unit of S S Industries Enterprise. All Rights Reserved.',
                'social_instagram' => 'https://instagram.com/ganvgalihat',
                'social_youtube' => 'https://youtube.com/@ganvgalihat',
                'social_facebook' => 'https://facebook.com/ganvgalihat',
                'social_whatsapp' => 'https://wa.me/917897492100',
                // Hero Section Customizer Defaults (Festivals, Sales, Seasonal)
                'hero_festive_preset' => 'general',
                'hero_theme_color' => 'emerald',
                'hero_badge_text' => '🌾 S S Industries Enterprise • Desh ki mitti ka swad',
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
                'hero_badge_text' => '🌾 S S Industries Enterprise • Desh ki mitti ka swad',
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
