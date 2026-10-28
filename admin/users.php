<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$admin_user = get_logged_in_user();
$is_super = is_super_admin();

// Allowed roles based on administrator privilege
$allowed_roles = $is_super ? ['customer', 'vendor', 'admin', 'super_admin'] : ['customer', 'vendor'];

// 1. Handle ADD USER (POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'CSRF सुरक्षा सत्यापन विफल रहा। (Invalid CSRF Token)');
        header('Location: /admin/users.php');
        exit;
    }

    $name = clean_input($_POST['name'] ?? '');
    $username = strtolower(clean_input($_POST['username'] ?? ''));
    $email = strtolower(clean_input($_POST['email'] ?? ''));
    $phone = clean_input($_POST['phone'] ?? '');
    $raw_role = clean_input($_POST['role'] ?? 'customer');
    $department = clean_input($_POST['department'] ?? 'Operations');
    $password = $_POST['password'] ?? '';

    // Only super_admin may create 'admin' or 'super_admin' roles
    if (!$is_super && in_array($raw_role, ['admin', 'super_admin'], true)) {
        log_audit_action('admin_unauthorized_role_create', 'users', "Admin #{$admin_user['id']} attempted to create privileged user with role '{$raw_role}'");
        set_flash('error', 'अनाधिकृत प्रयास: केवल सुपर एडमिन ही प्रशासनिक अथवा सुपर एडमिन खाते बना सकते हैं।');
        header('Location: /admin/users.php');
        exit;
    }

    $role = in_array($raw_role, $allowed_roles, true) ? $raw_role : 'customer';

    $pass_err = '';
    if (empty($name) || empty($username) || empty($email) || empty($password)) {
        set_flash('error', 'कृपया नाम, यूजरनेम, ईमेल एवं पासवर्ड अनिवार्य रूप से भरें।');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('error', 'कृपया एक वैध ईमेल पता दर्ज करें।');
    } elseif (!is_strong_password($password, $pass_err)) {
        set_flash('error', $pass_err);
    } else {
        // Check uniqueness
        $stmt_chk = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt_chk->execute([$username, $email]);
        if ($stmt_chk->fetch()) {
            set_flash('error', 'यह यूजरनेम या ईमेल पहले से ही किसी अन्य खाते में दर्ज है।');
        } else {
            $unique_code = 'GGH-USR-' . bin2hex(random_bytes(3));
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            try {
                $insert = $pdo->prepare("
                    INSERT INTO users (unique_code, name, username, email, phone, password, role, department, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', CURRENT_TIMESTAMP)
                ");
                $insert->execute([$unique_code, $name, $username, $email, $phone, $hashed_password, $role, $department]);
                $new_uid = $pdo->lastInsertId();
                log_audit_action('admin_create_user', 'users', "Created new {$role} account '{$username}' (#{$new_uid})");

                // Item 32: Owner Alert on administrative role creation
                if (in_array($role, ['admin', 'super_admin'], true)) {
                    send_owner_security_alert(
                        "New Administrative Account Created: @{$username} ({$role})",
                        [
                            'created_by' => $admin_user['username'],
                            'created_by_id' => $admin_user['id'],
                            'target_username' => $username,
                            'target_email' => $email,
                            'target_role' => $role,
                            'ip' => get_client_ip()
                        ]
                    );
                }

                set_flash('success', 'नया यूजर (' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . ') सुरक्षित Bcrypt एन्क्रिप्शन के साथ सफलतापूर्वक बनाया गया!');
                header('Location: /admin/users.php');
                exit;
            } catch (Exception $e) {
                error_log("Error creating user: " . $e->getMessage());
                set_flash('error', 'उपयोगकर्ता बनाने में डेटाबेस त्रुटि हुई।');
            }
        }
    }
}

// 2. Handle EDIT USER (POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'CSRF सुरक्षा सत्यापन विफल रहा। (Invalid CSRF Token)');
        header('Location: /admin/users.php');
        exit;
    }

    $user_id = intval($_POST['edit_user_id'] ?? 0);
    $name = clean_input($_POST['name'] ?? '');
    $username = strtolower(clean_input($_POST['username'] ?? ''));
    $email = strtolower(clean_input($_POST['email'] ?? ''));
    $phone = clean_input($_POST['phone'] ?? '');
    $raw_role = clean_input($_POST['role'] ?? 'customer');
    $department = clean_input($_POST['department'] ?? 'Operations');
    $status = clean_input($_POST['status'] ?? 'active');
    $new_password = $_POST['new_password'] ?? '';

    // Fetch existing target user to enforce role boundaries
    $stmt_tgt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt_tgt->execute([$user_id]);
    $target_user = $stmt_tgt->fetch();

    if (!$target_user || $user_id <= 0 || empty($name) || empty($username) || empty($email)) {
        set_flash('error', 'कृपया नाम, यूजरनेम एवं ईमेल अनिवार्य रूप से भरें।');
        header('Location: /admin/users.php');
        exit;
    }

    // SECURITY CHECK: Only super_admin may edit a super_admin account!
    if ($target_user['role'] === 'super_admin' && !$is_super) {
        log_audit_action('admin_unauthorized_edit', 'users', "Admin #{$admin_user['id']} attempted to edit Super Admin #{$user_id}");
        set_flash('error', 'सुरक्षा प्रतिबंध: केवल सुपर एडमिन ही सुपर एडमिन खातों को संपादित कर सकते हैं।');
        header('Location: /admin/users.php');
        exit;
    }

    // SECURITY CHECK: Admins can only manage customer and vendor accounts
    if (!$is_super && in_array($target_user['role'], ['admin', 'super_admin'], true) && $admin_user['id'] !== $target_user['id']) {
        log_audit_action('admin_unauthorized_staff_edit', 'users', "Admin #{$admin_user['id']} attempted to edit staff account #{$user_id}");
        set_flash('error', 'सुरक्षा प्रतिबंध: सामान्य व्यवस्थापक केवल ग्राहक व विक्रेता खातों का प्रबंधन कर सकते हैं।');
        header('Location: /admin/users.php');
        exit;
    }

    // SECURITY CHECK: Changing role to super_admin or admin requires super_admin clearance
    if (!$is_super && in_array($raw_role, ['admin', 'super_admin'], true)) {
        set_flash('error', 'सुरक्षा प्रतिबंध: आपके पास प्रशासनिक पद आवंटित करने की अनुमति नहीं है।');
        header('Location: /admin/users.php');
        exit;
    }

    // SECURITY CHECK: Prevent removing the last super_admin
    if ($target_user['role'] === 'super_admin' && $raw_role !== 'super_admin') {
        $other_supers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'super_admin' AND id != {$user_id}")->fetchColumn();
        if ($other_supers <= 0) {
            set_flash('error', 'सुरक्षा अवरोध: सिस्टम में कम से कम एक सुपर एडमिन होना अनिवार्य है। अंतिम सुपर एडमिन की भूमिका नहीं बदली जा सकती।');
            header('Location: /admin/users.php');
            exit;
        }
    }

    $role = in_array($raw_role, $allowed_roles, true) ? $raw_role : $target_user['role'];
    $status = in_array($status, ['active', 'suspended', 'pending'], true) ? $status : 'active';

    // Prevent suspending the last super admin
    if ($target_user['role'] === 'super_admin' && $status === 'suspended') {
        $other_active_supers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'super_admin' AND status = 'active' AND id != {$user_id}")->fetchColumn();
        if ($other_active_supers <= 0) {
            set_flash('error', 'सुरक्षा अवरोध: अंतिम सक्रिय सुपर एडमिन को निलंबित नहीं किया जा सकता।');
            header('Location: /admin/users.php');
            exit;
        }
    }

    // Check uniqueness excluding this user
    $stmt_chk = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt_chk->execute([$username, $email, $user_id]);
    if ($stmt_chk->fetch()) {
        set_flash('error', 'यह यूजरनेम या ईमेल पहले से ही किसी अन्य खाते में दर्ज है।');
    } else {
        try {
            if (!empty($new_password)) {
                $pass_err = '';
                if (!is_strong_password($new_password, $pass_err)) {
                    set_flash('error', $pass_err);
                    header('Location: /admin/users.php');
                    exit;
                }
                $hashed = password_hash($new_password, PASSWORD_BCRYPT);
                $stmt_up = $pdo->prepare("
                    UPDATE users 
                    SET name = ?, username = ?, email = ?, phone = ?, role = ?, department = ?, status = ?, password = ?
                    WHERE id = ?
                ");
                $stmt_up->execute([$name, $username, $email, $phone, $role, $department, $status, $hashed, $user_id]);
            } else {
                $stmt_up = $pdo->prepare("
                    UPDATE users 
                    SET name = ?, username = ?, email = ?, phone = ?, role = ?, department = ?, status = ?
                    WHERE id = ?
                ");
                $stmt_up->execute([$name, $username, $email, $phone, $role, $department, $status, $user_id]);
            }
            log_audit_action('admin_edit_user', 'users', "User #{$user_id} ({$username}) updated by Admin #{$admin_user['id']}");

            // Item 32: Owner Alert on role escalation
            if ($target_user['role'] !== $role && in_array($role, ['admin', 'super_admin'], true)) {
                send_owner_security_alert(
                    "Administrative Role Escalation for @{$username} ({$target_user['role']} -> {$role})",
                    [
                        'escalated_by' => $admin_user['username'],
                        'escalated_by_id' => $admin_user['id'],
                        'target_username' => $username,
                        'target_id' => $user_id,
                        'previous_role' => $target_user['role'],
                        'new_role' => $role,
                        'ip' => get_client_ip()
                    ]
                );
            }

            set_flash('success', 'उपयोगकर्ता जानकारी (' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . ') सफलतापूर्वक अपडेट की गई!');
            header('Location: /admin/users.php');
            exit;
        } catch (Exception $e) {
            error_log("Error updating user: " . $e->getMessage());
            set_flash('error', 'डेटाबेस अपडेट में त्रुटि हुई।');
        }
    }
}

// 3. Handle PASSWORD RESET (POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_user_password'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'CSRF सुरक्षा सत्यापन विफल रहा। (Invalid CSRF Token)');
        header('Location: /admin/users.php');
        exit;
    }

    $target_user_id = intval($_POST['target_user_id'] ?? 0);
    $new_password = $_POST['new_password'] ?? '';

    $stmt_tgt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt_tgt->execute([$target_user_id]);
    $target_user = $stmt_tgt->fetch();

    if (!$target_user) {
        set_flash('error', 'उपयोगकर्ता खाता नहीं मिला।');
        header('Location: /admin/users.php');
        exit;
    }

    // SECURITY CHECK: Only super_admin may reset a super_admin's password
    if ($target_user['role'] === 'super_admin' && !$is_super) {
        log_audit_action('admin_unauthorized_pwd_reset', 'users', "Admin #{$admin_user['id']} attempted to reset password of Super Admin #{$target_user_id}");
        set_flash('error', 'सुरक्षा प्रतिबंध: केवल सुपर एडमिन ही सुपर एडमिन का पासवर्ड रीसेट कर सकते हैं।');
        header('Location: /admin/users.php');
        exit;
    }

    // Admins can only reset customers/vendors
    if (!$is_super && in_array($target_user['role'], ['admin', 'super_admin'], true)) {
        set_flash('error', 'सुरक्षा प्रतिबंध: सामान्य व्यवस्थापक केवल ग्राहक व विक्रेता खातों का पासवर्ड रीसेट कर सकते हैं।');
        header('Location: /admin/users.php');
        exit;
    }

    $pass_err = '';
    if (!is_strong_password($new_password, $pass_err)) {
        set_flash('error', $pass_err);
    } else {
        $hashed = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt_up = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt_up->execute([$hashed, $target_user_id]);
        log_audit_action('admin_password_reset', 'users', "Password for user #{$target_user_id} was reset by Admin #{$admin_user['id']}");
        set_flash('success', 'यूजर का पासवर्ड सुरक्षित Bcrypt एन्क्रिप्शन के साथ सफलतापूर्वक रीसेट कर दिया गया!');
        header('Location: /admin/users.php');
        exit;
    }
}

// 4. Handle TOGGLE STATUS (POST + CSRF - GET is strictly blocked)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'CSRF सुरक्षा सत्यापन विफल रहा। (Invalid CSRF Token)');
        header('Location: /admin/users.php');
        exit;
    }

    $target_id = intval($_POST['target_user_id'] ?? 0);
    $stmt_cur = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt_cur->execute([$target_id]);
    $target_user = $stmt_cur->fetch();

    if (!$target_user) {
        set_flash('error', 'उपयोगकर्ता नहीं मिला।');
        header('Location: /admin/users.php');
        exit;
    }

    if ($target_id === $admin_user['id']) {
        set_flash('error', 'आप स्वयं के खाते को निलंबित नहीं कर सकते।');
        header('Location: /admin/users.php');
        exit;
    }

    if ($target_user['role'] === 'super_admin' && !$is_super) {
        log_audit_action('admin_unauthorized_suspend', 'users', "Admin #{$admin_user['id']} attempted to suspend Super Admin #{$target_id}");
        set_flash('error', 'सुरक्षा प्रतिबंध: केवल सुपर एडमिन ही सुपर एडमिन की स्थिति बदल सकते हैं।');
        header('Location: /admin/users.php');
        exit;
    }

    if (!$is_super && in_array($target_user['role'], ['admin', 'super_admin'], true)) {
        set_flash('error', 'सुरक्षा प्रतिबंध: सामान्य व्यवस्थापक केवल ग्राहक व विक्रेता खातों की स्थिति बदल सकते हैं।');
        header('Location: /admin/users.php');
        exit;
    }

    $new_stat = ($target_user['status'] === 'active') ? 'suspended' : 'active';

    // Prevent suspending the last active super_admin
    if ($target_user['role'] === 'super_admin' && $new_stat === 'suspended') {
        $other_active = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'super_admin' AND status = 'active' AND id != {$target_id}")->fetchColumn();
        if ($other_active <= 0) {
            set_flash('error', 'सुरक्षा अवरोध: अंतिम सक्रिय सुपर एडमिन को निलंबित नहीं किया जा सकता।');
            header('Location: /admin/users.php');
            exit;
        }
    }

    $pdo->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$new_stat, $target_id]);
    log_audit_action('admin_toggle_status', 'users', "User #{$target_id} status changed to '{$new_stat}' by Admin #{$admin_user['id']}");
    set_flash('success', 'उपयोगकर्ता खाता स्थिति अपडेट की गई: ' . $new_stat);
    header('Location: /admin/users.php');
    exit;
}

// 5. Handle DELETE USER (POST + CSRF - GET is strictly blocked)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'CSRF सुरक्षा सत्यापन विफल रहा। (Invalid CSRF Token)');
        header('Location: /admin/users.php');
        exit;
    }

    $del_id = intval($_POST['target_user_id'] ?? 0);
    $stmt_tgt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt_tgt->execute([$del_id]);
    $target_user = $stmt_tgt->fetch();

    if (!$target_user) {
        set_flash('error', 'उपयोगकर्ता खाता नहीं मिला।');
        header('Location: /admin/users.php');
        exit;
    }

    if ($del_id === $admin_user['id']) {
        set_flash('error', 'आप स्वयं के खाते को डिलीट नहीं कर सकते।');
        header('Location: /admin/users.php');
        exit;
    }

    if ($target_user['role'] === 'super_admin' && !$is_super) {
        log_audit_action('admin_unauthorized_delete', 'users', "Admin #{$admin_user['id']} attempted to delete Super Admin #{$del_id}");
        set_flash('error', 'सुरक्षा प्रतिबंध: केवल सुपर एडमिन ही सुपर एडमिन को हटा सकते हैं।');
        header('Location: /admin/users.php');
        exit;
    }

    if (!$is_super && in_array($target_user['role'], ['admin', 'super_admin'], true)) {
        set_flash('error', 'सुरक्षा प्रतिबंध: सामान्य व्यवस्थापक केवल ग्राहक व विक्रेता खातों को हटा सकते हैं।');
        header('Location: /admin/users.php');
        exit;
    }

    // SECURITY REQUIREMENT: Prevent deleting the last super admin!
    if ($target_user['role'] === 'super_admin') {
        $super_count = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'super_admin' AND id != {$del_id}")->fetchColumn();
        if ($super_count <= 0) {
            log_audit_action('admin_delete_last_super_blocked', 'users', "Blocked attempt to delete the last Super Admin #{$del_id}");
            set_flash('error', 'सुरक्षा अवरोध: सिस्टम में कम से कम एक सुपर एडमिन खाता रहना अनिवार्य है। अंतिम सुपर एडमिन को हटाया नहीं जा सकता।');
            header('Location: /admin/users.php');
            exit;
        }
    }

    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$del_id]);
    log_audit_action('admin_delete_user', 'users', "User #{$del_id} ({$target_user['username']}, {$target_user['role']}) deleted by Admin #{$admin_user['id']}");
    set_flash('success', 'उपयोगकर्ता सफलतापूर्वक हटा दिया गया।');
    header('Location: /admin/users.php');
    exit;
}

// Fetch all users
$stmt_users = $pdo->query("SELECT * FROM users ORDER BY id ASC");
$all_users = $stmt_users->fetchAll();

$page_title = 'यूजर एवं स्टाफ प्रशासन (Users & Staff Management)';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title & Action Bar with Responsive Padding -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-users-gear text-indigo-600"></i>
      यूजर एवं स्टाफ प्रशासन (Staff & User Governance)
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      Bcrypt हार्ड एन्क्रिप्टेड क्रेडेंशियल्स • यूजरनेम, रोल और पासवर्ड का केंद्रीय प्रबंधन
    </p>
  </div>

  <div class="flex flex-wrap items-center gap-2">
    <a href="/admin/export_users.php" class="px-3 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-xl border border-stone-300 transition flex items-center gap-1.5" title="यूजर सूची को CSV फॉर्मेट में डाउनलोड करें">
      <i class="fa-solid fa-file-export text-emerald-600"></i> एक्सपोर्ट CSV
    </a>
    <button onclick="document.getElementById('importUsersModal').classList.remove('hidden')"
      class="px-3 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-xl border border-stone-300 transition flex items-center gap-1.5" title="CSV फ़ाइल से बल्क यूजर जोड़ें">
      <i class="fa-solid fa-file-import text-blue-600"></i> इम्पोर्ट CSV
    </button>
    <button onclick="document.getElementById('addUserModal').classList.remove('hidden')"
      class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
      <i class="fa-solid fa-user-plus"></i> नया यूजर / स्टाफ जोड़ें
    </button>
  </div>
</div>

<!-- Security Alert Notice -->
<div class="mb-6 p-4 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-900 text-xs flex items-center gap-3">
  <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0 text-sm">
    <i class="fa-solid fa-lock"></i>
  </div>
  <div class="flex-1">
    <strong class="font-bold">सख्त Bcrypt सुरक्षा नीति लागू:</strong>
    डेटाबेस में कोई भी पासवर्ड कभी भी प्लेन टेक्स्ट में नहीं रखा जाता है। सभी पासवर्ड वन-वे Bcrypt 60-कैरेक्टर हैश के साथ एन्क्रिप्टेड हैं। रीसेट करने के लिए नीचे दिए गए 'पासवर्ड रीसेट' बटन का उपयोग करें।
  </div>
</div>

<!-- Users Table Card with Proper Margins -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
      <thead class="bg-stone-50 border-b border-gray-200 text-gray-500 font-bold uppercase text-[10px]">
        <tr>
          <th class="px-4 py-3.5">कोड / आईडी</th>
          <th class="px-4 py-3.5">यूजर का नाम व यूजरनेम</th>
          <th class="px-4 py-3.5">रोल (Role)</th>
          <th class="px-4 py-3.5">विभाग / संस्थान</th>
          <th class="px-4 py-3.5">संपर्क ईमेल व फोन</th>
          <th class="px-4 py-3.5">पासवर्ड सुरक्षा</th>
          <th class="px-4 py-3.5">स्थिति</th>
          <th class="px-4 py-3.5 text-right">कार्य (Actions)</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($all_users as $u): ?>
          <tr class="hover:bg-stone-50/50 transition">
            <!-- Code -->
            <td class="px-4 py-3.5 font-mono text-[11px] font-bold text-gray-800">
              <?= htmlspecialchars($u['unique_code'] ?? ('GGH-USR-' . $u['id'])) ?>
            </td>

            <!-- Name & Username -->
            <td class="px-4 py-3.5">
              <div class="font-bold text-gray-900"><?= htmlspecialchars($u['name']) ?></div>
              <div class="text-[11px] text-indigo-700 font-mono font-semibold">@<?= htmlspecialchars($u['username']) ?></div>
            </td>

            <!-- Role Badge -->
            <td class="px-4 py-3.5">
              <?php if ($u['role'] === 'super_admin'): ?>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-purple-100 text-purple-800 border border-purple-200">
                  <i class="fa-solid fa-crown text-[9px] mr-1"></i> SUPER ADMIN
                </span>
              <?php elseif ($u['role'] === 'admin'): ?>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                  <i class="fa-solid fa-shield-halved text-[9px] mr-1"></i> ADMIN
                </span>
              <?php elseif ($u['role'] === 'vendor'): ?>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                  <i class="fa-solid fa-hammer text-[9px] mr-1"></i> ARTISAN / SELLER
                </span>
              <?php else: ?>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                  <i class="fa-solid fa-bag-shopping text-[9px] mr-1"></i> CUSTOMER
                </span>
              <?php endif; ?>
            </td>

            <!-- Department -->
            <td class="px-4 py-3.5 text-gray-600 max-w-xs truncate">
              <?= htmlspecialchars($u['department'] ?? 'General') ?>
            </td>

            <!-- Contact -->
            <td class="px-4 py-3.5">
              <div class="text-gray-800 truncate"><?= htmlspecialchars($u['email']) ?></div>
              <div class="text-[11px] text-gray-400 font-mono"><?= htmlspecialchars($u['phone']) ?></div>
            </td>

            <!-- Password Security (Never show raw password) -->
            <td class="px-4 py-3.5">
              <div class="flex items-center gap-1.5">
                <span class="font-mono text-[10px] bg-stone-100 text-stone-600 px-2 py-0.5 rounded border border-gray-200">
                  •••••••• (Bcrypt)
                </span>
                <button type="button" onclick="openResetPasswordModal(<?= $u['id'] ?>, '<?= htmlspecialchars($u['username']) ?>', '<?= htmlspecialchars($u['name']) ?>')"
                  class="text-[11px] text-indigo-600 hover:text-indigo-800 font-bold hover:underline" title="पासवर्ड रीसेट करें">
                  रीसेट
                </button>
              </div>
            </td>

            <!-- Status (POST + CSRF) -->
            <td class="px-4 py-3.5">
              <?php if ($u['id'] !== $admin_user['id'] && ($is_super || !in_array($u['role'], ['admin', 'super_admin'], true))): ?>
                <form method="POST" action="/admin/users.php" class="inline">
                  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                  <input type="hidden" name="toggle_status" value="1">
                  <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                  <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold cursor-pointer transition <?= $u['status'] === 'active' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-red-100 text-red-800 hover:bg-red-200' ?>"
                    title="स्थिति बदलें (POST + CSRF)">
                    <span class="w-1.5 h-1.5 rounded-full <?= $u['status'] === 'active' ? 'bg-emerald-600' : 'bg-red-600' ?>"></span>
                    <?= ucfirst($u['status']) ?>
                  </button>
                </form>
              <?php else: ?>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold <?= $u['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' ?>">
                  <span class="w-1.5 h-1.5 rounded-full <?= $u['status'] === 'active' ? 'bg-emerald-600' : 'bg-red-600' ?>"></span>
                  <?= ucfirst($u['status']) ?>
                </span>
              <?php endif; ?>
            </td>

            <!-- Actions (Strictly POST + CSRF) -->
            <td class="px-4 py-3.5 text-right whitespace-nowrap">
              <?php if ($is_super || !in_array($u['role'], ['admin', 'super_admin'], true) || $u['id'] === $admin_user['id']): ?>
                <button type="button" onclick='openEditUserModal(<?= json_encode([
                    "id" => $u["id"],
                    "name" => $u["name"],
                    "username" => $u["username"],
                    "email" => $u["email"],
                    "phone" => $u["phone"] ?? "",
                    "role" => $u["role"],
                    "department" => $u["department"] ?? "",
                    "status" => $u["status"]
                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                  class="px-2 py-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-[10px] rounded-lg border border-stone-300 transition inline-flex items-center gap-1 mr-1 cursor-pointer"
                  title="यूजर विवरण संपादित करें">
                  <i class="fa-solid fa-pen-to-square text-[9px] text-indigo-600"></i> एडिट
                </button>
              <?php endif; ?>

              <?php if ($u['id'] !== $admin_user['id']): ?>
                <!-- Impersonation: Super Admin Only, never on another Super Admin -->
                <?php if ($is_super && $u['role'] !== 'super_admin'): ?>
                  <form method="POST" action="/admin/impersonate.php" class="inline">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="login_as">
                    <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                    <button type="submit" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[10px] rounded-lg border border-indigo-200 transition inline-flex items-center gap-1 mr-1 cursor-pointer"
                      title="इस खाते में सुरक्षित लॉगिन करें (POST + CSRF)">
                      <i class="fa-solid fa-right-to-bracket text-[9px]"></i> लॉगिन
                    </button>
                  </form>
                <?php endif; ?>

                <!-- Delete User: Only Super Admin can delete admin/super_admin; POST + CSRF -->
                <?php if ($is_super || !in_array($u['role'], ['admin', 'super_admin'], true)): ?>
                  <form method="POST" action="/admin/users.php" class="inline" onsubmit="return confirm('क्या आप वाकई इस यूजर को हटाना चाहते हैं?');">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="delete_user" value="1">
                    <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50 transition cursor-pointer" title="हटाएं (POST + CSRF)">
                      <i class="fa-regular fa-trash-can"></i>
                    </button>
                  </form>
                <?php endif; ?>
              <?php else: ?>
                <span class="text-[10px] text-emerald-600 font-semibold italic">आप (Admin)</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add User Modal with Role Whitelisting & Strong Password -->
<div id="addUserModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
  <div class="bg-white rounded-3xl border border-gray-200 shadow-2xl max-w-lg w-full p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
      <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-user-plus text-indigo-600"></i>
        नया यूजर या स्टाफ खाता बनाएं (Add User)
      </h3>
      <button onclick="document.getElementById('addUserModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/admin/users.php" method="POST" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
      <input type="hidden" name="add_user" value="1">

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">पूरा नाम (Full Name) <span class="text-red-500">*</span></label>
        <input type="text" name="name" required placeholder="उदा. कुलदीप सिंह"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">यूजरनेम (Username - Login ID) <span class="text-red-500">*</span></label>
          <input type="text" name="username" required placeholder="उदा. kuldeep_ops"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">रोल (Role) <span class="text-red-500">*</span></label>
          <select name="role" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-indigo-600 transition">
            <?php if ($is_super): ?>
              <option value="admin">Admin (प्रशासनिक स्टाफ)</option>
              <option value="super_admin">Super Admin (सर्वोच्च नियंत्रण)</option>
            <?php endif; ?>
            <option value="vendor">Vendor (कारीगर / विक्रेता)</option>
            <option value="customer" selected>Customer (ग्राहक)</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">ईमेल पता (Email) <span class="text-red-500">*</span></label>
          <input type="email" name="email" required placeholder="name@ssindustries.in"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">फ़ोन नंबर (Mobile)</label>
          <input type="tel" name="phone" placeholder="+91 98765 43210"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">विभाग / पद (Department / Duty)</label>
        <input type="text" name="department" placeholder="उदा. Warehouse Dispatch, Quality Check..."
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <!-- Secure Password with Eye Toggle -->
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">
          लॉगिन पासवर्ड बनाएं (Password - न्यूनतम 10 अक्षर) <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <input type="password" name="password" id="new_user_password" required minlength="10" placeholder="न्यूनतम 10 अक्षर (मजबूत पासवर्ड)"
            class="w-full pl-3.5 pr-10 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
          <button type="button" onclick="togglePasswordVisibility('new_user_password', this)"
            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600">
            <i class="fa-regular fa-eye text-xs"></i>
          </button>
        </div>
        <p class="text-[11px] text-gray-400 mt-1">पासवर्ड कम से कम 10 अक्षरों का होना चाहिए तथा इसे Bcrypt द्वारा एन्क्रिप्ट किया जाएगा।</p>
      </div>

      <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
        <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')"
          class="px-4 py-2 bg-stone-100 text-stone-700 font-bold rounded-xl text-xs">
          रद्द करें
        </button>
        <button type="submit"
          class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition">
          उपयोगकर्ता जोड़ें (Create User)
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Reset Password Modal (POST + CSRF) -->
<div id="resetPasswordModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl border border-gray-200 shadow-2xl max-w-sm w-full p-6">
    <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
      <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-key text-amber-500"></i>
        पासवर्ड रीसेट करें
      </h3>
      <button onclick="document.getElementById('resetPasswordModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/admin/users.php" method="POST" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
      <input type="hidden" name="reset_user_password" value="1">
      <input type="hidden" name="target_user_id" id="reset_user_id">

      <div class="text-xs text-gray-600 bg-stone-50 p-3 rounded-xl border border-gray-100">
        उपयोगकर्ता: <strong id="reset_user_label" class="text-gray-900"></strong>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">नया पासवर्ड (New Password - न्यूनतम 10 अक्षर)</label>
        <div class="relative">
          <input type="password" name="new_password" id="reset_new_password" required minlength="10" placeholder="न्यूनतम 10 अक्षर"
            class="w-full pl-3.5 pr-10 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
          <button type="button" onclick="togglePasswordVisibility('reset_new_password', this)"
            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600">
            <i class="fa-regular fa-eye text-xs"></i>
          </button>
        </div>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" onclick="document.getElementById('resetPasswordModal').classList.add('hidden')"
          class="px-3 py-1.5 bg-stone-100 text-stone-700 font-bold rounded-lg text-xs">
          रद्द करें
        </button>
        <button type="submit"
          class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs transition">
          पासवर्ड सहेजें (Save)
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Import Users CSV Modal -->
<div id="importUsersModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl border border-gray-200 shadow-2xl max-w-md w-full p-6 sm:p-8">
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
      <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-file-import text-blue-600"></i>
        बल्क यूजर इम्पोर्ट करें (CSV Import)
      </h3>
      <button onclick="document.getElementById('importUsersModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/admin/import_users.php" method="POST" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">CSV फ़ाइल चुनें (.csv) <span class="text-red-500">*</span></label>
        <input type="file" name="csv_file" accept=".csv" required
          class="w-full text-xs text-stone-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl p-2 bg-stone-50">
        <p class="text-[11px] text-gray-400 mt-1.5">
          सुरक्षा: सभी इम्पोर्टेड उपयोगकर्ताओं के लिए सुरक्षित यादृच्छिक पासवर्ड बनाया जाएगा।
        </p>
      </div>

      <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
        <button type="button" onclick="document.getElementById('importUsersModal').classList.add('hidden')"
          class="px-4 py-2 bg-stone-100 text-stone-700 font-bold rounded-xl text-xs">
          रद्द करें
        </button>
        <button type="submit"
          class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-1.5 shadow-sm">
          <i class="fa-solid fa-cloud-arrow-up"></i>
          यूजर अपलोड करें
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
  <div class="bg-white rounded-3xl border border-gray-200 shadow-2xl max-w-lg w-full p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
      <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-user-pen text-indigo-600"></i>
        यूजर विवरण संपादित करें (Edit User)
      </h3>
      <button type="button" onclick="document.getElementById('editUserModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/admin/users.php" method="POST" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
      <input type="hidden" name="edit_user" value="1">
      <input type="hidden" name="edit_user_id" id="edit_user_id" value="">

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">पूरा नाम (Full Name) <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="edit_name" required
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">यूजरनेम (Username) <span class="text-red-500">*</span></label>
          <input type="text" name="username" id="edit_username" required
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">रोल (Role) <span class="text-red-500">*</span></label>
          <select name="role" id="edit_role" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-indigo-600 transition">
            <?php if ($is_super): ?>
              <option value="super_admin">Super Admin (सर्वोच्च नियंत्रण)</option>
              <option value="admin">Admin (प्रशासनिक स्टाफ)</option>
            <?php endif; ?>
            <option value="vendor">Vendor (कारीगर / विक्रेता)</option>
            <option value="customer">Customer (ग्राहक)</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">ईमेल (Email) <span class="text-red-500">*</span></label>
          <input type="email" name="email" id="edit_email" required
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">फ़ोन नंबर (Mobile)</label>
          <input type="tel" name="phone" id="edit_phone"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">विभाग / पद (Department)</label>
          <input type="text" name="department" id="edit_department"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">खाता स्थिति (Status) <span class="text-red-500">*</span></label>
          <select name="status" id="edit_status" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-indigo-600 transition">
            <option value="active">Active (सक्रिय)</option>
            <option value="suspended">Suspended (निलंबित)</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">
          नया पासवर्ड बदलें (वैकल्पिक - छोड़ें यदि नहीं बदलना हो, न्यूनतम 10 अक्षर)
        </label>
        <div class="relative">
          <input type="password" name="new_password" id="edit_user_password" minlength="10" placeholder="पासवर्ड नहीं बदलना हो तो खाली छोड़ें"
            class="w-full pl-3.5 pr-10 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
          <button type="button" onclick="togglePasswordVisibility('edit_user_password', this)"
            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600">
            <i class="fa-regular fa-eye text-xs"></i>
          </button>
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
        <button type="button" onclick="document.getElementById('editUserModal').classList.add('hidden')"
          class="px-4 py-2 bg-stone-100 text-stone-700 font-bold rounded-xl text-xs">
          रद्द करें
        </button>
        <button type="submit"
          class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition">
          अपडेट सहेजें (Save Changes)
        </button>
      </div>
    </form>
  </div>
</div>
            class="w-full pl-3.5 pr-10 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
          <button type="button" onclick="togglePasswordVisibility('edit_user_password', this)"
            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600">
            <i class="fa-regular fa-eye text-xs"></i>
          </button>
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
        <button type="button" onclick="document.getElementById('editUserModal').classList.add('hidden')"
          class="px-4 py-2 bg-stone-100 text-stone-700 font-bold rounded-xl text-xs">
          रद्द करें
        </button>
        <button type="submit"
          class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition">
          अपडेट सहेजें (Save Changes)
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditUserModal(u) {
  document.getElementById('edit_user_id').value = u.id || '';
  document.getElementById('edit_name').value = u.name || '';
  document.getElementById('edit_username').value = u.username || '';
  document.getElementById('edit_email').value = u.email || '';
  document.getElementById('edit_phone').value = u.phone || '';
  document.getElementById('edit_role').value = u.role || 'customer';
  document.getElementById('edit_department').value = u.department || '';
  document.getElementById('edit_status').value = u.status || 'active';
  document.getElementById('edit_user_password').value = '';
  document.getElementById('editUserModal').classList.remove('hidden');
}

function openResetPasswordModal(userId, username, name) {
  document.getElementById('reset_user_id').value = userId;
  document.getElementById('reset_user_label').innerText = name + ' (@' + username + ')';
  document.getElementById('resetPasswordModal').classList.remove('hidden');
}

function togglePasswordVisibility(inputId, btn) {
  const input = document.getElementById(inputId);
  const icon = btn.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.remove('fa-eye');
    icon.classList.add('fa-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.remove('fa-eye-slash');
    icon.classList.add('fa-eye');
  }
}
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
