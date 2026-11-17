<?php
/**
 * GanvGaliHat Server-to-Server Payment Webhook Handler (Razorpay & PayU)
 * Handles asynchronous payment captures even if the buyer closes the browser window.
 */

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$raw_body = file_get_contents('php://input');
if (empty($raw_body)) {
    http_response_code(400);
    echo json_encode(['error' => 'Empty payload']);
    exit;
}

// 1. RAZORPAY WEBHOOK HANDLING
$rzp_sig = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';
if (!empty($rzp_sig)) {
    $cfg = function_exists('get_platform_config') ? get_platform_config('payments') : [];
    $webhook_secret = (string)(getenv('RAZORPAY_WEBHOOK_SECRET') ?: ($cfg['razorpay']['webhook_secret'] ?? (defined('RAZORPAY_KEY_SECRET') ? RAZORPAY_KEY_SECRET : '')));

    if (!empty($webhook_secret)) {
        $expected = hash_hmac('sha256', $raw_body, $webhook_secret);
        if (!hash_equals($expected, $rzp_sig)) {
            log_audit_action('webhook_signature_failed', 'orders', 'Razorpay webhook signature verification mismatch');
            http_response_code(401);
            echo json_encode(['error' => 'Signature verification failed']);
            exit;
        }
    }

    $event_data = json_decode($raw_body, true);
    if (!is_array($event_data)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }

    $event = $event_data['event'] ?? '';
    if ($event === 'payment.captured' || $event === 'order.paid') {
        $payment_entity = $event_data['payload']['payment']['entity'] ?? [];
        $rzp_payment_id = $payment_entity['id'] ?? '';
        $rzp_order_id = $payment_entity['order_id'] ?? '';
        $amount_paise = (int)($payment_entity['amount'] ?? 0);

        if (!empty($rzp_order_id)) {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE gateway_order_id = ? LIMIT 1");
            $stmt->execute([$rzp_order_id]);
            $order = $stmt->fetch();

            if ($order && $order['payment_status'] !== 'completed') {
                $access_token = $order['order_access_token'] ?: bin2hex(random_bytes(32));
                $upd = $pdo->prepare("
                    UPDATE orders 
                    SET payment_status = 'completed',
                        order_status = 'Confirmed',
                        payment_txn_id = ?,
                        order_access_token = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ? AND payment_status != 'completed'
                ");
                $upd->execute([$rzp_payment_id, $access_token, $order['id']]);

                if ($upd->rowCount() > 0) {
                    // Decrement stock
                    try {
                        $stmt_items = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                        $stmt_items->execute([$order['id']]);
                        while ($oi = $stmt_items->fetch()) {
                            $p_qty = max(1, (int)$oi['quantity']);
                            $p_id = (int)$oi['product_id'];
                            $pdo->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?")->execute([$p_qty, $p_id]);
                            $pdo->prepare("UPDATE products SET stock_status = 'Out of Stock' WHERE id = ? AND stock <= 0")->execute([$p_id]);
                        }
                    } catch (Throwable $t) {}

                    log_audit_action('webhook_payment_captured', 'orders', "Order #{$order['id']} captured via Razorpay webhook");
                }
            }
        }
    }

    echo json_encode(['status' => 'ok']);
    exit;
}

// 2. Generic acknowledge
echo json_encode(['status' => 'received']);
