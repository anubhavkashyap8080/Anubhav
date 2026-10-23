<?php
/**
 * AJAX Live Search API
 * Fast, real-time product query with category filter
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

$query = trim($_GET['q'] ?? '');
$category_id = (int)($_GET['category_id'] ?? 0);

if (mb_strlen($query) < 2) {
    echo json_encode(['results' => [], 'total' => 0]);
    exit;
}

try {
    $search_param = '%' . $query . '%';
    $sql = "
        SELECT p.id, p.code, p.name, p.hindi_name, p.slug, p.price, p.original_price, p.discount, p.image, p.stock_status, c.name as category_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE (p.name LIKE ? OR p.hindi_name LIKE ? OR p.code LIKE ? OR p.description LIKE ?)
    ";
    $params = [$search_param, $search_param, $search_param, $search_param];

    if ($category_id > 0) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category_id;
    }

    $sql .= " ORDER BY p.is_best_seller DESC, p.id DESC LIMIT 8";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    $results = [];
    foreach ($products as $p) {
        $results[] = [
            'id' => $p['id'],
            'code' => $p['code'],
            'name' => $p['name'],
            'hindi_name' => $p['hindi_name'] ?? '',
            'price' => format_price($p['price']),
            'original_price' => format_price($p['original_price']),
            'discount' => (int)$p['discount'],
            'image' => $p['image'],
            'category_name' => $p['category_name'] ?? 'Village Staples',
            'stock_status' => $p['stock_status'],
            'url' => '/product.php?id=' . $p['id']
        ];
    }

    echo json_encode([
        'query' => $query,
        'results' => $results,
        'total' => count($results),
        'view_all_url' => '/products.php?q=' . urlencode($query) . ($category_id ? '&category=' . $category_id : '')
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed: ' . $e->getMessage()]);
}
