import re

# 1. Update product.php
prod_path = r"C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat\product.php"
with open(prod_path, 'r', encoding='utf-8') as f:
    prod_content = f.read()

old_prod_block = """<!-- JSON-LD Structured Data: Product & Breadcrumbs -->
<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "name": "<?= htmlspecialchars($product['name']) ?>",
  "image": [
    "<?= htmlspecialchars($product['image']) ?>"
  ],
  "description": "<?= htmlspecialchars(strip_tags($product['description'] ?? $product['name'])) ?>",
  "sku": "<?= htmlspecialchars($product['code'] ?: ('GGH-PRD-' . $product['id'])) ?>",
  "mpn": "<?= htmlspecialchars($product['code'] ?: ('GGH-PRD-' . $product['id'])) ?>",
  "brand": {
    "@type": "Brand",
    "name": "<?= htmlspecialchars($product['vendor_name'] ?? APP_NAME) ?>"
  },
  "offers": {
    "@type": "Offer",
    "url": "<?= htmlspecialchars($current_canonical_url) ?>",
    "priceCurrency": "INR",
    "price": "<?= number_format((float)$product['price'], 2, '.', '') ?>",
    "priceValidUntil": "<?= date('Y-12-31') ?>",
    "itemCondition": "https://schema.org/NewCondition",
    "availability": "<?= $product['stock'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' ?>",
    "seller": {
      "@type": "Organization",
      "name": "<?= htmlspecialchars($product['vendor_business'] ?? $product['vendor_name'] ?? APP_NAME) ?>"
    }
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "<?= number_format((float)($product['rating'] ?? 4.9), 1) ?>",
    "reviewCount": "<?= max(1, (int)($product['review_count'] ?? 15)) ?>"
  }
}
</script>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Home",
      "item": "<?= htmlspecialchars($canonical_base) ?>/"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "<?= htmlspecialchars($product['category_name'] ?? 'Products') ?>",
      "item": "<?= htmlspecialchars($canonical_base) ?>/products.php?category=<?= urlencode($product['category_slug'] ?? '') ?>"
    },
    {
      "@type": "ListItem",
      "position": 3,
      "name": "<?= htmlspecialchars($product['name']) ?>"
    }
  ]
}
</script>"""

new_prod_block = """<!-- JSON-LD Structured Data: Product & Breadcrumbs (Google Merchant Listings Compliant) -->
<?php
// Compute Schema Offer Dates & Shipping Rates for Merchant Listings
$offer_valid_from = !empty($product['created_at']) 
    ? date('Y-m-d\\TH:i:sP', strtotime($product['created_at'])) 
    : date('Y-01-01\\T00:00:00P');
$price_valid_until = date('Y-m-d', strtotime('+1 year'));

$product_images = [];
if (!empty($product['image'])) {
    $img_url = $product['image'];
    if (!preg_match('~^https?://~i', $img_url)) {
        $img_url = rtrim($canonical_base, '/') . '/' . ltrim($img_url, '/');
    }
    $product_images[] = $img_url;
}
if (!empty($gallery) && is_array($gallery)) {
    foreach ($gallery as $g_img) {
        if (!empty($g_img)) {
            if (!preg_match('~^https?://~i', $g_img)) {
                $g_img = rtrim($canonical_base, '/') . '/' . ltrim($g_img, '/');
            }
            if (!in_array($g_img, $product_images)) {
                $product_images[] = $g_img;
            }
        }
    }
}
if (empty($product_images)) {
    $product_images[] = rtrim($canonical_base, '/') . '/assets/images/logo.png';
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

$breadcrumb_schema = [
    "@context" => "https://schema.org",
    "@type" => "BreadcrumbList",
    "itemListElement" => [
        [
            "@type" => "ListItem",
            "position" => 1,
            "name" => "Home",
            "item" => rtrim($canonical_base, '/') . "/"
        ],
        [
            "@type" => "ListItem",
            "position" => 2,
            "name" => !empty($product['category_name']) ? $product['category_name'] : "Products",
            "item" => rtrim($canonical_base, '/') . "/products.php?category=" . urlencode($product['category_slug'] ?? '')
        ],
        [
            "@type" => "ListItem",
            "position" => 3,
            "name" => $product['name']
        ]
    ]
];
?>
<script type="application/ld+json">
<?= json_encode($product_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>

<script type="application/ld+json">
<?= json_encode($breadcrumb_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>"""

# Normalize newlines for robust matching
norm_content = prod_content.replace('\r\n', '\n')
norm_old = old_prod_block.replace('\r\n', '\n')

if norm_old in norm_content:
    updated_prod = norm_content.replace(norm_old, new_prod_block)
    with open(prod_path, 'w', encoding='utf-8') as f:
        f.write(updated_prod)
    print("SUCCESS: product.php updated with Google Merchant Listings structured data.")
else:
    print("WARNING: Could not find exact old block in product.php")

# 2. Update includes/header.php
header_path = r"C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat\includes\header.php"
with open(header_path, 'r', encoding='utf-8') as f:
    header_content = f.read()

old_org_block = """        "sameAs": [
          "https://www.facebook.com/ganvgalihat",
          "https://twitter.com/ganvgalihat",
          "https://www.instagram.com/ganvgalihat"
        ]
      },"""

new_org_block = """        "sameAs": [
          "https://www.facebook.com/ganvgalihat",
          "https://twitter.com/ganvgalihat",
          "https://www.instagram.com/ganvgalihat"
        ],
        "hasMerchantReturnPolicy": {
          "@type": "MerchantReturnPolicy",
          "applicableCountry": "IN",
          "returnPolicyCategory": "https://schema.org/MerchantReturnFiniteReturnWindow",
          "merchantReturnDays": 7,
          "returnMethod": "https://schema.org/ReturnByMail",
          "returnFees": "https://schema.org/FreeReturn",
          "returnShippingFeesAmount": {
            "@type": "MonetaryAmount",
            "value": "0.00",
            "currency": "INR"
          },
          "merchantReturnLink": "<?= htmlspecialchars($canonical_base) ?>/shipping-policy.php"
        }
      },"""

norm_header = header_content.replace('\r\n', '\n')
norm_old_org = old_org_block.replace('\r\n', '\n')

if norm_old_org in norm_header:
    updated_header = norm_header.replace(norm_old_org, new_org_block)
    with open(header_path, 'w', encoding='utf-8') as f:
        f.write(updated_header)
    print("SUCCESS: includes/header.php updated with Organization return policy.")
else:
    print("WARNING: Could not find old org block in includes/header.php")
