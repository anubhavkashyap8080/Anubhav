<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "=== TESTING ROUTES SIMULATION ===\n";

$_SERVER['HTTP_HOST'] = 'gaungalihat.womenvission.in';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

$test_urls = [
    '/' => 'index.php',
    '/products.php' => 'products.php',
    '/products.php?category=farming-traditional-tools' => 'products.php',
    '/login.php' => 'login.php',
    '/login.php?tab=seller' => 'login.php',
    '/login.php?tab=staff' => 'login.php',
    '/cart.php' => 'cart.php',
    '/wishlist.php' => 'wishlist.php',
    '/product.php?id=1' => 'product.php',
];

foreach ($test_urls as $uri => $script) {
    $_SERVER['REQUEST_URI'] = $uri;
    $_SERVER['REQUEST_METHOD'] = 'GET';
    echo "\nTesting URI: $uri ($script):\n";
    try {
        ob_start();
        include __DIR__ . '/../' . $script;
        $out = ob_get_clean();
        echo "  [PASS] Rendered successfully (" . strlen($out) . " bytes)\n";
    } catch (Throwable $e) {
        ob_end_clean();
        echo "  [FAIL] Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}

echo "\nAll route simulations completed.\n";
