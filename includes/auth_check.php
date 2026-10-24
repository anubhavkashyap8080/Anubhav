<?php
/**
 * Role Clearance & Authorization Gates
 */

require_once __DIR__ . '/functions.php';

// Prevent browser caching of protected dashboards (prevents back-button access after logout)
function send_no_cache_headers() {
    if (!headers_sent()) {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }
}

function require_login($redirect_to = '/login.php') {
    send_no_cache_headers();
    if (!is_logged_in()) {
        set_flash('error', 'Please sign in to access this area.');
        header("Location: $redirect_to");
        exit;
    }
}

function require_vendor($redirect_to = '/login.php?tab=seller') {
    send_no_cache_headers();
    if (!is_vendor()) {
        set_flash('error', 'Artisan / Seller clearance required. Please login with authorized workshop credentials.');
        header("Location: $redirect_to");
        exit;
    }
}

function require_admin($redirect_to = '/login.php?tab=staff') {
    send_no_cache_headers();
    if (!is_admin()) {
        set_flash('error', 'Access Denied: Administrative security credentials required.');
        header("Location: $redirect_to");
        exit;
    }
}

function require_super_admin($redirect_to = '/login.php?tab=staff') {
    send_no_cache_headers();
    if (!is_super_admin()) {
        set_flash('error', 'Access Denied: Super Administrator privilege required.');
        header("Location: $redirect_to");
        exit;
    }
}

function require_permission($permission_slug, $redirect_to = '/admin/index.php') {
    send_no_cache_headers();
    require_admin();
    if (!user_has_permission($permission_slug)) {
        record_security_event('permission_denied', ['permission' => $permission_slug, 'user_role' => $_SESSION['user_role'] ?? '']);
        set_flash('error', 'अनाधिकृत पहुंच: आपके पास इस क्रिया को निष्पादित करने की अनुमति नहीं है (' . htmlspecialchars($permission_slug) . ')।');
        header("Location: $redirect_to");
        exit;
    }
}
