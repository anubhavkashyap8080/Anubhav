<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

require_login();
$user = get_logged_in_user();
$active_tab = clean_input($_GET['tab'] ?? 'orders');

$success_msg = '';
$error_msg = '';

// Resend Verification Email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_verification'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security session expired. Please refresh the page.';
    } else {
        if (!empty($user['email'])) {
            send_email_verification_link($user['id'], $user['email'], $user['name'], $user['phone'] ?? '');
            $success_msg = 'A verification code and link have been dispatched to ' . htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') . '. Please check your inbox.';
        }
    }
}

// Handle Profile & Avatar Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security session expired. Please refresh the page.';
        $active_tab = 'profile';
    } else {
        $name = clean_input($_POST['name'] ?? '');
        $phone = clean_input($_POST['phone'] ?? '');
        $city = clean_input($_POST['city'] ?? '');
        $pincode = clean_input($_POST['pincode'] ?? '');
        $address = clean_input($_POST['address'] ?? '');
        $avatar_url = $user['avatar'] ?? null;

        // Handle File Upload if provided
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

        if (empty($error_msg) && !empty($name) && !empty($phone)) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, city = ?, pincode = ?, address = ?, avatar = ? WHERE id = ?");
                $stmt->execute([$name, $phone, $city, $pincode, $address, $avatar_url, $user['id']]);
                $success_msg = 'Profile details and avatar updated successfully!';
                $user = get_logged_in_user(); // refresh
                $active_tab = 'profile';
            } catch (Exception $e) {
                $error_msg = 'Update error: ' . $e->getMessage();
            }
        }
    }
}

// Handle Password Change
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
            }
        }
    }
}

// Handle New Support Ticket Submission
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
                $success_msg = "Support ticket #$ticket_num has been created successfully. Our support desk will assist you shortly.";
                $active_tab = 'tickets';
            } catch (Exception $e) {
                $error_msg = 'Error creating ticket: ' . $e->getMessage();
            }
        } else {
            $error_msg = 'Please fill in both the subject and detailed message.';
        }
    }
}

// Handle Ticket Reply from Customer
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
                }
            } catch (Exception $e) {
                $error_msg = 'Error submitting reply: ' . $e->getMessage();
            }
        }
    }
}

// Fetch User Orders
$is_email_verified = !empty($user['email_verified_at']);

if ($is_email_verified && !empty($user['email'])) {
    $stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? OR customer_email = ? ORDER BY id DESC");
    $stmt_orders->execute([$user['id'], $user['email']]);
} else {
    $stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
    $stmt_orders->execute([$user['id']]);
}
$user_orders = $stmt_orders->fetchAll();

// Fetch User Support Tickets
if ($is_email_verified && !empty($user['email'])) {
    $stmt_tkts = $pdo->prepare("SELECT * FROM tickets WHERE user_id = ? OR email = ? ORDER BY id DESC");
    $stmt_tkts->execute([$user['id'], $user['email']]);
} else {
    $stmt_tkts = $pdo->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY id DESC");
    $stmt_tkts->execute([$user['id']]);
}
$user_tickets = $stmt_tkts->fetchAll();

$page_title = 'My Account — GaunGaliHat';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-stone-50 min-h-screen py-8">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Header Summary Card -->
    <div class="bg-white rounded-3xl border border-gray-200 p-6 sm:p-8 shadow-sm mb-8 flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="flex items-center gap-4">
        <?php if (!empty($user['avatar'])): ?>
          <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="<?= htmlspecialchars($user['name']) ?>"
            class="w-16 h-16 rounded-2xl object-cover shadow-md border-2 border-emerald-100 bg-stone-100">
        <?php else: ?>
          <div class="w-16 h-16 rounded-2xl bg-brand-green text-white flex items-center justify-center text-2xl font-bold shadow-md">
            <?= strtoupper(mb_substr($user['name'], 0, 1)) ?>
          </div>
        <?php endif; ?>

        <div>
          <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900"><?= htmlspecialchars($user['name']) ?></h1>
          <p class="text-xs text-gray-500 mt-0.5">
            <?= htmlspecialchars($user['email']) ?> • +91 <?= htmlspecialchars($user['phone']) ?>
          </p>
          <?php if (!empty($user['email_verified_at'])): ?>
            <span class="inline-block mt-1 text-[11px] font-bold text-emerald-800 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
              <i class="fa-solid fa-check-circle"></i> Verified Shopper
            </span>
          <?php else: ?>
            <form method="POST" action="/account.php" class="inline-flex items-center gap-2 mt-1">
              <?= csrf_field() ?>
              <input type="hidden" name="resend_verification" value="1">
              <span class="text-[11px] font-bold text-amber-800 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-300">
                <i class="fa-solid fa-triangle-exclamation"></i> Unverified Email
              </span>
              <button type="submit" class="text-[11px] text-brand-green font-bold hover:underline">
                Resend Verification Link
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>

      <div class="flex items-center gap-2">
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

    <!-- Notices -->
    <?php if (!empty($success_msg)): ?>
      <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs flex items-center gap-2 font-medium">
        <i class="fa-solid fa-circle-check text-base"></i>
        <span><?= htmlspecialchars($success_msg) ?></span>
      </div>
    <?php endif; ?>
    <?php if (!empty($error_msg)): ?>
      <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-xs flex items-center gap-2 font-medium">
        <i class="fa-solid fa-circle-exclamation text-base"></i>
        <span><?= htmlspecialchars($error_msg) ?></span>
      </div>
    <?php endif; ?>

    <!-- Main Navigation & Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      
      <!-- Left Tab Navigation (3 cols) -->
      <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-200 p-3 shadow-sm space-y-1">
        <a href="/account.php?tab=orders"
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition <?= $active_tab === 'orders' ? 'bg-brand-green text-white shadow-sm' : 'text-gray-700 hover:bg-stone-50' ?>">
          <i class="fa-solid fa-box-archive text-sm"></i>
          <span>My Orders</span>
          <span class="ml-auto text-[10px] <?= $active_tab === 'orders' ? 'bg-white/20' : 'bg-stone-100' ?> px-2 py-0.5 rounded-full">
            <?= count($user_orders) ?>
          </span>
        </a>

        <a href="/account.php?tab=profile"
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition <?= $active_tab === 'profile' ? 'bg-brand-green text-white shadow-sm' : 'text-gray-700 hover:bg-stone-50' ?>">
          <i class="fa-solid fa-id-card text-sm"></i>
          <span>Profile & Info</span>
        </a>

        <a href="/account.php?tab=tickets"
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition <?= $active_tab === 'tickets' ? 'bg-brand-green text-white shadow-sm' : 'text-gray-700 hover:bg-stone-50' ?>">
          <i class="fa-solid fa-headset text-sm"></i>
          <span>Support Desk</span>
          <span class="ml-auto text-[10px] <?= $active_tab === 'tickets' ? 'bg-white/20' : 'bg-stone-100' ?> px-2 py-0.5 rounded-full">
            <?= count($user_tickets) ?>
          </span>
        </a>

        <a href="/account.php?tab=security"
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition <?= $active_tab === 'security' ? 'bg-brand-green text-white shadow-sm' : 'text-gray-700 hover:bg-stone-50' ?>">
          <i class="fa-solid fa-shield-halved text-sm"></i>
          <span>Security & Password</span>
        </a>

        <a href="/wishlist.php"
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition text-gray-700 hover:bg-stone-50">
          <i class="fa-regular fa-heart text-sm"></i>
          <span>Wishlist</span>
        </a>
      </div>

      <!-- Right Tab Content (9 cols) -->
      <div class="lg:col-span-9">
        
        <!-- Tab 1: Orders -->
        <?php if ($active_tab === 'orders'): ?>
          <div class="space-y-4">
            <h2 class="text-lg font-bold text-gray-900 mb-2">Order History</h2>

            <?php if (empty($user_orders)): ?>
              <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
                <i class="fa-solid fa-box-open text-4xl text-stone-300 mb-3"></i>
                <h3 class="text-base font-bold text-gray-700">No orders placed yet</h3>
                <p class="text-xs text-gray-500 mt-1 mb-4">Explore authentic hand-forged tools, farm staples, and artisan crafts.</p>
                <a href="/products.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-green text-white text-xs font-bold rounded-xl hover:bg-emerald-800 transition">
                  Start Shopping <i class="fa-solid fa-arrow-right"></i>
                </a>
              </div>
            <?php else: ?>
              <?php foreach ($user_orders as $ord): 
                $stmt_ord_items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $stmt_ord_items->execute([$ord['id']]);
                $items = $stmt_ord_items->fetchAll();
              ?>
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                  <div class="bg-stone-50 p-4 sm:p-5 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div>
                      <span class="text-gray-500">Order No:</span>
                      <strong class="text-gray-900 ml-1 font-mono">#<?= htmlspecialchars($ord['order_number']) ?></strong>
                      <span class="text-gray-400 mx-2">•</span>
                      <span class="text-gray-500"><?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?></span>
                    </div>

                    <div class="flex items-center gap-2">
                      <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?= $ord['order_status'] === 'Delivered' ? 'bg-emerald-100 text-emerald-800' : ($ord['order_status'] === 'Dispatched' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') ?>">
                        <?= htmlspecialchars($ord['order_status']) ?>
                      </span>
                      <a href="/order-success.php?id=<?= $ord['id'] ?>" class="px-3 py-1 bg-white border border-gray-200 rounded-lg text-gray-700 hover:bg-stone-100 font-bold transition text-[11px]">
                        View Receipt
                      </a>
                    </div>
                  </div>

                  <div class="p-4 bg-amber-50/50 border-b border-amber-100 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                      <i class="fa-solid fa-truck-fast text-red-600"></i>
                      <span class="text-gray-700">India Post Consignment (AWB):</span>
                      <span class="font-mono font-bold text-gray-900"><?= htmlspecialchars($ord['tracking_consignment_no'] ?? 'AWB-PENDING') ?></span>
                    </div>
                    <span class="text-[11px] text-gray-500"><?= htmlspecialchars($ord['tracking_carrier'] ?? 'India Post') ?></span>
                  </div>

                  <div class="divide-y divide-gray-100 p-4">
                    <?php foreach ($items as $it): ?>
                      <div class="py-2.5 flex items-center justify-between text-xs">
                        <div>
                          <p class="font-bold text-gray-900"><?= htmlspecialchars($it['product_name']) ?></p>
                          <p class="text-gray-500 text-[11px]">Code: <?= htmlspecialchars($it['product_code']) ?> • Qty: <?= $it['quantity'] ?></p>
                        </div>
                        <div class="font-bold text-gray-900">
                          <?= format_price($it['total']) ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>

                  <div class="bg-stone-50 p-4 border-t border-gray-100 flex items-center justify-between text-xs">
                    <span class="text-gray-600">Payment: <strong class="uppercase text-gray-800"><?= htmlspecialchars($ord['payment_method']) ?></strong> (<?= htmlspecialchars($ord['payment_status']) ?>)</span>
                    <span class="font-bold text-sm text-gray-900">Total: <span class="text-brand-green font-extrabold"><?= format_price($ord['total_amount']) ?></span></span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

        <!-- Tab 2: Profile & Avatar -->
        <?php elseif ($active_tab === 'profile'): ?>
          <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-sm">
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
                  <label class="block text-xs font-bold text-gray-700 mb-1">Mobile Number</label>
                  <input type="tel" name="phone" required pattern="[0-9]{10}" maxlength="10" value="<?= htmlspecialchars($user['phone']) ?>"
                    class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
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
                Save Changes
              </button>
            </form>
          </div>

        <!-- Tab 3: Support Tickets -->
        <?php elseif ($active_tab === 'tickets'): ?>
          <div class="space-y-6">
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
              <div>
                <h2 class="text-lg font-bold text-gray-900">Support Desk & Assistance Tickets</h2>
                <p class="text-xs text-gray-500 mt-0.5">Submit a ticket for order tracking, product inquiry, or returns</p>
              </div>
              <button type="button" onclick="document.getElementById('newTicketCard').classList.toggle('hidden')"
                class="px-4 py-2 bg-brand-green hover:bg-emerald-800 text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-plus"></i> New Support Ticket
              </button>
            </div>

            <!-- New Ticket Submission Form (Collapsible) -->
            <div id="newTicketCard" class="hidden bg-white rounded-2xl border border-emerald-200 p-6 shadow-sm">
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
              <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
                <i class="fa-solid fa-headset text-4xl text-stone-300 mb-3"></i>
                <h3 class="text-base font-bold text-gray-700">No support tickets found</h3>
                <p class="text-xs text-gray-500 mt-1 mb-4">If you need assistance with an order, item, or delivery, please create a ticket.</p>
                <button type="button" onclick="document.getElementById('newTicketCard').classList.remove('hidden')"
                  class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-green text-white text-xs font-bold rounded-xl hover:bg-emerald-800 transition">
                  <i class="fa-solid fa-plus"></i> Submit First Ticket
                </button>
              </div>
            <?php else: ?>
              <div class="space-y-4">
                <?php foreach ($user_tickets as $tkt): 
                  $stmt_rep = $pdo->prepare("SELECT * FROM ticket_replies WHERE ticket_id = ? ORDER BY id ASC");
                  $stmt_rep->execute([$tkt['id']]);
                  $replies = $stmt_rep->fetchAll();
                  $reply_count = count($replies);
                ?>
                  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
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
                            <div class="p-3.5 rounded-xl text-xs <?= $is_admin_reply ? 'bg-indigo-50/80 border border-indigo-100 ml-4' : 'bg-stone-50 border border-gray-200 mr-4' ?>">
                              <div class="flex items-center justify-between mb-1.5">
                                <span class="font-bold <?= $is_admin_reply ? 'text-indigo-900' : 'text-gray-900' ?>">
                                  <i class="fa-solid <?= $is_admin_reply ? 'fa-user-tie text-indigo-600' : 'fa-user' ?>"></i>
                                  <?= htmlspecialchars($rep['sender_name']) ?> <?= $is_admin_reply ? '(Support Team)' : '(You)' ?>
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
                              class="flex-1 px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green">
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

        <!-- Tab 4: Security -->
        <?php elseif ($active_tab === 'security'): ?>
          <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-sm">
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
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
