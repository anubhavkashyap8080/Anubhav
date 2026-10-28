<?php
/**
 * AJAX Wishlist Actions
 */

require_once __DIR__ . '/../includes/functions.php';

function send_json_wishlist_response($data) {
    if (ob_get_length()) {
        ob_clean();
    }
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=UTF-8');
    }
    echo json_encode($data);
    exit;
}

$product_id = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);

// Item 16: Central CSRF Protection for state-changing wishlist actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();
}

if ($product_id <= 0) {
    send_json_wishlist_response(['success' => false, 'message' => 'Invalid product.']);
}

$is_added = toggle_wishlist($product_id);

send_json_wishlist_response([
    'success' => true,
    'in_wishlist' => $is_added,
    'message' => $is_added ? 'Item saved to your wishlist!' : 'Item removed from your wishlist.',
    'wishlist_count' => get_wishlist_count()
]);
