<?php
/**
 * GanvGaliHat Authenticated KYC Document Viewer (Security Audit H3)
 * Streams protected artisan KYC verification documents strictly to authenticated administrators.
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strict Admin / Super Admin Authorization Check
require_admin();

$file = trim((string)($_GET['file'] ?? ''));
$vendor_id = intval($_GET['vendor_id'] ?? 0);

if (empty($file) && $vendor_id > 0) {
    $stmt = $pdo->prepare("SELECT document_file FROM vendors WHERE id = ? LIMIT 1");
    $stmt->execute([$vendor_id]);
    $doc = $stmt->fetchColumn();
    if ($doc) {
        $file = basename($doc);
    }
}

// Strict filename validation: alphanumeric kyc_ prefix, no path separators or traversal
if (empty($file) || !preg_match('/^kyc_[a-zA-Z0-9_.-]+$/', $file) || strpos($file, '..') !== false) {
    http_response_code(400);
    die("Invalid KYC document identifier requested.");
}

$kyc_dir = realpath(__DIR__ . '/../uploads/kyc');
$file_path = realpath($kyc_dir . DIRECTORY_SEPARATOR . $file);

if (!$file_path || strpos($file_path, $kyc_dir) !== 0 || !file_exists($file_path)) {
    http_response_code(404);
    die("KYC Document not found on server.");
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file_path);
finfo_close($finfo);

$allowed_mimes = [
    'image/jpeg'      => 'jpg',
    'image/png'       => 'png',
    'image/webp'      => 'webp',
    'application/pdf' => 'pdf'
];

if (!isset($allowed_mimes[$mime_type])) {
    http_response_code(415);
    die("Unsupported document MIME type.");
}

// Stream document safely
header('Content-Type: ' . $mime_type);
header('Content-Length: ' . filesize($file_path));
header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=3600');

readfile($file_path);
exit;
