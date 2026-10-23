<?php
/**
 * GaunGaliHat PayU Gateway Callback Handler
 * Receives Return Post/Get from PayU
 */

require_once __DIR__ . '/../includes/functions.php';

$order_id = intval($_GET['order_id'] ?? $_POST['order_id'] ?? 0);
$status = strtolower($_POST['status'] ?? $_GET['status'] ?? 'failure');
$txnid = $_POST['txnid'] ?? '';
$payu_money_id = $_POST['payuMoneyId'] ?? $_POST['mihpayid'] ?? ('PAYU-' . rand(100000, 999999));

if (!$order_id) {
    set_flash('error', 'Invalid callback parameters from payment gateway.');
    header('Location: /cart.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        set_flash('error', 'Order not found.');
        header('Location: /cart.php');
        exit;
    }

    if ($status === 'success') {
        // Success execution
        $update = $pdo->prepare("
            UPDATE orders 
            SET payment_status = 'completed', 
                order_status = 'Confirmed', 
                transaction_id = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $update->execute([$payu_money_id, $order_id]);

        clear_cart();
        set_flash('success', 'PayU payment received successfully!');
        header('Location: /order-success.php?id=' . $order_id);
        exit;
    } else {
        // Payment failed or cancelled
        $update = $pdo->prepare("
            UPDATE orders 
            SET payment_status = 'failed', 
                order_status = 'Payment Failed',
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $update->execute([$order_id]);

        set_flash('error', 'PayU transaction was not completed or was cancelled by user.');
        header('Location: /checkout.php');
        exit;
    }

} catch (Exception $e) {
    set_flash('error', 'Database error on payment callback: ' . $e->getMessage());
    header('Location: /cart.php');
    exit;
}
