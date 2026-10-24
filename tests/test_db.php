<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "=== TESTING DB & SCHEMA ===\n";
require_once __DIR__ . '/../config/db.php';

if ($pdo) {
    echo "Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
    ensure_platform_schema($pdo);
    echo "ensure_platform_schema completed successfully.\n";

    // Check tables
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    }
    echo "Total tables: " . count($tables) . "\n";
    echo "Tables: " . implode(', ', array_slice($tables, 0, 15)) . "...\n";
} else {
    echo "No PDO instance!\n";
}
