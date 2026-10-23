<?php
/**
 * GaunGaliHat Google OAuth 2.0 / Google Identity Services Callback
 */

require_once __DIR__ . '/../includes/functions.php';

// Fetch Google Settings
$stmt_settings = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('google_auth_enabled', 'google_client_id', 'google_client_secret', 'social_auto_register')");
$settings = [];
while ($r = $stmt_settings->fetch()) {
    $settings[$r['setting_key']] = $r['setting_value'];
}

$google_enabled = ($settings['google_auth_enabled'] ?? '1') == '1';
if (!$google_enabled) {
    set_flash('error', 'Google सोशल लॉगिन वर्तमान में व्यवस्थापक द्वारा अक्षम है।');
    header('Location: /login.php');
    exit;
}

$client_id = $settings['google_client_id'] ?? '';
$auto_register = ($settings['social_auto_register'] ?? '1') == '1';

$email = '';
$name = '';
$picture = '';
$google_id = '';

// Case 1: Google Identity Services POST credential (JWT ID Token)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['credential'])) {
    $id_token = $_POST['credential'];
    $parts = explode('.', $id_token);
    if (count($parts) === 3) {
        $payload_json = base64_decode(strtr($parts[1], '-_', '+/'));
        $payload = json_decode($payload_json, true);
        if ($payload && !empty($payload['email'])) {
            $email = strtolower(trim($payload['email']));
            $name = trim($payload['name'] ?? explode('@', $email)[0]);
            $picture = trim($payload['picture'] ?? '');
            $google_id = trim($payload['sub'] ?? '');
        }
    }
}
// Case 2: Standard OAuth 2.0 Authorization Code flow via GET
elseif (!empty($_GET['code'])) {
    $code = $_GET['code'];
    $redirect_uri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/auth/google-callback.php';

    // Exchange code for token via Google Token Endpoint
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'code'          => $code,
        'client_id'     => $client_id,
        'client_secret' => $settings['google_client_secret'] ?? '',
        'redirect_uri'  => $redirect_uri,
        'grant_type'    => 'authorization_code'
    ]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $token_data = json_decode($response, true);
        if (!empty($token_data['id_token'])) {
            $parts = explode('.', $token_data['id_token']);
            if (count($parts) === 3) {
                $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
                if ($payload && !empty($payload['email'])) {
                    $email = strtolower(trim($payload['email']));
                    $name = trim($payload['name'] ?? explode('@', $email)[0]);
                    $picture = trim($payload['picture'] ?? '');
                    $google_id = trim($payload['sub'] ?? '');
                }
            }
        }
    }
}

if (empty($email)) {
    set_flash('error', 'Google प्रमाणीकरण विफल रहा या ईमेल प्राप्त नहीं हुआ। कृपया पुनः प्रयास करें।');
    header('Location: /login.php');
    exit;
}

// Check if user exists in database
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // User exists -> Log them in!
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        // If avatar is empty, update with Google avatar
        if (empty($user['avatar']) && !empty($picture)) {
            $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?")->execute([$picture, $user['id']]);
        }

        set_flash('success', 'नमस्ते ' . htmlspecialchars($user['name']) . ', Google से सफलतापूर्वक लॉगिन किया गया!');
        
        if (in_array($user['role'], ['super_admin', 'admin'])) {
            header('Location: /admin/index.php');
        } elseif ($user['role'] === 'vendor') {
            header('Location: /seller/dashboard.php');
        } else {
            header('Location: /account.php');
        }
        exit;
    } else {
        // User does not exist -> Register new user if enabled
        if (!$auto_register) {
            set_flash('error', 'इस Google ईमेल से कोई खाता नहीं मिला। कृपया पहले पंजीकरण करें।');
            header('Location: /register.php');
            exit;
        }

        $base_username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $email)[0]));
        $username = $base_username . '_' . rand(100, 999);
        $unique_code = 'GGH-' . rand(10000, 99999);
        $rand_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);

        $stmt_ins = $pdo->prepare("
            INSERT INTO users (unique_code, name, username, email, phone, password, role, avatar, status, created_at)
            VALUES (?, ?, ?, ?, '', ?, 'customer', ?, 'active', CURRENT_TIMESTAMP)
        ");
        $stmt_ins->execute([
            $unique_code,
            $name,
            $username,
            $email,
            $rand_password,
            $picture
        ]);

        $new_id = $pdo->lastInsertId();
        $_SESSION['user_id'] = $new_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_role'] = 'customer';

        set_flash('success', 'स्वागत है ' . htmlspecialchars($name) . '! आपका नया GaunGaliHat ग्राहक खाता स्वतः बन गया है।');
        header('Location: /account.php');
        exit;
    }
} catch (Exception $e) {
    set_flash('error', 'डेटाबेस त्रुटि: ' . $e->getMessage());
    header('Location: /login.php');
    exit;
}
