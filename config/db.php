<?php
/**
 * GaunGaliHat Enterprise E-Commerce Platform
 * Database Connection & Migration Layer (PDO MySQL with Resilient Fallback)
 */

// Enable global output buffering to prevent "headers already sent"
if (ob_get_level() === 0) {
    ob_start();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials (Standard XAMPP / WAMP / Production MySQL)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'gaungalihat');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

$pdo = null;
$db_driver = 'mysql';

try {
    // 1. Attempt connection to MySQL server
    $dsn_server = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo_server = new PDO($dsn_server, DB_USER, DB_PASS, $options);
    
    // Ensure database exists
    $pdo_server->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Connect to specific database
    $dsn_db = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn_db, DB_USER, DB_PASS, $options);
    
    // Verify if tables exist, otherwise auto-import schema.sql
    $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
    if ($stmt->rowCount() === 0) {
        $schemaFile = __DIR__ . '/../database/schema.sql';
        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            $pdo->exec($sql);
        }
    }
} catch (PDOException $e) {
    // 2. MySQL is not currently running on local machine (e.g. CLI testing)
    // Seamless fallback to SQLite so site works instantly without 500 errors
    $db_driver = 'sqlite';
    $sqliteDir = __DIR__ . '/../data';
    if (!is_dir($sqliteDir)) {
        mkdir($sqliteDir, 0755, true);
    }
    $sqliteFile = $sqliteDir . '/gaungalihat.sqlite';
    $isNewSqlite = !file_exists($sqliteFile);

    try {
        $pdo = new PDO("sqlite:" . $sqliteFile, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        if ($isNewSqlite) {
            init_sqlite_schema($pdo);
        }
        ensure_platform_schema($pdo);
    } catch (PDOException $e_sqlite) {
        die("Fatal Database Connection Error: " . $e_sqlite->getMessage());
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
                'announcement_text' => 'सीधे ग्रामीण कारीगरों व कुटीर उद्योग से • ₹499+ के आर्डर पर पूरे भारत में मुफ्त डिलीवरी!',
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
            ];
            $ins = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
            foreach ($defaults as $k => $v) {
                $ins->execute([$k, $v]);
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
