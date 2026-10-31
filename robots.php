<?php
/**
 * GaunGaliHat Enterprise Dynamic Robots.txt Engine
 */

require_once __DIR__ . '/includes/functions.php';

global $pdo;

// Detect canonical base URL
$stmt_set = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'seo_canonical_url' LIMIT 1");
$stmt_set->execute();
$db_canonical = trim((string)$stmt_set->fetchColumn());

if (!empty($db_canonical)) {
    $base_url = rtrim($db_canonical, '/');
} elseif (defined('SITE_URL')) {
    $base_url = rtrim(SITE_URL, '/');
} else {
    $base_url = 'https://ganvgalihat.com';
}

header('Content-Type: text/plain; charset=utf-8');

echo "# GaunGaliHat Robots.txt\n";
echo "# User-Agent Directives for Web Crawlers and Search Engines\n\n";

echo "User-agent: *\n";
echo "Disallow: /cart.php\n";
echo "Disallow: /checkout.php\n";
echo "Disallow: /account.php\n";
echo "Disallow: /login.php\n";
echo "Disallow: /register.php\n";
echo "Disallow: /logout.php\n\n";

echo "Allow: /\n";
echo "Allow: /products.php\n";
echo "Allow: /product.php\n";
echo "Allow: /blogs.php\n";
echo "Allow: /blog-detail.php\n";
echo "Allow: /artisans.php\n";
echo "Allow: /about.php\n";
echo "Allow: /contact.php\n\n";

echo "Sitemap: {$base_url}/sitemap.xml\n";
