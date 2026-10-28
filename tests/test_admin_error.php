<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

define('TESTING_MOCK_DB', true);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['user_name'] = 'Test Admin';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['PHP_SELF'] = '/admin/index.php';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require_once __DIR__ . '/../admin/index.php';
