<?php
/**
 * AJAX: newsletter subscription (used by the Video Story Home, section "Newsletter").
 * POST only, CSRF protected, rate limited, honeypot, JSON response.
 * Emails are stored in the newsletter_subscribers table (see Admin > Newsletter).
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/video_helpers.php';

function nl_json($ok, $message, $status = 200) {
    if (ob_get_length()) { ob_clean(); }
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['success' => (bool)$ok, 'message' => $message]);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    nl_json(false, 'Invalid request.', 405);
}

csrf_protect(); // answers with a JSON 403 for AJAX requests when the token is missing/invalid

// Honeypot: bots fill the hidden field. Pretend success, store nothing.
if (!empty($_POST['website'])) {
    nl_json(true, 'Thank you for subscribing!');
}

$ip = get_client_ip();
if (!check_rate_limit('newsletter_ip', $ip, 5, 3600)) {
    nl_json(false, 'Too many attempts. Please try again later.', 429);
}

try {
    $result = ggh_newsletter_subscribe($pdo, $_POST['email'] ?? '', $ip);
} catch (Throwable $e) {
    error_log('newsletter_subscribe: ' . $e->getMessage());
    nl_json(false, 'Could not subscribe right now. Please try again later.', 500);
}

if ($result !== 'ok') {
    nl_json(false, 'Please enter a valid email address.', 422);
}

// Same answer for new and existing subscribers (does not reveal who is subscribed).
nl_json(true, 'Thank you for subscribing!');
