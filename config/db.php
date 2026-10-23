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
    } catch (PDOException $e_sqlite) {
        die("Fatal Database Connection Error: " . $e_sqlite->getMessage());
    }
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
