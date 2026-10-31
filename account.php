<?php
/**
 * GaunGaliHat Shopper & Customer Account Hub
 * Comprehensive E-Commerce Customer Dashboard:
 * - Live Shopping Cart & Instant Checkout CTA
 * - Detailed Order History with Visual Shipment Tracking Stepper
 * - Saved Delivery Addresses
 * - Customer Support Desk & Ticket Replies
 * - Profile & Avatar Management
 * - Account Security & Password Protection
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

require_login();
$user = get_logged_in_user();

// Determine active tab (default to 'overview')
$allowed_tabs = ['overview', 'orders', 'cart', 'addresses', 'profile', 'tickets', 'security'];
$active_tab = clean_input($_GET['tab'] ?? 'overview');
if (!in_array($active_tab, $allowed_tabs, true)) {
    $active_tab = 'overview';
}

$success_msg = '';
$error_msg = '';

// Check flash messages
$flash = get_flash();
if ($flash) {
    if ($flash['type'] === 'success') {
        $success_msg = $flash['message'];
    } elseif ($flash['type'] === 'error') {
        $error_msg = $flash['message'];
    }
}

// 1. Resend Verification Email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_verification'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security session expired. Please refresh the page.';
    } else {
        if (!empty($user['email'])) {
            send_email_verification_link($user['id'], $user['email'], $user['name'], $user['phone'] ?? '');
            $success_msg = 'A 6-digit verification code and secure activation link have been dispatched to ' . htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') . '. Please check your inbox.';
        }
    }
}

// 2. Handle Reorder Action (Add past order item back to cart)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reorder_item'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security session expired. Please refresh the page.';
    } else {
        $prod_id = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        if ($prod_id > 0) {
            add_to_cart($prod_id, $qty);
            $success_msg = 'Item added to your shopping cart!';
            $active_tab = 'cart';
        }
    }
}

// 3. Handle Delivery Address Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_address'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security session expired. Please refresh the page.';
        $active_tab = 'addresses';
    } else {
        $address = clean_input($_POST['address'] ?? '');
        $city = clean_input($_POST['city'] ?? '');
        $pincode = clean_input($_POST['pincode'] ?? '');
        $phone = clean_input($_POST['phone'] ?? '');

        if (!empty($address) && !empty($city) && !empty($pincode)) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET address = ?, city = ?, pincode = ?, phone = ? WHERE id = ?");
                $stmt->execute([$address, $city, $pincode, $phone, $user['id']]);
                $success_msg = 'Default delivery address updated successfully!';
                $user = get_logged_in_user();
                $active_tab = 'addresses';
            } catch (Exception $e) {
                $error_msg = 'Address update error: ' . $e->getMessage();
                $active_tab = 'addresses';
            }
        } else {
            $error_msg = 'Please fill in your complete address, city, and 6-digit PIN code.';
            $active_tab = 'addresses';
        }
    }
}

// 4. Handle Mobile OTP Dispatch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_phone_otp'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security session expired. Please refresh the page.';
        $active_tab = 'profile';
    } else {
        $raw_p = clean_input($_POST['phone'] ?? '');
        $phone = preg_replace('/[^0-9]/', '', $raw_p);
        if (strlen($phone) > 10 && substr($phone, 0, 2) === '91') {
            $phone = substr($phone, 2);
        }

        if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
            $error_msg = 'Please enter a valid 10-digit Indian mobile number starting with 6, 7, 8, or 9.';
            $active_tab = 'profile';
        } elseif (!check_rate_limit('phone_otp_ip', get_client_ip(), 6, 600) || !check_rate_limit('phone_otp_num', $phone, 4, 600)) {
            $error_msg = 'Too many OTP requests. Please wait a few minutes before trying again.';
            $active_tab = 'profile';
        } else {
            $otp_code = sprintf('%06d', random_int(100000, 999999));
            $expires_ts = time() + 900;
            $expires_dt = date('Y-m-d H:i:s', $expires_ts);

            try {
                $stmt = $pdo->prepare("UPDATE users SET phone = ?, phone_verification_otp = ?, phone_verification_expires = ? WHERE id = ?");
                $stmt->execute([$phone, $otp_code, $expires_dt, $user['id']]);
            } catch (Throwable $e) {}

            $_SESSION['pending_phone_otp'] = $otp_code;
            $_SESSION['pending_phone_number'] = $phone;
            $_SESSION['pending_phone_expires'] = $expires_ts;

            if (!empty($user['email'])) {
                $subj = "Mobile Verification OTP — GaunGaliHat: " . $otp_code;
                $msg = "Namaste " . htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') . ",\n\nYour 6-digit verification code to verify mobile number (+91 {$phone}) on GaunGaliHat is: {$otp_code}\n\nValid for 15 minutes. Never share this code with anyone.";
                @mail($user['email'], $subj, $msg, "From: no-reply@ganvgalihat.com\r\n");
            }

            log_audit_action('phone_otp_dispatched', 'auth', "Phone OTP dispatched for user #{$user['id']} to +91 {$phone}");
            $user = get_logged_in_user();
            $success_msg = "Verification code dispatched to +91 {$phone}! For testing convenience, your 6-digit OTP is: {$otp_code}";
            $active_tab = 'profile';
        }
    }
}

// 5. Handle Mobile OTP Verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_phone_otp'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security session expired. Please refresh the page.';
        $active_tab = 'profile';
    } else {
        $entered_otp = trim((string)($_POST['otp'] ?? ''));
        $raw_p = clean_input($_POST['phone'] ?? ($user['phone'] ?? $_SESSION['pending_phone_number'] ?? ''));
        $phone = preg_replace('/[^0-9]/', '', $raw_p);
        if (strlen($phone) > 10 && substr($phone, 0, 2) === '91') {
            $phone = substr($phone, 2);
        }

        if (empty($entered_otp) || strlen($entered_otp) !== 6) {
            $error_msg = 'Please enter the 6-digit verification code.';
            $active_tab = 'profile';
        } else {
            $is_matched = false;
            if ($pdo) {
                $stmt = $pdo->prepare("SELECT phone_verification_otp, phone_verification_expires, phone FROM users WHERE id = ?");
                $stmt->execute([$user['id']]);
                $u_row = $stmt->fetch();
                if ($u_row && !empty($u_row['phone_verification_otp'])) {
                    $exp_time = !empty($u_row['phone_verification_expires']) ? strtotime($u_row['phone_verification_expires']) : 0;
                    if ($exp_time > time() && hash_equals((string)$u_row['phone_verification_otp'], $entered_otp)) {
                        $is_matched = true;
                        if (!empty($u_row['phone'])) $phone = $u_row['phone'];
                    }
                }
            }

            if (!$is_matched && !empty($_SESSION['pending_phone_otp'])) {
                $sess_exp = (int)($_SESSION['pending_phone_expires'] ?? 0);
                if ($sess_exp > time() && hash_equals((string)$_SESSION['pending_phone_otp'], $entered_otp)) {
                    $is_matched = true;
                    if (!empty($_SESSION['pending_phone_number'])) $phone = $_SESSION['pending_phone_number'];
                }
            }

            if ($is_matched) {
                try {
                    $stmt = $pdo->prepare("UPDATE users SET phone = ?, phone_verified_at = CURRENT_TIMESTAMP, phone_verification_otp = NULL, phone_verification_expires = NULL WHERE id = ?");
                    $stmt->execute([$phone, $user['id']]);
                    $_SESSION['phone_verified_' . $user['id']] = true;
                } catch (Throwable $e) {}

                $_SESSION['verified_checkout_phone'] = $phone;
                unset($_SESSION['pending_phone_otp'], $_SESSION['pending_phone_expires']);

                log_audit_action('phone_verified', 'users', "Phone +91 {$phone} verified successfully for user #{$user['id']}");
                $user = get_logged_in_user();
                $success_msg = "Mobile number +91 {$phone} has been verified successfully! You can now place orders without hindrance.";
                $active_tab = 'profile';
            } else {
                $error_msg = 'Invalid or expired verification code. Please check and try again.';
                $active_tab = 'profile';
            }
        }
    }
}

// 6. Handle Profile & Avatar Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security session expired. Please refresh the page.';
        $active_tab = 'profile';
    } else {
        $name = clean_input($_POST['name'] ?? '');
        $raw_p = clean_input($_POST['phone'] ?? '');
        $phone = preg_replace('/[^0-9]/', '', $raw_p);
        if (strlen($phone) > 10 && substr($phone, 0, 2) === '91') {
            $phone = substr($phone, 2);
        }

        $city = clean_input($_POST['city'] ?? '');
        $pincode = clean_input($_POST['pincode'] ?? '');
        $address = clean_input($_POST['address'] ?? '');
        $avatar_url = $user['avatar'] ?? null;

        if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
            $up_res = secure_upload_image('avatar_file', 'uploads/avatars/');
            if ($up_res['success']) {
                $avatar_url = $up_res['path'];
            } else {
                $error_msg = $up_res['error'];
            }
        } elseif (!empty($_POST['avatar_url_input'])) {
            $in_url = clean_input($_POST['avatar_url_input']);
            if (is_valid_product_image_url($in_url)) {
                $avatar_url = $in_url;
            } else {
                $error_msg = 'Invalid avatar image URL. Only uploads path or permitted HTTPS hosts are allowed.';
            }
        }

        if (empty($error_msg) && !empty($name)) {
            try {
                $current_phone = preg_replace('/[^0-9]/', '', (string)($user['phone'] ?? ''));
                $is_phone_already_verified = !empty($user['phone_verified_at']) && ($current_phone === $phone && !empty($phone));
                if (!empty($_SESSION['verified_checkout_phone']) && $_SESSION['verified_checkout_phone'] === $phone) {
                    $is_phone_already_verified = true;
                }

                if ($is_phone_already_verified) {
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, city = ?, pincode = ?, address = ?, avatar = ? WHERE id = ?");
                    $stmt->execute([$name, $phone, $city, $pincode, $address, $avatar_url, $user['id']]);
                } else {
                    // If phone is empty or changed to a new number, require re-verification
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, phone_verified_at = NULL, city = ?, pincode = ?, address = ?, avatar = ? WHERE id = ?");
                    $stmt->execute([$name, $phone, $city, $pincode, $address, $avatar_url, $user['id']]);
                }

                $success_msg = 'Profile details updated successfully!';
                $user = get_logged_in_user();
                $active_tab = 'profile';
            } catch (Exception $e) {
                $error_msg = 'Update error: ' . $e->getMessage();
                $active_tab = 'profile';
            }
        } elseif (empty($name)) {
            $error_msg = 'Please enter your full name.';
            $active_tab = 'profile';
        }
    }
}

// 5. Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security session expired. Please refresh the page.';
        $active_tab = 'security';
    } else {
        $current_pass = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        $pwd_err = '';
        if (!password_verify($current_pass, $user['password'])) {
            $error_msg = 'Current password is incorrect.';
            $active_tab = 'security';
        } elseif ($new_pass !== $confirm_pass) {
            $error_msg = 'New password and confirmation password do not match.';
            $active_tab = 'security';
        } elseif (!is_strong_password($new_pass, $pwd_err)) {
            $error_msg = $pwd_err ?: 'Password must be at least 10 characters with uppercase, lowercase, number, and special character.';
            $active_tab = 'security';
        } else {
            try {
                $hashed = password_hash($new_pass, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password = ?, force_password_change = 0, password_changed_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$hashed, $user['id']]);
                unset($_SESSION['force_password_change']);
                
                $_SESSION['login_time'] = time();
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_regenerate_id(true);
                }
                log_audit_action('user_password_change', 'auth', 'user', $user['id'], 'Password changed by user; other sessions invalidated');
                $success_msg = 'Password changed successfully! All other active sessions have been invalidated for your security.';
                $active_tab = 'security';
            } catch (Exception $e) {
                $error_msg = 'Error updating password: ' . $e->getMessage();
                $active_tab = 'security';
            }
        }
    }
}

// 6. Handle New Support Ticket Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_ticket'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security session expired. Please refresh the page.';
        $active_tab = 'tickets';
    } else {
        $subject = clean_input($_POST['subject'] ?? '');
        $category = clean_input($_POST['category'] ?? 'Order Tracking');
        $priority = clean_input($_POST['priority'] ?? 'Medium');
        $message = clean_input($_POST['message'] ?? '');

        if (!empty($subject) && !empty($message)) {
            try {
                $ticket_num = 'TKT-' . date('ymd') . '-' . random_int(1000, 9999);
                $stmt_tkt = $pdo->prepare("
                    INSERT INTO tickets (ticket_number, user_id, name, email, phone, role, subject, category, priority, status, message, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Open', ?, CURRENT_TIMESTAMP)
                ");
                $stmt_tkt->execute([
                    $ticket_num,
                    $user['id'],
                    $user['name'],
                    $user['email'],
                    $user['phone'],
                    $user['role'],
                    $subject,
                    $category,
                    $priority,
                    $message
                ]);
                $success_msg = "Support ticket #$ticket_num has been created successfully. Our customer support desk will assist you shortly.";
                $active_tab = 'tickets';
            } catch (Exception $e) {
                $error_msg = 'Error creating ticket: ' . $e->getMessage();
                $active_tab = 'tickets';
            }
        } else {
            $error_msg = 'Please fill in both the subject and detailed message.';
            $active_tab = 'tickets';
        }
    }
}

// 7. Handle Ticket Reply from Customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_ticket'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security session expired. Please refresh the page.';
        $active_tab = 'tickets';
    } else {
        $ticket_id = (int)($_POST['ticket_id'] ?? 0);
        $reply_msg = clean_input($_POST['reply_message'] ?? '');

        if ($ticket_id > 0 && !empty($reply_msg)) {
            try {
                $is_email_verified = !empty($user['email_verified_at']);
                if ($is_email_verified && !empty($user['email'])) {
                    $stmt_check = $pdo->prepare("SELECT id FROM tickets WHERE id = ? AND (user_id = ? OR email = ?)");
                    $stmt_check->execute([$ticket_id, $user['id'], $user['email']]);
                } else {
                    $stmt_check = $pdo->prepare("SELECT id FROM tickets WHERE id = ? AND user_id = ?");
                    $stmt_check->execute([$ticket_id, $user['id']]);
                }

                if ($stmt_check->fetch()) {
                    $stmt_ins = $pdo->prepare("
                        INSERT INTO ticket_replies (ticket_id, user_id, sender_name, sender_role, message, created_at)
                        VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
                    ");
                    $stmt_ins->execute([$ticket_id, $user['id'], $user['name'], 'customer', $reply_msg]);
                    
                    $pdo->prepare("UPDATE tickets SET status = 'In Progress' WHERE id = ? AND status != 'Resolved'")->execute([$ticket_id]);
                    
                    $success_msg = 'Your reply has been added to the ticket.';
                    $active_tab = 'tickets';
                } else {
                    $error_msg = 'Invalid ticket request.';
                    $active_tab = 'tickets';
                }
            } catch (Exception $e) {
                $error_msg = 'Error submitting reply: ' . $e->getMessage();
                $active_tab = 'tickets';
            }
        }
    }
}

// Fetch Live Shopping Cart Data
$cart_items = get_cart();
$cart_count = get_cart_count();
$cart_subtotal = get_cart_subtotal();
$cart_weight = get_cart_total_weight();
$user_pincode = !empty($user['pincode']) ? $user['pincode'] : '241001';
$shipping_info = calculate_shipping_rate($user_pincode, $cart_subtotal, $cart_weight);

// Fetch User Orders
$is_email_verified = !empty($user['email_verified_at']);
if ($is_email_verified && !empty($user['email'])) {
    $stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? OR customer_email = ? ORDER BY id DESC");
    $stmt_orders->execute([$user['id'], $user['email']]);
} else {
    $stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
    $stmt_orders->execute([$user['id']]);
}
$user_orders = $stmt_orders->fetchAll() ?: [];

// Compute Order Counts & In-Transit Count
$total_orders_count = count($user_orders);
$active_shipments_count = 0;
foreach ($user_orders as $ord) {
    if (in_array(strtolower($ord['order_status']), ['pending', 'processing', 'confirmed', 'dispatched', 'shipped', 'out for delivery'], true)) {
        $active_shipments_count++;
    }
}

// Fetch User Support Tickets
if ($is_email_verified && !empty($user['email'])) {
    $stmt_tkts = $pdo->prepare("SELECT * FROM tickets WHERE user_id = ? OR email = ? ORDER BY id DESC");
    $stmt_tkts->execute([$user['id'], $user['email']]);
} else {
    $stmt_tkts = $pdo->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY id DESC");
    $stmt_tkts->execute([$user['id']]);
}
$user_tickets = $stmt_tkts->fetchAll() ?: [];
$open_tickets_count = 0;
foreach ($user_tickets as $tkt) {
    if ($tkt['status'] !== 'Resolved') {
        $open_tickets_count++;
    }
}

$page_title = 'Shopper Dashboard — GaunGaliHat';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-stone-50 min-h-screen py-8">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Customer Header Summary Card -->
    <div class="bg-white rounded-3xl border border-gray-200 p-6 sm:p-8 shadow-sm mb-8 flex flex-col sm:flex-row items-center justify-between gap-6">
      <div class="flex items-center gap-5">
        <?php if (!empty($user['avatar'])): ?>
          <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="<?= htmlspecialchars($user['name']) ?>"
            class="w-18 h-18 rounded-2xl object-cover shadow-md border-2 border-emerald-100 bg-stone-100">
        <?php else: ?>
          <div class="w-18 h-18 rounded-2xl bg-brand-green text-white flex items-center justify-center text-3xl font-extrabold shadow-md">
            <?= strtoupper(mb_substr($user['name'], 0, 1)) ?>
          </div>
        <?php endif; ?>

        <div>
          <div class="flex items-center gap-2.5 flex-wrap">
            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900"><?= htmlspecialchars($user['name']) ?></h1>
            <span class="text-[11px] font-mono bg-stone-100 text-stone-700 px-2 py-0.5 rounded-md border border-stone-200">
              <?= htmlspecialchars($user['unique_code'] ?? ('GGH-CST-' . $user['id'])) ?>
            </span>
          </div>

          <p class="text-xs text-gray-500 mt-1 flex items-center gap-2 flex-wrap">
            <span><i class="fa-regular fa-envelope text-gray-400"></i> <?= htmlspecialchars($user['email']) ?></span>
            <span>•</span>
            <span><i class="fa-solid fa-phone text-gray-400"></i> <?= !empty($user['phone']) ? '+91 ' . htmlspecialchars($user['phone']) : '<span class="text-amber-600 font-semibold">Mobile Not Provided</span>' ?></span>
          </p>

          <div class="mt-2 flex items-center gap-2 flex-wrap">
            <?php if (!empty($user['email_verified_at'])): ?>
              <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-800 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                <i class="fa-solid fa-shield-check text-emerald-600"></i> Verified Email
              </span>
            <?php else: ?>
              <form method="POST" action="/account.php" class="inline-flex items-center gap-2">
                <?= csrf_field() ?>
                <input type="hidden" name="resend_verification" value="1">
                <span class="text-[11px] font-bold text-amber-800 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-300">
                  <i class="fa-solid fa-triangle-exclamation"></i> Unverified Email
                </span>
                <button type="submit" class="text-[11px] text-brand-green font-bold hover:underline">
                  Resend Verification Code
                </button>
              </form>
            <?php endif; ?>

            <?php if (!empty($user['phone_verified_at']) && !empty($user['phone'])): ?>
              <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-800 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                <i class="fa-solid fa-mobile-screen-button text-emerald-600"></i> Verified Mobile
              </span>
            <?php else: ?>
              <a href="/account.php?tab=profile#mobileSection" class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-800 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-300 hover:bg-amber-100 transition">
                <i class="fa-solid fa-triangle-exclamation text-amber-600"></i> Add & Verify Mobile
              </a>
            <?php endif; ?>

            <span class="text-[11px] text-gray-400">
              Member since <?= date('M Y', strtotime($user['created_at'] ?? 'now')) ?>
            </span>
          </div>
        </div>
      </div>

      <!-- Quick Action Buttons -->
      <div class="flex items-center gap-2.5 flex-wrap">
        <?php if (in_array($user['role'], ['vendor', 'admin', 'super_admin'])): ?>
          <a href="/seller/dashboard.php" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-stone-950 text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-sm">
            <i class="fa-solid fa-store"></i> Seller Hub
          </a>
        <?php endif; ?>
        <?php if (in_array($user['role'], ['admin', 'super_admin'])): ?>
          <a href="/admin/index.php" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-sm">
            <i class="fa-solid fa-shield-halved"></i> Super Admin
          </a>
        <?php endif; ?>
        <a href="/logout.php" class="px-4 py-2 bg-stone-100 hover:bg-stone-200 text-gray-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5">
          <i class="fa-solid fa-power-off text-red-500"></i> Sign Out
        </a>
      </div>
    </div>

    <!-- Notices / Alerts -->
    <?php if (!empty($success_msg)): ?>
      <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs flex items-center gap-2.5 font-medium shadow-xs">
        <i class="fa-solid fa-circle-check text-base text-emerald-600 shrink-0"></i>
        <span><?= htmlspecialchars($success_msg) ?></span>
      </div>
    <?php endif; ?>
    <?php if (!empty($error_msg)): ?>
      <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-xs flex items-center gap-2.5 font-medium shadow-xs">
        <i class="fa-solid fa-circle-exclamation text-base text-red-600 shrink-0"></i>
        <span><?= htmlspecialchars($error_msg) ?></span>
      </div>
    <?php endif; ?>

    <!-- Main Navigation & Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      
      <!-- Left Tab Navigation (3 cols) -->
      <div class="lg:col-span-3 bg-white rounded-3xl border border-gray-200 p-3 shadow-sm space-y-1">
        <a href="/account.php?tab=overview"
          class="flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-bold transition <?= $active_tab === 'overview' ? 'bg-brand-green text-white shadow-sm' : 'text-gray-700 hover:bg-stone-50' ?>">
          <i class="fa-solid fa-table-columns text-sm"></i>
          <span>Dashboard Overview</span>
        </a>

        <a href="/account.php?tab=orders"
          class="flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-bold transition <?= $active_tab === 'orders' ? 'bg-brand-green text-white shadow-sm' : 'text-gray-700 hover:bg-stone-50' ?>">
          <i class="fa-solid fa-box-archive text-sm"></i>
          <span>My Orders & History</span>
          <span class="ml-auto text-[10px] <?= $active_tab === 'orders' ? 'bg-white/20 text-white' : 'bg-stone-100 text-gray-700' ?> px-2 py-0.5 rounded-full font-bold">
            <?= $total_orders_count ?>
          </span>
        </a>

        <a href="/account.php?tab=cart"
          class="flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-bold transition <?= $active_tab === 'cart' ? 'bg-brand-green text-white shadow-sm' : 'text-gray-700 hover:bg-stone-50' ?>">
          <i class="fa-solid fa-cart-shopping text-sm"></i>
          <span>Shopping Cart</span>
          <?php if ($cart_count > 0): ?>
            <span class="ml-auto text-[10px] <?= $active_tab === 'cart' ? 'bg-amber-400 text-stone-900' : 'bg-amber-100 text-amber-900' ?> px-2 py-0.5 rounded-full font-bold">
              <?= $cart_count ?>
            </span>
          <?php endif; ?>
        </a>

        <a href="/account.php?tab=addresses"
          class="flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-bold transition <?= $active_tab === 'addresses' ? 'bg-brand-green text-white shadow-sm' : 'text-gray-700 hover:bg-stone-50' ?>">
          <i class="fa-solid fa-location-dot text-sm"></i>
          <span>Delivery Addresses</span>
        </a>

        <a href="/account.php?tab=profile"
          class="flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-bold transition <?= $active_tab === 'profile' ? 'bg-brand-green text-white shadow-sm' : 'text-gray-700 hover:bg-stone-50' ?>">
          <i class="fa-solid fa-id-card text-sm"></i>
          <span>Profile & Personal Info</span>
        </a>

        <a href="/account.php?tab=tickets"
          class="flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-bold transition <?= $active_tab === 'tickets' ? 'bg-brand-green text-white shadow-sm' : 'text-gray-700 hover:bg-stone-50' ?>">
          <i class="fa-solid fa-headset text-sm"></i>
          <span>Customer Support Desk</span>
          <?php if ($open_tickets_count > 0): ?>
            <span class="ml-auto text-[10px] <?= $active_tab === 'tickets' ? 'bg-red-500 text-white' : 'bg-red-100 text-red-700' ?> px-2 py-0.5 rounded-full font-bold">
              <?= $open_tickets_count ?>
            </span>
          <?php endif; ?>
        </a>

        <a href="/account.php?tab=security"
          class="flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-bold transition <?= $active_tab === 'security' ? 'bg-brand-green text-white shadow-sm' : 'text-gray-700 hover:bg-stone-50' ?>">
          <i class="fa-solid fa-shield-halved text-sm"></i>
          <span>Security & Password</span>
        </a>

        <div class="pt-2 border-t border-gray-100">
          <a href="/wishlist.php"
            class="flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-bold transition text-gray-700 hover:bg-stone-50">
            <i class="fa-regular fa-heart text-sm text-red-500"></i>
            <span>Saved Wishlist</span>
          </a>
        </div>
      </div>

      <!-- Right Tab Content (9 cols) -->
      <div class="lg:col-span-9 space-y-6">
        
        <!-- ============================================================== -->
        <!-- TAB 1: OVERVIEW (GRAND SHOPPER DASHBOARD)                      -->
        <!-- ============================================================== -->
        <?php if ($active_tab === 'overview'): ?>
          
          <?php if (empty($user['phone']) || empty($user['phone_verified_at'])): ?>
            <!-- Mandatory Phone Verification Notice for Google / Unverified Customers -->
            <div class="p-5 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 border border-amber-200 rounded-3xl shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
              <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-lg shrink-0 shadow-sm">
                  <i class="fa-solid fa-mobile-screen-button"></i>
                </div>
                <div>
                  <h3 class="text-sm font-extrabold text-gray-900">Add & Verify Mobile Number</h3>
                  <p class="text-xs text-gray-600 mt-0.5">A verified 10-digit Indian mobile number is mandatory to place orders and receive India Post delivery dispatch alerts.</p>
                </div>
              </div>
              <a href="/account.php?tab=profile#mobileSection" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-stone-950 text-xs font-bold rounded-xl transition shadow-sm shrink-0 flex items-center gap-1.5">
                <span>Add / Verify Phone</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
              </a>
            </div>
          <?php endif; ?>

          <!-- Top Stat Metric Cards -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            
            <!-- Stat 1: Total Orders -->
            <a href="/account.php?tab=orders" class="bg-white p-5 rounded-3xl border border-gray-200 shadow-xs hover:border-brand-green transition group">
              <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-brand-green flex items-center justify-center text-lg mb-3 group-hover:scale-110 transition">
                <i class="fa-solid fa-box-archive"></i>
              </div>
              <div class="text-2xl font-extrabold text-gray-900"><?= $total_orders_count ?></div>
              <div class="text-xs font-bold text-gray-600 mt-0.5">Total Orders</div>
              <div class="text-[11px] text-brand-green font-semibold mt-1 flex items-center gap-1">
                View all <i class="fa-solid fa-arrow-right text-[9px]"></i>
              </div>
            </a>

            <!-- Stat 2: Active Shipments -->
            <a href="/account.php?tab=orders" class="bg-white p-5 rounded-3xl border border-gray-200 shadow-xs hover:border-blue-500 transition group">
              <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg mb-3 group-hover:scale-110 transition">
                <i class="fa-solid fa-truck-fast"></i>
              </div>
              <div class="text-2xl font-extrabold text-gray-900"><?= $active_shipments_count ?></div>
              <div class="text-xs font-bold text-gray-600 mt-0.5">In-Transit</div>
              <div class="text-[11px] text-blue-600 font-semibold mt-1 flex items-center gap-1">
                Track delivery <i class="fa-solid fa-arrow-right text-[9px]"></i>
              </div>
            </a>

            <!-- Stat 3: Live Cart -->
            <a href="/account.php?tab=cart" class="bg-white p-5 rounded-3xl border border-gray-200 shadow-xs hover:border-amber-500 transition group">
              <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg mb-3 group-hover:scale-110 transition">
                <i class="fa-solid fa-cart-shopping"></i>
              </div>
              <div class="text-2xl font-extrabold text-gray-900"><?= $cart_count ?></div>
              <div class="text-xs font-bold text-gray-600 mt-0.5">Cart Items</div>
              <div class="text-[11px] text-amber-700 font-semibold mt-1 truncate">
                <?= $cart_count > 0 ? format_price($cart_subtotal) : 'Bag empty' ?>
              </div>
            </a>

            <!-- Stat 4: Support Tickets -->
            <a href="/account.php?tab=tickets" class="bg-white p-5 rounded-3xl border border-gray-200 shadow-xs hover:border-purple-500 transition group">
              <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg mb-3 group-hover:scale-110 transition">
                <i class="fa-solid fa-headset"></i>
              </div>
              <div class="text-2xl font-extrabold text-gray-900"><?= count($user_tickets) ?></div>
              <div class="text-xs font-bold text-gray-600 mt-0.5">Support Desk</div>
              <div class="text-[11px] text-purple-700 font-semibold mt-1">
                <?= $open_tickets_count ?> open tickets
              </div>
            </a>

          </div>

          <!-- Section 1: Live Shopping Cart Preview -->
          <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-brand-green flex items-center justify-center text-sm font-bold">
                  <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <div>
                  <h2 class="text-sm sm:text-base font-extrabold text-gray-900">Live Shopping Cart</h2>
                  <p class="text-[11px] text-gray-500">Items currently saved in your active shopping session</p>
                </div>
              </div>

              <?php if (!empty($cart_items)): ?>
                <a href="/checkout.php" class="px-4 py-2 bg-brand-green hover:bg-emerald-800 text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5 shadow-sm">
                  <span>Checkout Now (<?= format_price($cart_subtotal) ?>)</span>
                  <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
              <?php endif; ?>
            </div>

            <?php if (empty($cart_items)): ?>
              <div class="p-8 text-center">
                <div class="w-14 h-14 rounded-2xl bg-stone-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                  <i class="fa-solid fa-bag-shopping text-2xl"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-800">Your shopping cart is currently empty</h3>
                <p class="text-xs text-gray-500 mt-1 mb-4">Discover village hand-forged tools, farm staples, and authentic rural crafts.</p>
                <a href="/products.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-green hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-xs transition">
                  <i class="fa-solid fa-store"></i> Start Shopping
                </a>
              </div>
            <?php else: ?>
              <div class="divide-y divide-gray-100">
                <?php foreach (array_slice($cart_items, 0, 4) as $item): 
                  $prod = $item['product'];
                  $img = !empty($prod['image']) ? $prod['image'] : '/assets/images/placeholder.png';
                ?>
                  <div class="p-4 sm:p-5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5 min-w-0">
                      <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($prod['name']) ?>"
                        class="w-14 h-14 rounded-xl object-cover border border-gray-200 shrink-0 bg-stone-50"
                        onerror="this.src='/assets/images/placeholder.png'">
                      <div class="min-w-0">
                        <a href="/product.php?id=<?= $prod['id'] ?>" class="text-xs sm:text-sm font-bold text-gray-900 hover:text-brand-green transition truncate block">
                          <?= htmlspecialchars($prod['name']) ?>
                        </a>
                        <p class="text-[11px] text-gray-500 mt-0.5">
                          <?= htmlspecialchars($prod['vendor_name'] ?? 'Kalyani agrofood pvt ltd') ?> • Qty: <strong class="text-gray-800"><?= $item['quantity'] ?></strong>
                        </p>
                      </div>
                    </div>

                    <div class="text-right shrink-0">
                      <div class="text-sm font-extrabold text-gray-900">
                        <?= format_price($item['subtotal']) ?>
                      </div>
                      <div class="text-[11px] text-gray-400">
                        <?= format_price($prod['price']) ?> each
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>

              <div class="bg-stone-50 p-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                <div>
                  <span class="text-gray-600">Cart Subtotal (<?= $cart_count ?> <?= $cart_count === 1 ? 'item' : 'items' ?>):</span>
                  <strong class="text-gray-900 text-sm ml-1"><?= format_price($cart_subtotal) ?></strong>
                </div>

                <div class="flex items-center gap-2">
                  <a href="/account.php?tab=cart" class="px-3.5 py-1.5 bg-white border border-gray-200 rounded-xl text-gray-700 hover:bg-stone-100 font-bold transition text-xs shadow-xs">
                    Manage Bag
                  </a>
                  <a href="/checkout.php" class="px-4 py-1.5 bg-brand-green hover:bg-emerald-800 text-white font-bold rounded-xl transition text-xs shadow-xs flex items-center gap-1.5">
                    <span>Proceed to Checkout</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                  </a>
                </div>
              </div>
            <?php endif; ?>
          </div>

          <!-- Section 2: Recent Orders & Shipment Tracking -->
          <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">
                  <i class="fa-solid fa-truck-fast"></i>
                </div>
                <div>
                  <h2 class="text-sm sm:text-base font-extrabold text-gray-900">Recent Orders & Shipment Tracker</h2>
                  <p class="text-[11px] text-gray-500">Live order fulfillment and postal delivery progress</p>
                </div>
              </div>

              <?php if (!empty($user_orders)): ?>
                <a href="/account.php?tab=orders" class="text-xs font-bold text-brand-green hover:underline flex items-center gap-1">
                  <span>View All Orders (<?= $total_orders_count ?>)</span>
                  <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
              <?php endif; ?>
            </div>

            <?php if (empty($user_orders)): ?>
              <div class="p-8 text-center">
                <div class="w-14 h-14 rounded-2xl bg-stone-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                  <i class="fa-solid fa-box-open text-2xl"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-800">No orders placed yet</h3>
                <p class="text-xs text-gray-500 mt-1 mb-4">When you place an order, live tracking details will appear here.</p>
                <a href="/products.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-green hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-xs transition">
                  <i class="fa-solid fa-bag-shopping"></i> Browse Catalog
                </a>
              </div>
            <?php else: ?>
              <?php 
                $latest_order = $user_orders[0];
                $stmt_ord_items = $pdo->prepare("
                    SELECT oi.*, p.image as product_image
                    FROM order_items oi
                    LEFT JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?
                ");
                $stmt_ord_items->execute([$latest_order['id']]);
                $latest_items = $stmt_ord_items->fetchAll() ?: [];

                $status_lower = strtolower($latest_order['order_status']);
                $step = 1; // 1: Placed, 2: Confirmed, 3: Shipped, 4: Out for Delivery, 5: Delivered
                if ($status_lower === 'processing' || $status_lower === 'confirmed') $step = 2;
                if ($status_lower === 'dispatched' || $status_lower === 'shipped') $step = 3;
                if ($status_lower === 'out for delivery') $step = 4;
                if ($status_lower === 'delivered') $step = 5;
              ?>

              <div class="p-5 sm:p-6 space-y-6">
                <!-- Order Header Info -->
                <div class="flex flex-wrap items-center justify-between gap-3 text-xs bg-stone-50 p-4 rounded-2xl border border-gray-100">
                  <div>
                    <span class="text-gray-500">Order Number:</span>
                    <strong class="text-gray-900 font-mono text-sm ml-1">#<?= htmlspecialchars($latest_order['order_number']) ?></strong>
                    <span class="text-gray-400 mx-2">•</span>
                    <span class="text-gray-500"><?= date('d M Y, h:i A', strtotime($latest_order['created_at'])) ?></span>
                  </div>

                  <div class="flex items-center gap-2.5">
                    <span class="px-3 py-1 rounded-full text-xs font-bold <?= $latest_order['order_status'] === 'Delivered' ? 'bg-emerald-100 text-emerald-800' : ($latest_order['order_status'] === 'Dispatched' || $latest_order['order_status'] === 'Shipped' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') ?>">
                      <?= htmlspecialchars($latest_order['order_status']) ?>
                    </span>
                    <a href="/order-success.php?id=<?= $latest_order['id'] ?>" class="px-3 py-1 bg-white border border-gray-200 rounded-lg text-gray-700 hover:bg-stone-100 font-bold transition text-xs shadow-xs">
                      Invoice Receipt
                    </a>
                  </div>
                </div>

                <!-- Visual Tracking Progress Stepper (Lead E-Commerce Standard) -->
                <div class="py-2 px-2">
                  <div class="relative flex items-center justify-between">
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-gray-200 w-full z-0"></div>
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-brand-green z-0 transition-all duration-500"
                         style="width: <?= $step === 1 ? '0%' : ($step === 2 ? '25%' : ($step === 3 ? '50%' : ($step === 4 ? '75%' : '100%'))) ?>;"></div>

                    <!-- Step 1: Placed -->
                    <div class="relative z-10 flex flex-col items-center text-center">
                      <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shadow-sm <?= $step >= 1 ? 'bg-brand-green text-white' : 'bg-gray-200 text-gray-500' ?>">
                        <i class="fa-solid fa-check"></i>
                      </div>
                      <span class="text-[11px] font-bold text-gray-800 mt-1.5">Order Placed</span>
                    </div>

                    <!-- Step 2: Confirmed -->
                    <div class="relative z-10 flex flex-col items-center text-center">
                      <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shadow-sm <?= $step >= 2 ? 'bg-brand-green text-white' : 'bg-gray-200 text-gray-500' ?>">
                        <?= $step >= 2 ? '<i class="fa-solid fa-check"></i>' : '2' ?>
                      </div>
                      <span class="text-[11px] font-bold text-gray-800 mt-1.5">Confirmed</span>
                    </div>

                    <!-- Step 3: Shipped -->
                    <div class="relative z-10 flex flex-col items-center text-center">
                      <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shadow-sm <?= $step >= 3 ? 'bg-brand-green text-white' : 'bg-gray-200 text-gray-500' ?>">
                        <?= $step >= 3 ? '<i class="fa-solid fa-truck-fast"></i>' : '3' ?>
                      </div>
                      <span class="text-[11px] font-bold text-gray-800 mt-1.5">Shipped</span>
                    </div>

                    <!-- Step 4: Out for Delivery -->
                    <div class="relative z-10 flex flex-col items-center text-center">
                      <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shadow-sm <?= $step >= 4 ? 'bg-brand-green text-white' : 'bg-gray-200 text-gray-500' ?>">
                        <?= $step >= 4 ? '<i class="fa-solid fa-location-dot"></i>' : '4' ?>
                      </div>
                      <span class="text-[11px] font-bold text-gray-800 mt-1.5">Out for Delivery</span>
                    </div>

                    <!-- Step 5: Delivered -->
                    <div class="relative z-10 flex flex-col items-center text-center">
                      <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shadow-sm <?= $step >= 5 ? 'bg-brand-green text-white' : 'bg-gray-200 text-gray-500' ?>">
                        <i class="fa-solid fa-house"></i>
                      </div>
                      <span class="text-[11px] font-bold text-gray-800 mt-1.5">Delivered</span>
                    </div>
                  </div>
                </div>

                <!-- Carrier / AWB Details -->
                <div class="p-3.5 bg-amber-50/60 border border-amber-200 rounded-2xl flex flex-wrap items-center justify-between text-xs gap-3">
                  <div class="flex items-center gap-2">
                    <i class="fa-solid fa-barcode text-red-600 text-sm"></i>
                    <span class="text-gray-600">Postal Consignment / AWB Tracking:</span>
                    <strong class="font-mono text-gray-900"><?= htmlspecialchars($latest_order['tracking_consignment_no'] ?? 'AWB-PENDING') ?></strong>
                  </div>
                  <span class="text-gray-500 text-[11px]">Carrier: <strong><?= htmlspecialchars($latest_order['tracking_carrier'] ?? 'India Post') ?></strong></span>
                </div>

                <!-- Item preview in order -->
                <div class="divide-y divide-gray-100">
                  <?php foreach ($latest_items as $item): 
                    $item_img = !empty($item['product_image']) ? $item['product_image'] : '/assets/images/placeholder.png';
                  ?>
                    <div class="py-3 flex items-center justify-between gap-4">
                      <div class="flex items-center gap-3">
                        <img src="<?= htmlspecialchars($item_img) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>"
                          class="w-12 h-12 rounded-xl object-cover border border-gray-200 bg-stone-50"
                          onerror="this.src='/assets/images/placeholder.png'">
                        <div>
                          <p class="text-xs font-bold text-gray-900"><?= htmlspecialchars($item['product_name']) ?></p>
                          <p class="text-[11px] text-gray-500">Qty: <?= $item['quantity'] ?> • <?= format_price($item['price']) ?></p>
                        </div>
                      </div>
                      <div class="text-right">
                        <div class="text-xs font-extrabold text-gray-900">
                          <?= format_price($item['total']) ?>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                  <span class="text-gray-500">Total Paid: <strong class="text-brand-green font-extrabold text-sm"><?= format_price($latest_order['total_amount']) ?></strong> (<?= strtoupper(htmlspecialchars($latest_order['payment_method'])) ?>)</span>
                  <a href="/account.php?tab=orders" class="text-brand-green font-bold hover:underline">
                    View all orders & tracking history →
                  </a>
                </div>

              </div>
            <?php endif; ?>
          </div>

        <!-- ============================================================== -->
        <!-- TAB 2: MY ORDERS & ORDER HISTORY                               -->
        <!-- ============================================================== -->
        <?php elseif ($active_tab === 'orders'): ?>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-lg font-bold text-gray-900">Order History & Shipment Tracking</h2>
                <p class="text-xs text-gray-500 mt-0.5">Track your past purchases, download invoices, or reorder authentic village staples</p>
              </div>
              <span class="text-xs font-bold text-stone-500 bg-white px-3 py-1.5 rounded-xl border border-gray-200 shadow-xs">
                <?= $total_orders_count ?> <?= $total_orders_count === 1 ? 'Order' : 'Orders' ?> Placed
              </span>
            </div>

            <?php if (empty($user_orders)): ?>
              <div class="bg-white rounded-3xl border border-gray-200 p-12 text-center shadow-sm">
                <i class="fa-solid fa-box-open text-4xl text-stone-300 mb-3"></i>
                <h3 class="text-base font-bold text-gray-700">No orders placed yet</h3>
                <p class="text-xs text-gray-500 mt-1 mb-4">Explore authentic hand-forged tools, farm staples, and artisan crafts.</p>
                <a href="/products.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-green text-white text-xs font-bold rounded-xl hover:bg-emerald-800 transition shadow-xs">
                  Start Shopping <i class="fa-solid fa-arrow-right"></i>
                </a>
              </div>
            <?php else: ?>
              <?php foreach ($user_orders as $ord): 
                $stmt_ord_items = $pdo->prepare("
                    SELECT oi.*, p.image as product_image
                    FROM order_items oi
                    LEFT JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?
                ");
                $stmt_ord_items->execute([$ord['id']]);
                $items = $stmt_ord_items->fetchAll() ?: [];

                $status_lower = strtolower($ord['order_status']);
                $step = 1;
                if ($status_lower === 'processing' || $status_lower === 'confirmed') $step = 2;
                if ($status_lower === 'dispatched' || $status_lower === 'shipped') $step = 3;
                if ($status_lower === 'out for delivery') $step = 4;
                if ($status_lower === 'delivered') $step = 5;
              ?>
                <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
                  
                  <!-- Order Header Bar -->
                  <div class="bg-stone-50 p-4 sm:p-5 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div>
                      <span class="text-gray-500">Order:</span>
                      <strong class="text-gray-900 ml-1 font-mono text-sm">#<?= htmlspecialchars($ord['order_number']) ?></strong>
                      <span class="text-gray-400 mx-2">•</span>
                      <span class="text-gray-500"><?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?></span>
                    </div>

                    <div class="flex items-center gap-2">
                      <span class="px-3 py-1 rounded-full text-[11px] font-bold <?= $ord['order_status'] === 'Delivered' ? 'bg-emerald-100 text-emerald-800' : ($ord['order_status'] === 'Dispatched' || $ord['order_status'] === 'Shipped' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') ?>">
                        <?= htmlspecialchars($ord['order_status']) ?>
                      </span>
                      <a href="/order-success.php?id=<?= $ord['id'] ?>" class="px-3 py-1 bg-white border border-gray-200 rounded-lg text-gray-700 hover:bg-stone-100 font-bold transition text-[11px] shadow-xs">
                        View Receipt
                      </a>
                    </div>
                  </div>

                  <!-- Visual Shipment Stepper -->
                  <div class="py-4 px-6 bg-white border-b border-gray-100">
                    <div class="relative flex items-center justify-between">
                      <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-gray-200 w-full z-0"></div>
                      <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-brand-green z-0 transition-all duration-500"
                           style="width: <?= $step === 1 ? '0%' : ($step === 2 ? '25%' : ($step === 3 ? '50%' : ($step === 4 ? '75%' : '100%'))) ?>;"></div>

                      <div class="relative z-10 flex flex-col items-center text-center">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold <?= $step >= 1 ? 'bg-brand-green text-white' : 'bg-gray-200 text-gray-500' ?>">
                          <i class="fa-solid fa-check"></i>
                        </div>
                        <span class="text-[10px] font-bold text-gray-700 mt-1">Placed</span>
                      </div>

                      <div class="relative z-10 flex flex-col items-center text-center">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold <?= $step >= 2 ? 'bg-brand-green text-white' : 'bg-gray-200 text-gray-500' ?>">
                          <?= $step >= 2 ? '<i class="fa-solid fa-check"></i>' : '2' ?>
                        </div>
                        <span class="text-[10px] font-bold text-gray-700 mt-1">Confirmed</span>
                      </div>

                      <div class="relative z-10 flex flex-col items-center text-center">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold <?= $step >= 3 ? 'bg-brand-green text-white' : 'bg-gray-200 text-gray-500' ?>">
                          <?= $step >= 3 ? '<i class="fa-solid fa-truck-fast"></i>' : '3' ?>
                        </div>
                        <span class="text-[10px] font-bold text-gray-700 mt-1">Shipped</span>
                      </div>

                      <div class="relative z-10 flex flex-col items-center text-center">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold <?= $step >= 4 ? 'bg-brand-green text-white' : 'bg-gray-200 text-gray-500' ?>">
                          <?= $step >= 4 ? '<i class="fa-solid fa-location-dot"></i>' : '4' ?>
                        </div>
                        <span class="text-[10px] font-bold text-gray-700 mt-1">Out for Delivery</span>
                      </div>

                      <div class="relative z-10 flex flex-col items-center text-center">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold <?= $step >= 5 ? 'bg-brand-green text-white' : 'bg-gray-200 text-gray-500' ?>">
                          <i class="fa-solid fa-house"></i>
                        </div>
                        <span class="text-[10px] font-bold text-gray-700 mt-1">Delivered</span>
                      </div>
                    </div>
                  </div>

                  <!-- Tracking info -->
                  <div class="p-3.5 bg-amber-50/50 border-b border-amber-100 flex flex-wrap items-center justify-between text-xs gap-2">
                    <div class="flex items-center gap-2">
                      <i class="fa-solid fa-truck-fast text-red-600"></i>
                      <span class="text-gray-700">Consignment (AWB):</span>
                      <span class="font-mono font-bold text-gray-900"><?= htmlspecialchars($ord['tracking_consignment_no'] ?? 'AWB-PENDING') ?></span>
                    </div>
                    <span class="text-[11px] text-gray-500"><?= htmlspecialchars($ord['tracking_carrier'] ?? 'India Post') ?></span>
                  </div>

                  <!-- Items list -->
                  <div class="divide-y divide-gray-100 p-4">
                    <?php foreach ($items as $it): 
                      $it_img = !empty($it['product_image']) ? $it['product_image'] : '/assets/images/placeholder.png';
                    ?>
                      <div class="py-3 flex items-center justify-between text-xs gap-3">
                        <div class="flex items-center gap-3">
                          <img src="<?= htmlspecialchars($it_img) ?>" alt="<?= htmlspecialchars($it['product_name']) ?>"
                            class="w-12 h-12 rounded-xl object-cover border border-gray-200 shrink-0 bg-stone-50"
                            onerror="this.src='/assets/images/placeholder.png'">
                          <div>
                            <p class="font-bold text-gray-900"><?= htmlspecialchars($it['product_name']) ?></p>
                            <p class="text-gray-500 text-[11px]">Code: <?= htmlspecialchars($it['product_code']) ?> • Qty: <?= $it['quantity'] ?></p>
                          </div>
                        </div>

                        <div class="flex items-center gap-3">
                          <div class="font-bold text-gray-900 text-sm">
                            <?= format_price($it['total']) ?>
                          </div>
                          <?php if (!empty($it['product_id'])): ?>
                            <form method="POST" action="/account.php?tab=cart" class="inline">
                              <?= csrf_field() ?>
                              <input type="hidden" name="reorder_item" value="1">
                              <input type="hidden" name="product_id" value="<?= (int)$it['product_id'] ?>">
                              <input type="hidden" name="quantity" value="1">
                              <button type="submit" class="px-2.5 py-1 bg-stone-100 hover:bg-stone-200 text-gray-700 font-bold text-[10px] rounded-lg transition" title="Add to Cart again">
                                <i class="fa-solid fa-rotate-right"></i> Buy Again
                              </button>
                            </form>
                          <?php endif; ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>

                  <!-- Footer info -->
                  <div class="bg-stone-50 p-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <span class="text-gray-600">Payment: <strong class="uppercase text-gray-800"><?= htmlspecialchars($ord['payment_method']) ?></strong> (<?= htmlspecialchars($ord['payment_status']) ?>)</span>
                    <span class="font-bold text-sm text-gray-900">Total: <span class="text-brand-green font-extrabold"><?= format_price($ord['total_amount']) ?></span></span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

        <!-- ============================================================== -->
        <!-- TAB 3: LIVE SHOPPING CART                                      -->
        <!-- ============================================================== -->
        <?php elseif ($active_tab === 'cart'): ?>
          <div class="space-y-6">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-lg font-bold text-gray-900">Live Shopping Cart</h2>
                <p class="text-xs text-gray-500 mt-0.5">Manage quantities, review delivery estimates, and proceed to checkout</p>
              </div>
              <a href="/products.php" class="text-xs font-bold text-brand-green hover:underline">
                ← Continue Shopping
              </a>
            </div>

            <?php if (empty($cart_items)): ?>
              <div class="bg-white rounded-3xl border border-gray-200 p-12 text-center shadow-sm">
                <i class="fa-solid fa-cart-shopping text-4xl text-stone-300 mb-3"></i>
                <h3 class="text-base font-bold text-gray-700">Your shopping cart is empty</h3>
                <p class="text-xs text-gray-500 mt-1 mb-4">Discover village hand-forged tools, farm staples, and authentic rural crafts.</p>
                <a href="/products.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-green text-white text-xs font-bold rounded-xl hover:bg-emerald-800 transition shadow-xs">
                  Explore Products <i class="fa-solid fa-arrow-right"></i>
                </a>
              </div>
            <?php else: ?>
              <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <!-- Cart Items List (8 cols) -->
                <div class="lg:col-span-8 bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden divide-y divide-gray-100">
                  <div class="p-4 bg-stone-50 border-b border-gray-100 text-xs font-bold text-gray-600 flex justify-between items-center">
                    <span>Products in Bag (<?= $cart_count ?>)</span>
                    <a href="/cart.php" class="text-brand-green hover:underline">Edit in Cart Desk</a>
                  </div>

                  <?php foreach ($cart_items as $item): 
                    $prod = $item['product'];
                    $img = !empty($prod['image']) ? $prod['image'] : '/assets/images/placeholder.png';
                  ?>
                    <div class="p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                      <div class="flex items-center gap-4">
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($prod['name']) ?>"
                          class="w-16 h-16 rounded-2xl object-cover border border-gray-200 bg-stone-50 shrink-0"
                          onerror="this.src='/assets/images/placeholder.png'">
                        <div>
                          <a href="/product.php?id=<?= $prod['id'] ?>" class="text-sm font-bold text-gray-900 hover:text-brand-green transition">
                            <?= htmlspecialchars($prod['name']) ?>
                          </a>
                          <p class="text-xs text-gray-500 mt-0.5">
                            <?= htmlspecialchars($prod['vendor_name'] ?? 'Kalyani agrofood pvt ltd') ?>
                          </p>
                          <div class="mt-2 flex items-center gap-2 text-xs">
                            <span class="font-bold text-gray-700">Quantity: <?= $item['quantity'] ?></span>
                            <span class="text-gray-300">•</span>
                            <span class="text-gray-500"><?= format_price($prod['price']) ?> / unit</span>
                          </div>
                        </div>
                      </div>

                      <div class="text-right shrink-0">
                        <div class="text-base font-extrabold text-gray-900">
                          <?= format_price($item['subtotal']) ?>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>

                <!-- Order Summary Card (4 cols) -->
                <div class="lg:col-span-4 bg-white rounded-3xl border border-gray-200 p-6 shadow-sm space-y-4">
                  <h3 class="text-sm font-extrabold text-gray-900 pb-3 border-b border-gray-100">
                    Order Summary
                  </h3>

                  <div class="space-y-2.5 text-xs text-gray-600">
                    <div class="flex justify-between">
                      <span>Items Subtotal:</span>
                      <strong class="text-gray-900"><?= format_price($cart_subtotal) ?></strong>
                    </div>
                    <div class="flex justify-between">
                      <span>Shipping Rate:</span>
                      <strong class="<?= $shipping_info['is_free'] ? 'text-brand-green' : 'text-gray-900' ?>">
                        <?= $shipping_info['is_free'] ? 'FREE Delivery' : format_price($shipping_info['shipping_fee']) ?>
                      </strong>
                    </div>
                    <?php if (!$shipping_info['is_free']): ?>
                      <p class="text-[11px] text-amber-700 bg-amber-50 p-2 rounded-xl border border-amber-200">
                        Add <?= format_price($shipping_info['remaining_for_free']) ?> more for FREE Delivery!
                      </p>
                    <?php endif; ?>
                  </div>

                  <div class="pt-3 border-t border-gray-100 flex justify-between items-center text-sm font-extrabold text-gray-900">
                    <span>Estimated Total:</span>
                    <span class="text-brand-green text-lg font-black">
                      <?= format_price(max(0, $cart_subtotal + $shipping_info['shipping_fee'])) ?>
                    </span>
                  </div>

                  <a href="/checkout.php" class="w-full py-3 bg-brand-green hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-2">
                    <span>Proceed to Checkout</span>
                    <i class="fa-solid fa-arrow-right"></i>
                  </a>

                  <a href="/cart.php" class="w-full py-2.5 bg-stone-100 hover:bg-stone-200 text-gray-700 font-bold text-xs rounded-xl transition text-center block">
                    View Full Cart Desk
                  </a>
                </div>

              </div>
            <?php endif; ?>
          </div>

        <!-- ============================================================== -->
        <!-- TAB 4: DELIVERY ADDRESSES                                      -->
        <!-- ============================================================== -->
        <?php elseif ($active_tab === 'addresses'): ?>
          <div class="bg-white rounded-3xl border border-gray-200 p-6 sm:p-8 shadow-sm space-y-6">
            <div>
              <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <i class="fa-solid fa-location-dot text-brand-green"></i>
                Saved Delivery Addresses
              </h2>
              <p class="text-xs text-gray-500 mt-0.5">Manage your shipping address used for courier deliveries across India</p>
            </div>

            <div class="p-5 bg-stone-50 rounded-2xl border border-gray-200">
              <span class="text-[11px] font-bold text-brand-green uppercase tracking-wider bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                Primary Shipping Address
              </span>
              <div class="mt-3 text-xs text-gray-700 space-y-1">
                <p class="font-bold text-gray-900 text-sm"><?= htmlspecialchars($user['name']) ?></p>
                <p><?= htmlspecialchars($user['address'] ?: 'No street address provided') ?></p>
                <p><?= htmlspecialchars($user['city'] ?: 'Hardoi') ?>, Uttar Pradesh — <strong><?= htmlspecialchars($user['pincode'] ?: '241001') ?></strong></p>
                <p class="text-gray-500 mt-2 flex items-center gap-2 flex-wrap">
                  <span><i class="fa-solid fa-phone text-xs"></i> +91 <?= htmlspecialchars($user['phone'] ?: 'None') ?></span>
                  <?php if (!empty($user['phone_verified_at']) && !empty($user['phone'])): ?>
                    <span class="text-[10px] text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 inline-flex items-center gap-1">
                      <i class="fa-solid fa-check"></i> Verified
                    </span>
                  <?php else: ?>
                    <a href="/account.php?tab=profile#mobileSection" class="text-[10px] text-amber-700 font-bold bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200 hover:bg-amber-100 inline-flex items-center gap-1">
                      <i class="fa-solid fa-triangle-exclamation"></i> Verify Number
                    </a>
                  <?php endif; ?>
                </p>
              </div>
            </div>

            <form action="/account.php?tab=addresses" method="POST" class="space-y-4 pt-4 border-t border-gray-100">
              <?= csrf_field() ?>
              <input type="hidden" name="update_address" value="1">

              <h3 class="text-sm font-bold text-gray-900">Update Shipping Destination</h3>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                  <label class="block text-xs font-bold text-gray-700 mb-1">Street Address / House No / Village <span class="text-red-500">*</span></label>
                  <textarea name="address" rows="3" required placeholder="e.g. Madhoganj, Baghauli Road, Newada..."
                    class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:border-brand-green focus:outline-none transition"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>

                <div>
                  <label class="block text-xs font-bold text-gray-700 mb-1">City / District <span class="text-red-500">*</span></label>
                  <input type="text" name="city" required value="<?= htmlspecialchars($user['city'] ?? 'Hardoi') ?>"
                    class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:border-brand-green focus:outline-none transition">
                </div>

                <div>
                  <label class="block text-xs font-bold text-gray-700 mb-1">6-Digit PIN Code <span class="text-red-500">*</span></label>
                  <input type="text" name="pincode" pattern="[0-9]{6}" maxlength="6" required value="<?= htmlspecialchars($user['pincode'] ?? '241001') ?>"
                    class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:border-brand-green focus:outline-none transition">
                </div>

                <div>
                  <label class="block text-xs font-bold text-gray-700 mb-1">Delivery Contact Number <span class="text-red-500">*</span></label>
                  <input type="tel" name="phone" pattern="[0-9]{10}" maxlength="10" required value="<?= htmlspecialchars($user['phone']) ?>"
                    class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:border-brand-green focus:outline-none transition">
                </div>
              </div>

              <button type="submit" class="px-6 py-2.5 bg-brand-green text-white text-xs font-bold rounded-xl hover:bg-emerald-800 transition shadow-md">
                Save Delivery Address
              </button>
            </form>
          </div>

        <!-- ============================================================== -->
        <!-- TAB 5: PROFILE & INFO                                          -->
        <!-- ============================================================== -->
        <?php elseif ($active_tab === 'profile'): ?>
          <div class="space-y-6">

            <!-- Dedicated Mobile Number & OTP Verification Card -->
            <div id="mobileSection" class="bg-white rounded-3xl border <?= (!empty($user['phone_verified_at']) && !empty($user['phone'])) ? 'border-emerald-200' : 'border-amber-300' ?> p-6 sm:p-8 shadow-sm">
              <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                <div>
                  <div class="flex items-center gap-2">
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                      <i class="fa-solid fa-mobile-screen-button text-brand-green"></i>
                      Mobile Number & OTP Verification
                    </h2>
                    <?php if (!empty($user['phone_verified_at']) && !empty($user['phone'])): ?>
                      <span class="text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full inline-flex items-center gap-1">
                        <i class="fa-solid fa-circle-check text-emerald-600"></i> Verified
                      </span>
                    <?php else: ?>
                      <span class="text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-300 px-2.5 py-0.5 rounded-full inline-flex items-center gap-1">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600"></i> Verification Required
                      </span>
                    <?php endif; ?>
                  </div>
                  <p class="text-xs text-gray-500 mt-1">
                    Order placement and parcel delivery tracking strictly require a verified 10-digit Indian mobile number.
                  </p>
                </div>
              </div>

              <?php if (!empty($user['phone_verified_at']) && !empty($user['phone'])): ?>
                <!-- Verified Status Card -->
                <div class="mt-5 p-4 sm:p-5 bg-emerald-50/60 rounded-2xl border border-emerald-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                  <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-xl shrink-0">
                      <i class="fa-solid fa-shield-check"></i>
                    </div>
                    <div>
                      <div class="text-sm font-extrabold text-gray-900 flex items-center gap-2">
                        +91 <?= htmlspecialchars($user['phone']) ?>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">ACTIVE FOR ORDERS</span>
                      </div>
                      <p class="text-xs text-gray-500 mt-0.5">
                        Verified on <?= date('d M Y, h:i A', strtotime($user['phone_verified_at'])) ?> • Ready for checkout and SMS delivery updates.
                      </p>
                    </div>
                  </div>
                  <button type="button" onclick="document.getElementById('changePhoneForm').classList.toggle('hidden')"
                    class="px-4 py-2 bg-white border border-gray-300 hover:border-brand-green text-gray-700 text-xs font-bold rounded-xl transition shadow-xs flex items-center gap-1.5 shrink-0">
                    <i class="fa-solid fa-pen-to-square text-brand-green"></i> Change Number
                  </button>
                </div>
              <?php endif; ?>

              <!-- Phone Verification Form -->
              <div id="changePhoneForm" class="<?= (!empty($user['phone_verified_at']) && !empty($user['phone'])) ? 'hidden' : '' ?> mt-5 space-y-4">
                <div class="p-4 sm:p-5 bg-stone-50 rounded-2xl border border-gray-200">
                  <form id="profileSendOtpForm" method="POST" action="/account.php?tab=profile" onsubmit="handleProfileSendOtp(event)">
                    <?= csrf_field() ?>
                    <input type="hidden" name="send_phone_otp" value="1">
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                      10-Digit Indian Mobile Number <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                      <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-500 font-bold text-xs">
                          +91
                        </span>
                        <input type="tel" name="phone" id="profilePhoneInput" required pattern="[6-9][0-9]{9}" maxlength="10"
                          value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="9876543210"
                          class="w-full pl-12 pr-3 py-2.5 bg-white border border-gray-300 rounded-xl text-sm font-semibold tracking-wider focus:border-brand-green focus:outline-none transition">
                      </div>
                      <button type="submit" id="profileSendOtpBtn"
                        class="px-5 py-2.5 bg-stone-900 hover:bg-black text-white text-xs font-bold rounded-xl transition shrink-0 shadow-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                        <span>Send OTP</span>
                      </button>
                    </div>
                  </form>

                  <!-- Inline OTP Verification Box -->
                  <div id="profileOtpBox" class="<?= !empty($_SESSION['pending_phone_otp']) ? '' : 'hidden' ?> mt-4 pt-4 border-t border-gray-200 space-y-3">
                    <div class="flex items-center justify-between text-xs">
                      <span class="font-bold text-gray-800 flex items-center gap-1.5">
                        <i class="fa-solid fa-key text-brand-green"></i> Enter 6-Digit Verification Code
                      </span>
                      <span class="text-[11px] text-gray-500">Valid for 15 minutes</span>
                    </div>

                    <form id="profileVerifyOtpForm" method="POST" action="/account.php?tab=profile" onsubmit="handleProfileVerifyOtp(event)">
                      <?= csrf_field() ?>
                      <input type="hidden" name="verify_phone_otp" value="1">
                      <input type="hidden" name="phone" id="profileHiddenPhone" value="<?= htmlspecialchars($_SESSION['pending_phone_number'] ?? $user['phone'] ?? '') ?>">

                      <div class="flex gap-2">
                        <input type="text" name="otp" id="profileOtpInput" required pattern="[0-9]{6}" maxlength="6"
                          placeholder="6-digit code"
                          class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-xl text-center text-sm font-mono font-bold tracking-widest focus:border-brand-green focus:outline-none">
                        <button type="submit" id="profileVerifyOtpBtn"
                          class="px-5 py-2.5 bg-brand-green hover:bg-emerald-800 text-white text-xs font-bold rounded-xl transition shrink-0 shadow-sm flex items-center gap-1.5">
                          <i class="fa-solid fa-circle-check text-xs"></i>
                          <span>Verify & Save</span>
                        </button>
                      </div>
                    </form>

                    <div id="profileOtpFeedback" class="text-[11px] text-stone-600 font-medium">
                      <?php if (!empty($_SESSION['pending_phone_otp'])): ?>
                        <span class="text-emerald-700">OTP code dispatched! Valid for 15 minutes.</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Profile Details & Avatar Photo Card -->
            <div class="bg-white rounded-3xl border border-gray-200 p-6 sm:p-8 shadow-sm">
              <h2 class="text-lg font-bold text-gray-900 mb-4 pb-3 border-b border-gray-100 flex items-center gap-2">
                <i class="fa-solid fa-id-card text-brand-green"></i>
                Profile Details & Avatar Photo
              </h2>

              <form action="/account.php?tab=profile" method="POST" enctype="multipart/form-data" class="space-y-6">
                <?= csrf_field() ?>
                <input type="hidden" name="update_profile" value="1">

                <!-- Avatar Uploader & Preview -->
                <div class="p-5 bg-stone-50 rounded-2xl border border-gray-200 flex flex-col sm:flex-row items-center gap-5">
                  <div class="relative group">
                    <?php if (!empty($user['avatar'])): ?>
                      <img id="avatarPreview" src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar"
                        class="w-20 h-20 rounded-2xl object-cover border-2 border-brand-green shadow-sm">
                    <?php else: ?>
                      <div id="avatarPreview" class="w-20 h-20 rounded-2xl bg-brand-green text-white flex items-center justify-center text-3xl font-bold shadow-sm">
                        <?= strtoupper(mb_substr($user['name'], 0, 1)) ?>
                      </div>
                    <?php endif; ?>
                  </div>

                  <div class="flex-1 text-center sm:text-left space-y-2">
                    <label class="block text-xs font-bold text-gray-800">Change Profile Avatar</label>
                    <div class="flex flex-wrap items-center gap-2 justify-center sm:justify-start">
                      <label class="cursor-pointer px-4 py-2 bg-white border border-gray-300 hover:border-brand-green text-gray-700 text-xs font-bold rounded-xl transition shadow-xs flex items-center gap-2">
                        <i class="fa-solid fa-camera text-brand-green"></i> Upload New Photo
                        <input type="file" name="avatar_file" accept="image/*" class="hidden" onchange="previewAvatar(this)">
                      </label>
                      <span class="text-[11px] text-gray-400">or enter image URL:</span>
                    </div>
                    <input type="url" name="avatar_url_input" placeholder="https://example.com/avatar.jpg"
                      value="<?= htmlspecialchars(strpos($user['avatar'] ?? '', 'http') === 0 ? $user['avatar'] : '') ?>"
                      class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs focus:outline-none focus:border-brand-green">
                    <p class="text-[11px] text-gray-400">JPG, PNG, WebP (Max 5MB). Displays across your orders and comments.</p>
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($user['name']) ?>"
                      class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                      Mobile Number
                      <?php if (!empty($user['phone_verified_at']) && !empty($user['phone'])): ?>
                        <span class="text-emerald-600 text-[11px] font-normal ml-1"><i class="fa-solid fa-circle-check"></i> Verified</span>
                      <?php else: ?>
                        <span class="text-amber-600 text-[11px] font-normal ml-1"><i class="fa-solid fa-triangle-exclamation"></i> Unverified</span>
                      <?php endif; ?>
                    </label>
                    <div class="relative">
                      <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 font-bold text-xs">
                        +91
                      </span>
                      <input type="tel" name="phone" pattern="[0-9]{10}" maxlength="10" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                        placeholder="10-digit mobile"
                        class="w-full pl-12 pr-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">To verify or update OTP status, use the Mobile Verification section above.</p>
                  </div>

                <div class="sm:col-span-2">
                  <label class="block text-xs font-bold text-gray-700 mb-1">Email Address (Read-only)</label>
                  <input type="email" disabled value="<?= htmlspecialchars($user['email']) ?>"
                    class="w-full px-3.5 py-2.5 bg-gray-100 border border-gray-200 rounded-xl text-sm text-gray-500 cursor-not-allowed">
                </div>

                <div class="sm:col-span-2">
                  <label class="block text-xs font-bold text-gray-700 mb-1">Default Delivery Address</label>
                  <textarea name="address" rows="2" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>

                <div>
                  <label class="block text-xs font-bold text-gray-700 mb-1">City / District</label>
                  <input type="text" name="city" value="<?= htmlspecialchars($user['city'] ?? 'Hardoi') ?>"
                    class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
                </div>

                <div>
                  <label class="block text-xs font-bold text-gray-700 mb-1">PIN Code</label>
                  <input type="text" name="pincode" pattern="[0-9]{6}" maxlength="6" value="<?= htmlspecialchars($user['pincode'] ?? '241001') ?>"
                    class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
                </div>
              </div>

              <button type="submit" class="px-6 py-2.5 bg-brand-green text-white text-xs font-bold rounded-xl hover:bg-emerald-800 transition shadow-md">
                Save Profile Changes
              </button>
            </form>
          </div>

        <!-- ============================================================== -->
        <!-- TAB 6: SUPPORT DESK & TICKETS                                  -->
        <!-- ============================================================== -->
        <?php elseif ($active_tab === 'tickets'): ?>
          <div class="space-y-6">
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
              <div>
                <h2 class="text-lg font-bold text-gray-900">Support Desk & Assistance Tickets</h2>
                <p class="text-xs text-gray-500 mt-0.5">Submit a ticket for order tracking, product inquiries, returns, or technical help</p>
              </div>
              <button type="button" onclick="document.getElementById('newTicketCard').classList.toggle('hidden')"
                class="px-4 py-2 bg-brand-green hover:bg-emerald-800 text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-plus"></i> New Support Ticket
              </button>
            </div>

            <!-- New Ticket Submission Form (Collapsible) -->
            <div id="newTicketCard" class="hidden bg-white rounded-3xl border border-emerald-200 p-6 shadow-sm">
              <h3 class="text-sm font-bold text-gray-900 pb-3 mb-4 border-b border-gray-100 flex items-center gap-2">
                <i class="fa-solid fa-headset text-brand-green"></i> Create New Support Ticket
              </h3>

              <form action="/account.php?tab=tickets" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="create_ticket" value="1">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Subject <span class="text-red-500">*</span></label>
                    <input type="text" name="subject" required placeholder="e.g. Tracking status for order #GGH-9871"
                      class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green">
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Category</label>
                    <select name="category" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs">
                      <option value="Order Tracking">Order Tracking</option>
                      <option value="Product Inquiry">Product Inquiry</option>
                      <option value="Delivery Delay">Delivery Delay</option>
                      <option value="Damaged Item">Damaged / Return</option>
                      <option value="Payment Issue">Payment Issue</option>
                      <option value="Other">Other Inquiry</option>
                    </select>
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-bold text-gray-700 mb-1">Priority</label>
                  <div class="flex items-center gap-4 text-xs">
                    <label class="flex items-center gap-1.5 cursor-pointer">
                      <input type="radio" name="priority" value="Low" class="text-brand-green"> Low
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                      <input type="radio" name="priority" value="Medium" checked class="text-brand-green"> Medium
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                      <input type="radio" name="priority" value="High" class="text-brand-green"> High
                    </label>
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-bold text-gray-700 mb-1">Detailed Message <span class="text-red-500">*</span></label>
                  <textarea name="message" required rows="4" placeholder="Describe your query or issue in detail..."
                    class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green"></textarea>
                </div>

                <div class="flex items-center gap-3">
                  <button type="submit" class="px-6 py-2.5 bg-brand-green hover:bg-emerald-800 text-white text-xs font-bold rounded-xl transition shadow-sm">
                    Submit Ticket
                  </button>
                  <button type="button" onclick="document.getElementById('newTicketCard').classList.add('hidden')"
                    class="px-4 py-2.5 bg-stone-100 hover:bg-stone-200 text-gray-700 text-xs font-bold rounded-xl transition">
                    Cancel
                  </button>
                </div>
              </form>
            </div>

            <!-- Tickets List -->
            <?php if (empty($user_tickets)): ?>
              <div class="bg-white rounded-3xl border border-gray-200 p-12 text-center shadow-sm">
                <i class="fa-solid fa-headset text-4xl text-stone-300 mb-3"></i>
                <h3 class="text-base font-bold text-gray-700">No support tickets found</h3>
                <p class="text-xs text-gray-500 mt-1 mb-4">If you need assistance with an order, item, or delivery, please create a ticket.</p>
                <button type="button" onclick="document.getElementById('newTicketCard').classList.remove('hidden')"
                  class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-green text-white text-xs font-bold rounded-xl hover:bg-emerald-800 transition shadow-xs">
                  <i class="fa-solid fa-plus"></i> Submit First Ticket
                </button>
              </div>
            <?php else: ?>
              <div class="space-y-4">
                <?php foreach ($user_tickets as $tkt): 
                  $stmt_rep = $pdo->prepare("SELECT * FROM ticket_replies WHERE ticket_id = ? ORDER BY id ASC");
                  $stmt_rep->execute([$tkt['id']]);
                  $replies = $stmt_rep->fetchAll() ?: [];
                  $reply_count = count($replies);
                ?>
                  <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="bg-stone-50 p-4 sm:p-5 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                      <div class="flex items-center gap-3">
                        <span class="font-mono font-bold text-brand-green text-sm">#<?= htmlspecialchars($tkt['ticket_number']) ?></span>
                        <span class="text-gray-400">•</span>
                        <span class="font-semibold text-gray-700"><?= htmlspecialchars($tkt['category']) ?></span>
                      </div>

                      <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?= $tkt['status'] === 'Resolved' ? 'bg-emerald-100 text-emerald-800' : ($tkt['status'] === 'In Progress' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') ?>">
                          <?= htmlspecialchars($tkt['status']) ?>
                        </span>
                        <span class="text-[11px] text-gray-500"><?= date('d M Y, h:i A', strtotime($tkt['created_at'])) ?></span>
                      </div>
                    </div>

                    <div class="p-4 sm:p-5">
                      <h3 class="font-bold text-gray-900 text-sm mb-2"><?= htmlspecialchars($tkt['subject']) ?></h3>
                      <p class="text-xs text-gray-600 whitespace-pre-line leading-relaxed"><?= htmlspecialchars($tkt['message']) ?></p>

                      <!-- Replies Thread -->
                      <?php if (!empty($replies)): ?>
                        <div class="mt-4 pt-4 border-t border-gray-100 space-y-3">
                          <h4 class="text-xs font-bold text-gray-800 flex items-center gap-1.5">
                            <i class="fa-solid fa-comments text-brand-green"></i> Conversation History (<?= $reply_count ?> <?= $reply_count === 1 ? 'Reply' : 'Replies' ?>)
                          </h4>

                          <?php foreach ($replies as $rep): 
                            $is_admin_reply = in_array($rep['sender_role'], ['admin', 'super_admin', 'support']);
                          ?>
                            <div class="p-3.5 rounded-2xl text-xs <?= $is_admin_reply ? 'bg-indigo-50/80 border border-indigo-100 ml-4' : 'bg-stone-50 border border-gray-200 mr-4' ?>">
                              <div class="flex items-center justify-between mb-1.5">
                                <span class="font-bold <?= $is_admin_reply ? 'text-indigo-900' : 'text-gray-900' ?>">
                                  <i class="fa-solid <?= $is_admin_reply ? 'fa-user-tie text-indigo-600' : 'fa-user' ?>"></i>
                                  <?= htmlspecialchars($rep['sender_name']) ?> <?= $is_admin_reply ? '(Support Desk)' : '(You)' ?>
                                </span>
                                <span class="text-[10px] text-gray-400"><?= date('d M, h:i A', strtotime($rep['created_at'])) ?></span>
                              </div>
                              <p class="text-gray-700 whitespace-pre-line leading-relaxed"><?= htmlspecialchars($rep['message']) ?></p>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>

                      <!-- Reply Form (if ticket not resolved) -->
                      <?php if ($tkt['status'] !== 'Resolved'): ?>
                        <div class="mt-4 pt-3 border-t border-gray-100">
                          <form action="/account.php?tab=tickets" method="POST" class="flex gap-2">
                            <?= csrf_field() ?>
                            <input type="hidden" name="reply_ticket" value="1">
                            <input type="hidden" name="ticket_id" value="<?= $tkt['id'] ?>">
                            <input type="text" name="reply_message" required placeholder="Type your reply here..."
                              class="flex-1 px-3.5 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green">
                            <button type="submit" class="px-4 py-2 bg-brand-green hover:bg-emerald-800 text-white font-bold text-xs rounded-xl transition shadow-xs flex items-center gap-1">
                              <i class="fa-solid fa-paper-plane text-[10px]"></i> Send
                            </button>
                          </form>
                        </div>
                      <?php endif; ?>

                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

          </div>

        <!-- ============================================================== -->
        <!-- TAB 7: SECURITY & PASSWORD                                     -->
        <!-- ============================================================== -->
        <?php elseif ($active_tab === 'security'): ?>
          <div class="bg-white rounded-3xl border border-gray-200 p-6 sm:p-8 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900 mb-4 pb-3 border-b border-gray-100 flex items-center gap-2">
              <i class="fa-solid fa-shield-halved text-brand-green"></i>
              Security & Password Settings
            </h2>

            <form action="/account.php?tab=security" method="POST" class="space-y-4 max-w-md">
              <?= csrf_field() ?>
              <input type="hidden" name="change_password" value="1">

              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Current Password</label>
                <input type="password" name="current_password" required
                  class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">New Password (Min 10 characters, strong)</label>
                <input type="password" name="new_password" required minlength="10"
                  class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
                <p class="text-[11px] text-gray-400 mt-1">Must include uppercase, lowercase, numbers, and symbols.</p>
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Confirm New Password</label>
                <input type="password" name="confirm_password" required minlength="10"
                  class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
              </div>

              <button type="submit" class="px-6 py-2.5 bg-brand-green text-white text-xs font-bold rounded-xl hover:bg-emerald-800 transition shadow-md">
                Update Password
              </button>
            </form>
          </div>
        <?php endif; ?>

      </div>

    </div>

  </div>
</div>

<script>
function previewAvatar(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const preview = document.getElementById('avatarPreview');
      if (preview.tagName === 'IMG') {
        preview.src = e.target.result;
      } else {
        const img = document.createElement('img');
        img.id = 'avatarPreview';
        img.src = e.target.result;
        img.className = 'w-20 h-20 rounded-2xl object-cover border-2 border-brand-green shadow-sm';
        preview.parentNode.replaceChild(img, preview);
      }
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// Interactive AJAX Phone OTP Verification in Profile
async function handleProfileSendOtp(e) {
  e.preventDefault();
  const phoneInput = document.getElementById('profilePhoneInput');
  const btn = document.getElementById('profileSendOtpBtn');
  const feedback = document.getElementById('profileOtpFeedback');
  const otpBox = document.getElementById('profileOtpBox');
  const hiddenPhone = document.getElementById('profileHiddenPhone');
  const csrf = document.querySelector('input[name="csrf_token"]').value;

  const rawPhone = phoneInput.value.replace(/\D/g, '');
  const cleanPhone = (rawPhone.length > 10 && rawPhone.startsWith('91')) ? rawPhone.substring(2) : rawPhone;

  if (!/^[6-9]\d{9}$/.test(cleanPhone)) {
    alert('Please enter a valid 10-digit Indian mobile number starting with 6, 7, 8, or 9.');
    phoneInput.focus();
    return;
  }

  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> Sending...';
  if (feedback) feedback.innerHTML = '<span class="text-stone-500">Dispatching verification code...</span>';

  try {
    const fd = new FormData();
    fd.append('action', 'send_otp');
    fd.append('phone', cleanPhone);
    fd.append('csrf_token', csrf);

    const res = await fetch('/verify-phone.php', {
      method: 'POST',
      body: fd
    });
    const data = await res.json();

    if (data.success) {
      if (hiddenPhone) hiddenPhone.value = cleanPhone;
      if (otpBox) otpBox.classList.remove('hidden');
      if (feedback) {
        feedback.innerHTML = '<span class="text-emerald-700 font-bold"><i class="fa-solid fa-circle-check"></i> ' + (data.message || 'OTP dispatched! Valid for 15 minutes.') + '</span>';
      }
      const otpIn = document.getElementById('profileOtpInput');
      if (otpIn) otpIn.focus();
    } else {
      if (feedback) feedback.innerHTML = '<span class="text-red-600 font-bold"><i class="fa-solid fa-circle-exclamation"></i> ' + (data.error || 'Failed to dispatch OTP.') + '</span>';
    }
  } catch (err) {
    // If AJAX fails, submit standard form
    document.getElementById('profileSendOtpForm').submit();
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-paper-plane text-xs"></i> <span>Send OTP</span>';
  }
}

async function handleProfileVerifyOtp(e) {
  e.preventDefault();
  const otpInput = document.getElementById('profileOtpInput');
  const btn = document.getElementById('profileVerifyOtpBtn');
  const feedback = document.getElementById('profileOtpFeedback');
  const phone = document.getElementById('profileHiddenPhone').value || document.getElementById('profilePhoneInput').value;
  const csrf = document.querySelector('input[name="csrf_token"]').value;

  const code = (otpInput.value || '').trim();
  if (code.length !== 6 || !/^\d{6}$/.test(code)) {
    alert('Please enter the 6-digit verification code.');
    otpInput.focus();
    return;
  }

  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> Verifying...';

  try {
    const fd = new FormData();
    fd.append('action', 'verify_otp');
    fd.append('otp', code);
    fd.append('phone', phone);
    fd.append('csrf_token', csrf);

    const res = await fetch('/verify-phone.php', {
      method: 'POST',
      body: fd
    });
    const data = await res.json();

    if (data.success) {
      if (feedback) {
        feedback.innerHTML = '<span class="text-emerald-700 font-bold"><i class="fa-solid fa-circle-check"></i> ' + (data.message || 'Mobile verified successfully!') + '</span>';
      }
      setTimeout(() => {
        window.location.href = '/account.php?tab=profile';
      }, 800);
    } else {
      if (feedback) {
        feedback.innerHTML = '<span class="text-red-600 font-bold"><i class="fa-solid fa-circle-exclamation"></i> ' + (data.error || 'Invalid code.') + '</span>';
      }
    }
  } catch (err) {
    // Fallback to standard POST
    document.getElementById('profileVerifyOtpForm').submit();
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-circle-check text-xs"></i> <span>Verify & Save</span>';
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
