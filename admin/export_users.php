<?php
/**
 * GaunGaliHat Users CSV Exporter
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="gaungalihat_users_' . date('Y-m-d_His') . '.csv"');

$output = fopen('php://output', 'w');
// UTF-8 BOM
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($output, [
    'ID', 'Unique_Code', 'Full_Name', 'Username', 'Email', 'Phone',
    'Role', 'Department', 'City', 'Pincode', 'Status', 'Created_At'
]);

$stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");
while ($u = $stmt->fetch()) {
    fputcsv($output, array_map('escape_csv_cell', [
        $u['id'],
        $u['unique_code'] ?? '',
        $u['name'],
        $u['username'],
        $u['email'],
        $u['phone'] ?? '',
        $u['role'],
        $u['department'] ?? '',
        $u['city'] ?? '',
        $u['pincode'] ?? '',
        $u['status'],
        $u['created_at'] ?? ''
    ]));
}

fclose($output);
exit;
