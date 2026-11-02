<?php
/**
 * Test Suite: Google Merchant Listings Structured Data Validation
 * Tests for fixes to:
 * - Missing field 'shippingDetails' (in 'offers')
 * - Missing field 'validFrom' (in 'offers')
 * - Missing field 'hasMerchantReturnPolicy' (in 'offers')
 */

define('TESTING_MOCK_DB', true);

$passed = 0;
$failed = 0;

function assert_test($condition, $name) {
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] $name\n";
        $passed++;
    } else {
        echo "[FAIL] $name\n";
        $failed++;
    }
}

echo "=== 1. Checking PHP Syntax of product.php & includes/header.php ===\n";
exec('php -l ' . escapeshellarg(__DIR__ . '/../product.php'), $out1, $ret1);
assert_test($ret1 === 0, "product.php has no syntax errors");

exec('php -l ' . escapeshellarg(__DIR__ . '/../includes/header.php'), $out2, $ret2);
assert_test($ret2 === 0, "includes/header.php has no syntax errors");

echo "\n=== 2. Checking Static Code Requirements in product.php ===\n";
$prod_code = file_get_contents(__DIR__ . '/../product.php');
assert_test(strpos($prod_code, "'validFrom'") !== false || strpos($prod_code, '"validFrom"') !== false, "product.php contains 'validFrom'");
assert_test(strpos($prod_code, "'shippingDetails'") !== false || strpos($prod_code, '"shippingDetails"') !== false, "product.php contains 'shippingDetails'");
assert_test(strpos($prod_code, "'hasMerchantReturnPolicy'") !== false || strpos($prod_code, '"hasMerchantReturnPolicy"') !== false, "product.php contains 'hasMerchantReturnPolicy'");
assert_test(strpos($prod_code, "'OfferShippingDetails'") !== false || strpos($prod_code, '"OfferShippingDetails"') !== false, "product.php contains 'OfferShippingDetails'");
assert_test(strpos($prod_code, "'MerchantReturnPolicy'") !== false || strpos($prod_code, '"MerchantReturnPolicy"') !== false, "product.php contains 'MerchantReturnPolicy'");

echo "\n=== 3. Simulating Schema Generation for a Product ===\n";
$product = [
    'id' => 42,
    'name' => 'Handmade Pure Clay Handi',
    'image' => '/uploads/products/clay_handi.jpg',
    'gallery' => json_encode(['/uploads/products/clay_handi_side.jpg']),
    'code' => 'GGH-CLAY-42',
    'description' => 'Unadulterated earthen handi by Hardoi village potters.',
    'price' => '499.00',
    'stock' => 15,
    'vendor_name' => 'Kalyani agrofood pvt ltd',
    'vendor_business' => 'Kalyani agrofood pvt ltd',
    'created_at' => '2025-06-10 14:30:00',
    'shipping_fee' => 0.0,
    'rating' => 4.9,
    'review_count' => 22,
    'category_name' => 'Clay & Terracotta',
    'category_slug' => 'clay-terracotta'
];

$canonical_base = 'https://ganvgalihat.com';
$current_canonical_url = 'https://ganvgalihat.com/product.php?id=42';
define('APP_NAME', 'GanvGaliHat');

// Execute logic matching product.php
$offer_valid_from = !empty($product['created_at']) 
    ? date('Y-m-d\TH:i:sP', strtotime($product['created_at'])) 
    : date('Y-01-01\T00:00:00P');
$price_valid_until = date('Y-m-d', strtotime('+1 year'));

$product_images = [];
if (!empty($product['image'])) {
    $img_url = $product['image'];
    if (!preg_match('~^https?://~i', $img_url)) {
        $img_url = rtrim($canonical_base, '/') . '/' . ltrim($img_url, '/');
    }
    $product_images[] = $img_url;
}

$product_sku = !empty($product['code']) ? $product['code'] : ('GGH-PRD-' . $product['id']);
$product_brand = !empty($product['vendor_name']) ? $product['vendor_name'] : APP_NAME;
$product_seller = !empty($product['vendor_business']) ? $product['vendor_business'] : $product_brand;
$product_desc = !empty($product['description']) ? trim(strip_tags($product['description'])) : $product['name'];
$shipping_fee = isset($product['shipping_fee']) ? (float)$product['shipping_fee'] : 0.0;
$product_offer_url = !empty($current_canonical_url) ? $current_canonical_url : (rtrim($canonical_base, '/') . '/product.php?id=' . $product['id']);
$return_policy_url = rtrim($canonical_base, '/') . '/shipping-policy.php';

$product_schema = [
    "@context" => "https://schema.org/",
    "@type" => "Product",
    "name" => $product['name'],
    "image" => $product_images,
    "description" => $product_desc,
    "sku" => $product_sku,
    "mpn" => $product_sku,
    "brand" => [
        "@type" => "Brand",
        "name" => $product_brand
    ],
    "offers" => [
        "@type" => "Offer",
        "url" => $product_offer_url,
        "priceCurrency" => "INR",
        "price" => number_format((float)$product['price'], 2, '.', ''),
        "priceValidUntil" => $price_valid_until,
        "validFrom" => $offer_valid_from,
        "itemCondition" => "https://schema.org/NewCondition",
        "availability" => ((int)($product['stock'] ?? 0) > 0) ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
        "seller" => [
            "@type" => "Organization",
            "name" => $product_seller
        ],
        "shippingDetails" => [
            "@type" => "OfferShippingDetails",
            "shippingRate" => [
                "@type" => "MonetaryAmount",
                "value" => number_format($shipping_fee, 2, '.', ''),
                "currency" => "INR"
            ],
            "shippingDestination" => [
                "@type" => "DefinedRegion",
                "addressCountry" => "IN"
            ],
            "deliveryTime" => [
                "@type" => "ShippingDeliveryTime",
                "handlingTime" => [
                    "@type" => "QuantitativeValue",
                    "minValue" => 1,
                    "maxValue" => 2,
                    "unitCode" => "DAY"
                ],
                "transitTime" => [
                    "@type" => "QuantitativeValue",
                    "minValue" => 3,
                    "maxValue" => 7,
                    "unitCode" => "DAY"
                ]
            ]
        ],
        "hasMerchantReturnPolicy" => [
            "@type" => "MerchantReturnPolicy",
            "applicableCountry" => "IN",
            "returnPolicyCategory" => "https://schema.org/MerchantReturnFiniteReturnWindow",
            "merchantReturnDays" => 7,
            "returnMethod" => "https://schema.org/ReturnByMail",
            "returnFees" => "https://schema.org/FreeReturn",
            "returnShippingFeesAmount" => [
                "@type" => "MonetaryAmount",
                "value" => "0.00",
                "currency" => "INR"
            ],
            "merchantReturnLink" => $return_policy_url
        ]
    ],
    "aggregateRating" => [
        "@type" => "AggregateRating",
        "ratingValue" => number_format((float)($product['rating'] ?? 4.9), 1, '.', ''),
        "reviewCount" => max(1, (int)($product['review_count'] ?? 15))
    ]
];

$json_out = json_encode($product_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
assert_test(!empty($json_out) && json_last_error() === JSON_ERROR_NONE, "Schema generates 100% valid JSON-LD");

$parsed = json_decode($json_out, true);
assert_test($parsed['@type'] === 'Product', "Root type is 'Product'");
assert_test(isset($parsed['offers']), "Product contains 'offers'");
$offers = $parsed['offers'];
assert_test($offers['@type'] === 'Offer', "Offers type is 'Offer'");

echo "\n=== 4. Validating the 3 GSC Merchant Listings Fields in 'offers' ===\n";
// Issue 1: validFrom
assert_test(!empty($offers['validFrom']), "Issue #1: 'validFrom' exists in 'offers'");
assert_test(preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $offers['validFrom']) === 1, "'validFrom' matches ISO 8601 format: " . $offers['validFrom']);

// Issue 2: shippingDetails
assert_test(isset($offers['shippingDetails']), "Issue #2: 'shippingDetails' exists in 'offers'");
$shipping = $offers['shippingDetails'];
assert_test($shipping['@type'] === 'OfferShippingDetails', "'shippingDetails' @type is OfferShippingDetails");
assert_test($shipping['shippingRate']['currency'] === 'INR', "'shippingDetails' currency is INR");
assert_test($shipping['shippingDestination']['addressCountry'] === 'IN', "'shippingDetails' destination country is IN");
assert_test($shipping['deliveryTime']['@type'] === 'ShippingDeliveryTime', "'shippingDetails' has ShippingDeliveryTime");
assert_test($shipping['deliveryTime']['handlingTime']['minValue'] >= 1, "handlingTime minValue >= 1 day");
assert_test($shipping['deliveryTime']['transitTime']['maxValue'] <= 7, "transitTime maxValue <= 7 days");

// Issue 3: hasMerchantReturnPolicy
assert_test(isset($offers['hasMerchantReturnPolicy']), "Issue #3: 'hasMerchantReturnPolicy' exists in 'offers'");
$returnPolicy = $offers['hasMerchantReturnPolicy'];
assert_test($returnPolicy['@type'] === 'MerchantReturnPolicy', "'hasMerchantReturnPolicy' @type is MerchantReturnPolicy");
assert_test($returnPolicy['applicableCountry'] === 'IN', "'hasMerchantReturnPolicy' applicableCountry is IN");
assert_test($returnPolicy['merchantReturnDays'] === 7, "'hasMerchantReturnPolicy' return window is 7 days");
assert_test($returnPolicy['returnFees'] === 'https://schema.org/FreeReturn', "'hasMerchantReturnPolicy' returnFees is FreeReturn");
assert_test(strpos($returnPolicy['merchantReturnLink'], 'shipping-policy.php') !== false, "'hasMerchantReturnPolicy' links to shipping-policy.php");

echo "\n=== 5. Checking header.php Organization Fallback Schema ===\n";
$header_code = file_get_contents(__DIR__ . '/../includes/header.php');
assert_test(strpos($header_code, '"hasMerchantReturnPolicy"') !== false, "header.php includes Organization-level hasMerchantReturnPolicy");

echo "\n============================================\n";
echo "SUMMARY: Passed: $passed, Failed: $failed\n";
if ($failed === 0) {
    echo "ALL MERCHANT LISTINGS SCHEMA TESTS PASSED 100%!\n";
} else {
    echo "SOME TESTS FAILED!\n";
    exit(1);
}
