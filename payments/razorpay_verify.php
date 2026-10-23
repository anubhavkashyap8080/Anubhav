<?php
/**
 * GaunGaliHat Razorpay Verification Endpoint
 * Validates Razorpay Transaction & Completes Order
 */

require_once __DIR__ . '/../includes/functions.php';

$order_id = intval($_POST['order_id'] ?? 0);
$razorpay_payment_id = clean_input($_POST['razorpay_payment_id'] ?? '');

if (!$order_id || empty($razorpay_payment_id)) {
    set_flash('error', 'Payment verification failed or missing transaction tokens.');
    header('Location: /checkout.php');
    exit;
}

try {
    // Fetch Order
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        set_flash('error', 'Order reference not found in system.');
        header('Location: /cart.php');
        exit;
    }

    // Update Order as Completed with Payment Reference
    $update = $pdo->prepare("
        UPDATE orders 
        SET payment_status = 'completed', 
            order_status = 'Confirmed', 
            transaction_id = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    $update->execute([$razorpay_payment_id, $order_id]);

    // Clear cart items
    clear_cart();

    set_flash('success', 'Razorpay Payment Successful! Transaction Ref: ' . $razorpay_payment_id);
    header('Location: /order-success.php?id=' . $order_id);
    exit;

} catch (Exception $e) {
    set_flash('error', 'Payment processing error: ' . $e->getMessage());
    header('Location: /checkout.php');
    exit;
}
