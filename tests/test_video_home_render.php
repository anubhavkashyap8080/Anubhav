<?php
define('TESTING_MOCK_DB', true);

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

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Mock PDO with in-memory sqlite
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
    CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(100),
        slug VARCHAR(100),
        sort_order INTEGER DEFAULT 0
    );
    INSERT INTO users (id, username, role, status) VALUES (1, 'superadmin', 'super_admin', 'active');
");
$GLOBALS['pdo'] = $sqlite;

ob_start();
try {
    include __DIR__ . '/../admin/video_home.php';
    $output = ob_get_clean();
    echo "RENDER SUCCESS: Output length " . strlen($output) . " bytes\n";
    if (strpos($output, 'Video Story Home (Layout 6) Customizer') !== false) {
        echo "VERIFIED: Page title and customizer rendered properly!\n";
    } else {
        echo "WARNING: Expected title not found in output\n";
    }
} catch (Throwable $e) {
    ob_end_clean();
    echo "FATAL ERROR: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
}
