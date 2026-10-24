<?php
/**
 * AJAX Cart Actions
 */

require_once __DIR__ . '/../includes/functions.php';

function send_json_cart_response($data) {
    if (ob_get_length()) {
        ob_clean();
    }
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=UTF-8');
    }
    echo json_encode($data);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$product_id = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
$qty = (int)($_POST['quantity'] ?? 1);

switch ($action) {
    case 'add':
        if ($product_id <= 0) {
            send_json_cart_response(['success' => false, 'message' => 'Invalid product identifier.']);
        }
        $added = add_to_cart($product_id, $qty);
        if ($added) {
            send_json_cart_response([
                'success' => true,
                'message' => 'Product successfully added to your shopping cart!',
                'cart_count' => get_cart_count(),
                'cart_subtotal' => format_price(get_cart_subtotal())
            ]);
        } else {
            send_json_cart_response(['success' => false, 'message' => 'Product out of stock or unavailable.']);
        }
        break;

    case 'update':
        if ($product_id <= 0) {
            send_json_cart_response(['success' => false, 'message' => 'Invalid product.']);
        }
        update_cart_qty($product_id, $qty);
        $cart_subtotal = get_cart_subtotal();
        $pincode = $_SESSION['shipping_pincode'] ?? '241126';
        $shipping = calculate_shipping_rate($pincode, $cart_subtotal, get_cart_total_weight());

        send_json_cart_response([
            'success' => true,
            'cart_count' => get_cart_count(),
            'cart_subtotal' => format_price($cart_subtotal),
            'shipping_fee' => $shipping['is_free'] ? 'FREE' : format_price($shipping['shipping_fee']),
            'total_amount' => format_price($cart_subtotal + $shipping['shipping_fee'])
        ]);
        break;

    case 'remove':
        remove_from_cart($product_id);
        $cart_subtotal = get_cart_subtotal();
        $pincode = $_SESSION['shipping_pincode'] ?? '241126';
        $shipping = calculate_shipping_rate($pincode, $cart_subtotal, get_cart_total_weight());

        send_json_cart_response([
            'success' => true,
            'message' => 'Item removed from your cart.',
            'cart_count' => get_cart_count(),
            'cart_subtotal' => format_price($cart_subtotal),
            'shipping_fee' => $shipping['is_free'] ? 'FREE' : format_price($shipping['shipping_fee']),
            'total_amount' => format_price($cart_subtotal + $shipping['shipping_fee'])
        ]);
        break;

    case 'clear':
        clear_cart();
        send_json_cart_response([
            'success' => true,
            'message' => 'Cart cleared.',
            'cart_count' => 0,
            'cart_subtotal' => format_price(0)
        ]);
        break;

    case 'get_count':
    default:
        send_json_cart_response([
            'success' => true,
            'cart_count' => get_cart_count(),
            'cart_subtotal' => format_price(get_cart_subtotal())
        ]);
        break;
}
