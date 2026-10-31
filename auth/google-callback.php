<?php
/**
 * GaunGaliHat Google Identity Services & OAuth 2.0 Callback
 * Security Hardened: Full ID Token Verification (Signature, aud, iss, exp, email_verified)
 * Restricts Google Login strictly to 'customer' role.
 */

require_once __DIR__ . '/../includes/functions.php';

// Fetch Google Settings from config / site_settings
$google_client_id = defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : get_site_setting('google_client_id', '');
$google_client_secret = defined('GOOGLE_CLIENT_SECRET') ? GOOGLE_CLIENT_SECRET : get_site_setting('google_client_secret', '');
$google_enabled = (get_site_setting('google_auth_enabled', '1') == '1') && !empty($google_client_id);

if (!$google_enabled) {
    set_flash('error', 'Google सोशल लॉगिन वर्तमान में व्यवस्थापक द्वारा अक्षम है।');
    header('Location: /login.php');
    exit;
}

$auto_register = (get_site_setting('social_auto_register', '1') == '1');

/**
 * Verify Google ID Token using Google's TokenInfo API
 * Cryptographically checks signature against Google's public certs, expiry, audience, issuer.
 */
function verify_google_id_token($id_token, $expected_client_id) {
    if (empty($id_token) || !is_string($id_token)) {
        return false;
    }

    $ch = curl_init('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($id_token));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || empty($response)) {
        error_log("Google ID Token verification failed with HTTP " . $http_code);
        return false;
    }

    $payload = json_decode($response, true);
    if (!is_array($payload)) {
        return false;
    }

    // 1. Verify Audience matches configured Client ID
    if (empty($payload['aud']) || $payload['aud'] !== $expected_client_id) {
        error_log("Google Token Audience mismatch: expected {$expected_client_id}, got " . ($payload['aud'] ?? 'none'));
        return false;
    }

    // 2. Verify Issuer is Google
    $valid_issuers = ['accounts.google.com', 'https://accounts.google.com'];
    if (empty($payload['iss']) || !in_array($payload['iss'], $valid_issuers, true)) {
        error_log("Google Token Issuer invalid: " . ($payload['iss'] ?? 'none'));
        return false;
    }

    // 3. Verify Expiry
    if (empty($payload['exp']) || (int)$payload['exp'] < time()) {
        error_log("Google Token has expired");
        return false;
    }

    // 4. Verify Email is Verified by Google
    $email_verified = $payload['email_verified'] ?? false;
    if ($email_verified !== true && $email_verified !== 'true') {
        error_log("Google Token email not verified by Google");
        return false;
    }

    // 5. Must have email
    if (empty($payload['email']) || !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    return $payload;
}

$verified_payload = null;

// Case 1: Google Identity Services POST credential (One-tap / Button JWT ID Token)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['credential'])) {
    $verified_payload = verify_google_id_token($_POST['credential'], $google_client_id);
}
// Case 2: Standard OAuth 2.0 Authorization Code flow via GET
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['code'])) {
    $code = trim($_GET['code']);
    $redirect_uri = defined('SITE_URL') ? SITE_URL . '/auth/google-callback.php' : 'https://ganvgalihat.com/auth/google-callback.php';

    // Exchange authorization code for token via Google Token Endpoint
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'code'          => $code,
        'client_id'     => $google_client_id,
        'client_secret' => $google_client_secret,
        'redirect_uri'  => $redirect_uri,
        'grant_type'    => 'authorization_code'
    ]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200 && !empty($response)) {
        $token_data = json_decode($response, true);
        if (!empty($token_data['id_token'])) {
            $verified_payload = verify_google_id_token($token_data['id_token'], $google_client_id);
        }
    } else {
        error_log("Google OAuth code exchange failed with HTTP " . $http_code);
    }
}

if (!$verified_payload || empty($verified_payload['email'])) {
    log_audit_action('auth_google_failed', 'auth', 'Google identity token verification rejected or unverified');
    set_flash('error', 'Google प्रमाणीकरण विफल रहा या सुरक्षा सत्यापन अस्वीकृत हुआ। कृपया पुनः प्रयास करें।');
    header('Location: /login.php');
    exit;
}

$email = strtolower(trim($verified_payload['email']));
$name = trim($verified_payload['name'] ?? explode('@', $email)[0]);
$picture = trim($verified_payload['picture'] ?? '');
$google_id = trim($verified_payload['sub'] ?? '');

// Check if user exists in database
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // SECURITY REQUIREMENT: Never auto-login existing admin / super_admin / vendor accounts through Google!
        // Google login is strictly permitted for role 'customer' only.
        if ($user['role'] !== 'customer') {
            log_audit_action(
                'auth_google_blocked_privileged',
                'users',
                "Blocked Google login attempt for privileged account {$email} (Role: {$user['role']})"
            );
            set_flash('error', 'सुरक्षा सूचना: प्रशासनिक अथवा विक्रेता खातों के लिए Google सोशल लॉगिन की अनुमति नहीं है। कृपया अपने अधिकृत पासवर्ड के साथ सामान्य लॉगिन करें।');
            header('Location: /login.php');
            exit;
        }

        if ($user['status'] !== 'active') {
            set_flash('error', 'आपका खाता निलंबित (Suspended) या निष्क्रिय है। कृपया व्यवस्थापक से संपर्क करें।');
            header('Location: /login.php');
            exit;
        }

        // Regenerate Session ID on successful login to prevent Session Fixation
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = 'customer';
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['last_activity'] = time();

        // Update avatar and email verification if needed
        if (empty($user['avatar']) && !empty($picture)) {
            $pdo->prepare("UPDATE users SET avatar = ?, email_verified_at = IFNULL(email_verified_at, CURRENT_TIMESTAMP) WHERE id = ?")
                ->execute([$picture, $user['id']]);
        }

        log_audit_action('auth_google_login', 'users', "Customer #{$user['id']} logged in via Google OAuth");
        set_flash('success', 'नमस्ते ' . htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') . ', Google से सफलतापूर्वक लॉगिन किया गया!');
        header('Location: /account.php');
        exit;

    } else {
        // User does not exist -> Register new user ONLY as 'customer'
        if (!$auto_register) {
            set_flash('error', 'इस Google ईमेल से कोई खाता पंजीकृत नहीं है। कृपया पहले पंजीकरण करें।');
            header('Location: /register.php');
            exit;
        }

        $base_username = strtolower(preg_replace('/[^a-z0-9]/', '', explode('@', $email)[0]));
        if (empty($base_username)) $base_username = 'user';
        $username = $base_username . '_' . random_int(100, 999);
        $unique_code = 'GGH-CST-' . bin2hex(random_bytes(3));
        
        // Random secure password for OAuth accounts
        $rand_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);

        $stmt_ins = $pdo->prepare("
            INSERT INTO users (
                unique_code, name, username, email, phone, password, role, avatar, status, email_verified_at, created_at
            ) VALUES (
                ?, ?, ?, ?, '', ?, 'customer', ?, 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            )
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

        // Regenerate Session ID
        session_regenerate_id(true);

        $_SESSION['user_id'] = $new_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_role'] = 'customer';
        $_SESSION['user_email'] = $email;
        $_SESSION['last_activity'] = time();

        log_audit_action('auth_google_register', 'users', "New customer #{$new_id} registered via Google OAuth");
        set_flash('success', 'स्वागत है ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '! आपका नया GaunGaliHat ग्राहक खाता बन गया है।');
        header('Location: /account.php');
        exit;
    }
} catch (Exception $e) {
    error_log("Google Auth Database Error: " . $e->getMessage());
    set_flash('error', 'लॉगिन प्रक्रिया के दौरान तकनीकी त्रुटि उत्पन्न हुई। कृपया पुनः प्रयास करें।');
    header('Location: /login.php');
    exit;
}
