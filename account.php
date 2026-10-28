<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

require_login();
$user = get_logged_in_user();
$active_tab = clean_input($_GET['tab'] ?? 'orders');

$success_msg = '';
$error_msg = '';

// Item 10: Resend Verification Email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_verification'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error_msg = 'सुरक्षा टोकन अमान्य है। (Invalid CSRF Token)';
    } else {
        if (!empty($user['email'])) {
            send_email_verification_link($user['id'], $user['email'], $user['name']);
            $success_msg = 'सत्यापन लिंक आपके ईमेल (' . htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') . ') पर पुनः भेज दिया गया है। कृपया अपना इनबॉक्स देखें।';
        }
    }
}

// Handle Profile & Avatar Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error_msg = 'सुरक्षा टोकन अमान्य है। कृपया पृष्ठ रीफ़्रेश करें। (Invalid CSRF Token)';
        $active_tab = 'profile';
    } else {
        $name = clean_input($_POST['name'] ?? '');
        $phone = clean_input($_POST['phone'] ?? '');
        $city = clean_input($_POST['city'] ?? '');
        $pincode = clean_input($_POST['pincode'] ?? '');
        $address = clean_input($_POST['address'] ?? '');
        $avatar_url = $user['avatar'] ?? null;

        // Handle File Upload if provided via secure_upload_image() (Item 8 Hardening)
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
                $error_msg = 'अमान्य अवतार छवि यूआरएल! केवल /uploads/ पथ या अनुमत HTTPS होस्ट ही मान्य हैं।';
            }
        }

        if (empty($error_msg) && !empty($name) && !empty($phone)) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, city = ?, pincode = ?, address = ?, avatar = ? WHERE id = ?");
                $stmt->execute([$name, $phone, $city, $pincode, $address, $avatar_url, $user['id']]);
                $success_msg = 'प्रोफ़ाइल एवं अवतार सफलतापूर्वक अपडेट कर दिया गया।';
                $user = get_logged_in_user(); // refresh
                $active_tab = 'profile';
            } catch (Exception $e) {
                $error_msg = 'त्रुटि: ' . $e->getMessage();
            }
        }
    }
}

// Handle Password Change (Item 22: Strong Password Policy)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error_msg = 'सुरक्षा टोकन अमान्य है। (Invalid CSRF Token)';
        $active_tab = 'security';
    } else {
        $current_pass = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        $pwd_err = '';
        if (!password_verify($current_pass, $user['password'])) {
            $error_msg = 'वर्तमान पासवर्ड गलत है।';
            $active_tab = 'security';
        } elseif ($new_pass !== $confirm_pass) {
            $error_msg = 'नया पासवर्ड और पुष्टि पासवर्ड मेल नहीं खाते।';
            $active_tab = 'security';
        } elseif (!is_strong_password($new_pass, $pwd_err)) {
            $error_msg = $pwd_err;
            $active_tab = 'security';
        } else {
            try {
                $hashed = password_hash($new_pass, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password = ?, force_password_change = 0, password_changed_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$hashed, $user['id']]);
                unset($_SESSION['force_password_change']);
                // Item 10: Invalidate all other active sessions
                $_SESSION['login_time'] = time();
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_regenerate_id(true);
                }
                log_audit_action('user_password_change', 'auth', 'user', $user['id'], 'Password changed by user; other sessions invalidated');
                $success_msg = 'पासवर्ड सफलतापूर्वक बदल दिया गया है। सुरक्षा हेतु अन्य सभी सत्र समाप्त कर दिए गए हैं।';
                $active_tab = 'security';
            } catch (Exception $e) {
                $error_msg = 'त्रुटि: ' . $e->getMessage();
            }
        }
    }
}

// Handle New Support Ticket Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_ticket'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error_msg = 'सुरक्षा टोकन अमान्य है।';
        $active_tab = 'tickets';
    } else {
        $subject = clean_input($_POST['subject'] ?? '');
        $category = clean_input($_POST['category'] ?? 'Order Tracking');
        $priority = clean_input($_POST['priority'] ?? 'Medium');
        $message = clean_input($_POST['message'] ?? '');

        if (!empty($subject) && !empty($message)) {
            try {
                // Item 11: Cryptographically secure random ticket number
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
                $success_msg = "सपोर्ट टिकट #$ticket_num सफलतापूर्वक दर्ज कर दिया गया है। हमारी टीम शीघ्र उत्तर देगी।";
                $active_tab = 'tickets';
            } catch (Exception $e) {
                $error_msg = 'टिकट दर्ज करने में त्रुटि: ' . $e->getMessage();
            }
        } else {
            $error_msg = 'कृपया विषय और संदेश दोनों भरें।';
        }
    }
}

// Handle Ticket Reply from Customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_ticket'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error_msg = 'सुरक्षा टोकन अमान्य है।';
        $active_tab = 'tickets';
    } else {
        $ticket_id = (int)($_POST['ticket_id'] ?? 0);
        $reply_msg = clean_input($_POST['reply_message'] ?? '');

        if ($ticket_id > 0 && !empty($reply_msg)) {
            try {
                // Item 10: Check ticket ownership (Allow email match ONLY if email is verified)
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
                    
                    // Update ticket status to In Progress
                    $pdo->prepare("UPDATE tickets SET status = 'In Progress' WHERE id = ? AND status != 'Resolved'")->execute([$ticket_id]);
                    
                    $success_msg = 'आपका संदेश टिकट में जोड़ दिया गया है।';
                    $active_tab = 'tickets';
                } else {
                    $error_msg = 'अमान्य टिकट अनुरोध।';
                }
            } catch (Exception $e) {
                $error_msg = 'उत्तर दर्ज करने में त्रुटि: ' . $e->getMessage();
            }
        }
    }
}

// Item 10: Fetch User Orders (Match by email ONLY if email is verified)
$is_email_verified = !empty($user['email_verified_at']);

if ($is_email_verified && !empty($user['email'])) {
    $stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? OR customer_email = ? ORDER BY id DESC");
    $stmt_orders->execute([$user['id'], $user['email']]);
} else {
    $stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
    $stmt_orders->execute([$user['id']]);
}
$user_orders = $stmt_orders->fetchAll();

// Item 10: Fetch User Support Tickets (Match by email ONLY if email is verified)
if ($is_email_verified && !empty($user['email'])) {
    $stmt_tkts = $pdo->prepare("SELECT * FROM tickets WHERE user_id = ? OR email = ? ORDER BY id DESC");
    $stmt_tkts->execute([$user['id'], $user['email']]);
} else {
    $stmt_tkts = $pdo->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY id DESC");
    $stmt_tkts->execute([$user['id']]);
}
$user_tickets = $stmt_tkts->fetchAll();

$page_title = 'मेरा खाता (My Account) — GaunGaliHat';
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
              <i class="fa-solid fa-check-circle"></i> सत्यापित ग्राहक (Verified Shopper)
            </span>
          <?php else: ?>
            <form method="POST" action="/account.php" class="inline-flex items-center gap-2 mt-1">
              <?= csrf_field() ?>
              <input type="hidden" name="resend_verification" value="1">
              <span class="text-[11px] font-bold text-amber-800 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-300">
                <i class="fa-solid fa-triangle-exclamation"></i> ईमेल असत्यापित (Unverified)
              </span>
              <button type="submit" class="text-[11px] text-brand-green font-bold hover:underline">
                सत्यापन लिंक भेजें (Resend Verification)
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <?php if (in_array($user['role'], ['vendor', 'admin', 'super_admin'])): ?>
          <a href="/seller/dashboard.php" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-stone-950 text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-sm">
            <i class="fa-solid fa-store"></i> विक्रेता पोर्टल
          </a>
        <?php endif; ?>
        <?php if (in_array($user['role'], ['admin', 'super_admin'])): ?>
          <a href="/admin/index.php" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-sm">
            <i class="fa-solid fa-shield-halved"></i> एडमिन कंट्रोल
          </a>
        <?php endif; ?>
        <a href="/logout.php" class="px-4 py-2 bg-stone-100 hover:bg-stone-200 text-gray-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5">
          <i class="fa-solid fa-power-off text-red-500"></i> लॉगआउट
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
          <span>My Orders (मेरे आर्डर)</span>
          <span class="ml-auto text-[10px] <?= $active_tab === 'orders' ? 'bg-white/20' : 'bg-stone-100' ?> px-2 py-0.5 rounded-full">
            <?= count($user_orders) ?>
          </span>
        </a>

        <a href="/account.php?tab=profile"
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition <?= $active_tab === 'profile' ? 'bg-brand-green text-white shadow-sm' : 'text-gray-700 hover:bg-stone-50' ?>">
          <i class="fa-solid fa-id-card text-sm"></i>
          <span>Profile & Info (प्रोफ़ाइल विवरण)</span>
        </a>

        <a href="/account.php?tab=tickets"
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition <?= $active_tab === 'tickets' ? 'bg-brand-green text-white shadow-sm' : 'text-gray-700 hover:bg-stone-50' ?>">
          <i class="fa-solid fa-headset text-sm"></i>
          <span>Support Desk (सपोर्ट टिकट्स)</span>
          <span class="ml-auto text-[10px] <?= $active_tab === 'tickets' ? 'bg-white/20' : 'bg-stone-100' ?> px-2 py-0.5 rounded-full">
            <?= count($user_tickets) ?>
          </span>
        </a>

        <a href="/account.php?tab=security"
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition <?= $active_tab === 'security' ? 'bg-brand-green text-white shadow-sm' : 'text-gray-700 hover:bg-stone-50' ?>">
          <i class="fa-solid fa-shield-halved text-sm"></i>
          <span>Security & Password (सुरक्षा व पासवर्ड)</span>
        </a>

        <a href="/wishlist.php"
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition text-gray-700 hover:bg-stone-50">
          <i class="fa-regular fa-heart text-sm"></i>
          <span>Wishlist (मेरी विशलिस्ट)</span>
        </a>
      </div>

      <!-- Right Tab Content (9 cols) -->
      <div class="lg:col-span-9">
        
        <!-- Tab 1: Orders -->
        <?php if ($active_tab === 'orders'): ?>
          <div class="space-y-4">
            <h2 class="text-lg font-bold text-gray-900 mb-2">Order History (मेरे आर्डर का इतिहास)</h2>

            <?php if (empty($user_orders)): ?>
              <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
                <i class="fa-solid fa-box-open text-4xl text-stone-300 mb-3"></i>
                <h3 class="text-base font-bold text-gray-700">अभी तक कोई आर्डर नहीं दिया गया</h3>
                <p class="text-xs text-gray-500 mt-1 mb-4">हथकरघा और देसी कुटीर उत्पाद अभी एक्सप्लोर करें।</p>
                <a href="/products.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-green text-white text-xs font-bold rounded-xl hover:bg-emerald-800 transition">
                  शॉपिंग शुरू करें <i class="fa-solid fa-arrow-right"></i>
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
                      <span class="text-gray-500">आर्डर सं.:</span>
                      <strong class="text-gray-900 ml-1 font-mono">#<?= htmlspecialchars($ord['order_number']) ?></strong>
                      <span class="text-gray-400 mx-2">•</span>
                      <span class="text-gray-500"><?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?></span>
                    </div>

                    <div class="flex items-center gap-2">
                      <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?= $ord['order_status'] === 'Delivered' ? 'bg-emerald-100 text-emerald-800' : ($ord['order_status'] === 'Dispatched' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') ?>">
                        <?= htmlspecialchars($ord['order_status']) ?>
                      </span>
                      <a href="/order-success.php?id=<?= $ord['id'] ?>" class="px-3 py-1 bg-white border border-gray-200 rounded-lg text-gray-700 hover:bg-stone-100 font-bold transition text-[11px]">
                        विवरण / रसीद
                      </a>
                    </div>
                  </div>

                  <div class="p-4 bg-amber-50/50 border-b border-amber-100 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                      <i class="fa-solid fa-truck-fast text-red-600"></i>
                      <span class="text-gray-700">भारतीय डाक (India Post AWB):</span>
                      <span class="font-mono font-bold text-gray-900"><?= htmlspecialchars($ord['tracking_consignment_no'] ?? 'AWB-PENDING') ?></span>
                    </div>
                    <span class="text-[11px] text-gray-500"><?= htmlspecialchars($ord['tracking_carrier'] ?? 'India Post') ?></span>
                  </div>

                  <div class="divide-y divide-gray-100 p-4">
                    <?php foreach ($items as $it): ?>
                      <div class="py-2.5 flex items-center justify-between text-xs">
                        <div>
                          <p class="font-bold text-gray-900"><?= htmlspecialchars($it['product_name']) ?></p>
                          <p class="text-gray-500 text-[11px]">कोड: <?= htmlspecialchars($it['product_code']) ?> • मात्रा: <?= $it['quantity'] ?></p>
                        </div>
                        <div class="font-bold text-gray-900">
                          <?= format_price($it['total']) ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>

                  <div class="bg-stone-50 p-4 border-t border-gray-100 flex items-center justify-between text-xs">
                    <span class="text-gray-600">भुगतान विधि: <strong class="uppercase text-gray-800"><?= htmlspecialchars($ord['payment_method']) ?></strong> (<?= htmlspecialchars($ord['payment_status']) ?>)</span>
                    <span class="font-bold text-sm text-gray-900">कुल: <span class="text-brand-green font-extrabold"><?= format_price($ord['total_amount']) ?></span></span>
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
              प्रोफ़ाइल विवरण एवं प्रोफ़ाइल फ़ोटो संपादित करें (Profile & Avatar)
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
                  <label class="block text-xs font-bold text-gray-800">प्रोफ़ाइल फ़ोटो / अवतार बदलें</label>
                  <div class="flex flex-wrap items-center gap-2 justify-center sm:justify-start">
                    <label class="cursor-pointer px-4 py-2 bg-white border border-gray-300 hover:border-brand-green text-gray-700 text-xs font-bold rounded-xl transition shadow-xs flex items-center gap-2">
                      <i class="fa-solid fa-camera text-brand-green"></i> नई फ़ोटो अपलोड करें
                      <input type="file" name="avatar_file" accept="image/*" class="hidden" onchange="previewAvatar(this)">
                    </label>
                    <span class="text-[11px] text-gray-400">या छवि URL दर्ज करें:</span>
                  </div>
                  <input type="url" name="avatar_url_input" placeholder="https://example.com/avatar.jpg"
                    value="<?= htmlspecialchars(strpos($user['avatar'] ?? '', 'http') === 0 ? $user['avatar'] : '') ?>"
                    class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs focus:outline-none focus:border-brand-green">
                  <p class="text-[11px] text-gray-400">JPG, PNG, WebP (अधिकतम 5MB)। यह सभी डैशबोर्ड और ऑर्डर्स पर दिखाई देगी।</p>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-bold text-gray-700 mb-1">पूरा नाम</label>
                  <input type="text" name="name" required value="<?= htmlspecialchars($user['name']) ?>"
                    class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
                </div>

                <div>
                  <label class="block text-xs font-bold text-gray-700 mb-1">मोबाइल नंबर</label>
                  <input type="tel" name="phone" required pattern="[0-9]{10}" maxlength="10" value="<?= htmlspecialchars($user['phone']) ?>"
                    class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
                </div>

                <div class="sm:col-span-2">
                  <label class="block text-xs font-bold text-gray-700 mb-1">ईमेल पता (बदला नहीं जा सकता)</label>
                  <input type="email" disabled value="<?= htmlspecialchars($user['email']) ?>"
                    class="w-full px-3.5 py-2.5 bg-gray-100 border border-gray-200 rounded-xl text-sm text-gray-500 cursor-not-allowed">
                </div>

                <div class="sm:col-span-2">
                  <label class="block text-xs font-bold text-gray-700 mb-1">डिफ़ॉल्ट डिलीवरी पता</label>
                  <textarea name="address" rows="2" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>

                <div>
                  <label class="block text-xs font-bold text-gray-700 mb-1">शहर / जिला</label>
                  <input type="text" name="city" value="<?= htmlspecialchars($user['city'] ?? 'Hardoi') ?>"
                    class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
                </div>

                <div>
                  <label class="block text-xs font-bold text-gray-700 mb-1">पिन कोड</label>
                  <input type="text" name="pincode" pattern="[0-9]{6}" maxlength="6" value="<?= htmlspecialchars($user['pincode'] ?? '241001') ?>"
                    class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
                </div>
              </div>

              <button type="submit" class="px-6 py-2.5 bg-brand-green text-white text-xs font-bold rounded-xl hover:bg-emerald-800 transition shadow-md">
                परिवर्तन सहेजें (Save Changes)
              </button>
            </form>
          </div>

        <!-- Tab 3: Support Tickets -->
        <?php elseif ($active_tab === 'tickets'): ?>
          <div class="space-y-6">
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
              <div>
                <h2 class="text-lg font-bold text-gray-900">सपोर्ट डेस्क एवं सहायता टिकट्स (Support Desk)</h2>
                <p class="text-xs text-gray-500 mt-0.5">ऑर्डर ट्रैकिंग, उत्पाद संबंधी प्रश्न या रिटर्न के लिए टिकट दर्ज करें</p>
              </div>
              <button type="button" onclick="document.getElementById('newTicketCard').classList.toggle('hidden')"
                class="px-4 py-2 bg-brand-green hover:bg-emerald-800 text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-plus"></i> नया टिकट दर्ज करें (New Ticket)
              </button>
            </div>

            <!-- New Ticket Submission Form (Collapsible) -->
            <div id="newTicketCard" class="hidden bg-white rounded-2xl border border-emerald-200 p-6 shadow-sm">
              <h3 class="text-sm font-bold text-gray-900 pb-3 mb-4 border-b border-gray-100 flex items-center gap-2">
                <i class="fa-solid fa-headset text-brand-green"></i> नया सपोर्ट टिकट बनाएं
              </h3>

              <form action="/account.php?tab=tickets" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="create_ticket" value="1">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">विषय (Subject) <span class="text-red-500">*</span></label>
                    <input type="text" name="subject" required placeholder="उदा. आर्डर #GGH-9871 की ट्रैकिंग स्थिति"
                      class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green">
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">श्रेणी (Category)</label>
                    <select name="category" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs">
                      <option value="Order Tracking">आर्डर ट्रैकिंग (Tracking)</option>
                      <option value="Product Inquiry">उत्पाद पूछताछ (Inquiry)</option>
                      <option value="Delivery Delay">डिलीवरी में देरी (Delay)</option>
                      <option value="Damaged Item">क्षतिग्रस्त सामान (Damage/Return)</option>
                      <option value="Payment Issue">भुगतान संबंधी (Payment)</option>
                      <option value="Other">अन्य (Other)</option>
                    </select>
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-bold text-gray-700 mb-1">प्राथमिकता (Priority)</label>
                  <div class="flex items-center gap-4 text-xs">
                    <label class="flex items-center gap-1.5 cursor-pointer">
                      <input type="radio" name="priority" value="Low" class="text-brand-green"> सामान्य (Low)
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                      <input type="radio" name="priority" value="Medium" checked class="text-brand-green"> मध्यम (Medium)
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                      <input type="radio" name="priority" value="High" class="text-brand-green"> उच्च (High)
                    </label>
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-bold text-gray-700 mb-1">विस्तृत संदेश (Detailed Message) <span class="text-red-500">*</span></label>
                  <textarea name="message" required rows="4" placeholder="अपनी समस्या या प्रश्न का विवरण यहाँ लिखें..."
                    class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green"></textarea>
                </div>

                <div class="flex items-center gap-3">
                  <button type="submit" class="px-6 py-2.5 bg-brand-green hover:bg-emerald-800 text-white text-xs font-bold rounded-xl transition shadow-sm">
                    टिकट सबमिट करें
                  </button>
                  <button type="button" onclick="document.getElementById('newTicketCard').classList.add('hidden')"
                    class="px-4 py-2.5 bg-stone-100 hover:bg-stone-200 text-gray-700 text-xs font-bold rounded-xl transition">
                    रद्द करें
                  </button>
                </div>
              </form>
            </div>

            <!-- Tickets List -->
            <?php if (empty($user_tickets)): ?>
              <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
                <i class="fa-solid fa-headset text-4xl text-stone-300 mb-3"></i>
                <h3 class="text-base font-bold text-gray-700">अभी तक कोई सपोर्ट टिकट नहीं है</h3>
                <p class="text-xs text-gray-500 mt-1 mb-4">यदि आपको किसी आर्डर या उत्पाद में सहायता चाहिए, तो नया टिकट बनाएं।</p>
                <button type="button" onclick="document.getElementById('newTicketCard').classList.remove('hidden')"
                  class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-green text-white text-xs font-bold rounded-xl hover:bg-emerald-800 transition">
                  <i class="fa-solid fa-plus"></i> पहला टिकट दर्ज करें
                </button>
              </div>
            <?php else: ?>
              <div class="space-y-4">
                <?php foreach ($user_tickets as $tkt): 
                  // Fetch replies
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
                            <i class="fa-solid fa-comments text-brand-green"></i> बातचीत का इतिहास (Replies - <?= $reply_count ?>)
                          </h4>

                          <?php foreach ($replies as $rep): 
                            $is_admin_reply = in_array($rep['sender_role'], ['admin', 'super_admin', 'support']);
                          ?>
                            <div class="p-3.5 rounded-xl text-xs <?= $is_admin_reply ? 'bg-indigo-50/80 border border-indigo-100 ml-4' : 'bg-stone-50 border border-gray-200 mr-4' ?>">
                              <div class="flex items-center justify-between mb-1.5">
                                <span class="font-bold <?= $is_admin_reply ? 'text-indigo-900' : 'text-gray-900' ?>">
                                  <i class="fa-solid <?= $is_admin_reply ? 'fa-user-tie text-indigo-600' : 'fa-user' ?>"></i>
                                  <?= htmlspecialchars($rep['sender_name']) ?> <?= $is_admin_reply ? '(सपोर्ट टीम)' : '(आप)' ?>
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
                            <input type="text" name="reply_message" required placeholder="यहाँ उत्तर लिखें..."
                              class="flex-1 px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green">
                            <button type="submit" class="px-4 py-2 bg-brand-green hover:bg-emerald-800 text-white font-bold text-xs rounded-xl transition shadow-xs flex items-center gap-1">
                              <i class="fa-solid fa-paper-plane text-[10px]"></i> भेजें
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
              सुरक्षा एवं पासवर्ड बदलें (Change Password)
            </h2>

            <form action="/account.php?tab=security" method="POST" class="space-y-4 max-w-md">
              <?= csrf_field() ?>
              <input type="hidden" name="change_password" value="1">

              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">वर्तमान पासवर्ड (Current Password)</label>
                <input type="password" name="current_password" required
                  class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">नया पासवर्ड (New Password - Min 10 chars, मजबूत पासवर्ड)</label>
                <input type="password" name="new_password" required minlength="10"
                  class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">नया पासवर्ड दोबारा लिखें (Confirm New Password)</label>
                <input type="password" name="confirm_password" required minlength="10"
                  class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
              </div>

              <button type="submit" class="px-6 py-2.5 bg-brand-green text-white text-xs font-bold rounded-xl hover:bg-emerald-800 transition shadow-md">
                पासवर्ड अपडेट करें (Update Password)
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
