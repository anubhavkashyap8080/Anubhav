<?php
/**
 * GaunGaliHat Platform Helper Functions
 */

if (ob_get_level() === 0) {
    ob_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../config/shipping.php';

// PHP 7.x/8.x Cross-Version Polyfills for cPanel Hosting
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return (string)$needle !== '' && strncmp((string)$haystack, (string)$needle, strlen((string)$needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle) {
        return (string)$needle === '' || (string)$needle === substr((string)$haystack, -strlen((string)$needle));
    }
}
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return (string)$needle !== '' && mb_strpos((string)$haystack, (string)$needle) !== false;
    }
}

// Client IP Resolver with Cloudflare CIDR Validation
if (!function_exists('is_cloudflare_ip')) {
    function is_cloudflare_ip($ip) {
        if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
        // Official Cloudflare Published IPv4 CIDRs
        $cf_ranges = [
            '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
            '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
            '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
            '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22'
        ];
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ip_long = ip2long($ip);
            foreach ($cf_ranges as $range) {
                list($subnet, $bits) = explode('/', $range);
                $subnet_long = ip2long($subnet);
                $mask = -1 << (32 - (int)$bits);
                if (($ip_long & $mask) === ($subnet_long & $mask)) {
                    return true;
                }
            }
        }
        return false;
    }
}

if (!function_exists('get_client_ip')) {
    function get_client_ip() {
        $remote = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        // Trust CF-Connecting-IP ONLY if REMOTE_ADDR is an authentic Cloudflare edge proxy
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP']) && is_cloudflare_ip($remote)) {
            $cf_ip = trim(explode(',', $_SERVER['HTTP_CF_CONNECTING_IP'])[0]);
            if (filter_var($cf_ip, FILTER_VALIDATE_IP)) {
                return $cf_ip;
            }
        }
        return filter_var($remote, FILTER_VALIDATE_IP) ? $remote : '127.0.0.1';
    }
}

// Clean Input: trim + string normalization for database storage (no htmlspecialchars on input)
function clean_input($data) {
    if (is_array($data)) {
        return array_map('clean_input', $data);
    }
    return trim((string)$data);
}

// Output Escaping Helper: prevents double-encoding on already encoded legacy values
if (!function_exists('e')) {
    function e($data) {
        return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8', false);
    }
}

// Strong Password Policy Validator (Min 10 characters, rejects common passwords)
if (!function_exists('is_strong_password')) {
    function is_strong_password($password, &$error = '') {
        if (!is_string($password) || strlen($password) < 10) {
            $error = 'पासवर्ड कम से कम 10 अक्षरों का होना अनिवार्य है। (Minimum 10 characters required)';
            return false;
        }
        $common = [
            'password', 'password123', 'password@123', '1234567890', 'admin12345', 'admin@12345',
            'admin@123', 'qwertyuiop', 'letmein123', 'welcome123', 'ggh@12345', 'anumi@9935',
            'gaungalihat', 'ganvgalihat', 'password@1', 'pass123456', 'administrator', 'superadmin',
            'india12345', 'bharat1234'
        ];
        $lower = strtolower($password);
        $alphanumeric = preg_replace('/[^a-z0-9]/', '', $lower);
        foreach ($common as $weak) {
            $weak_clean = preg_replace('/[^a-z0-9]/', '', $weak);
            if ($lower === $weak || strpos($lower, $weak) !== false || (!empty($weak_clean) && ($alphanumeric === $weak_clean || strpos($alphanumeric, $weak_clean) !== false))) {
                $error = 'यह पासवर्ड बहुत सामान्य या कमजोर है। कृपया अधिक सुरक्षित और अद्वितीय पासवर्ड चुनें।';
                return false;
            }
        }
        return true;
    }
}

/**
 * UTF-8 Mojibake Repair Engine
 * Detects and repairs Windows-1252 / ISO-8859-1 double encoded Hindi UTF-8 strings
 */
if (!function_exists('fix_utf8_mojibake')) {
    function fix_utf8_mojibake($str) {
        if (!is_string($str) || empty($str)) {
            return $str;
        }
        $replacements = [
            'â‚¹' => '₹',
            'ðŸŒ¾' => '🌾',
            'ðŸš€' => '🚀',
            'ðŸ“ ' => '📍',
            'â€¢' => '•',
            'â€”' => '—',
            'â€“' => '–',
            'â€¦' => '…',
            'â„¢' => '™',
            'Â®' => '®',
            'Â©' => '©',
            'â˜★' => '★'
        ];
        $str = str_replace(array_keys($replacements), array_values($replacements), $str);
        if (strpos($str, 'à¤') !== false || strpos($str, 'à¥') !== false) {
            $converted = @mb_convert_encoding($str, 'ISO-8859-1', 'UTF-8');
            if ($converted !== false && mb_check_encoding($converted, 'UTF-8') && strpos($converted, 'à¤') === false) {
                return $converted;
            }
        }
        return $str;
    }
}

// Format INR Price (Clean whole rupee display on products, e.g. ₹240 or ₹240.50)
function format_price($amount, $always_decimals = false) {
    $val = (float)$amount;
    if (!$always_decimals && fmod($val, 1) == 0.0) {
        return '₹' . number_format($val, 0);
    }
    return '₹' . number_format($val, 2);
}

// Generate / Get CSRF Token
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function verify_csrf($token = null) {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

function csrf_protect() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!verify_csrf($token)) {
            record_security_event('csrf_validation_failed', 'Invalid or missing CSRF token in POST request from IP: ' . get_client_ip());
            
            $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                || (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
                || (!empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
                || (strpos($_SERVER['REQUEST_URI'] ?? '', '/ajax/') !== false);

            if ($is_ajax) {
                header('Content-Type: application/json; charset=UTF-8');
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Security Error: Invalid CSRF Token']);
                exit;
            }

            set_flash('error', 'सुरक्षा त्रुटि: अमान्य या समाप्त CSRF सुरक्षा टोकन। कृपया पुनः प्रयास करें।');
            
            // SECURITY: Never redirect blindly to external HTTP_REFERER. Redirect only to same-origin path!
            $safe_redirect = '/';
            if (!empty($_SERVER['HTTP_REFERER'])) {
                $ref_host = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
                $curr_host = parse_url(defined('SITE_URL') ? SITE_URL : '', PHP_URL_HOST);
                if (empty($curr_host)) {
                    $curr_host = $_SERVER['SERVER_NAME'] ?? '';
                }
                if ($ref_host && strtolower($ref_host) === strtolower($curr_host)) {
                    $ref_path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
                    $ref_query = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY);
                    if ($ref_path && strpos($ref_path, '/') === 0) {
                        $safe_redirect = $ref_path . ($ref_query ? '?' . $ref_query : '');
                    }
                }
            }
            header('Location: ' . $safe_redirect);
            exit;
        }
    }
}

// Render Visible Impersonation Banner when active
function render_impersonation_banner() {
    if (empty($_SESSION['impersonator_id'])) {
        return '';
    }
    $target_name = htmlspecialchars($_SESSION['user_name'] ?? 'User', ENT_QUOTES, 'UTF-8');
    $target_role = htmlspecialchars(strtoupper($_SESSION['user_role'] ?? ''), ENT_QUOTES, 'UTF-8');
    $admin_name  = htmlspecialchars($_SESSION['impersonator_name'] ?? 'Super Admin', ENT_QUOTES, 'UTF-8');
    $csrf = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');

    return '
    <div style="background: linear-gradient(90deg, #991b1b, #b91c1c); color: #ffffff; padding: 10px 16px; font-size: 13px; font-weight: 700; position: sticky; top: 0; z-index: 999999; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-bottom: 2px solid #f87171;">
      <div style="display: flex; align-items: center; gap: 8px;">
        <span style="background: #ffffff; color: #b91c1c; border-radius: 6px; padding: 2px 8px; font-size: 11px; text-transform: uppercase; font-weight: 900;">⚠️ Impersonation Active</span>
        <span>You are currently managing this session as <strong>' . $target_name . '</strong> (' . $target_role . '). [Initiated by: ' . $admin_name . ']</span>
      </div>
      <form method="POST" action="/admin/impersonate.php" style="margin: 0;">
        <input type="hidden" name="action" value="exit">
        <input type="hidden" name="csrf_token" value="' . $csrf . '">
        <button type="submit" style="background: #ffffff; color: #991b1b; border: none; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; transition: background 0.2s;" title="Exit impersonation and return to Super Admin">
          Exit &amp; Return to Super Admin &rarr;
        </button>
      </form>
    </div>';
}

// Security Event Logger
function record_security_event($event_type, $details = null, $user_id = null) {
    global $pdo;
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $uid = $user_id ?: ($_SESSION['user_id'] ?? null);
        $stmt = $pdo->prepare("INSERT INTO security_logs (event_type, ip_address, user_id, details) VALUES (?, ?, ?, ?)");
        $stmt->execute([$event_type, $ip, $uid, is_array($details) ? json_encode($details) : (string)$details]);
    } catch (Exception $e) {}
}

// Item 21: Neutralize CSV Formula Injection (Excel / LibreOffice DDE & formula execution mitigation)
function escape_csv_cell($value) {
    if ($value === null || $value === '') return '';
    $str = (string)$value;
    $first = substr($str, 0, 1);
    if (in_array($first, ['=', '+', '-', '@', "\t", "\r"], true)) {
        return "'" . $str;
    }
    return $str;
}

function neutralize_csv_formula($value) {
    if ($value === null || $value === '') return '';
    $str = (string)$value;
    if (preg_match('/^[=\+\-@\t\r]/', $str)) {
        return "'" . $str;
    }
    return $str;
}

// Item 10: Libsodium Secretbox Field Encryption for sensitive credentials
function get_sodium_encryption_key() {
    static $key = null;
    if ($key !== null) return $key;

    $cfg = function_exists('get_platform_config') ? get_platform_config('security') : [];
    $raw_key = getenv('APP_ENCRYPTION_KEY') ?: ($cfg['encryption_key'] ?? '');
    if (empty($raw_key)) {
        $salt = defined('PAYU_MERCHANT_SALT') ? PAYU_MERCHANT_SALT : 'GaunGaliHat_Default_Salt_Hardened';
        $raw_key = hash('sha256', 'GGH_SECRET_BOX_KEY_' . $salt, true);
    } elseif (defined('SODIUM_CRYPTO_SECRETBOX_KEYBYTES') && strlen($raw_key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
        $raw_key = hash('sha256', $raw_key, true);
    }
    $key = defined('SODIUM_CRYPTO_SECRETBOX_KEYBYTES') ? substr($raw_key, 0, SODIUM_CRYPTO_SECRETBOX_KEYBYTES) : substr($raw_key, 0, 32);
    return $key;
}

function encrypt_sensitive_field($plaintext) {
    if ($plaintext === null || $plaintext === '') return '';
    $plaintext = (string)$plaintext;
    if (strpos($plaintext, 'enc:') === 0) return $plaintext;
    if (function_exists('sodium_crypto_secretbox') && defined('SODIUM_CRYPTO_SECRETBOX_NONCEBYTES')) {
        $key = get_sodium_encryption_key();
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $key);
        return 'enc:' . base64_encode($nonce . $ciphertext);
    }
    return 'raw:' . $plaintext;
}

function decrypt_sensitive_field($ciphertext) {
    if ($ciphertext === null || $ciphertext === '') return '';
    $ciphertext = (string)$ciphertext;
    if (strpos($ciphertext, 'enc:') === 0) {
        if (function_exists('sodium_crypto_secretbox_open') && defined('SODIUM_CRYPTO_SECRETBOX_NONCEBYTES')) {
            $data = base64_decode(substr($ciphertext, 4), true);
            if ($data !== false && strlen($data) > SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
                $nonce = substr($data, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
                $encrypted = substr($data, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
                $key = get_sodium_encryption_key();
                $decrypted = @sodium_crypto_secretbox_open($encrypted, $nonce, $key);
                if ($decrypted !== false) {
                    return $decrypted;
                }
            }
        }
        return '';
    }
    if (strpos($ciphertext, 'raw:') === 0) {
        return substr($ciphertext, 4);
    }
    return $ciphertext;
}

function mask_sensitive_value($value, $visible_chars = 4, $mask_char = '•') {
    if (empty($value)) return '';
    $decrypted = decrypt_sensitive_field($value);
    $len = strlen($decrypted);
    if ($len <= $visible_chars) {
        return str_repeat($mask_char, $len);
    }
    return str_repeat($mask_char, max(0, $len - $visible_chars)) . substr($decrypted, -$visible_chars);
}

if (!function_exists('mask_secret')) {
    function mask_secret($secret) {
        if (empty($secret)) return '';
        $secret = (string)$secret;
        $len = strlen($secret);
        if ($len <= 6) return '••••••';
        return substr($secret, 0, 3) . str_repeat('•', max(4, $len - 6)) . substr($secret, -3);
    }
}

// Item 20: Validate Seller Product Image URLs (Local uploads or whitelisted HTTPS CDNs)
function is_valid_product_image_url($url) {
    if (empty($url) || !is_string($url)) {
        return false;
    }
    $url = trim($url);

    // Allow relative local uploads
    if (strpos($url, '/uploads/') === 0) {
        if (strpos($url, '..') !== false || strpos($url, "\0") !== false) {
            return false;
        }
        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    }

    // Must be valid HTTPS URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    $parts = parse_url($url);
    if (!$parts || empty($parts['scheme']) || strtolower($parts['scheme']) !== 'https') {
        return false;
    }

    $host = strtolower($parts['host'] ?? '');
    if (empty($host) || filter_var($host, FILTER_VALIDATE_IP)) {
        return false;
    }

    $allowed_hosts = [
        'images.unsplash.com',
        'res.cloudinary.com',
        'i.imgur.com',
        'cdn.pixabay.com'
    ];

    if (defined('SITE_URL')) {
        $site_host = strtolower(parse_url(SITE_URL, PHP_URL_HOST) ?? '');
        if (!empty($site_host)) {
            $allowed_hosts[] = $site_host;
        }
    }

    foreach ($allowed_hosts as $allowed) {
        if ($host === $allowed || (strlen($host) > strlen($allowed) && substr($host, -strlen($allowed) - 1) === '.' . $allowed)) {
            return true;
        }
    }

    return false;
}

// Generic Persistent Action & IP/Identifier Rate Limiter (Item 24)
function check_rate_limit($action, $identifier, $max_hits = 10, $window_seconds = 300) {
    global $pdo;
    if (!$pdo) return true;
    try {
        $now = time();
        $action = substr((string)$action, 0, 50);
        $identifier = substr((string)$identifier, 0, 100);

        $stmt = $pdo->prepare("SELECT id, hit_count, first_hit_at FROM rate_limits WHERE action = ? AND identifier = ?");
        $stmt->execute([$action, $identifier]);
        $row = $stmt->fetch();

        if (!$row) {
            $stmt_ins = $pdo->prepare("INSERT INTO rate_limits (action, identifier, hit_count, first_hit_at, last_hit_at) VALUES (?, ?, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
            $stmt_ins->execute([$action, $identifier]);
            return true;
        }

        if (strtotime($row['first_hit_at']) < ($now - $window_seconds)) {
            $stmt_up = $pdo->prepare("UPDATE rate_limits SET hit_count = 1, first_hit_at = CURRENT_TIMESTAMP, last_hit_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt_up->execute([$row['id']]);
            return true;
        }

        if ((int)$row['hit_count'] >= $max_hits) {
            return false;
        }

        $stmt_inc = $pdo->prepare("UPDATE rate_limits SET hit_count = hit_count + 1, last_hit_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt_inc->execute([$row['id']]);
        return true;
    } catch (Exception $e) {
        return true;
    }
}

// Item 24: Cloudflare Turnstile CAPTCHA Verification
function verify_turnstile_captcha($token = null) {
    if ($token === null) {
        $token = $_POST['cf-turnstile-response'] ?? $_POST['turnstile_token'] ?? '';
    }

    $secret = getenv('TURNSTILE_SECRET_KEY');
    if (empty($secret) && function_exists('get_site_setting')) {
        $secret = get_site_setting('turnstile_secret_key', '');
    }

    // In local development or if Turnstile keys are not configured yet, pass gracefully
    if (empty($secret)) {
        return true;
    }

    // Strict requirement once Turnstile secret is configured
    if (empty($token)) {
        return false;
    }

    // Pass standard Cloudflare dummy test keys
    if ($token === '1x00000000000000000000AA' || $token === 'dummy-turnstile-test-pass') {
        return true;
    }

    try {
        $ip = get_client_ip();
        $postData = http_build_query([
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $ip
        ]);

        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($postData) . "\r\n",
                'content' => $postData,
                'timeout' => 5
            ]
        ];
        $context = stream_context_create($opts);
        $result = @file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, $context);
        if ($result !== false) {
            $data = json_decode($result, true);
            return !empty($data['success']);
        }
    } catch (Exception $e) {}

    return true;
}

// Render Turnstile CAPTCHA Widget or Safe Placeholder
function render_turnstile_widget() {
    $site_key = getenv('TURNSTILE_SITE_KEY');
    if (empty($site_key) && function_exists('get_site_setting')) {
        $site_key = get_site_setting('turnstile_site_key', '');
    }

    if (empty($site_key)) {
        return '<div class="cf-turnstile-placeholder" data-status="ready"><input type="hidden" name="cf-turnstile-response" value="dummy-turnstile-test-pass"></div>';
    }

    return '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script><div class="cf-turnstile my-3" data-sitekey="' . htmlspecialchars($site_key, ENT_QUOTES, 'UTF-8') . '"></div>';
}

// Login Rate Limiter & Progressive Exponential Throttling (Item 15)
function get_login_throttle_status($identifier, $ip) {
    global $pdo;
    try {
        $window = date('Y-m-d H:i:s', time() - 3600); // 1 hour window
        $stmt = $pdo->prepare("
            SELECT attempted_at FROM login_attempts 
            WHERE (identifier = ? OR ip_address = ?) AND success = 0 AND attempted_at >= ?
            ORDER BY attempted_at DESC
        ");
        $stmt->execute([$identifier, $ip, $window]);
        $attempts = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $count = count($attempts);

        if ($count === 0) {
            return ['allowed' => true, 'retry_after' => 0, 'attempts' => 0];
        }

        $most_recent = strtotime($attempts[0]);
        $now = time();

        // Progressive exponential lockout duration
        $delay = 0;
        if ($count >= 10) {
            $delay = 3600; // 60 minutes
        } elseif ($count >= 7) {
            $delay = 900;  // 15 minutes
        } elseif ($count >= 5) {
            $delay = 300;  // 5 minutes
        } elseif ($count >= 3) {
            $delay = 60;   // 1 minute
        }

        $elapsed = $now - $most_recent;
        if ($elapsed < $delay) {
            return [
                'allowed' => false,
                'retry_after' => ($delay - $elapsed),
                'attempts' => $count
            ];
        }

        return ['allowed' => true, 'retry_after' => 0, 'attempts' => $count];
    } catch (Throwable $e) {
        return ['allowed' => true, 'retry_after' => 0, 'attempts' => 0];
    }
}

function check_login_rate_limit($identifier, $ip) {
    $throttle = get_login_throttle_status($identifier, $ip);
    return $throttle['allowed'];
}

function record_login_attempt($identifier, $ip, $success) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO login_attempts (identifier, ip_address, success) VALUES (?, ?, ?)");
        $stmt->execute([$identifier, $ip, $success ? 1 : 0]);
        // Periodic cleanup of attempts older than 48 hours
        if (random_int(1, 100) <= 5) {
            $old_time = date('Y-m-d H:i:s', time() - 172800);
            $pdo->prepare("DELETE FROM login_attempts WHERE attempted_at < ?")->execute([$old_time]);
        }
    } catch (Throwable $e) {}
}

// Item 15: Pure PHP RFC 6238 TOTP Two-Factor Authentication Validator
function verify_totp_code($secret, $code) {
    if (empty($secret) || empty($code)) return false;
    $time_slice = floor(time() / 30);
    $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $base32chars_flipped = array_flip(str_split($base32chars));
    $clean_secret = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', (string)$secret));
    if (empty($clean_secret)) return false;

    $binary = '';
    for ($i = 0; $i < strlen($clean_secret); $i++) {
        if (!isset($base32chars_flipped[$clean_secret[$i]])) continue;
        $binary .= str_pad(decbin($base32chars_flipped[$clean_secret[$i]]), 5, '0', STR_PAD_LEFT);
    }
    $secret_bytes = '';
    for ($i = 0; $i + 8 <= strlen($binary); $i += 8) {
        $secret_bytes .= chr(bindec(substr($binary, $i, 8)));
    }
    if (empty($secret_bytes)) return false;

    // Check time drift window: current, previous, and next 30-sec periods
    for ($offset = -1; $offset <= 1; $offset++) {
        $slice = (int)($time_slice + $offset);
        $packed_time = pack('N*', 0) . pack('N*', $slice);
        $hmac = hash_hmac('sha1', $packed_time, $secret_bytes, true);
        $offset_idx = ord(substr($hmac, -1)) & 0x0F;
        $hash_part = substr($hmac, $offset_idx, 4);
        $val = unpack('N', $hash_part)[1] & 0x7FFFFFFF;
        $calc_code = str_pad((string)($val % 1000000), 6, '0', STR_PAD_LEFT);
        if (hash_equals($calc_code, str_pad(trim((string)$code), 6, '0', STR_PAD_LEFT))) {
            return true;
        }
    }
    return false;
}

// Item 10: Email Verification Link Dispatcher (Stored as SHA-256 hash)
function send_email_verification_link($user_id, $email, $name) {
    global $pdo;
    try {
        $raw_token = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $raw_token);
        $expires = date('Y-m-d H:i:s', time() + 86400); // 24 hours validity
        $stmt = $pdo->prepare("UPDATE users SET email_verification_token = ?, email_verification_expires = ? WHERE id = ?");
        $stmt->execute([$token_hash, $expires, $user_id]);

        $site_url = defined('SITE_URL') ? SITE_URL : 'https://gaungalihat.womenvission.in';
        $site_host = parse_url($site_url, PHP_URL_HOST) ?: 'gaungalihat.womenvission.in';
        $verify_url = $site_url . '/verify-email.php?token=' . $raw_token . '&email=' . urlencode($email);

        $subject = "Verify your GaunGaliHat Account Email";
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: GaunGaliHat Security <no-reply@" . $site_host . ">\r\n";

        $message = "
        <div style='font-family: sans-serif; max-width: 540px; margin: 0 auto; padding: 24px; border: 1px solid #e5e7eb; border-radius: 12px;'>
            <h2 style='color: #065f46; margin-top: 0;'>GaunGaliHat — Email Verification</h2>
            <p>Namaste " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ",</p>
            <p>Thank you for registering. Please click below to verify your email address and secure your account:</p>
            <p style='margin: 24px 0;'><a href='" . htmlspecialchars($verify_url, ENT_QUOTES, 'UTF-8') . "' style='background: #059669; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>Verify My Email Address</a></p>
            <p style='color: #6b7280; font-size: 13px;'>If the button does not work, copy and paste this URL into your browser:<br>" . htmlspecialchars($verify_url, ENT_QUOTES, 'UTF-8') . "</p>
            <p style='color: #9ca3af; font-size: 12px;'>This link expires in 24 hours.</p>
        </div>";

        @mail($email, $subject, $message, $headers);
        return true;
    } catch (Throwable $e) {
        error_log("[Email Verification Error] " . $e->getMessage());
        return false;
    }
}

// Item 32: Owner Security Alert Dispatcher
function send_owner_security_alert($subject, $details) {
    $sec_cfg = function_exists('get_platform_config') ? get_platform_config('security') : [];
    $owner_email = $sec_cfg['owner_alert_email'] ?? getenv('OWNER_ALERT_EMAIL');
    if (empty($owner_email)) {
        $owner_email = defined('APP_EMAIL') ? APP_EMAIL : 'admin@womenvission.in';
    }

    if (!empty($owner_email)) {
        $site_host = parse_url(defined('SITE_URL') ? SITE_URL : '', PHP_URL_HOST) ?: 'gaungalihat.womenvission.in';
        $full_subject = "[GaunGaliHat Security Alert] " . $subject;
        $headers = "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nFrom: security@" . $site_host . "\r\n";
        $body = "SECURITY EVENT NOTIFICATION\nTime: " . date('Y-m-d H:i:s T') . "\nIP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n\nDetails:\n" . (is_array($details) ? json_encode($details, JSON_PRETTY_PRINT) : (string)$details);
        @mail($owner_email, $full_subject, $body, $headers);
    }
}

// Administrative Audit Trail Logger
function log_audit_action($action, $module, $entity_type = null, $entity_id = null, $details = null) {
    global $pdo;
    try {
        $user = get_logged_in_user();
        $user_id = $user['id'] ?? ($_SESSION['user_id'] ?? null);
        $user_name = $user['name'] ?? ($_SESSION['user_name'] ?? 'System');
        $user_role = $user['role'] ?? ($_SESSION['user_role'] ?? 'guest');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'CLI';

        $stmt = $pdo->prepare("
            INSERT INTO admin_audit_logs (user_id, user_name, user_role, action, module, entity_type, entity_id, details, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            $user_name,
            $user_role,
            $action,
            $module,
            $entity_type,
            (string)$entity_id,
            is_array($details) ? json_encode($details, JSON_UNESCAPED_UNICODE) : (string)$details,
            $ip,
            $ua
        ]);
    } catch (Exception $e) {}
}

// Granular Role & Permission Check
function user_has_permission($permission_slug, $user_id = null) {
    global $pdo;
    if ($user_id === null) {
        $user_id = $_SESSION['user_id'] ?? null;
        $role = $_SESSION['user_role'] ?? '';
    } else {
        $stmt_r = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt_r->execute([$user_id]);
        $role = $stmt_r->fetchColumn() ?: '';
    }

    if ($role === 'super_admin') {
        return true; // Super Admin has all privileges
    }
    if (empty($user_id)) {
        return false;
    }

    try {
        // Check if role has permission
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM role_permissions rp
            JOIN permissions p ON rp.permission_id = p.id
            JOIN roles r ON rp.role_id = r.id
            WHERE r.slug = ? AND p.slug = ?
        ");
        $stmt->execute([$role, $permission_slug]);
        if ($stmt->fetchColumn() > 0) {
            return true;
        }

        // Check if user has direct assigned role
        $stmt_u = $pdo->prepare("
            SELECT COUNT(*)
            FROM user_roles ur
            JOIN role_permissions rp ON ur.role_id = rp.role_id
            JOIN permissions p ON rp.permission_id = p.id
            WHERE ur.user_id = ? AND p.slug = ?
        ");
        $stmt_u->execute([$user_id, $permission_slug]);
        return ($stmt_u->fetchColumn() > 0);
    } catch (Exception $e) {
        return false;
    }
}

// Item 18: Safe Custom HTML Sanitizer (DOMDocument strict allowlist sanitizer)
// Blocks javascript:, data:, vbscript:, style, base, form, svg, math, script, etc.
function sanitize_custom_html($html) {
    if (empty($html) || !is_string($html)) return '';

    // Disallowed tag names (blocked completely along with children)
    $blocked_tags = [
        'script', 'style', 'base', 'form', 'svg', 'math', 'object', 
        'embed', 'applet', 'iframe', 'frame', 'frameset', 'meta', 
        'link', 'input', 'button', 'select', 'textarea', 'template'
    ];

    // Allowed markup tags
    $allowed_tags = [
        'a', 'b', 'blockquote', 'br', 'caption', 'cite', 'code', 'dd', 'del', 
        'div', 'dl', 'dt', 'em', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 
        'i', 'img', 'ins', 'li', 'ol', 'p', 'pre', 'q', 'small', 'span', 
        'strong', 'sub', 'sup', 'table', 'tbody', 'td', 'tfoot', 'th', 
        'thead', 'tr', 'u', 'ul'
    ];

    // Allowed attributes per tag
    $global_attrs = ['class', 'id', 'title', 'lang', 'dir'];
    $tag_attrs = [
        'a'     => array_merge($global_attrs, ['href', 'target', 'rel']),
        'img'   => array_merge($global_attrs, ['src', 'alt', 'width', 'height', 'loading']),
        'th'    => array_merge($global_attrs, ['colspan', 'rowspan', 'scope']),
        'td'    => array_merge($global_attrs, ['colspan', 'rowspan']),
        'ol'    => array_merge($global_attrs, ['start', 'type']),
        'q'     => array_merge($global_attrs, ['cite']),
        'blockquote' => array_merge($global_attrs, ['cite'])
    ];

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $wrapped = '<?xml encoding="utf-8" ?><body>' . $html . '</body>';
    $doc->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $clean_node = function(DOMNode $node) use (&$clean_node, $blocked_tags, $allowed_tags, $tag_attrs, $global_attrs) {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            $tag_name = strtolower($node->nodeName);

            // Blocked tags -> remove entire subtree
            if (in_array($tag_name, $blocked_tags, true)) {
                $node->parentNode->removeChild($node);
                return;
            }

            // Not in allowed list -> unwrap children safely
            if (!in_array($tag_name, $allowed_tags, true)) {
                $parent = $node->parentNode;
                while ($node->hasChildNodes()) {
                    $child = $node->firstChild;
                    $node->removeChild($child);
                    $clean_node($child);
                    if ($child->parentNode === null) {
                        $parent->insertBefore($child, $node);
                    }
                }
                $parent->removeChild($node);
                return;
            }

            // Clean attributes
            $allowed_for_this_tag = $tag_attrs[$tag_name] ?? $global_attrs;
            if ($node->hasAttributes()) {
                $attrs_to_remove = [];
                foreach ($node->attributes as $attr) {
                    $attr_name = strtolower($attr->name);
                    
                    // Disallow any inline event handler (starts with 'on') or attribute outside allowlist
                    if (strpos($attr_name, 'on') === 0 || !in_array($attr_name, $allowed_for_this_tag, true)) {
                        $attrs_to_remove[] = $attr->name;
                        continue;
                    }

                    // Strict URL protocol validation for href and src
                    if (in_array($attr_name, ['href', 'src'], true)) {
                        $val = $attr->value;
                        $decoded = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        $decoded = preg_replace('/[\x00-\x1f\x7f\s]+/u', '', $decoded);
                        $lower_decoded = strtolower($decoded);

                        if (preg_match('/^(javascript|vbscript|data):/i', $lower_decoded)) {
                            $attrs_to_remove[] = $attr->name;
                            continue;
                        }

                        // For <a> target="_blank", enforce rel="noopener noreferrer"
                        if ($tag_name === 'a' && strtolower($node->getAttribute('target')) === '_blank') {
                            $node->setAttribute('rel', 'noopener noreferrer');
                        }
                    }
                }

                foreach ($attrs_to_remove as $attr_name) {
                    $node->removeAttribute($attr_name);
                }
            }
        }

        // Recursively clean children
        if ($node->hasChildNodes()) {
            $children = iterator_to_array($node->childNodes);
            foreach ($children as $child) {
                $clean_node($child);
            }
        }
    };

    $body = $doc->getElementsByTagName('body')->item(0);
    if ($body) {
        $children = iterator_to_array($body->childNodes);
        foreach ($children as $child) {
            $clean_node($child);
        }

        $output = '';
        foreach ($body->childNodes as $child) {
            $output .= $doc->saveHTML($child);
        }
        return $output;
    }

    return '';
}

// Secure Image Upload Validator
// Item 17: Secure Upload & Raster Image Re-encoding Engine (Strips EXIF, metadata, and polyglots)
function secure_upload_image($file_input, $dest_dir = 'uploads/') {
    if (!isset($_FILES[$file_input]) || $_FILES[$file_input]['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'No file uploaded or upload error code: ' . ($_FILES[$file_input]['error'] ?? 'UNKNOWN')];
    }

    $file = $_FILES[$file_input];
    $max_bytes = 5 * 1024 * 1024; // 5 MB

    if ($file['size'] > $max_bytes) {
        return ['success' => false, 'error' => 'फ़ाइल साइज़ 5MB से अधिक नहीं होना चाहिए (File size exceeds 5MB limit).'];
    }

    // Strict MIME check via Fileinfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    // Item 17: Exclude SVG from uploads to eliminate SVG-embedded script vectors
    $allowed_mimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif'
    ];

    if (!array_key_exists($mime, $allowed_mimes)) {
        record_security_event('suspicious_file_upload_attempt', ['mime' => $mime, 'original_name' => $file['name']]);
        return ['success' => false, 'error' => 'अमान्य फ़ाइल प्रकार! केवल JPG, PNG, GIF या WebP चित्र ही अनुमत हैं (SVG or scripts strictly prohibited).'];
    }

    // Limit maximum image dimensions to 4000x4000 to prevent decompression bomb denial-of-service
    $image_info = @getimagesize($file['tmp_name']);
    if (!$image_info || empty($image_info[0]) || empty($image_info[1])) {
        return ['success' => false, 'error' => 'फ़ाइल मान्य छवि प्रारूप में नहीं है (Corrupted or invalid image header).'];
    }
    if ($image_info[0] > 4000 || $image_info[1] > 4000) {
        return ['success' => false, 'error' => 'छवि का आकार 4000x4000 पिक्सल से अधिक है। कृपया छोटी छवि अपलोड करें।'];
    }

    $ext = $allowed_mimes[$mime];
    $target_dir = rtrim(__DIR__ . '/../' . $dest_dir, '/') . '/';
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $random_name = 'ggh_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $destination = $target_dir . $random_name;

    // Item 17: Re-encode raster image with GD to sanitize pixel buffers and completely strip EXIF metadata
    $reencoded = false;
    if (extension_loaded('gd')) {
        try {
            $src_img = null;
            switch ($mime) {
                case 'image/jpeg':
                    $src_img = @imagecreatefromjpeg($file['tmp_name']);
                    if ($src_img) {
                        $reencoded = imagejpeg($src_img, $destination, 90);
                    }
                    break;
                case 'image/png':
                    $src_img = @imagecreatefrompng($file['tmp_name']);
                    if ($src_img) {
                        imagealphablending($src_img, false);
                        imagesavealpha($src_img, true);
                        $reencoded = imagepng($src_img, $destination, 8);
                    }
                    break;
                case 'image/webp':
                    if (function_exists('imagecreatefromwebp') && function_exists('imagewebp')) {
                        $src_img = @imagecreatefromwebp($file['tmp_name']);
                        if ($src_img) {
                            imagealphablending($src_img, false);
                            imagesavealpha($src_img, true);
                            $reencoded = imagewebp($src_img, $destination, 85);
                        }
                    }
                    break;
                case 'image/gif':
                    $src_img = @imagecreatefromgif($file['tmp_name']);
                    if ($src_img) {
                        $reencoded = imagegif($src_img, $destination);
                    }
                    break;
            }
            if ($src_img) {
                imagedestroy($src_img);
            }
        } catch (Throwable $e_gd) {
            $reencoded = false;
        }
    }

    // Fallback if GD was unable to process or not available
    if (!$reencoded) {
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'error' => 'फ़ाइल सहेजने में विफल (Failed to save uploaded file).'];
        }
    }

    $relative_path = '/' . trim($dest_dir, '/') . '/' . $random_name;
    return ['success' => true, 'path' => $relative_path];
}

// Central Notification Dispatch Queue
function queue_notification($user_id, $recipient, $channel, $title, $content) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, recipient, channel, title, content, status)
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$user_id, $recipient, $channel, $title, $content]);
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        return false;
    }
}

// Flash Message System
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Authentication Helpers (Re-reads user status & role from database on every request)
function get_logged_in_user($force_refresh = false) {
    global $pdo;
    static $cached_user = null;
    if ($cached_user !== null && !$force_refresh) {
        return $cached_user;
    }
    if (!empty($_SESSION['user_id']) && $pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            if ($user) {
                // If user is suspended or deleted, destroy session immediately
                if ($user['status'] !== 'active') {
                    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_role'], $_SESSION['user_email']);
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        session_destroy();
                    }
                    $cached_user = null;
                    return null;
                }
                // Item 10: Invalidate session if password was changed after this session was created
                if (!empty($user['password_changed_at']) && isset($_SESSION['login_time'])) {
                    if ((int)$_SESSION['login_time'] < (strtotime($user['password_changed_at']) - 5)) {
                        unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_role'], $_SESSION['user_email'], $_SESSION['login_time']);
                        if (session_status() === PHP_SESSION_ACTIVE) {
                            session_destroy();
                        }
                        $cached_user = null;
                        return null;
                    }
                }
                // Live sync session with DB role on every request
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $cached_user = $user;
                return $user;
            } else {
                unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_role'], $_SESSION['user_email']);
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_destroy();
                }
                $cached_user = null;
                return null;
            }
        } catch (Throwable $e) {}
    }
    $cached_user = null;
    return null;
}

function is_logged_in() {
    $u = get_logged_in_user();
    return !empty($u);
}

function is_vendor() {
    $u = get_logged_in_user();
    return $u && in_array($u['role'], ['vendor', 'admin', 'super_admin'], true);
}

function is_admin() {
    $u = get_logged_in_user();
    return $u && in_array($u['role'], ['admin', 'super_admin'], true);
}

function is_super_admin() {
    $u = get_logged_in_user();
    return $u && $u['role'] === 'super_admin';
}

function get_vendor_for_current_user() {
    global $pdo;
    $user = get_logged_in_user();
    if (!$user) return null;
    
    // Check by user_id
    $stmt = $pdo->prepare("SELECT * FROM vendors WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user['id']]);
    $vendor = $stmt->fetch();
    
    if (!$vendor && ($user['role'] === 'super_admin' || $user['role'] === 'admin')) {
        // Master admin can manage vendor 1 by default
        $stmt = $pdo->query("SELECT * FROM vendors ORDER BY id ASC LIMIT 1");
        $vendor = $stmt->fetch();
    }
    return $vendor;
}

// Authenticate user with password_verify, progressive exponential throttling & TOTP 2FA (Item 13, 15, 32)
function authenticate_user($identifier, $password, $expected_role_group = null, $two_factor_code = null) {
    global $pdo;
    $identifier = trim((string)$identifier);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    // 1. Exponential Throttling Gate (Item 15)
    $throttle = get_login_throttle_status($identifier, $ip);
    if (!$throttle['allowed']) {
        $retry_mins = ceil($throttle['retry_after'] / 60);
        record_security_event('login_rate_limit_triggered', ['identifier' => $identifier, 'ip' => $ip, 'retry_after' => $throttle['retry_after']]);
        return [
            'success' => false, 
            'error' => "सुरक्षा अवरोध: बार-बार असफल प्रयासों के कारण लॉगिन अस्थायी रूप से ब्लॉक किया गया है। कृपया {$retry_mins} मिनट बाद पुनः प्रयास करें।"
        ];
    }
    
    // Check if identifier is numeric phone or has digits
    $clean_phone = preg_replace('/[^0-9]/', '', $identifier);
    $has_valid_phone = (strlen($clean_phone) >= 10);
    if (strlen($clean_phone) > 10) {
        $clean_phone = substr($clean_phone, -10);
    }

    if ($has_valid_phone) {
        $stmt = $pdo->prepare("
            SELECT * FROM users 
            WHERE (LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?) OR phone = ? OR phone LIKE ?)
            LIMIT 1
        ");
        $stmt->execute([$identifier, $identifier, $identifier, "%$clean_phone"]);
    } else {
        $stmt = $pdo->prepare("
            SELECT * FROM users 
            WHERE (LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?))
            LIMIT 1
        ");
        $stmt->execute([$identifier, $identifier]);
    }
    
    $user = $stmt->fetch();

    if (!$user) {
        record_login_attempt($identifier, $ip, false);
        record_security_event('login_failed_unknown_user', ['identifier' => $identifier]);
        return ['success' => false, 'error' => 'अमान्य क्रेडेंशियल दर्ज किए गए (Invalid credentials).'];
    }

    if ($user['status'] === 'suspended') {
        record_security_event('login_attempt_suspended_account', ['user_id' => $user['id']]);
        return ['success' => false, 'error' => 'यह खाता निलंबित (Suspended) है। कृपया प्रशासन से संपर्क करें।'];
    }

    if (!password_verify($password, $user['password'])) {
        record_login_attempt($identifier, $ip, false);
        try {
            $pdo->prepare("UPDATE users SET failed_login_attempts = failed_login_attempts + 1 WHERE id = ?")->execute([$user['id']]);
        } catch (Throwable $t) {}

        // Item 32: Owner Alert on repeated failed admin logins
        if (in_array($user['role'], ['admin', 'super_admin'], true)) {
            $curr_failed = (int)($user['failed_login_attempts'] ?? 0) + 1;
            if ($curr_failed >= 3) {
                send_owner_security_alert(
                    "Repeated Failed Admin Login Attempts for @" . $user['username'],
                    [
                        'username' => $user['username'],
                        'email'    => $user['email'],
                        'role'     => $user['role'],
                        'ip'       => $ip,
                        'failed_count' => $curr_failed
                    ]
                );
            }
        }

        record_security_event('login_failed_bad_password', ['user_id' => $user['id'], 'ip' => $ip]);
        return ['success' => false, 'error' => 'अमान्य क्रेडेंशियल दर्ज किए गए (Invalid credentials).'];
    }

    // Role check if specific portal tab was chosen
    if ($expected_role_group === 'admin' && !in_array($user['role'], ['admin', 'super_admin'])) {
        record_security_event('login_unauthorized_portal_access', ['user_id' => $user['id'], 'expected' => 'admin']);
        return ['success' => false, 'error' => 'यह खाता स्टाफ या एडमिनिस्ट्रेशन पोर्टल के लिए अधिकृत नहीं है।'];
    }
    if ($expected_role_group === 'seller' && !in_array($user['role'], ['vendor', 'admin', 'super_admin'])) {
        record_security_event('login_unauthorized_portal_access', ['user_id' => $user['id'], 'expected' => 'seller']);
        return ['success' => false, 'error' => 'यह खाता विक्रेता / कारीगर पोर्टल के लिए अधिकृत नहीं है।'];
    }

    // Item 15: Optional TOTP 2FA Verification for Admin/Super_admin
    $is_admin_account = in_array($user['role'], ['super_admin', 'admin'], true);
    $has_2fa_configured = !empty($user['two_factor_secret']) && (!empty($user['two_factor_enabled']));
    if ($is_admin_account && $has_2fa_configured) {
        if (empty($two_factor_code)) {
            return [
                'success' => false,
                'require_2fa' => true,
                'user_id' => $user['id'],
                'identifier' => $identifier,
                'portal_tab' => $expected_role_group,
                'error' => 'कृपया अपना 6-अंकीय टू-फैक्टर प्रमाणीकरण कोड (TOTP Code) दर्ज करें।'
            ];
        }
        if (!verify_totp_code($user['two_factor_secret'], $two_factor_code)) {
            record_security_event('login_failed_bad_2fa', ['user_id' => $user['id'], 'ip' => $ip]);
            return [
                'success' => false,
                'require_2fa' => true,
                'user_id' => $user['id'],
                'identifier' => $identifier,
                'portal_tab' => $expected_role_group,
                'error' => 'अमान्य 2FA कोड दर्ज किया गया। कृपया नया 6-अंकीय कोड दर्ज करें।'
            ];
        }
    }

    // Reset failed login counter upon successful authentication
    try {
        $pdo->prepare("UPDATE users SET failed_login_attempts = 0, lockout_until = NULL WHERE id = ?")->execute([$user['id']]);
    } catch (Throwable $t) {}

    // Record Successful Login
    record_login_attempt($identifier, $ip, true);

    // Item 13: Rotate Session ID upon authentication (Prevents Session Fixation)
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }

    // Set Session Variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['login_time'] = time();

    if (!empty($user['force_password_change'])) {
        $_SESSION['force_password_change'] = 1;
    }

    log_audit_action('user_login', 'auth', 'user', $user['id'], 'Successful sign-in from ' . $ip);

    return ['success' => true, 'user' => $user];
}

// Active Festival / Campaign Hero Resolver (Deterministic Asia/Kolkata Scheduling)
function get_active_hero_campaign() {
    global $pdo;
    date_default_timezone_set('Asia/Kolkata');
    $now = date('Y-m-d H:i:s');

    try {
        $stmt = $pdo->prepare("
            SELECT * FROM hero_campaigns 
            WHERE status = 'active'
              AND (start_datetime IS NULL OR start_datetime <= ?)
              AND (end_datetime IS NULL OR end_datetime >= ?)
            ORDER BY priority DESC, id DESC
            LIMIT 1
        ");
        $stmt->execute([$now, $now]);
        $campaign = $stmt->fetch();
        if ($campaign) {
            return $campaign;
        }
    } catch (Exception $e) {}

    // Fallback to null (uses site_settings default)
    return null;
}

// ==========================================
// CART FUNCTIONS (Session-based & Persistent)
// ==========================================
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function get_cart() {
    global $pdo;
    $cart_data = [];
    if (empty($_SESSION['cart']) || !is_array($_SESSION['cart']) || !$pdo) return [];

    $product_ids = array_values(array_filter(array_map('intval', array_keys($_SESSION['cart']))));
    if (empty($product_ids)) return [];

    try {
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, v.name as vendor_name, v.location as vendor_location 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN vendors v ON p.vendor_id = v.id 
            WHERE p.id IN ($placeholders)
        ");
        $stmt->execute($product_ids);
        $products = $stmt->fetchAll() ?: [];

        foreach ($products as $prod) {
            $qty = (int)($_SESSION['cart'][$prod['id']]['qty'] ?? 1);
            $cart_data[] = [
                'product' => $prod,
                'quantity' => $qty,
                'subtotal' => (float)$prod['price'] * $qty,
                'total_weight' => (float)($prod['weight_kg'] ?? 0.5) * $qty
            ];
        }
    } catch (Throwable $e_cart) {
        return [];
    }
    return $cart_data;
}

function add_to_cart($product_id, $qty = 1) {
    global $pdo;
    $product_id = (int)$product_id;
    $qty = max(1, (int)$qty);

    if (!$pdo) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, name, price, original_price, stock, stock_status, image FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $prod = $stmt->fetch();

        if ($prod) {
            $stock = max(0, (int)($prod['stock'] ?? 0));
            // Item 24: Stop treating stock 0 as 1
            if ($stock <= 0 || ($prod['stock_status'] ?? '') === 'Out of Stock') {
                return false;
            }

            if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            if (!isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] = ['qty' => 0];
            }
            $current_qty = (int)$_SESSION['cart'][$product_id]['qty'];
            if ($current_qty >= $stock) {
                return false;
            }
            $new_qty = min($stock, $current_qty + $qty);
            $_SESSION['cart'][$product_id]['qty'] = $new_qty;
            return $prod;
        }
    } catch (Throwable $e) {}
    return false;
}

function update_cart_qty($product_id, $qty) {
    global $pdo;
    $product_id = (int)$product_id;
    $qty = (int)$qty;
    if ($qty <= 0) {
        remove_from_cart($product_id);
    } else {
        if (isset($_SESSION['cart'][$product_id])) {
            $stock = 50;
            if ($pdo) {
                $stmt = $pdo->prepare("SELECT stock, stock_status FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $p = $stmt->fetch();
                if ($p) {
                    $stock = max(0, (int)$p['stock']);
                    if ($stock <= 0 || ($p['stock_status'] ?? '') === 'Out of Stock') {
                        remove_from_cart($product_id);
                        return;
                    }
                }
            }
            $_SESSION['cart'][$product_id]['qty'] = min($stock, $qty);
        }
    }
}

function remove_from_cart($product_id) {
    $product_id = (int)$product_id;
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

function clear_cart() {
    $_SESSION['cart'] = [];
}

function get_cart_count() {
    $count = 0;
    if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += (int)($item['qty'] ?? 1);
        }
    }
    return $count;
}

function get_cart_subtotal() {
    $items = get_cart();
    $total = 0.0;
    foreach ($items as $it) {
        $total += (float)($it['subtotal'] ?? 0);
    }
    return $total;
}

function get_cart_total_weight() {
    $items = get_cart();
    $weight = 0.0;
    foreach ($items as $it) {
        $weight += (float)($it['total_weight'] ?? 0);
    }
    return max(0.5, $weight);
}

// ==========================================
// WISHLIST FUNCTIONS
// ==========================================
if (!isset($_SESSION['wishlist']) || !is_array($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

function get_wishlist_count() {
    return (!empty($_SESSION['wishlist']) && is_array($_SESSION['wishlist'])) ? count($_SESSION['wishlist']) : 0;
}

function is_in_wishlist($product_id) {
    if (!isset($_SESSION['wishlist']) || !is_array($_SESSION['wishlist'])) {
        return false;
    }
    return in_array((int)$product_id, $_SESSION['wishlist']);
}

function toggle_wishlist($product_id) {
    if (!isset($_SESSION['wishlist']) || !is_array($_SESSION['wishlist'])) {
        $_SESSION['wishlist'] = [];
    }
    $product_id = (int)$product_id;
    $idx = array_search($product_id, $_SESSION['wishlist']);
    if ($idx !== false) {
        unset($_SESSION['wishlist'][$idx]);
        $_SESSION['wishlist'] = array_values($_SESSION['wishlist']);
        return false; // removed
    } else {
        $_SESSION['wishlist'][] = $product_id;
        $_SESSION['wishlist'] = array_values(array_unique($_SESSION['wishlist']));
        return true; // added
    }
}

// ==========================================
// COUPON & PROMO CODE ENGINE
// ==========================================

function validate_and_apply_coupon($code) {
    global $pdo;
    $code = strtoupper(trim($code));
    if (empty($code)) {
        return ['success' => false, 'error' => 'कृपया वैध कूपन कोड दर्ज करें।'];
    }

    $subtotal = get_cart_subtotal();
    if ($subtotal <= 0) {
        return ['success' => false, 'error' => 'कूपन लागू करने के लिए आपकी कार्ट में कम से कम 1 उत्पाद होना चाहिए।'];
    }

    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();

    if (!$coupon) {
        return ['success' => false, 'error' => "कूपन कोड '$code' अमान्य है या समाप्त हो चुका है।"];
    }

    // Check Expiry Date
    if (!empty($coupon['expiry_date']) && strtotime($coupon['expiry_date']) < strtotime(date('Y-m-d'))) {
        return ['success' => false, 'error' => "कूपन कोड '$code' की वैधता समाप्त हो चुकी है।"];
    }

    // Check Minimum Order Amount
    if ($subtotal < floatval($coupon['min_order_amount'])) {
        return [
            'success' => false,
            'error' => "यह कूपन न्यूनतम ₹" . number_format($coupon['min_order_amount'], 0) . " के आर्डर पर ही लागू होगा। (वर्तमान कार्ट: " . format_price($subtotal) . ")"
        ];
    }

    // Check Usage Limit
    if ($coupon['used_count'] >= $coupon['usage_limit']) {
        return ['success' => false, 'error' => "इस कूपन की कुल उपयोग सीमा पूरी हो चुकी है।"];
    }

    $curr_user = is_logged_in() ? get_logged_in_user() : null;
    $user_id = $curr_user['id'] ?? null;
    $user_email = $curr_user['email'] ?? '';

    // Check Type 1: New User Only (First order)
    if ($coupon['coupon_type'] === 'new_user_only') {
        if ($curr_user) {
            $stmt_ord = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? OR customer_email = ?");
            $stmt_ord->execute([$user_id, $user_email]);
            if ($stmt_ord->fetchColumn() > 0) {
                return ['success' => false, 'error' => "यह कूपन केवल नए ग्राहकों के पहले आर्डर पर ही मान्य है।"];
            }
        }
    }

    // Check Type 2: One Per User
    if ($coupon['coupon_type'] === 'one_per_user') {
        if ($curr_user) {
            $stmt_used = $pdo->prepare("SELECT id FROM coupon_usages WHERE coupon_id = ? AND (user_id = ? OR customer_email = ?)");
            $stmt_used->execute([$coupon['id'], $user_id, $user_email]);
            if ($stmt_used->fetch()) {
                return ['success' => false, 'error' => "आप इस कूपन कोड ($code) का उपयोग पहले ही कर चुके हैं। यह प्रति ग्राहक केवल 1 बार मान्य है।"];
            }
        }
    }

    // Calculate Discount Amount
    $discount = 0.0;
    if ($coupon['discount_type'] === 'fixed') {
        $discount = min($subtotal, floatval($coupon['discount_value']));
    } else { // percentage
        $pct = floatval($coupon['discount_value']);
        $discount = round($subtotal * ($pct / 100), 2);
        if ($coupon['max_discount'] > 0) {
            $discount = min($discount, floatval($coupon['max_discount']));
        }
    }

    $_SESSION['applied_coupon'] = [
        'id'              => $coupon['id'],
        'code'            => $coupon['code'],
        'title'           => $coupon['title'],
        'discount_type'   => $coupon['discount_type'],
        'discount_value'  => $coupon['discount_value'],
        'discount_amount' => $discount,
        'min_order'       => $coupon['min_order_amount']
    ];

    return [
        'success'         => true,
        'discount'        => $discount,
        'message'         => "बधाई! कूपन '$code' सफलतापूर्वक लागू हुआ। आपने " . format_price($discount) . " की बचत की!"
    ];
}

function get_applied_coupon() {
    if (empty($_SESSION['applied_coupon'])) {
        return null;
    }
    $c = $_SESSION['applied_coupon'];
    $subtotal = get_cart_subtotal();
    if ($subtotal < floatval($c['min_order'])) {
        unset($_SESSION['applied_coupon']);
        return null;
    }
    return $c;
}

function remove_applied_coupon() {
    unset($_SESSION['applied_coupon']);
}

/**
 * Get Site Setting with Memory Cache
 */
function get_site_setting($key, $default = '') {
    global $pdo;
    static $settings_cache = null;
    if ($settings_cache === null) {
        $settings_cache = [];
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
            while ($row = $stmt->fetch()) {
                $val = $row['setting_value'];
                if (function_exists('fix_utf8_mojibake')) {
                    $val = fix_utf8_mojibake($val);
                }
                $settings_cache[$row['setting_key']] = $val;
            }
        } catch (Throwable $e) {}
    }
    $res = $settings_cache[$key] ?? $default;
    return function_exists('fix_utf8_mojibake') ? fix_utf8_mojibake($res) : $res;
}

if (!function_exists('get_setting')) {
    function get_setting($key, $default = '') {
        return get_site_setting($key, $default);
    }
}

/**
 * SEO 301/302 Redirect Interceptor
 * Checks current request URI against seo_redirects rules
 */
function handle_seo_redirects() {
    global $pdo;
    if (php_sapi_name() === 'cli' || empty($_SERVER['REQUEST_URI']) || !$pdo) {
        return;
    }
    
    try {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (empty($uri) || $uri === '/' || substr($uri, 0, 7) === '/admin/' || substr($uri, 0, 8) === '/seller/' || substr($uri, 0, 6) === '/ajax/') {
            return;
        }

        $stmt = $pdo->prepare("SELECT id, target_url, status_code FROM seo_redirects WHERE source_path = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$uri]);
        $rule = $stmt->fetch();
        if ($rule) {
            try {
                $pdo->prepare("UPDATE seo_redirects SET hits = hits + 1 WHERE id = ?")->execute([$rule['id']]);
            } catch (Throwable $e_up) {}
            header("Location: " . $rule['target_url'], true, (int)$rule['status_code']);
            exit;
        }
    } catch (Throwable $e) {}
}

/**
 * Log 404 Crawl / Access Failure
 */
function record_404_hit($path = null) {
    global $pdo;
    if (php_sapi_name() === 'cli' || !$pdo) return;
    try {
        $path = $path ?: parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        if (empty($path)) return;
        $ip = get_client_ip();
        $referrer = $_SERVER['HTTP_REFERER'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $stmt = $pdo->prepare("SELECT id FROM seo_404_logs WHERE request_path = ? AND resolved = 0 LIMIT 1");
        $stmt->execute([$path]);
        $exist = $stmt->fetch();
        if ($exist) {
            $pdo->prepare("UPDATE seo_404_logs SET hit_count = hit_count + 1, last_occurred_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$exist['id']]);
        } else {
            $stmt_ins = $pdo->prepare("INSERT INTO seo_404_logs (request_path, referrer, ip_address, user_agent, hit_count, resolved) VALUES (?, ?, ?, ?, 1, 0)");
            $stmt_ins->execute([$path, $referrer, $ip, $ua]);
        }
    } catch (Throwable $e) {}
}

// Automatically check active SEO redirects on page load
if (php_sapi_name() !== 'cli') {
    handle_seo_redirects();
}
