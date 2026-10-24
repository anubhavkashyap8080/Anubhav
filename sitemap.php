<?php
/**
 * GaunGaliHat Enterprise Dynamic XML Sitemap Engine
 * Complies with Sitemaps.org 0.9 protocol and Google Webmaster Guidelines
 */

require_once __DIR__ . '/includes/functions.php';

global $pdo;

// Detect canonical base URL
$stmt_set = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'seo_canonical_url' LIMIT 1");
$stmt_set->execute();
$db_canonical = trim((string)$stmt_set->fetchColumn());

if (!empty($db_canonical)) {
    $base_url = rtrim($db_canonical, '/');
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443 ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'ganvgalihat.com';
    $base_url = $protocol . $host;
}

$type = trim($_GET['type'] ?? '');
header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex, follow');

$today_iso = date('c');

if (empty($type) || $type === 'index') {
    // Generate Sitemap Index
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    $sections = ['products', 'categories', 'sellers', 'blogs'];
    foreach ($sections as $sec) {
        $loc = htmlspecialchars($base_url . '/sitemap-' . $sec . '.xml', ENT_XML1, 'UTF-8');
        echo "  <sitemap>\n";
        echo "    <loc>{$loc}</loc>\n";
        echo "    <lastmod>{$today_iso}</lastmod>\n";
        echo "  </sitemap>\n";
    }
    
    echo '</sitemapindex>';
    return;
}

// Generate Sub-sitemaps
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

// Always include homepage in the first sitemap
if ($type === 'products') {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($base_url . '/', ENT_XML1, 'UTF-8') . "</loc>\n";
    echo "    <lastmod>{$today_iso}</lastmod>\n";
    echo "    <changefreq>daily</changefreq>\n";
    echo "    <priority>1.0</priority>\n";
    echo "  </url>\n";
    
    try {
        $stmt = $pdo->query("SELECT id, name, hindi_name, slug, image, created_at FROM products WHERE stock_status != 'Out of Stock' ORDER BY id DESC LIMIT 5000");
        while ($row = $stmt->fetch()) {
            $loc = htmlspecialchars($base_url . '/product.php?id=' . $row['id'], ENT_XML1, 'UTF-8');
            $lastmod = !empty($row['created_at']) ? date('c', strtotime($row['created_at'])) : $today_iso;
            $img = trim((string)($row['image'] ?? ''));
            if (!empty($img) && strpos($img, 'http') !== 0) {
                $img = $base_url . '/' . ltrim($img, '/');
            }
            
            echo "  <url>\n";
            echo "    <loc>{$loc}</loc>\n";
            echo "    <lastmod>{$lastmod}</lastmod>\n";
            echo "    <changefreq>weekly</changefreq>\n";
            echo "    <priority>0.9</priority>\n";
            if (!empty($img)) {
                $img_loc = htmlspecialchars($img, ENT_XML1, 'UTF-8');
                $img_title = htmlspecialchars($row['name'] . ($row['hindi_name'] ? ' - ' . $row['hindi_name'] : ''), ENT_XML1, 'UTF-8');
                echo "    <image:image>\n";
                echo "      <image:loc>{$img_loc}</image:loc>\n";
                echo "      <image:title>{$img_title}</image:title>\n";
                echo "    </image:image>\n";
            }
            echo "  </url>\n";
        }
    } catch (Exception $e) {}
} elseif ($type === 'categories') {
    try {
        $stmt = $pdo->query("SELECT id, name, slug, image, created_at FROM categories ORDER BY sort_order ASC");
        while ($row = $stmt->fetch()) {
            $loc = htmlspecialchars($base_url . '/products.php?category=' . urlencode($row['slug']), ENT_XML1, 'UTF-8');
            $lastmod = !empty($row['created_at']) ? date('c', strtotime($row['created_at'])) : $today_iso;
            
            echo "  <url>\n";
            echo "    <loc>{$loc}</loc>\n";
            echo "    <lastmod>{$lastmod}</lastmod>\n";
            echo "    <changefreq>weekly</changefreq>\n";
            echo "    <priority>0.8</priority>\n";
            echo "  </url>\n";
        }
    } catch (Exception $e) {}
} elseif ($type === 'sellers') {
    try {
        $stmt = $pdo->query("SELECT id, name, business_name, slug, created_at FROM vendors WHERE verified = 1 ORDER BY id ASC");
        while ($row = $stmt->fetch()) {
            $loc = htmlspecialchars($base_url . '/products.php?vendor=' . urlencode($row['id']), ENT_XML1, 'UTF-8');
            $lastmod = !empty($row['created_at']) ? date('c', strtotime($row['created_at'])) : $today_iso;
            
            echo "  <url>\n";
            echo "    <loc>{$loc}</loc>\n";
            echo "    <lastmod>{$lastmod}</lastmod>\n";
            echo "    <changefreq>weekly</changefreq>\n";
            echo "    <priority>0.7</priority>\n";
            echo "  </url>\n";
        }
    } catch (Exception $e) {}
} elseif ($type === 'blogs') {
    try {
        $stmt = $pdo->query("SELECT id, title, slug, image, created_at, updated_at FROM blogs WHERE status = 'published' ORDER BY id DESC");
        while ($row = $stmt->fetch()) {
            $loc = htmlspecialchars($base_url . '/blog-detail.php?slug=' . urlencode($row['slug']), ENT_XML1, 'UTF-8');
            $date = !empty($row['updated_at']) ? $row['updated_at'] : $row['created_at'];
            $lastmod = !empty($date) ? date('c', strtotime($date)) : $today_iso;
            
            echo "  <url>\n";
            echo "    <loc>{$loc}</loc>\n";
            echo "    <lastmod>{$lastmod}</lastmod>\n";
            echo "    <changefreq>monthly</changefreq>\n";
            echo "    <priority>0.6</priority>\n";
            echo "  </url>\n";
        }
    } catch (Exception $e) {}
}

echo '</urlset>';
