<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$_SERVER['DOCUMENT_ROOT'] = 'C:/Users/HP/.gemini/antigravity/scratch/ganvgalihat';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = '/admin/video_home.php';
$_SERVER['PHP_SELF'] = '/admin/video_home.php';
$_SERVER['HTTP_HOST'] = 'localhost';

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'super_admin';
$_SESSION['user'] = [
    'id' => 1,
    'username' => 'superadmin',
    'role' => 'super_admin',
    'status' => 'active'
];

// Set up SQLite memory DB
$sqlite = new PDO('sqlite::memory:');
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sqlite->exec("
    CREATE TABLE IF NOT EXISTS site_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        setting_key VARCHAR(100) UNIQUE,
        setting_value TEXT
    );
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(50),
        role VARCHAR(20),
        status VARCHAR(20)
    );
    INSERT INTO users (id, username, role, status) VALUES (1, 'superadmin', 'super_admin', 'active');
    CREATE TABLE IF NOT EXISTS audit_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        action VARCHAR(100),
        category VARCHAR(50),
        target_type VARCHAR(50),
        target_id VARCHAR(50),
        details TEXT,
        ip_address VARCHAR(50),
        user_agent TEXT,
        created_at DATETIME
    );
");

$GLOBALS['pdo'] = $sqlite;

try {
    ob_start();
    // Include video_home.php
    require_once __DIR__ . '/../admin/video_home.php';
    $out = ob_get_clean();
    echo "SUCCESS: Rendered " . strlen($out) . " bytes\n";
} catch (Throwable $t) {
    if (ob_get_level() > 0) ob_end_clean();
    echo "FATAL ERROR: " . $t->getMessage() . " in " . $t->getFile() . ":" . $t->getLine() . "\n";
    echo $t->getTraceAsString() . "\n";
}
