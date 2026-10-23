<?php
/**
 * Role Clearance & Authorization Gates
 */

require_once __DIR__ . '/functions.php';

function require_login($redirect_to = '/login.php') {
    if (!is_logged_in()) {
        set_flash('error', 'Please sign in to access this area.');
        header("Location: $redirect_to");
        exit;
    }
}

function require_vendor($redirect_to = '/login.php?tab=seller') {
    if (!is_vendor()) {
        set_flash('error', 'Artisan / Seller clearance required. Please login with authorized workshop credentials.');
        header("Location: $redirect_to");
        exit;
    }
}

function require_admin($redirect_to = '/login.php?tab=admin') {
    if (!is_admin()) {
        set_flash('error', 'Access Denied: Administrative security credentials required.');
        header("Location: $redirect_to");
        exit;
    }
}

function require_super_admin($redirect_to = '/login.php?tab=admin') {
    if (!is_super_admin()) {
        set_flash('error', 'Access Denied: Super Administrator privilege required.');
        header("Location: $redirect_to");
        exit;
    }
}
