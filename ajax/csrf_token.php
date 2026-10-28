<?php
/**
 * Returns the current session's CSRF token (never cached).
 * Used by main.js to recover when a cached page carries a stale token.
 */
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, private');
header('Pragma: no-cache');
echo json_encode(['success' => true, 'csrf_token' => csrf_token()]);
