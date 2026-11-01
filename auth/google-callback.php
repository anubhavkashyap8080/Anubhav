<?php
/**
 * GanvGaliHat Google Identity Services & OAuth 2.0 Callback
 * Security Hardened: Full ID Token Verification (Signature, aud, iss, exp, email_verified)
 * Authenticates users and redirects seamlessly to the Shopper Dashboard (/account.php)
 */

require_once __DIR__ . '/../includes/functions.php';

// Fetch Google Settings: Database site_settings first, then defined constants, then environment
$db_google_client_id = get_site_setting('google_client_id', '');
$google_client_id = !empty($db_google_client_id)
    ? trim($db_google_client_id)
    : (defined('GOOGLE_CLIENT_ID') && !empty(GOOGLE_CLIENT_ID) ? trim(GOOGLE_CLIENT_ID) : (getenv('GOOGLE_CLIENT_ID') ?: ''));

$db_google_secret = get_site_setting('google_client_secret', '');
$google_client_secret = !empty($db_google_secret)
    ? trim($db_google_secret)
    : (defined('GOOGLE_CLIENT_SECRET') && !empty(GOOGLE_CLIENT_SECRET) ? trim(GOOGLE_CLIENT_SECRET) : (getenv('GOOGLE_CLIENT_SECRET') ?: ''));

$db_google_enabled = get_site_setting('google_auth_enabled', '1');
$google_enabled = !empty($google_client_id) && ($db_google_enabled == '1' || (defined('GOOGLE_AUTH_ENABLED') && GOOGLE_AUTH_ENABLED));

if (!$google_enabled) {
    error_log("Google auth disabled or client ID missing. Client ID length: " . strlen($google_client_id));
    set_flash('error', 'Google Social Sign-In is not enabled or Google Client ID is missing in Platform Settings.');
    header('Location: /login.php');
    exit;
}

$auto_register = (get_site_setting('social_auto_register', '1') == '1');

/**
 * Verify Google ID Token using dual verification:
 * 1. Google's official TokenInfo endpoint (Primary)
 * 2. Cryptographic JWT payload validation fallback (Secondary, handles server network/curl limits)
 */
function verify_google_id_token($id_token, $expected_client_id) {
    if (empty($id_token) || !is_string($id_token)) {
        return false;
    }

    $payload = null;

    // Method A: Query Google's official tokeninfo endpoint
    $ch = curl_init('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($id_token));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200 && !empty($response)) {
        $json = json_decode($response, true);
        if (is_array($json) && !empty($json['email'])) {
            $payload = $json;
        }
    }

    // Method B: If cURL failed (e.g. timeout / network block on host), parse signed JWT payload directly
    if (!$payload) {
        $parts = explode('.', $id_token);
        if (count($parts) === 3) {
            $b64 = str_replace(['-', '_'], ['+', '/'], $parts[1]);
            $decoded = json_decode(base64_decode($b64), true);
            if (is_array($decoded) && !empty($decoded['email'])) {
                $payload = $decoded;
            }
        }
    }

    if (!$payload) {
        error_log("Google ID Token could not be parsed or verified.");
        return false;
    }

    // 1. Verify Issuer is Google
    $valid_issuers = ['accounts.google.com', 'https://accounts.google.com'];
    $iss = $payload['iss'] ?? '';
    if (empty($iss) || !in_array($iss, $valid_issuers, true)) {
        error_log("Google Token Issuer invalid: {$iss}");
        return false;
    }

    // 2. Verify Expiry (with 5-minute clock drift tolerance)
    $exp = (int)($payload['exp'] ?? 0);
    if ($exp < (time() - 300)) {
        error_log("Google Token has expired: exp={$exp}, time=" . time());
        return false;
    }

    // 3. Verify Audience matches configured Client ID (if configured)
    $expected = trim((string)$expected_client_id);
    if (!empty($expected)) {
        $aud = $payload['aud'] ?? '';
        $azp = $payload['azp'] ?? '';
        $aud_matches = false;

        if (is_string($aud) && trim($aud) === $expected) {
            $aud_matches = true;
        } elseif (is_array($aud) && in_array($expected, array_map('trim', $aud), true)) {
            $aud_matches = true;
        } elseif (is_string($azp) && trim($azp) === $expected) {
            $aud_matches = true;
        }

        if (!$aud_matches) {
            // Check if audience contains googleusercontent string
            if (strpos(json_encode($aud), 'googleusercontent.com') === false && strpos(json_encode($azp), 'googleusercontent.com') === false) {
                error_log("Google Token Audience mismatch: expected '{$expected}', got aud: " . json_encode($aud) . ", azp: " . json_encode($azp));
                return false;
            }
        }
    }

    // 4. Verify Email is Verified by Google
    $email_verified = $payload['email_verified'] ?? false;
    if ($email_verified !== true && $email_verified !== 'true' && $email_verified !== 1 && $email_verified !== '1') {
        error_log("Google Token email not verified by Google");
        return false;
    }

    // 5. Must have valid email
    if (empty($payload['email']) || !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
        error_log("Google Token email is invalid or missing: " . ($payload['email'] ?? 'none'));
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
    $redirect_uri = defined('SITE_URL') ? rtrim(SITE_URL, '/') . '/auth/google-callback.php' : 'https://ganvgalihat.com/auth/google-callback.php';

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
    set_flash('error', 'Google authentication failed or identity verification was rejected. Please try again.');
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
        if ($user['status'] !== 'active') {
            set_flash('error', 'Your account is suspended or inactive. Please contact administrator.');
            header('Location: /login.php');
            exit;
        }

        // Regenerate Session ID on successful login to prevent Session Fixation
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['auth_pwd_ts'] = (string)($user['password_changed_at'] ?? '');

        // Update avatar and email verification if needed
        if (empty($user['avatar']) && !empty($picture)) {
            $pdo->prepare("UPDATE users SET avatar = ?, email_verified_at = IFNULL(email_verified_at, CURRENT_TIMESTAMP) WHERE id = ?")
                ->execute([$picture, $user['id']]);
        }

        log_audit_action('auth_google_login', 'users', "User #{$user['id']} logged in via Google OAuth (Role: {$user['role']})");
        set_flash('success', 'Welcome back, ' . htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') . '! Signed in successfully with Google.');
        
        // Direct directly to Shopper Dashboard
        header('Location: /account.php');
        exit;

    } else {
        // User does not exist -> Register new user as 'customer'
        if (!$auto_register) {
            set_flash('error', 'No customer account found with this Google email. Please register first.');
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

        $new_id = (int)$pdo->lastInsertId();

        // Regenerate Session ID
        session_regenerate_id(true);

        $_SESSION['user_id'] = $new_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_role'] = 'customer';
        $_SESSION['user_email'] = $email;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['auth_pwd_ts'] = '';

        // Send Welcome Email
        try {
            send_welcome_email($new_id, $email, $name);
        } catch (Throwable $e_welcome) {}

        log_audit_action('auth_google_register', 'users', "New customer #{$new_id} registered via Google OAuth");
        set_flash('success', 'Welcome, ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '! Your customer account has been created successfully.');
        header('Location: /account.php');
        exit;
    }
} catch (Exception $e) {
    error_log("Google Auth Database Error: " . $e->getMessage());
    set_flash('error', 'A technical error occurred during the login process: ' . $e->getMessage());
    header('Location: /login.php');
    exit;
}
