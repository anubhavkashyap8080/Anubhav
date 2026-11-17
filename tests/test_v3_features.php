<?php
/**
 * Test Suite for GanvGaliHat v3 Features:
 * Task A: English System Messages
 * Task B: Hero Buttons Customizer & Show/Hide
 * Task C: Video Story Home (layout_6), YouTube Helpers, and CSP
 */

$test_count = 0;
$pass_count = 0;

function assert_test($condition, $desc) {
    global $test_count, $pass_count;
    $test_count++;
    if ($condition) {
        $pass_count++;
        echo "  [PASS] $desc\n";
    } else {
        echo "  [FAIL] $desc\n";
    }
}

echo "=== 1. Testing Video Helpers & YouTube ID Extraction ===\n";
require_once __DIR__ . '/../includes/video_helpers.php';

// Valid YouTube formats
assert_test(ggh_youtube_id('k4m3y1eH5Ww') === 'k4m3y1eH5Ww', 'Bare 11-char ID parsed');
assert_test(ggh_youtube_id('https://www.youtube.com/watch?v=k4m3y1eH5Ww') === 'k4m3y1eH5Ww', 'Standard watch?v= URL parsed');
assert_test(ggh_youtube_id('https://youtu.be/k4m3y1eH5Ww') === 'k4m3y1eH5Ww', 'Short youtu.be/ URL parsed');
assert_test(ggh_youtube_id('https://www.youtube-nocookie.com/embed/k4m3y1eH5Ww') === 'k4m3y1eH5Ww', 'youtube-nocookie embed URL parsed');
assert_test(ggh_youtube_id('https://www.youtube.com/shorts/k4m3y1eH5Ww') === 'k4m3y1eH5Ww', 'YouTube shorts URL parsed');
assert_test(ggh_youtube_id('https://www.youtube.com/live/k4m3y1eH5Ww?feature=share') === 'k4m3y1eH5Ww', 'YouTube live URL parsed');

// Invalid & Malicious inputs
assert_test(ggh_youtube_id('javascript:alert(1)') === '', 'Rejects javascript: URI');
assert_test(ggh_youtube_id('k4m3y1eH5W') === '', 'Rejects 10-char string');
assert_test(ggh_youtube_id('k4m3y1eH5Ww123') === '', 'Rejects 14-char string');
assert_test(ggh_youtube_id('<script>alert("xss")</script>') === '', 'Rejects XSS tag');
assert_test(ggh_youtube_id('https://malicious.com/watch?v=k4m3y1eH5Ww') === '', 'Rejects non-youtube domain');

echo "\n=== 2. Testing Media and Button URL Validation ===\n";
assert_test(ggh_is_valid_media_url('k4m3y1eH5Ww', 'youtube'), 'Valid YouTube ID accepted');
assert_test(ggh_is_valid_media_url('https://example.com/video.mp4', 'mp4'), 'Valid https MP4 accepted');
assert_test(ggh_is_valid_media_url('/uploads/banners/video.webm', 'mp4'), 'Valid /uploads/ webm accepted');
assert_test(!ggh_is_valid_media_url('http://insecure.com/video.mp4', 'mp4'), 'Rejects insecure http MP4');
assert_test(!ggh_is_valid_media_url('//external.com/video.mp4', 'mp4'), 'Rejects protocol-relative MP4');
assert_test(!ggh_is_valid_media_url('javascript:evil()', 'mp4'), 'Rejects javascript MP4');

assert_test(ggh_is_valid_btn_url('/products.php'), 'Valid relative URL /products.php accepted');
assert_test(ggh_is_valid_btn_url('https://ganvgalihat.com/artisans.php'), 'Valid https URL accepted');
assert_test(!ggh_is_valid_btn_url('//evil.com'), 'Rejects protocol-relative button link');
assert_test(!ggh_is_valid_btn_url('javascript:void(0)'), 'Rejects javascript button link');
assert_test(!ggh_is_valid_btn_url('\\evil.com'), 'Rejects backslash button link');

echo "\n=== 3. Testing Section Configurations & Defaults ===\n";
for ($s = 1; $s <= 6; $s++) {
    $cfg = ggh_v6_get_section_defaults($s);
    assert_test(!empty($cfg['title']), "Section $s has a non-empty default title");
    assert_test(!empty($cfg['poster']), "Section $s has a default poster URL");
    assert_test(in_array($cfg['source'], ['youtube', 'mp4']), "Section $s default source is valid");
}

echo "\n=== 4. Testing CSP in .htaccess ===\n";
$htaccess = file_get_contents(__DIR__ . '/../.htaccess');
assert_test(strpos($htaccess, 'https://www.youtube-nocookie.com') !== false, '.htaccess frame-src contains youtube-nocookie.com');
assert_test(strpos($htaccess, 'https://www.youtube.com') !== false, '.htaccess contains https://www.youtube.com');
assert_test(strpos($htaccess, 'https://s.ytimg.com') !== false, '.htaccess script-src contains s.ytimg.com');

echo "\n=== 5. Testing Layout 6 Registration & Whitelisting ===\n";
$index_php = file_get_contents(__DIR__ . '/../index.php');
assert_test(strpos($index_php, "'layout_6'") !== false, 'index.php valid_layouts includes layout_6');
assert_test(file_exists(__DIR__ . '/../includes/home_layouts/layout_6.php'), 'layout_6.php file exists on disk');

$hero_customizer = file_get_contents(__DIR__ . '/../admin/hero_customizer.php');
assert_test(strpos($hero_customizer, 'value="layout_6"') !== false, 'hero_customizer.php includes layout_6 option card');
assert_test(strpos($hero_customizer, 'hero_btn1_show') !== false, 'hero_customizer.php handles hero_btn1_show toggle');
assert_test(strpos($hero_customizer, 'hero_btn2_show') !== false, 'hero_customizer.php handles hero_btn2_show toggle');

$admin_header = file_get_contents(__DIR__ . '/../admin/admin_header.php');
assert_test(strpos($admin_header, '/admin/video_home.php') !== false, 'admin_header.php includes link to video_home.php');

assert_test(file_exists(__DIR__ . '/../admin/video_home.php'), 'admin/video_home.php file exists on disk');
$video_home = file_get_contents(__DIR__ . '/../admin/video_home.php');
assert_test(strpos($video_home, 'require_admin()') !== false, 'video_home.php enforces require_admin()');
assert_test(strpos($video_home, 'csrf_protect()') !== false, 'video_home.php enforces csrf_protect()');

echo "\n=== 6. Testing Hero Button Show/Hide in Layouts 1-5 ===\n";
for ($l = 1; $l <= 5; $l++) {
    $layout_content = file_get_contents(__DIR__ . "/../includes/home_layouts/layout_{$l}.php");
    assert_test(strpos($layout_content, 'hero_btn1_show') !== false, "layout_{$l}.php checks hero_btn1_show toggle");
}

echo "\n=======================================================\n";
echo "SUMMARY: $pass_count / $test_count tests passed.\n";
if ($pass_count === $test_count) {
    echo "ALL TESTS PASSED SUCCESSFULLY!\n";
    exit(0);
} else {
    echo "SOME TESTS FAILED!\n";
    exit(1);
}
