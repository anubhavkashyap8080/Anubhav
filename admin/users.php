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
        set_flash('error', 'CSRF verification failed. Please refresh the page and try again.');
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
        set_flash('error', 'Unauthorized: Only Super Administrators can create Admin or Super Admin accounts.');
        header('Location: /admin/users.php');
        exit;
    }

    $role = in_array($raw_role, $allowed_roles, true) ? $raw_role : 'customer';

    $pass_err = '';
    if (empty($name) || empty($username) || empty($email) || empty($password)) {
        set_flash('error', 'Please fill in Name, Username, Email, and Password.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('error', 'Please enter a valid email address.');
    } elseif (!is_strong_password($password, $pass_err)) {
        set_flash('error', $pass_err);
    } else {
        // Check uniqueness
        $stmt_chk = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt_chk->execute([$username, $email]);
        if ($stmt_chk->fetch()) {
            set_flash('error', 'This username or email is already registered.');
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

                set_flash('success', 'New user (' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . ') created successfully with Bcrypt encryption!');
                header('Location: /admin/users.php');
                exit;
            } catch (Exception $e) {
                error_log("Error creating user: " . $e->getMessage());
                set_flash('error', 'Database error creating user account.');
            }
        }
    }
}

// 2. Handle EDIT USER (POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'CSRF verification failed. Please refresh.');
        header('Location: /admin/users.php');
        exit;
    }

    $user_id = intval($_POST['edit_user_id'] ?? 0);
    $name = clean_input($_POST['name'] ?? '');
    $username = strtolower(clean_input($_POST['username'] ?? ''));
    $email = strtolower(clean_input($_POST['email'] ?? ''));
    $phone = clean_input($_POST['phone'] ?? '');
    $raw_role = clean_input($_POST['role'] ?? 'customer');
    $department = clean_input($_POST['department'] ?? 'General');
    $status = in_array($_POST['status'] ?? '', ['active', 'suspended'], true) ? $_POST['status'] : 'active';
    $new_password = $_POST['new_password'] ?? '';

    $stmt_old = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt_old->execute([$user_id]);
    $old_user = $stmt_old->fetch();

    if (!$old_user) {
        set_flash('error', 'User account not found.');
        header('Location: /admin/users.php');
        exit;
    }

    if (empty($name) || empty($username) || empty($email)) {
        set_flash('error', 'Please fill in Name, Username, and Email.');
        header('Location: /admin/users.php');
        exit;
    }

    if (!$is_super && in_array($old_user['role'], ['admin', 'super_admin'], true) && $old_user['id'] !== $admin_user['id']) {
        set_flash('error', 'Unauthorized: Only Super Administrators can edit administrative accounts.');
        header('Location: /admin/users.php');
        exit;
    }

    if (!$is_super && in_array($raw_role, ['admin', 'super_admin'], true) && $old_user['role'] !== $raw_role) {
        set_flash('error', 'Unauthorized: Only Super Administrators can grant administrative privileges.');
        header('Location: /admin/users.php');
        exit;
    }

    // Check unique username and email excluding current user
    $stmt_chk = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt_chk->execute([$username, $email, $user_id]);
    if ($stmt_chk->fetch()) {
        set_flash('error', 'This username or email is already taken by another user.');
        header('Location: /admin/users.php');
        exit;
    }

    try {
        if (!empty($new_password)) {
            $pass_err = '';
            if (!is_strong_password($new_password, $pass_err)) {
                set_flash('error', $pass_err);
                header('Location: /admin/users.php');
                exit;
            }
            $hash = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt_up = $pdo->prepare("UPDATE users SET name = ?, username = ?, email = ?, phone = ?, role = ?, department = ?, status = ?, password = ? WHERE id = ?");
            $stmt_up->execute([$name, $username, $email, $phone, $raw_role, $department, $status, $hash, $user_id]);
        } else {
            $stmt_up = $pdo->prepare("UPDATE users SET name = ?, username = ?, email = ?, phone = ?, role = ?, department = ?, status = ? WHERE id = ?");
            $stmt_up->execute([$name, $username, $email, $phone, $raw_role, $department, $status, $user_id]);
        }

        log_audit_action('admin_edit_user', 'users', "Updated details for User #{$user_id} (@{$username})");
        set_flash('success', 'User details updated successfully for @' . htmlspecialchars($username) . '!');
        header('Location: /admin/users.php');
        exit;
    } catch (Exception $e) {
        set_flash('error', 'Error updating user: ' . $e->getMessage());
        header('Location: /admin/users.php');
        exit;
    }
}

// 3. Handle RESET PASSWORD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_user_password'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'CSRF verification failed.');
        header('Location: /admin/users.php');
        exit;
    }

    $target_id = intval($_POST['target_user_id'] ?? 0);
    $new_pass = $_POST['new_password'] ?? '';

    $pass_err = '';
    if (!is_strong_password($new_pass, $pass_err)) {
        set_flash('error', $pass_err);
        header('Location: /admin/users.php');
        exit;
    }

    $hash = password_hash($new_pass, PASSWORD_BCRYPT);
    $stmt_up = $pdo->prepare("UPDATE users SET password = ?, password_changed_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt_up->execute([$hash, $target_id]);

    log_audit_action('admin_reset_password', 'users', "Reset password for User #{$target_id}");
    set_flash('success', 'User password reset successfully with Bcrypt encryption.');
    header('Location: /admin/users.php');
    exit;
}

// 4. Handle TOGGLE STATUS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'CSRF verification failed.');
        header('Location: /admin/users.php');
        exit;
    }

    $target_id = intval($_POST['target_user_id'] ?? 0);
    $stmt_u = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt_u->execute([$target_id]);
    $target_u = $stmt_u->fetch();

    if ($target_u) {
        if ($target_u['id'] === $admin_user['id']) {
            set_flash('error', 'You cannot suspend your own account.');
            header('Location: /admin/users.php');
            exit;
        }

        $new_stat = ($target_u['status'] === 'active') ? 'suspended' : 'active';
        $pdo->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$new_stat, $target_id]);
        log_audit_action('admin_toggle_status', 'users', "User #{$target_id} status changed to '{$new_stat}'");
        set_flash('success', "User account status updated to {$new_stat}.");
        header('Location: /admin/users.php');
        exit;
    }
}

// 5. Handle DELETE USER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'CSRF verification failed.');
        header('Location: /admin/users.php');
        exit;
    }

    $del_id = intval($_POST['target_user_id'] ?? 0);
    $stmt_tgt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt_tgt->execute([$del_id]);
    $target_user = $stmt_tgt->fetch();

    if (!$target_user) {
        set_flash('error', 'User account not found.');
        header('Location: /admin/users.php');
        exit;
    }

    if ($del_id === $admin_user['id']) {
        set_flash('error', 'You cannot delete your own account.');
        header('Location: /admin/users.php');
        exit;
    }

    if ($target_user['role'] === 'super_admin') {
        $super_count = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'super_admin' AND id != {$del_id}")->fetchColumn();
        if ($super_count <= 0) {
            set_flash('error', 'Security rule: System must retain at least one Super Admin account.');
            header('Location: /admin/users.php');
            exit;
        }
    }

    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$del_id]);
    log_audit_action('admin_delete_user', 'users', "User #{$del_id} (@{$target_user['username']}) deleted");
    set_flash('success', 'User account removed successfully.');
    header('Location: /admin/users.php');
    exit;
}

// Fetch all users
$stmt_users = $pdo->query("SELECT * FROM users ORDER BY id ASC");
$all_users = $stmt_users->fetchAll();

$page_title = 'Users & Staff Administration — Master Admin';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title & Action Bar -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-users-gear text-indigo-600"></i>
      Users & Staff Administration
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      Bcrypt hardened credentials • Central role, privilege, and account governance
    </p>
  </div>

  <div class="flex flex-wrap items-center gap-2">
    <a href="/admin/export_users.php" class="px-3 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-xl border border-stone-300 transition flex items-center gap-1.5" title="Export users list as CSV">
      <i class="fa-solid fa-file-export text-emerald-600"></i> Export CSV
    </a>
    <button onclick="document.getElementById('importUsersModal').classList.remove('hidden')"
      class="px-3 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-xl border border-stone-300 transition flex items-center gap-1.5 cursor-pointer">
      <i class="fa-solid fa-file-import text-blue-600"></i> Import CSV
    </button>
    <button onclick="document.getElementById('addUserModal').classList.remove('hidden')"
      class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2 cursor-pointer">
      <i class="fa-solid fa-user-plus"></i> Add New User
    </button>
  </div>
</div>

<!-- Security Alert Notice -->
<div class="mb-6 p-4 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-900 text-xs flex items-center gap-3">
  <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0 text-sm">
    <i class="fa-solid fa-lock"></i>
  </div>
  <div class="flex-1">
    <strong class="font-bold">Strict Bcrypt Security Policy Active:</strong>
    All user passwords are encrypted using one-way 60-character Bcrypt hashes. No plaintext passwords exist in the database. Use the 'Reset' button to generate a new password when requested.
  </div>
</div>

<!-- Users Table Card -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
      <thead class="bg-stone-50 border-b border-gray-200 text-gray-500 font-bold uppercase text-[10px]">
        <tr>
          <th class="px-4 py-3.5">Code / ID</th>
          <th class="px-4 py-3.5">Name & Username</th>
          <th class="px-4 py-3.5">Role</th>
          <th class="px-4 py-3.5">Department</th>
          <th class="px-4 py-3.5">Contact Email & Phone</th>
          <th class="px-4 py-3.5">Security</th>
          <th class="px-4 py-3.5">Status</th>
          <th class="px-4 py-3.5 text-right">Actions</th>
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

            <!-- Password Security -->
            <td class="px-4 py-3.5">
              <div class="flex items-center gap-1.5">
                <span class="font-mono text-[10px] bg-stone-100 text-stone-600 px-2 py-0.5 rounded border border-gray-200">
                  •••••••• (Bcrypt)
                </span>
                <button type="button" onclick="openResetPasswordModal(<?= $u['id'] ?>, '<?= htmlspecialchars($u['username']) ?>', '<?= htmlspecialchars($u['name']) ?>')"
                  class="text-[11px] text-indigo-600 hover:text-indigo-800 font-bold hover:underline cursor-pointer">
                  Reset
                </button>
              </div>
            </td>

            <!-- Status -->
            <td class="px-4 py-3.5">
              <?php if ($u['id'] !== $admin_user['id'] && ($is_super || !in_array($u['role'], ['admin', 'super_admin'], true))): ?>
                <form method="POST" action="/admin/users.php" class="inline">
                  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                  <input type="hidden" name="toggle_status" value="1">
                  <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                  <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold cursor-pointer transition <?= $u['status'] === 'active' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-red-100 text-red-800 hover:bg-red-200' ?>"
                    title="Toggle Status">
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

            <!-- Actions -->
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
                  class="px-2 py-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-[10px] rounded-lg border border-stone-300 transition inline-flex items-center gap-1 mr-1 cursor-pointer">
                  <i class="fa-solid fa-pen-to-square text-[9px] text-indigo-600"></i> Edit
                </button>
              <?php endif; ?>

              <?php if ($u['id'] !== $admin_user['id']): ?>
                <!-- Impersonation -->
                <?php if ($is_super && $u['role'] !== 'super_admin'): ?>
                  <form method="POST" action="/admin/impersonate.php" class="inline">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="login_as">
                    <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                    <button type="submit" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[10px] rounded-lg border border-indigo-200 transition inline-flex items-center gap-1 mr-1 cursor-pointer">
                      <i class="fa-solid fa-right-to-bracket text-[9px]"></i> Sign In As
                    </button>
                  </form>
                <?php endif; ?>

                <!-- Delete User -->
                <?php if ($is_super || !in_array($u['role'], ['admin', 'super_admin'], true)): ?>
                  <form method="POST" action="/admin/users.php" class="inline" onsubmit="return confirm('Are you sure you want to remove this user account?');">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="delete_user" value="1">
                    <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50 transition cursor-pointer" title="Delete User">
                      <i class="fa-regular fa-trash-can"></i>
                    </button>
                  </form>
                <?php endif; ?>
              <?php else: ?>
                <span class="text-[10px] text-emerald-600 font-semibold italic">You (Current)</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
  <div class="bg-white rounded-3xl border border-gray-200 shadow-2xl max-w-lg w-full p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
      <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-user-plus text-indigo-600"></i>
        Add New User or Staff Account
      </h3>
      <button onclick="document.getElementById('addUserModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/admin/users.php" method="POST" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
      <input type="hidden" name="add_user" value="1">

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
        <input type="text" name="name" required placeholder="e.g. Kuldeep Singh"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Username (Login ID) <span class="text-red-500">*</span></label>
          <input type="text" name="username" required placeholder="e.g. kuldeep_ops"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
          <select name="role" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-indigo-600 transition">
            <?php if ($is_super): ?>
              <option value="admin">Admin (Operational Staff)</option>
              <option value="super_admin">Super Admin (Central Governance)</option>
            <?php endif; ?>
            <option value="vendor">Vendor (Artisan / Seller)</option>
            <option value="customer" selected>Customer (Shopper)</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
          <input type="email" name="email" required placeholder="name@ganvgalihat.com"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Mobile Phone</label>
          <input type="tel" name="phone" placeholder="+91 98765 43210"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Department / Assigned Function</label>
        <input type="text" name="department" placeholder="e.g. Warehouse Dispatch, Quality Check..."
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">
          Password (Min 10 characters) <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <input type="password" name="password" id="new_user_password" required minlength="10" placeholder="Minimum 10 characters (Strong password)"
            class="w-full pl-3.5 pr-10 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
          <button type="button" onclick="togglePasswordVisibility('new_user_password', this)"
            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600">
            <i class="fa-regular fa-eye text-xs"></i>
          </button>
        </div>
        <p class="text-[11px] text-gray-400 mt-1">Must contain uppercase, lowercase, number, and special character. Encrypted via Bcrypt.</p>
      </div>

      <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
        <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')"
          class="px-4 py-2 bg-stone-100 text-stone-700 font-bold rounded-xl text-xs cursor-pointer">
          Cancel
        </button>
        <button type="submit"
          class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition cursor-pointer">
          Create User Account
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Reset Password Modal -->
<div id="resetPasswordModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl border border-gray-200 shadow-2xl max-w-sm w-full p-6">
    <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
      <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-key text-amber-500"></i>
        Reset User Password
      </h3>
      <button onclick="document.getElementById('resetPasswordModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 cursor-pointer">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/admin/users.php" method="POST" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
      <input type="hidden" name="reset_user_password" value="1">
      <input type="hidden" name="target_user_id" id="reset_user_id">

      <div class="text-xs text-gray-600 bg-stone-50 p-3 rounded-xl border border-gray-100">
        User: <strong id="reset_user_label" class="text-gray-900"></strong>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">New Password (Min 10 characters)</label>
        <div class="relative">
          <input type="password" name="new_password" id="reset_new_password" required minlength="10" placeholder="Minimum 10 characters"
            class="w-full pl-3.5 pr-10 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
          <button type="button" onclick="togglePasswordVisibility('reset_new_password', this)"
            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600">
            <i class="fa-regular fa-eye text-xs"></i>
          </button>
        </div>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" onclick="document.getElementById('resetPasswordModal').classList.add('hidden')"
          class="px-3 py-1.5 bg-stone-100 text-stone-700 font-bold rounded-lg text-xs cursor-pointer">
          Cancel
        </button>
        <button type="submit"
          class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs transition cursor-pointer">
          Save Password
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
        Bulk User Import (CSV)
      </h3>
      <button onclick="document.getElementById('importUsersModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/admin/import_users.php" method="POST" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Select CSV File (.csv) <span class="text-red-500">*</span></label>
        <input type="file" name="csv_file" accept=".csv" required
          class="w-full text-xs text-stone-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl p-2 bg-stone-50">
        <p class="text-[11px] text-gray-400 mt-1.5">
          Security: Strong random initial passwords are automatically generated for all imported records.
        </p>
      </div>

      <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
        <button type="button" onclick="document.getElementById('importUsersModal').classList.add('hidden')"
          class="px-4 py-2 bg-stone-100 text-stone-700 font-bold rounded-xl text-xs cursor-pointer">
          Cancel
        </button>
        <button type="submit"
          class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-1.5 shadow-sm cursor-pointer">
          <i class="fa-solid fa-cloud-arrow-up"></i>
          Upload & Import
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
        Edit User Details
      </h3>
      <button type="button" onclick="document.getElementById('editUserModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/admin/users.php" method="POST" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
      <input type="hidden" name="edit_user" value="1">
      <input type="hidden" name="edit_user_id" id="edit_user_id" value="">

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="edit_name" required
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
          <input type="text" name="username" id="edit_username" required
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
          <select name="role" id="edit_role" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-indigo-600 transition">
            <?php if ($is_super): ?>
              <option value="super_admin">Super Admin</option>
              <option value="admin">Admin</option>
            <?php endif; ?>
            <option value="vendor">Vendor (Artisan / Seller)</option>
            <option value="customer">Customer (Shopper)</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
          <input type="email" name="email" id="edit_email" required
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Mobile Phone</label>
          <input type="tel" name="phone" id="edit_phone"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Department / Duty</label>
          <input type="text" name="department" id="edit_department"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Account Status <span class="text-red-500">*</span></label>
          <select name="status" id="edit_status" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-indigo-600 transition">
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">
          Change Password (Optional - leave blank to keep unchanged)
        </label>
        <div class="relative">
          <input type="password" name="new_password" id="edit_user_password" minlength="10" placeholder="Leave blank to keep unchanged"
            class="w-full pl-3.5 pr-10 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
          <button type="button" onclick="togglePasswordVisibility('edit_user_password', this)"
            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600">
            <i class="fa-regular fa-eye text-xs"></i>
          </button>
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
        <button type="button" onclick="document.getElementById('editUserModal').classList.add('hidden')"
          class="px-4 py-2 bg-stone-100 text-stone-700 font-bold rounded-xl text-xs cursor-pointer">
          Cancel
        </button>
        <button type="submit"
          class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition cursor-pointer">
          Save Changes
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
