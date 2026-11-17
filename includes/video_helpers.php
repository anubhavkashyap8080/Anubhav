<?php
/**
 * Video Story Home (Layout 6) Helper Utilities
 * GanvGaliHat v3 - YouTube Background Video Engine
 */

if (!defined('GGH_VIDEO_HELPERS_LOADED')) {
    define('GGH_VIDEO_HELPERS_LOADED', true);

    /**
     * Extracts and validates an 11-character YouTube video ID.
     * Rejects playlists, channels, and malicious payloads.
     *
     * @param string $input URL or bare ID
     * @return string Validated 11-character ID or empty string
     */
    function ggh_youtube_id($input): string {
        if (!is_string($input)) {
            return '';
        }
        $input = trim($input);
        if ($input === '') {
            return '';
        }

        // 1. Bare 11-char ID
        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $input)) {
            return $input;
        }

        // 2. Standard YouTube URL formats
        // Supports: watch?v=, youtu.be/, /embed/, /shorts/, /live/
        $pattern = '/(?:youtube(?:-nocookie)?\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts|live)\/|.*[?&]v=)|youtu\.be\/)([A-Za-z0-9_-]{11})/i';
        if (preg_match($pattern, $input, $matches)) {
            $id = $matches[1];
            if (preg_match('/^[A-Za-z0-9_-]{11}$/', $id)) {
                return $id;
            }
        }

        return '';
    }

    /**
     * Validates media/video URLs (YouTube or MP4/WebM)
     *
     * @param string $url
     * @param string $source 'youtube' or 'mp4'
     * @return bool
     */
    function ggh_is_valid_media_url(string $url, string $source = 'youtube'): bool {
        $url = trim($url);
        if ($url === '') {
            return true; // Optional/empty is allowed
        }

        if ($source === 'youtube') {
            return ggh_youtube_id($url) !== '';
        }

        if ($source === 'mp4') {
            // Must start with https:// or /uploads/
            if (!preg_match('/^(https:\/\/|\/uploads\/)/i', $url)) {
                return false;
            }
            // Reject protocol-relative //, backslashes, or control characters
            if (preg_match('/^(\/\/|\\\\|javascript:|data:)/i', $url)) {
                return false;
            }
            // Must end with .mp4 or .webm (ignoring query strings)
            $clean_path = parse_url($url, PHP_URL_PATH) ?? '';
            return (bool)preg_match('/\.(mp4|webm)$/i', $clean_path);
        }

        return false;
    }

    /**
     * Validates button link URL: relative starting with '/' or full 'https://'
     */
    function ggh_is_valid_btn_url(string $url): bool {
        $url = trim($url);
        if ($url === '') {
            return true;
        }
        if (preg_match('/^(\/\/|\\\\|javascript:|data:)/i', $url)) {
            return false;
        }
        return (bool)preg_match('/^(\/|https:\/\/)/i', $url);
    }

    /**
     * Returns curated default settings for Section N (1..6)
     */
    function ggh_v6_get_section_defaults(int $n): array {
        $defaults = [
            1 => [
                'enabled'   => '1',
                'source'    => 'youtube',
                'video'     => 'k4m3y1eH5Ww', // Village Forge & Traditional Blacksmithing
                'start'     => 0,
                'end'       => 0,
                'poster'    => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=1920&q=80',
                'overlay'   => 50,
                'align'     => 'left',
                'eyebrow'   => 'Generational Heritage • Direct From Village Forges',
                'title'     => 'Pure Handcrafted Craft & Farm Harvest',
                'subtitle'  => 'Every implement and food staple is crafted by master blacksmiths, weavers, and cold-press farmers across rural Bharat. High carbon steel, unpolished grains, 100% authentic.',
                'btn1_text' => 'Explore Marketplace',
                'btn1_link' => '/products.php',
                'btn1_show' => '1',
                'btn1_target' => '_self',
                'btn2_text' => 'Meet Verified Artisans',
                'btn2_link' => '/artisans.php',
                'btn2_show' => '1',
                'btn2_target' => '_self',
            ],
            2 => [
                'enabled'   => '1',
                'source'    => 'youtube',
                'video'     => 'tgbNymZ7vqY', // Rural crafts & farming
                'start'     => 0,
                'end'       => 0,
                'poster'    => 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=1920&q=80',
                'overlay'   => 55,
                'align'     => 'center',
                'eyebrow'   => 'Authentic Rural Guilds',
                'title'     => 'Explore Artisanal Categories',
                'subtitle'  => 'From heavy leaf-spring forged agricultural tools to wood-pressed oils and handloom cotton.',
                'btn1_text' => 'View All Categories',
                'btn1_link' => '/products.php',
                'btn1_show' => '1',
                'btn1_target' => '_self',
                'btn2_text' => '',
                'btn2_link' => '',
                'btn2_show' => '0',
                'btn2_target' => '_self',
                'cat_source'=> 'all',
                'cat_limit' => 6,
            ],
            3 => [
                'enabled'   => '1',
                'source'    => 'youtube',
                'video'     => '9No-FiEInLA', // Harvest & oil extraction
                'start'     => 0,
                'end'       => 0,
                'poster'    => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=1920&q=80',
                'overlay'   => 50,
                'align'     => 'left',
                'eyebrow'   => 'Most Loved By Indian Farmers',
                'title'     => 'Bestsellers Rail',
                'subtitle'  => 'Field-tested tools and farm-fresh harvest dispatched within 24 hours directly from village hubs.',
                'btn1_text' => 'Shop All Bestsellers',
                'btn1_link' => '/products.php?sort=popular',
                'btn1_show' => '1',
                'btn1_target' => '_self',
                'btn2_text' => '',
                'btn2_link' => '',
                'btn2_show' => '0',
                'btn2_target' => '_self',
                'prod_source' => 'best_sellers',
                'prod_limit'  => 8,
            ],
            4 => [
                'enabled'   => '1',
                'source'    => 'youtube',
                'video'     => 'M7lc1UVf-VE', // Craft spotlight
                'start'     => 0,
                'end'       => 0,
                'poster'    => 'https://images.unsplash.com/photo-1590736969955-71cc94801759?auto=format&fit=crop&w=1920&q=80',
                'overlay'   => 55,
                'align'     => 'left',
                'eyebrow'   => 'Curated Artisan Spotlight',
                'title'     => 'Featured Creations',
                'subtitle'  => 'Handcrafted with generational skill and modern quality control. Transparent village pricing.',
                'btn1_text' => 'Explore Full Catalog',
                'btn1_link' => '/products.php',
                'btn1_show' => '1',
                'btn1_target' => '_self',
                'btn2_text' => '',
                'btn2_link' => '',
                'btn2_show' => '0',
                'btn2_target' => '_self',
                'prod_source' => 'featured',
                'prod_limit'  => 5,
            ],
            5 => [
                'enabled'   => '1',
                'source'    => 'youtube',
                'video'     => 'sBws8MSXN7A', // Blacksmith forge fire
                'start'     => 0,
                'end'       => 0,
                'poster'    => 'https://images.unsplash.com/photo-1534447677768-be436bb09401?auto=format&fit=crop&w=1920&q=80',
                'overlay'   => 60,
                'align'     => 'center',
                'eyebrow'   => 'Makers Behind The Craft',
                'title'     => 'Generational Village Masters',
                'subtitle'  => 'Meet the blacksmiths of Hardoi, handloom weavers of Mallawan, and cold-press oil makers of Sandila.',
                'btn1_text' => 'Meet All Verified Artisans',
                'btn1_link' => '/artisans.php',
                'btn1_show' => '1',
                'btn1_target' => '_self',
                'btn2_text' => 'Join as Rural Seller',
                'btn2_link' => '/seller-register.php',
                'btn2_show' => '1',
                'btn2_target' => '_self',
                'artisan_limit' => 4,
            ],
            6 => [
                'enabled'   => '1',
                'source'    => 'youtube',
                'video'     => 'ScMzIvxBSi4', // Rural landscape celebration
                'start'     => 0,
                'end'       => 0,
                'poster'    => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1920&q=80',
                'overlay'   => 55,
                'align'     => 'center',
                'eyebrow'   => 'Atmanirbhar Bharat • Direct Livelihood',
                'title'     => 'Bring Bharat\'s Craft Home Today',
                'subtitle'  => 'Every purchase directly sustains traditional Indian rural families, guarantees fair pricing, and protects centuries of craft heritage.',
                'btn1_text' => 'Shop Village Products',
                'btn1_link' => '/products.php',
                'btn1_show' => '1',
                'btn1_target' => '_self',
                'btn2_text' => 'WhatsApp Support',
                'btn2_link' => 'https://wa.me/919935105282',
                'btn2_show' => '1',
                'btn2_target' => '_blank',
            ],
        ];

        return $defaults[$n] ?? $defaults[1];
    }

    /**
     * Resolves configuration for section N (combining saved site_settings with defaults)
     */
    function ggh_v6_get_section_config(int $n): array {
        $d = ggh_v6_get_section_defaults($n);
        $prefix = "v6_sec{$n}_";

        $res = [
            'enabled'   => get_setting($prefix . 'enabled', $d['enabled']),
            'source'    => get_setting($prefix . 'source', $d['source']),
            'video'     => get_setting($prefix . 'video', $d['video']),
            'start'     => (int)get_setting($prefix . 'start', (string)$d['start']),
            'end'       => (int)get_setting($prefix . 'end', (string)$d['end']),
            'poster'    => get_setting($prefix . 'poster', $d['poster']),
            'overlay'   => (int)get_setting($prefix . 'overlay', (string)$d['overlay']),
            'align'     => get_setting($prefix . 'align', $d['align']),
            'eyebrow'   => get_setting($prefix . 'eyebrow', $d['eyebrow']),
            'title'     => get_setting($prefix . 'title', $d['title']),
            'subtitle'  => get_setting($prefix . 'subtitle', $d['subtitle']),
            'btn1_text' => get_setting($prefix . 'btn1_text', $d['btn1_text']),
            'btn1_link' => get_setting($prefix . 'btn1_link', $d['btn1_link']),
            'btn1_show' => get_setting($prefix . 'btn1_show', $d['btn1_show']),
            'btn1_target' => get_setting($prefix . 'btn1_target', $d['btn1_target'] ?? '_self'),
            'btn2_text' => get_setting($prefix . 'btn2_text', $d['btn2_text']),
            'btn2_link' => get_setting($prefix . 'btn2_link', $d['btn2_link']),
            'btn2_show' => get_setting($prefix . 'btn2_show', $d['btn2_show']),
            'btn2_target' => get_setting($prefix . 'btn2_target', $d['btn2_target'] ?? '_self'),
        ];

        if (isset($d['cat_source'])) {
            $res['cat_source'] = get_setting($prefix . 'cat_source', $d['cat_source']);
            $res['cat_limit']  = (int)get_setting($prefix . 'cat_limit', (string)$d['cat_limit']);
        }
        if (isset($d['prod_source'])) {
            $res['prod_source'] = get_setting($prefix . 'prod_source', $d['prod_source']);
            $res['prod_limit']  = (int)get_setting($prefix . 'prod_limit', (string)$d['prod_limit']);
        }
        if (isset($d['artisan_limit'])) {
            $res['artisan_limit'] = (int)get_setting($prefix . 'artisan_limit', (string)$d['artisan_limit']);
        }

        // Validate video ID if YouTube
        if ($res['source'] === 'youtube') {
            $res['yt_id'] = ggh_youtube_id($res['video']);
        } else {
            $res['yt_id'] = '';
        }

        // Validate poster URL
        if (!preg_match('/^(https:\/\/|\/uploads\/)/i', $res['poster'])) {
            $res['poster'] = $d['poster'];
        }

        // Clamp overlay between 0 and 90
        $res['overlay'] = max(0, min(90, $res['overlay']));

        return $res;
    }
}
