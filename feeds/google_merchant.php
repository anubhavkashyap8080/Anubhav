<?php
/**
 * GaunGaliHat Official Google Merchant Center Product Feed (XML)
 * 100% Google Shopping & Merchant Center Policy Compliant RSS 2.0 Feed
 * Includes individual product-level shipping rates, GTIN exemption & weight tags
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/shipping.php';

// Canonical base URL
$default_base = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'https://gaungalihat.womenvission.in';
$site_url = rtrim(get_setting('seo_canonical_url', $default_base), '/');

// Fetch all active products with category and vendor details
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name, c.slug as category_slug, 
           v.name as vendor_name, v.district as vendor_district
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN vendors v ON p.vendor_id = v.id
    WHERE p.stock >= 0
    ORDER BY p.id ASC
");
$products = $stmt->fetchAll();

// Mapping to official Google Product Taxonomy
function get_google_category($cat_slug) {
    if (strpos($cat_slug, 'farming') !== false || strpos($cat_slug, 'tool') !== false) {
        return 'Business & Industrial > Agriculture > Agricultural Machinery & Equipment';
    } elseif (strpos($cat_slug, 'pulses') !== false || strpos($cat_slug, 'grain') !== false) {
        return 'Food, Beverages & Tobacco > Food Items > Grains, Rice & Cereal';
    } elseif (strpos($cat_slug, 'pickle') !== false || strpos($cat_slug, 'murabba') !== false) {
        return 'Food, Beverages & Tobacco > Food Items > Condiments & Sauces > Pickles';
    } elseif (strpos($cat_slug, 'jute') !== false || strpos($cat_slug, 'bag') !== false) {
        return 'Apparel & Accessories > Handbags, Wallets & Cases > Shopping Totes';
    } elseif (strpos($cat_slug, 'wood') !== false || strpos($cat_slug, 'decor') !== false) {
        return 'Home & Garden > Decor';
    }
    return 'Business & Industrial > Agriculture';
}

// Check if output as downloadable XML file or live streaming
if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/xml; charset=utf-8');
}

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">' . "\n";
$xml .= '  <channel>' . "\n";
$xml .= '    <title>' . htmlspecialchars(get_setting('brand_name', 'GaunGaliHat')) . ' — Rural Artisan Marketplace</title>' . "\n";
$xml .= '    <link>' . htmlspecialchars($site_url) . '</link>' . "\n";
$xml .= '    <description>Authentic hand-forged agricultural tools, stone-ground unpolished pulses, and Banarasi pickles direct from Indian village clusters.</description>' . "\n";

foreach ($products as $p) {
    $item_id = 'GGH-' . str_pad($p['id'], 4, '0', STR_PAD_LEFT);
    $item_title = trim($p['name']);
    $item_desc = !empty($p['description']) ? trim(strip_tags($p['description'])) : $item_title . ' crafted direct from Indian rural foundries and clusters.';
    
    // Product URL
    $item_url = $site_url . '/product.php?id=' . $p['id'];

    // Product Image Absolute URL
    $img = $p['image'] ?? ($p['image_url'] ?? '');
    if (strpos($img, 'http') === 0) {
        $image_url = $img;
    } elseif (strpos($img, '/') === 0) {
        $image_url = $site_url . $img;
    } else {
        $image_url = $site_url . '/' . $img;
    }

    $price = number_format((float)$p['price'], 2, '.', '');
    $in_stock = ($p['stock'] > 0);
    $availability = $in_stock ? 'in_stock' : 'out_of_stock';
    
    // Weight calculation
    $weight_kg = !empty($p['weight_kg']) ? floatval($p['weight_kg']) : 0.8;
    if ($weight_kg <= 0) $weight_kg = 0.8;

    // Accurate individual shipping calculation per product
    $ship_calc = calculate_shipping_rate('241126', (float)$p['price'], $weight_kg);
    $ship_cost = number_format($ship_calc['shipping_fee'], 2, '.', '');

    $google_cat = get_google_category($p['category_slug'] ?? '');
    $brand_name = !empty($p['vendor_name']) ? $p['vendor_name'] : 'GaunGaliHat';

    $xml .= '    <item>' . "\n";
    $xml .= '      <g:id>' . htmlspecialchars($item_id) . '</g:id>' . "\n";
    $xml .= '      <g:title><![CDATA[' . $item_title . ']]></g:title>' . "\n";
    $xml .= '      <g:description><![CDATA[' . $item_desc . ']]></g:description>' . "\n";
    $xml .= '      <g:link>' . htmlspecialchars($item_url) . '</g:link>' . "\n";
    $xml .= '      <g:image_link>' . htmlspecialchars($image_url) . '</g:image_link>' . "\n";
    $xml .= '      <g:condition>new</g:condition>' . "\n";
    $xml .= '      <g:availability>' . $availability . '</g:availability>' . "\n";
    $xml .= '      <g:price>' . $price . ' INR</g:price>' . "\n";
    $xml .= '      <g:brand><![CDATA[' . $brand_name . ']]></g:brand>' . "\n";
    
    // Crucial for Google approval of rural artisan & custom farm goods without UPC/EAN barcodes
    $xml .= '      <g:identifier_exists>no</g:identifier_exists>' . "\n";
    $xml .= '      <g:google_product_category><![CDATA[' . $google_cat . ']]></g:google_product_category>' . "\n";
    if (!empty($p['category_name'])) {
        $xml .= '      <g:product_type><![CDATA[' . $p['category_name'] . ']]></g:product_type>' . "\n";
    }

    // Individual Product Level Shipping (Complies with GMC rules for items with variable shipping)
    $xml .= '      <g:shipping>' . "\n";
    $xml .= '        <g:country>IN</g:country>' . "\n";
    $xml .= '        <g:service>India Post Speed Post</g:service>' . "\n";
    $xml .= '        <g:price>' . $ship_cost . ' INR</g:price>' . "\n";
    $xml .= '      </g:shipping>' . "\n";
    $xml .= '      <g:shipping_weight>' . number_format($weight_kg, 2, '.', '') . ' kg</g:shipping_weight>' . "\n";

    $xml .= '    </item>' . "\n";
}

$xml .= '  </channel>' . "\n";
$xml .= '</rss>' . "\n";

// Save static file copy for high-speed serving
file_put_contents(__DIR__ . '/google_merchant.xml', $xml);

echo $xml;
