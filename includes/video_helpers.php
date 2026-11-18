<?php
/**
 * GanvGaliHat - Video Story Home (layout_6) helpers
 * Validation + settings access for the YouTube / MP4 video background home page.
 * Required by includes/home_layouts/layout_6.php, admin/video_home.php, admin/hero_customizer.php.
 */

if (!function_exists('ggh_yt_id')) {
    /**
     * Return a validated 11-character YouTube video ID from a URL or bare ID.
     * Returns '' when the input is not a recognised single-video reference.
     */
    function ggh_yt_id($input) {
        $raw = trim((string)$input);
        if ($raw === '') {
            return '';
        }
        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $raw)) {
            return $raw;
        }
        $parts = @parse_url($raw);
        if (!$parts || empty($parts['host'])) {
            return '';
        }
        $host = strtolower(preg_replace('/^www\./i', '', $parts['host']));
        $path = $parts['path'] ?? '';
        $id = '';
        if ($host === 'youtu.be') {
            $id = trim($path, '/');
        } elseif (in_array($host, ['youtube.com', 'm.youtube.com', 'music.youtube.com', 'youtube-nocookie.com'], true)) {
            if (preg_match('#^/(?:embed|shorts|live|v)/([A-Za-z0-9_-]{11})#', $path, $m)) {
                $id = $m[1];
            } elseif (!empty($parts['query'])) {
                parse_str($parts['query'], $q);
                $id = isset($q['v']) ? (string)$q['v'] : '';
            }
        }
        return preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? $id : '';
    }
}

if (!function_exists('ggh_youtube_id')) {
    /** Alias for backward compatibility */
    function ggh_youtube_id($input) {
        return ggh_yt_id($input);
    }
}

if (!function_exists('ggh_is_valid_media_url')) {
    /** Alias for backward compatibility */
    function ggh_is_valid_media_url($url, $type = 'any') {
        if ($type === 'youtube') {
            return ggh_yt_id($url) !== '';
        }
        if ($type === 'mp4' || $type === 'video') {
            $url = trim((string)$url);
            if ($url === '' || strlen($url) > 500 || preg_match('/[\x00-\x1F\x7F\\\\\s"\'<>]/', $url)) {
                return false;
            }
            if (stripos($url, 'javascript:') === 0 || stripos($url, '//') === 0 || stripos($url, 'http://') === 0) {
                return false;
            }
            $path = parse_url($url, PHP_URL_PATH) ?: '';
            if (!preg_match('/\.(mp4|webm)$/i', $path)) {
                return false;
            }
            return (stripos($url, 'https://') === 0 || ($url[0] === '/' && (!isset($url[1]) || $url[1] !== '/')));
        }
        return ggh_safe_media_url($url) !== '';
    }
}

if (!function_exists('ggh_is_valid_btn_url')) {
    /** Alias for backward compatibility */
    function ggh_is_valid_btn_url($url) {
        return ggh_safe_link($url) !== '';
    }
}

if (!function_exists('ggh_v6_get_section_defaults')) {
    /** Helper for section defaults lookup */
    function ggh_v6_get_section_defaults($s = null) {
        $defs = ggh_v6_section_defaults();
        if ($s === null) return $defs;
        $d = $defs[$s] ?? [];
        if (!isset($d['source'])) $d['source'] = 'youtube';
        if (empty($d['poster'])) $d['poster'] = 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=1600&q=70';
        return $d;
    }
}

if (!function_exists('ggh_safe_link')) {
    /**
     * Accept only same-site relative paths, #anchors and https:// links.
     * Rejects javascript:, data:, protocol-relative (//host) and backslash tricks.
     */
    function ggh_safe_link($url) {
        $url = trim((string)$url);
        if ($url === '' || strlen($url) > 500) {
            return '';
        }
        if (preg_match('/[\x00-\x1F\x7F\\\\]/', $url)) {
            return '';
        }
        if ($url[0] === '#') {
            return $url;
        }
        if ($url[0] === '/') {
            return (isset($url[1]) && $url[1] === '/') ? '' : $url;
        }
        if (preg_match('#^https://[A-Za-z0-9.-]+(?::\d+)?(?:[/?\#].*)?$#', $url)) {
            return $url;
        }
        return '';
    }
}

if (!function_exists('ggh_safe_media_url')) {
    /**
     * Image / MP4 URLs: local /uploads/... path, or https:// URL.
     * $video_only = true additionally requires a .mp4/.webm extension and
     * only allows local paths or the site's own host (CSP media-src is 'self').
     */
    function ggh_safe_media_url($url, $video_only = false) {
        $url = trim((string)$url);
        if ($url === '' || strlen($url) > 500 || preg_match('/[\x00-\x1F\x7F\\\\\s"\'<>]/', $url)) {
            return '';
        }
        if ($video_only) {
            $path = parse_url($url, PHP_URL_PATH) ?: '';
            if (!preg_match('/\.(mp4|webm)$/i', $path)) {
                return '';
            }
            if ($url[0] === '/' && (!isset($url[1]) || $url[1] !== '/')) {
                return $url;
            }
            $host = parse_url($url, PHP_URL_HOST);
            $self = strtolower(preg_replace('/^www\./i', '', $_SERVER['HTTP_HOST'] ?? ''));
            if ($host && $self && strtolower(preg_replace('/^www\./i', '', $host)) === $self && stripos($url, 'https://') === 0) {
                return $url;
            }
            return '';
        }
        if ($url[0] === '/' && (!isset($url[1]) || $url[1] !== '/')) {
            return $url;
        }
        return preg_match('#^https://[A-Za-z0-9.-]+(?::\d+)?/.*$#', $url) ? $url : '';
    }
}

if (!function_exists('ggh_v6_section_defaults')) {
    /** Default content for the 8 sections of the Video Story Home. */
    function ggh_v6_section_defaults() {
        $company = function_exists('get_company_name') ? get_company_name() : 'GanvGaliHat';
        return [
            1 => [
                'name' => 'Hero',
                'enabled' => '1',
                'eyebrow' => $company,
                'title' => 'Gaon ke hunar, ab har ghar tak',
                'subtitle' => 'Hand-forged tools, pure pulses, village pickles and handcrafted goods - straight from the artisans of rural India.',
                'btn1_text' => 'Explore Marketplace',
                'btn1_link' => '/products.php',
                'btn1_show' => '1',
                'btn2_text' => 'Meet the Artisans',
                'btn2_link' => '/artisans.php',
                'btn2_show' => '1',
                'overlay' => '45',
                'align' => 'left',
                'poster' => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=1600&q=70',
                'default_video' => 'https://www.youtube.com/watch?v=ScMzIvxBSi4'
            ],
            2 => [
                'name' => 'Categories',
                'enabled' => '1',
                'eyebrow' => 'Shop by category',
                'title' => 'Everything the village makes',
                'subtitle' => 'Pick a category and discover products made by real hands.',
                'btn1_text' => 'View all products',
                'btn1_link' => '/products.php',
                'btn1_show' => '1',
                'btn2_text' => '',
                'btn2_link' => '',
                'btn2_show' => '0',
                'overlay' => '55',
                'align' => 'center',
                'poster' => '',
                'limit' => '8',
                'cat_source' => 'all',
                'cat_ids' => '',
                'show_products' => '1',
                'prod_limit' => '4'
            ],
            3 => [
                'name' => 'Best sellers',
                'enabled' => '1',
                'eyebrow' => 'Most loved',
                'title' => 'Best sellers this week',
                'subtitle' => 'What customers are adding to their carts right now.',
                'btn1_text' => 'See all best sellers',
                'btn1_link' => '/products.php',
                'btn1_show' => '1',
                'btn2_text' => '',
                'btn2_link' => '',
                'btn2_show' => '0',
                'overlay' => '55',
                'align' => 'left',
                'poster' => '',
                'limit' => '8',
                'prod_source' => 'best_sellers',
                'prod_cat' => ''
            ],
            4 => [
                'name' => 'Featured',
                'enabled' => '1',
                'eyebrow' => 'Fresh picks',
                'title' => 'Handpicked for you',
                'subtitle' => 'New and featured products from our verified artisans.',
                'btn1_text' => 'Browse the catalog',
                'btn1_link' => '/products.php',
                'btn1_show' => '1',
                'btn2_text' => '',
                'btn2_link' => '',
                'btn2_show' => '0',
                'overlay' => '50',
                'align' => 'right',
                'poster' => '',
                'limit' => '5',
                'prod_source' => 'featured',
                'prod_cat' => ''
            ],
            5 => [
                'name' => 'Artisans',
                'enabled' => '1',
                'eyebrow' => 'Meet the makers',
                'title' => 'Behind every product, a village story',
                'subtitle' => 'Verified artisans and producers who ship straight from their workshops.',
                'btn1_text' => 'All artisans',
                'btn1_link' => '/artisans.php',
                'btn1_show' => '1',
                'btn2_text' => '',
                'btn2_link' => '',
                'btn2_show' => '0',
                'overlay' => '55',
                'align' => 'center',
                'poster' => '',
                'limit' => '4'
            ],
            6 => [
                'name' => 'Trust + CTA',
                'enabled' => '1',
                'eyebrow' => 'Why GanvGaliHat',
                'title' => 'Authentic. Direct. Delivered.',
                'subtitle' => 'No middlemen. Honest prices. Doorstep delivery across India.',
                'btn1_text' => 'Start shopping',
                'btn1_link' => '/products.php',
                'btn1_show' => '1',
                'btn2_text' => 'Become a seller',
                'btn2_link' => '/seller-register.php',
                'btn2_show' => '1',
                'overlay' => '55',
                'align' => 'center',
                'poster' => ''
            ],
            7 => [
                'name' => 'Blog',
                'enabled' => '1',
                'eyebrow' => 'From our journal',
                'title' => 'Stories, tips and village know-how',
                'subtitle' => 'Read how our artisans work and how to get the best from their products.',
                'btn1_text' => 'Read all articles',
                'btn1_link' => '/blogs.php',
                'btn1_show' => '1',
                'btn2_text' => '',
                'btn2_link' => '',
                'btn2_show' => '0',
                'overlay' => '62',
                'align' => 'left',
                'poster' => '',
                'limit' => '3'
            ],
            8 => [
                'name' => 'Newsletter',
                'enabled' => '1',
                'eyebrow' => 'Stay in the loop',
                'title' => 'Festival offers and new arrivals, first',
                'subtitle' => 'Join our newsletter. No spam, only good news from the villages.',
                'btn1_text' => 'Subscribe',
                'btn1_link' => '/',
                'btn1_show' => '1',
                'btn2_text' => '',
                'btn2_link' => '',
                'btn2_show' => '0',
                'overlay' => '62',
                'align' => 'center',
                'poster' => ''
            ],
        ];
    }
}

if (!function_exists('ggh_v6_global')) {
    /** Global options for the video home (header, dots, snap). Values '1' / '0'. */
    function ggh_v6_global() {
        return [
            'header_transparent' => get_setting('v6_header_transparent', '1') !== '0',
            'dots'               => get_setting('v6_dots', '1') !== '0',
            'snap'               => get_setting('v6_snap', '0') === '1',
        ];
    }
}

if (!function_exists('ggh_v6_sections')) {
    /**
     * Load all 8 sections from site_settings, validate and sanitise every field.
     * Output is safe to print with htmlspecialchars().
     */
    function ggh_v6_sections() {
        $defs = ggh_v6_section_defaults();
        $out = [];
        foreach ($defs as $n => $d) {
            $s = [];
            foreach ($d as $k => $def) {
                $s[$k] = ($k === 'name') ? $def : (string)get_setting("v6_sec{$n}_{$k}", $def);
            }
            $s['n']        = $n;
            $s['enabled']  = $s['enabled'] !== '0';
            $s['source']   = get_setting("v6_sec{$n}_source", 'youtube') === 'mp4' ? 'mp4' : 'youtube';
            $video_raw     = (string)get_setting("v6_sec{$n}_video", '');
            // Section 1 fallback default video if none configured
            if ($n === 1 && $video_raw === '' && !empty($d['default_video'])) {
                $video_raw = $d['default_video'];
            }
            $s['yt_id']    = $s['source'] === 'youtube' ? ggh_yt_id($video_raw) : '';
            $s['mp4']      = $s['source'] === 'mp4' ? ggh_safe_media_url($video_raw, true) : '';
            $s['video_raw'] = $video_raw;
            $s['start']    = max(0, (int)get_setting("v6_sec{$n}_start", '0'));
            $end           = (string)get_setting("v6_sec{$n}_end", '');
            $s['end']      = ($end !== '' && (int)$end > $s['start']) ? (int)$end : 0;
            $s['overlay']  = max(0, min(90, (int)$s['overlay']));
            $s['align']    = in_array($s['align'], ['left', 'center', 'right'], true) ? $s['align'] : 'center';
            $s['poster']   = ggh_safe_media_url($s['poster']);
            if ($s['poster'] === '' && $s['yt_id'] !== '') {
                // YouTube thumbnail as automatic poster
                $s['poster'] = 'https://i.ytimg.com/vi/' . $s['yt_id'] . '/hqdefault.jpg';
            }
            foreach (['btn1', 'btn2'] as $b) {
                $s[$b . '_link'] = ggh_safe_link($s[$b . '_link']);
                $s[$b . '_text'] = trim($s[$b . '_text']);
                $s[$b . '_show'] = ($s[$b . '_show'] !== '0' && $s[$b . '_text'] !== '' && $s[$b . '_link'] !== '');
            }
            if (isset($s['limit'])) {
                $s['limit'] = max(1, min(12, (int)$s['limit']));
            }
            $out[$n] = $s;
        }
        return $out;
    }
}

if (!function_exists('ggh_ensure_newsletter_table')) {
    /** Create the subscribers table on first use (called only by the subscribe endpoint and its admin page). */
    function ggh_ensure_newsletter_table($pdo) {
        $sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
        if ($sqlite) {
            $pdo->exec("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
                id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL UNIQUE,
                status TEXT DEFAULT 'subscribed', ip_address TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
        } else {
            $pdo->exec("CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
                `id` INT AUTO_INCREMENT PRIMARY KEY, `email` VARCHAR(190) NOT NULL UNIQUE,
                `status` VARCHAR(20) DEFAULT 'subscribed', `ip_address` VARCHAR(64) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
    }
}

if (!function_exists('ggh_newsletter_subscribe')) {
    /** Returns 'ok' (new or already subscribed - the caller must not reveal which) or 'invalid'. */
    function ggh_newsletter_subscribe($pdo, $email, $ip = '') {
        $email = strtolower(trim((string)$email));
        if ($email === '' || strlen($email) > 190 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'invalid';
        }
        ggh_ensure_newsletter_table($pdo);
        $sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
        $sql = $sqlite
            ? "INSERT OR IGNORE INTO newsletter_subscribers (email, ip_address) VALUES (?, ?)"
            : "INSERT IGNORE INTO newsletter_subscribers (email, ip_address) VALUES (?, ?)";
        $pdo->prepare($sql)->execute([$email, substr((string)$ip, 0, 64)]);
        return 'ok';
    }
}
