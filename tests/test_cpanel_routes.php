<?php
// Mock server variables
$_SERVER['HTTP_HOST'] = 'ganvgalihat.com';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_URI'] = '/login.php';
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SESSION = [];

echo "=== TESTING ROUTES ===\n";

// Test 1: login.php
echo "\nTesting login.php:\n";
try {
    ob_start();
    include __DIR__ . '/../login.php';
    $out = ob_get_clean();
    echo "SUCCESS: login.php rendered (" . strlen($out) . " bytes)\n";
} catch (Throwable $e) {
    ob_end_clean();
    echo "ERROR in login.php: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// Test 2: cart.php
echo "\nTesting cart.php:\n";
try {
    ob_start();
    include __DIR__ . '/../cart.php';
    $out = ob_get_clean();
    echo "SUCCESS: cart.php rendered (" . strlen($out) . " bytes)\n";
} catch (Throwable $e) {
    ob_end_clean();
    echo "ERROR in cart.php: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// Test 3: wishlist.php
echo "\nTesting wishlist.php:\n";
try {
    ob_start();
    include __DIR__ . '/../wishlist.php';
    $out = ob_get_clean();
    echo "SUCCESS: wishlist.php rendered (" . strlen($out) . " bytes)\n";
} catch (Throwable $e) {
    ob_end_clean();
    echo "ERROR in wishlist.php: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// Test 4: ajax/cart_actions.php
echo "\nTesting ajax/cart_actions.php:\n";
try {
    ob_start();
    $_POST['action'] = 'add';
    $_POST['product_id'] = 1;
    $_POST['quantity'] = 1;
    $_SERVER['REQUEST_METHOD'] = 'POST';
    include __DIR__ . '/../ajax/cart_actions.php';
    $out = ob_get_clean();
    echo "RESULT ajax/cart_actions.php: " . $out . "\n";
} catch (Throwable $e) {
    ob_end_clean();
    echo "ERROR in ajax/cart_actions.php: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// Test 5: ajax/set_location.php
echo "\nTesting ajax/set_location.php:\n";
try {
    ob_start();
    $_POST['latitude'] = '27.1264';
    $_POST['longitude'] = '80.1444';
    $_POST['city'] = 'Hardoi';
    $_POST['state'] = 'Uttar Pradesh';
    $_POST['pincode'] = '241126';
    $_SERVER['REQUEST_METHOD'] = 'POST';
    include __DIR__ . '/../ajax/set_location.php';
    $out = ob_get_clean();
    echo "RESULT ajax/set_location.php: " . $out . "\n";
} catch (Throwable $e) {
    ob_end_clean();
    echo "ERROR in ajax/set_location.php: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
}
