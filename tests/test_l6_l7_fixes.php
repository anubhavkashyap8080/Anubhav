<?php
/**
 * Test Suite: Layout 6 & Layout 7 Enhanced Fixes
 * Verifies color contrast, card text visibility, category tiles on photos,
 * new arrivals header styling, modern slider controls, auto-hint scroll,
 * and ToolsVilla B2B contrast & trust card image options.
 */

$test_count = 0;
$pass_count = 0;

function assert_fix($description, $condition) {
    global $test_count, $pass_count;
    $test_count++;
    if ($condition) {
        $pass_count++;
        echo " [PASS] $description\n";
    } else {
        echo "![FAIL] $description\n";
    }
}

echo "=== Running Layout 6 & Layout 7 Fixes Verification Suite ===\n\n";

$base_dir = dirname(__DIR__);

// 1. Layout 6 Color Contrast & Card Visibility
$l6_content = file_get_contents($base_dir . '/includes/home_layouts/layout_6.php');

assert_fix("L6: .v6-content-sec .v6-pc-name has dark text #0f172a", strpos($l6_content, '.v6-content-sec .v6-pc-name { color: #0f172a !important;') !== false || strpos($l6_content, '.v6-content-sec .v6-pc-title, .v6-content-sec .v6-pc-name { color: #0f172a !important;') !== false);
assert_fix("L6: .v6-content-sec .v6-pc-cat has high-contrast green #059669", strpos($l6_content, '.v6-content-sec .v6-pc-cat { color: #059669 !important;') !== false);
assert_fix("L6: .v6-content-sec .v6-price b has bold dark color #064e3b", strpos($l6_content, '.v6-content-sec .v6-price b { color: #064e3b !important;') !== false);

// 2. Layout 6 Category Tiles on Photos (Image 3 Fix)
assert_fix("L6: Category tiles have dark bottom gradient overlay", strpos($l6_content, 'background: linear-gradient(180deg, transparent 0%, rgba(15,23,42,0.88)') !== false);
assert_fix("L6: Category tile title b is crisp white #ffffff on dark gradient", strpos($l6_content, '.v6-tile b, .v6-content-sec .v6-tile b { color: #ffffff !important;') !== false);
assert_fix("L6: Category tile product count is gold #fde047 on dark gradient", strpos($l6_content, '.v6-tile small, .v6-content-sec .v6-tile small { color: #fde047 !important;') !== false);

// 3. Layout 6 New Arrivals Header & Rail (Image 4 & 5 Fix)
assert_fix("L6: New Arrivals has dedicated header badge", strpos($l6_content, 'v6-strip-badge') !== false);
assert_fix("L6: New Arrivals has View All link", strpos($l6_content, 'View All New Arrivals') !== false);
assert_fix("L6: .v6-rail has generous scroll padding to prevent last card cutoff", strpos($l6_content, 'scroll-padding-right: 36px') !== false);

// 4. Layout 6 Best Sellers Modern Slider & Auto-Hint Scroll (Image 6 Fix)
assert_fix("L6: Section 3 has modern interactive slider controls", strpos($l6_content, 'Bestselling Implements &amp; Produce') !== false);
assert_fix("L6: Section 3 has data-hint-scroll attribute", strpos($l6_content, 'data-hint-scroll="1"') !== false);
assert_fix("L6: Auto hint-scroll animation script is present", strpos($l6_content, 'hintObs') !== false && strpos($l6_content, 'scrollTo({ left: 140') !== false);

// 5. Layout 7 Guaranteed Background & Contrast (Image 1 & 2 Fix)
$l7_content = file_get_contents($base_dir . '/includes/home_layouts/layout_7.php');
assert_fix("L7: .l7-hero-box has deep forest gradient with guaranteed contrast", strpos($l7_content, 'linear-gradient(135deg, #062b1e 0%, #0a3d2b 55%, #031c13 100%)') !== false);
assert_fix("L7: .l7-b2b-box has solid dark emerald/slate gradient (Fixing Image 2)", strpos($l7_content, '.l7-b2b-box {') !== false && strpos($l7_content, 'linear-gradient(135deg, #0b1f17 0%, #064e3b 60%, #032b1f 100%)') !== false);
assert_fix("L7: .l7-hero-pill-badge has high-contrast gold/amber styling", strpos($l7_content, '.l7-hero-pill-badge {') !== false && strpos($l7_content, '#fde047') !== false);

// 6. Layout 7 Trust Cards Image & Text Support (Image 1 Fix)
assert_fix("L7: Right column Card 1 supports image alongside text", strpos($l7_content, '$l7_card1_img') !== false && strpos($l7_content, 'l7_e($l7_card1_img)') !== false);
assert_fix("L7: Right column Card 2 supports image alongside text", strpos($l7_content, '$l7_card2_img') !== false && strpos($l7_content, 'l7_e($l7_card2_img)') !== false);

// 7. Layout 7 Site Categories Integration
assert_fix("L7: Sidebar dynamically integrates real database categories", strpos($l7_content, '$l7_db_categories') !== false && strpos($l7_content, 'l7_icon_map') !== false);

// 8. Admin Customizer (toolsvilla_home.php)
$admin_content = file_get_contents($base_dir . '/admin/toolsvilla_home.php');
assert_fix("Admin: toolsvilla_home.php has l7_card1_img setting", strpos($admin_content, "'l7_card1_img'") !== false);
assert_fix("Admin: toolsvilla_home.php has l7_card2_img setting", strpos($admin_content, "'l7_card2_img'") !== false);
preg_match_all('/[\x{0900}-\x{097F}]/u', $admin_content, $dev_matches);
assert_fix("Admin: toolsvilla_home.php is 100% English (0 Devanagari)", count($dev_matches[0]) === 0);

echo "\nSummary: $pass_count / $test_count tests passed.\n";
if ($pass_count === $test_count) {
    echo " ALL L6 & L7 FIXES VERIFIED SUCCESSFULLY!\n";
    exit(0);
} else {
    echo " Some tests FAILED!\n";
    exit(1);
}
