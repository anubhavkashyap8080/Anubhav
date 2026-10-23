<?php
/**
 * AJAX Wishlist Actions
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

$product_id = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit;
}

$is_added = toggle_wishlist($product_id);

echo json_encode([
    'success' => true,
    'in_wishlist' => $is_added,
    'message' => $is_added ? 'Item saved to your wishlist!' : 'Item removed from your wishlist.',
    'wishlist_count' => get_wishlist_count()
]);
