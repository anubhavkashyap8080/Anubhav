<?php
/**
 * AJAX Cart Actions
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$product_id = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
$qty = (int)($_POST['quantity'] ?? 1);

switch ($action) {
    case 'add':
        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product identifier.']);
            exit;
        }
        $added = add_to_cart($product_id, $qty);
        if ($added) {
            echo json_encode([
                'success' => true,
                'message' => 'Product successfully added to your shopping cart!',
                'cart_count' => get_cart_count(),
                'cart_subtotal' => format_price(get_cart_subtotal())
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product out of stock or unavailable.']);
        }
        break;

    case 'update':
        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit;
        }
        update_cart_qty($product_id, $qty);
        $cart_subtotal = get_cart_subtotal();
        $pincode = $_SESSION['shipping_pincode'] ?? '241126';
        $shipping = calculate_shipping_rate($pincode, $cart_subtotal, get_cart_total_weight());

        echo json_encode([
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

        echo json_encode([
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
        echo json_encode([
            'success' => true,
            'message' => 'Cart cleared.',
            'cart_count' => 0,
            'cart_subtotal' => format_price(0)
        ]);
        break;

    case 'get_count':
    default:
        echo json_encode([
            'success' => true,
            'cart_count' => get_cart_count(),
            'cart_subtotal' => format_price(get_cart_subtotal())
        ]);
        break;
}
