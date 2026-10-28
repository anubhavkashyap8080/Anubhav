<?php
/**
 * GaunGaliHat Product Catalog CSV Exporter
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="gaungalihat_products_' . date('Y-m-d_His') . '.csv"');

$output = fopen('php://output', 'w');
// UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// CSV Header
fputcsv($output, [
    'ID', 'SKU_Code', 'Name_English', 'Name_Hindi', 'Category_ID', 'Category_Name',
    'Vendor_ID', 'Vendor_Name', 'Price_INR', 'Original_Price_INR', 'Discount_Pct',
    'Weight_KG', 'Stock', 'Stock_Status', 'Unit', 'Image_URL', 'Description'
]);

$stmt = $pdo->query("
    SELECT p.*, c.name as category_name, v.business_name as vendor_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN vendors v ON p.vendor_id = v.id
    ORDER BY p.id ASC
");

while ($row = $stmt->fetch()) {
    fputcsv($output, array_map('escape_csv_cell', [
        $row['id'],
        $row['code'],
        $row['name'],
        $row['hindi_name'] ?? '',
        $row['category_id'],
        $row['category_name'] ?? '',
        $row['vendor_id'],
        $row['vendor_name'] ?? '',
        $row['price'],
        $row['original_price'],
        $row['discount'],
        $row['weight_kg'],
        $row['stock'],
        $row['stock_status'],
        $row['unit'] ?? 'piece',
        $row['image'] ?? '',
        $row['description'] ?? ''
    ]));
}

fclose($output);
exit;
