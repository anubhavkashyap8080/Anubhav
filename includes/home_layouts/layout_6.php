<?php
/**
 * GanvGaliHat - Layout 6: "Video Story Home"
 * Full-width, muted, looping YouTube / MP4 video background across sections.
 * Content (categories, products, artisans, trust, blogs, newsletter) is presented
 * cleanly on top of high-contrast video backdrops.
 * Admin controls: /admin/video_home.php
 *
 * Expects (from index.php): $pdo, $categories, $all_vendors, header.php variables.
 */

require_once __DIR__ . '/../video_helpers.php';

$v6 = ggh_v6_sections();
$v6g = ggh_v6_global();

if (!function_exists('v6e')) {
    function v6e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

/** Fetch active products for a section. */
if (!function_exists('v6_products')) {
    function v6_products($pdo, $source, $limit, $cat_slug = '', array $exclude = []) {
        $limit = max(1, min(12, (int)$limit));
        $fetch = $limit + count($exclude);
        $sql = "SELECT p.*, c.name AS category_name, c.hindi_name AS category_hindi_name, c.slug AS category_slug
                FROM products p LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.stock > 0";
        $params = [];
        $order = 'p.is_featured DESC, p.id DESC';
        if ($source === 'featured') {
            $sql .= ' AND p.is_featured = 1';
        } elseif ($source === 'best_sellers') {
            $order = 'p.is_featured DESC, p.is_best_seller DESC, p.id DESC';
        } elseif ($source === 'category' && $cat_slug !== '') {
            $sql .= ' AND c.slug = ?';
            $params[] = $cat_slug;
            $order = 'p.is_featured DESC, p.id DESC';
        }
        $sql .= " ORDER BY $order LIMIT " . (int)$fetch;
        try {
            $st = $pdo->prepare($sql);
            $st->execute($params);
            $rows = $st->fetchAll();
        } catch (Throwable $e) {
            error_log('layout_6 products: ' . $e->getMessage());
            $rows = [];
        }
        if ($source === 'featured' && !$rows) { // nothing marked featured -> newest
            return v6_products($pdo, 'newest', $limit, '', $exclude);
        }
        $out = [];
        foreach ($rows as $r) {
            if (in_array((int)$r['id'], $exclude, true)) continue;
            $out[] = $r;
            if (count($out) >= $limit) break;
        }
        if ($exclude && count($out) < min($limit, 3)) { // small catalog: allow repeats rather than an empty section
            return v6_products($pdo, $source, $limit, $cat_slug, []);
        }
        return $out;
    }
}

if (!function_exists('v6_buttons')) {
    function v6_buttons(array $s) {
        $h = '';
        if ($s['btn1_show']) {
            $h .= '<a class="v6-btn v6-btn-pri" href="' . v6e($s['btn1_link']) . '"><span>' . v6e($s['btn1_text']) . '</span><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>';
        }
        if ($s['btn2_show']) {
            $h .= '<a class="v6-btn v6-btn-sec" href="' . v6e($s['btn2_link']) . '"><span>' . v6e($s['btn2_text']) . '</span></a>';
        }
        return $h ? '<div class="v6-btns">' . $h . '</div>' : '';
    }
}

if (!function_exists('v6_open')) {
    function v6_open(array $s, $first = false) {
        $style = '--ov:' . ($s['overlay'] / 100) . ';';
        echo '<section id="v6-s' . (int)$s['n'] . '" class="v6-sec v6-a-' . v6e($s['align']) . ($s['n'] >= 7 ? ' v6-compact' : '') . '" data-name="' . v6e($s['name']) . '" '
           . 'data-src="' . ($s['yt_id'] ? 'yt' : ($s['mp4'] ? 'mp4' : '')) . '" '
           . 'data-yt="' . v6e($s['yt_id']) . '" data-mp4="' . v6e($s['mp4']) . '" '
           . 'data-start="' . (int)$s['start'] . '" data-end="' . (int)$s['end'] . '" style="' . $style . '">';
        echo '<div class="v6-media" aria-hidden="true">';
        if ($s['poster'] !== '') {
            echo '<img class="v6-poster" src="' . v6e($s['poster']) . '" alt="" '
               . ($first ? 'fetchpriority="high"' : 'loading="lazy"') . ' decoding="async">';
        }
        echo '<div class="v6-player"></div></div><div class="v6-ov" aria-hidden="true"></div>';
    }
}

if (!function_exists('v6_head')) {
    function v6_head(array $s, $h1 = false) {
        $tag = $h1 ? 'h1' : 'h2';
        echo '<header class="v6-head">';
        if ($s['eyebrow'] !== '') echo '<p class="v6-eyebrow">' . v6e($s['eyebrow']) . '</p>';
        echo "<$tag class=\"v6-title\">" . v6e($s['title']) . "</$tag>";
        if ($s['subtitle'] !== '') echo '<p class="v6-sub">' . v6e($s['subtitle']) . '</p>';
        echo '</header>';
    }
}

if (!function_exists('v6_card')) {
    function v6_card(array $p, $big = false) {
        $id = (int)$p['id'];
        $img = !empty($p['image']) ? $p['image'] : '/assets/images/placeholder.jpg';
        $price = (float)$p['price'];
        $orig = (float)($p['original_price'] ?? 0);
        $disc = (int)($p['discount'] ?? 0);
        echo '<article class="v6-pc' . ($big ? ' v6-pc-big' : '') . '">';
        echo '<a class="v6-pc-img" href="/product.php?id=' . $id . '"><img src="' . v6e($img) . '" alt="' . v6e($p['name']) . '" loading="lazy" decoding="async" width="400" height="500">';
        if ($disc > 0) echo '<span class="v6-badge">' . $disc . '% OFF</span>';
        echo '</a>';
        echo '<button type="button" class="v6-wish" aria-label="Add to wishlist" onclick="toggleWishlist(' . $id . ', this, event)"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8z"/></svg></button>';
        echo '<div class="v6-pc-body">';
        if (!empty($p['category_name'])) {
            echo '<span class="v6-pc-cat">';
            echo '<span class="lang-en">' . v6e($p['category_name']) . '</span>';
            if (!empty($p['category_hindi_name'])) {
                echo '<span class="lang-hi hidden">' . v6e($p['category_hindi_name']) . '</span>';
            }
            echo '</span>';
        }
        echo '<a class="v6-pc-name" href="/product.php?id=' . $id . '">';
        echo '<span class="lang-en">' . v6e($p['name']) . '</span>';
        if (!empty($p['hindi_name'])) {
            echo '<span class="lang-hi hidden">' . v6e($p['hindi_name']) . '</span>';
        }
        echo '</a>';
        echo '<div class="v6-pc-row"><div class="v6-price"><b>' . v6e(format_price($price)) . '</b>';
        if ($orig > $price) echo '<s>' . v6e(format_price($orig)) . '</s>';
        echo '</div><button type="button" class="v6-add" onclick="addToCart(' . $id . ', 1, event)"><span class="lang-en">Add</span><span class="lang-hi hidden">जोड़ें</span></button></div>';
        echo '</div></article>';
    }
}

// ---------- Data ----------
$v6_cat_counts = [];
try {
    foreach ($pdo->query("SELECT category_id, COUNT(*) AS c FROM products WHERE stock > 0 GROUP BY category_id")->fetchAll() as $r) {
        $v6_cat_counts[(int)$r['category_id']] = (int)$r['c'];
    }
} catch (Throwable $e) { error_log('layout_6 counts: ' . $e->getMessage()); }

$v6_used = [];
$v6_enabled = array_values(array_filter($v6, function ($s) { return $s['enabled']; }));
$v6_first_n = $v6_enabled ? $v6_enabled[0]['n'] : 0;
$gpal = ['#14532d', '#9a3412', '#1e3a8a', '#7c2d12', '#065f46', '#78350f', '#4c1d95', '#134e4a'];
?>
<noscript><style>.v6-rv{opacity:1!important;transform:none!important}</style></noscript>
<style>
/* ---------- Layout 6 : Video Story Home (Self-Contained Responsive CSS) ---------- */
body.v6-active { background: #06130d; }
<?php if ($v6g['header_transparent']): ?>
body.v6-active header.sticky { display: none !important; }
<?php endif; ?>
html.v6-snap { scroll-snap-type: y proximity; }
.v6-sec { position: relative; isolation: isolate; width: 100%; min-height: 100vh; min-height: 100svh; min-height: 100dvh; display: flex; align-items: center; color: #fff; overflow: hidden; background: #0b2a1c; }
html.v6-snap .v6-sec { scroll-snap-align: start; }
.v6-media { position: absolute; inset: 0; z-index: -2; overflow: hidden; background: linear-gradient(135deg, #0b2a1c, #14532d 55%, #7c2d12); }
.v6-poster { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1; transition: opacity .8s ease; }
.v6-player { position: absolute; inset: 0; opacity: 0; transition: opacity .8s ease; pointer-events: none; z-index: 2; overflow: hidden; }
.v6-sec.v6-playing .v6-player { opacity: 1; }
.v6-sec.v6-playing .v6-poster { opacity: 0; }
.v6-player iframe, .v6-player video { position: absolute; top: 50%; left: 50%; border: 0; pointer-events: none; width: var(--vw, 100%); height: var(--vh, 100%); transform: translate(-50%, -50%) scale(1.12); object-fit: cover; }
.v6-ov { position: absolute; inset: 0; z-index: -1; background: linear-gradient(180deg, rgba(0,0,0,.45) 0%, rgba(0,0,0,0) 28%, rgba(0,0,0,0) 58%, rgba(0,0,0,.65) 100%), rgba(5,18,11,var(--ov,.5)); }
.v6-wrap { width: 100%; max-width: 1240px; margin: 0 auto; padding: 104px 20px 72px; }
.v6-a-center .v6-head, .v6-a-center .v6-btns { text-align: center; margin-left: auto; margin-right: auto; justify-content: center; }
.v6-a-right .v6-head { margin-left: auto; text-align: right; }
.v6-a-right .v6-btns { justify-content: flex-end; }
.v6-head { max-width: 760px; margin-bottom: 34px; text-shadow: 0 2px 18px rgba(0,0,0,.45); }
.v6-eyebrow { display: inline-block; margin: 0 0 14px; padding: 6px 16px; border-radius: 999px; font-size: 12px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: #fcd34d; background: rgba(0,0,0,.4); border: 1px solid rgba(252,211,77,.35); backdrop-filter: blur(8px); }
.v6-title { margin: 0; font-family: Georgia, 'Times New Roman', serif; font-weight: 800; line-height: 1.05; letter-spacing: -.02em; font-size: clamp(2.1rem, 6.4vw, 4.6rem); }
.v6-sub { margin: 18px 0 0; font-size: clamp(1rem, 2.1vw, 1.25rem); line-height: 1.6; color: rgba(255,255,255,.9); max-width: 640px; }
.v6-a-center .v6-sub { margin-left: auto; margin-right: auto; }
.v6-a-right .v6-sub { margin-left: auto; }

/* ---------- Spacious, High-Contrast Pill Buttons (No Text Overflow) ---------- */
.v6-btns { display: flex; flex-wrap: wrap; gap: 14px; margin-top: 28px; align-items: center; }
.v6-btn {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 12px !important;
  padding: 16px 36px !important;
  min-height: 52px !important;
  border-radius: 9999px !important;
  font-weight: 800 !important;
  font-size: 15px !important;
  line-height: 1.2 !important;
  white-space: nowrap !important;
  min-width: max-content !important;
  text-decoration: none !important;
  box-sizing: border-box !important;
  transition: transform .2s ease, background .2s ease, box-shadow .2s ease !important;
}
.v6-btn span { display: inline-block !important; white-space: nowrap !important; }
.v6-btn svg, .v6-btn i { flex-shrink: 0 !important; }
.v6-btn:active { transform: scale(.97) !important; }
.v6-btn-pri {
  background: #f59e0b !important;
  color: #1c1204 !important;
  box-shadow: 0 10px 30px rgba(245,158,11,.35) !important;
}
.v6-btn-pri:hover {
  background: #fbbf24 !important;
  transform: translateY(-2px) !important;
}
.v6-btn-sec {
  background: rgba(255,255,255,.14) !important;
  color: #ffffff !important;
  border: 1.5px solid rgba(255,255,255,.45) !important;
  backdrop-filter: blur(8px) !important;
  -webkit-backdrop-filter: blur(8px) !important;
}
.v6-btn-sec:hover {
  background: rgba(255,255,255,.24) !important;
  transform: translateY(-2px) !important;
}
.v6-btn:focus-visible, .v6-pc a:focus-visible, .v6-tile:focus-visible, .v6-add:focus-visible { outline: 3px solid #fcd34d; outline-offset: 3px; }

/* Hero Chips & Scroll Indicator */
.v6-chips { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 34px; }
.v6-a-center .v6-chips { justify-content: center; }
.v6-a-right .v6-chips { justify-content: flex-end; }
.v6-chip { padding: 12px 20px; border-radius: 18px; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.25); backdrop-filter: blur(10px); min-width: 120px; }
.v6-chip b { display: block; font-size: 22px; color: #fcd34d; font-family: Georgia, serif; }
.v6-chip span { font-size: 12px; opacity: .9; }
.v6-scrollhint { position: absolute; left: 50%; bottom: 22px; transform: translateX(-50%); width: 28px; height: 46px; border: 2px solid rgba(255,255,255,.7); border-radius: 16px; opacity: .85; pointer-events: none; }
.v6-scrollhint::after { content: ""; position: absolute; top: 8px; left: 50%; width: 4px; height: 8px; margin-left: -2px; border-radius: 2px; background: #fff; animation: v6dot 1.6s infinite; }
@keyframes v6dot { 0%{opacity:0;transform:translateY(0)} 30%{opacity:1} 100%{opacity:0;transform:translateY(16px)} }

/* Header */
.v6-hdr { position: fixed; top: 0; left: 0; right: 0; z-index: 60; display: flex; align-items: center; gap: 14px; padding: 12px 20px; transition: background .3s, backdrop-filter .3s, padding .3s, box-shadow .3s; color: #fff; }
.v6-hdr.v6-solid { background: rgba(6,30,18,.92); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); padding: 8px 20px; box-shadow: 0 8px 30px rgba(0,0,0,.25); }
.v6-logo { display: flex; align-items: center; padding: 6px 12px; border-radius: 14px; background: rgba(255,255,255,.95); box-shadow: 0 4px 16px rgba(0,0,0,.2); }
.v6-logo img { height: 36px; width: auto; display: block; }
.v6-nav { display: none; gap: 6px; margin-left: 10px; }
.v6-nav a { padding: 9px 16px; border-radius: 999px; color: #fff; text-decoration: none; font-weight: 700; font-size: 14px; text-shadow: 0 1px 6px rgba(0,0,0,.4); }
.v6-nav a:hover { background: rgba(255,255,255,.16); }
.v6-search { margin-left: auto; display: none; position: relative; }
.v6-search input { width: 270px; padding: 10px 18px 10px 42px; border-radius: 999px; border: 1px solid rgba(255,255,255,.35); background: rgba(255,255,255,.14); color: #fff; font-size: 14px; outline: none; backdrop-filter: blur(8px); }
.v6-search input::placeholder { color: rgba(255,255,255,.75); }
.v6-search input:focus { background: rgba(255,255,255,.25); }
.v6-search svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); }
.v6-ico { position: relative; display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; border-radius: 50%; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.3); color: #fff; text-decoration: none; cursor: pointer; backdrop-filter: blur(8px); }
.v6-ico:hover { background: rgba(255,255,255,.28); }
.v6-badge-n { position: absolute; top: -4px; right: -4px; min-width: 18px; height: 18px; padding: 0 4px; border-radius: 9px; background: #f59e0b; color: #1c1204; font-size: 11px; font-weight: 900; display: flex; align-items: center; justify-content: center; }
.v6-right { margin-left: auto; display: flex; gap: 8px; align-items: center; }
.v6-hdr .v6-search + .v6-right { margin-left: 0; }
.v6-msearch { display: none; position: fixed; top: 64px; left: 12px; right: 12px; z-index: 61; }
.v6-msearch.open { display: block; }
.v6-msearch input { width: 100%; padding: 14px 18px; border-radius: 14px; border: 0; font-size: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.35); outline: none; }
@media(min-width:900px){ .v6-nav{display:flex} .v6-search{display:block} .v6-hdr .v6-search{margin-left:auto} .v6-ico.v6-sbtn, .v6-ico.v6-burger{display:none} .v6-logo img{height:42px} }

/* Dots */
.v6-dots { position: fixed; right: 14px; top: 50%; transform: translateY(-50%); z-index: 55; display: none; flex-direction: column; gap: 12px; }
.v6-dots a { position: relative; width: 11px; height: 11px; border-radius: 50%; background: rgba(255,255,255,.45); border: 1.5px solid rgba(255,255,255,.85); transition: all .25s; }
.v6-dots a.on { background: #f59e0b; border-color: #f59e0b; transform: scale(1.35); }
.v6-dots a span { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); white-space: nowrap; padding: 4px 10px; border-radius: 8px; background: rgba(0,0,0,.75); color: #fff; font-size: 12px; opacity: 0; pointer-events: none; transition: opacity .2s; }
.v6-dots a:hover span, .v6-dots a:focus-visible span { opacity: 1; }
@media(min-width:900px){ .v6-dots{display:flex} }

/* Category Tiles */
.v6-tiles { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
@media(min-width:700px){ .v6-tiles { grid-template-columns: repeat(4, 1fr); gap: 18px; } }
.v6-tile { position: relative; display: flex; flex-direction: column; justify-content: flex-end; min-height: 190px; aspect-ratio: 4/5; border-radius: 24px; overflow: hidden; color: #fff; text-decoration: none; background: var(--tc,#14532d); border: 1px solid rgba(255,255,255,.22); box-shadow: 0 14px 40px rgba(0,0,0,.35); transition: transform .35s, box-shadow .35s; }
.v6-tile:nth-child(3n+1) { border-radius: 24px 24px 24px 64px; }
.v6-tile:nth-child(3n+2) { border-radius: 64px 24px 24px 24px; }
.v6-tile img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .6s; }
.v6-tile::after { content: ""; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0) 35%, rgba(0,0,0,.78) 100%); }
.v6-tile:hover, .v6-tile:focus-visible { transform: translateY(-8px); }
.v6-tile:hover img { transform: scale(1.08); }
.v6-tile div { position: relative; z-index: 1; padding: 16px; }
.v6-tile b { display: block; font-size: clamp(1rem, 2.4vw, 1.3rem); font-family: Georgia, serif; line-height: 1.25; }
.v6-tile small { opacity: .85; font-size: 12px; margin-top: 4px; display: block; }
.v6-tile i { position: absolute; z-index: 1; right: 12px; top: 12px; width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,.2); backdrop-filter: blur(6px); display: flex; align-items: center; justify-content: center; font-style: normal; transition: transform .3s; }
.v6-tile:hover i { transform: rotate(-45deg); background: #f59e0b; color: #1c1204; }

/* Product Rail */
.v6-railwrap { position: relative; }
.v6-rail { display: flex; gap: 16px; overflow-x: auto; scroll-snap-type: x mandatory; padding: 6px 4px 22px; margin: 0 -20px; padding-left: 20px; padding-right: 20px; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
.v6-rail::-webkit-scrollbar { display: none; }
.v6-rail .v6-pc { flex: 0 0 min(68vw, 270px); scroll-snap-align: start; }
.v6-arrows { display: none; gap: 10px; margin-bottom: 16px; }
.v6-arrows button { width: 46px; height: 46px; border-radius: 50%; border: 1.5px solid rgba(255,255,255,.55); background: rgba(255,255,255,.12); color: #fff; cursor: pointer; backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; font-size: 16px; }
.v6-arrows button:hover { background: #f59e0b; color: #1c1204; border-color: #f59e0b; }
@media(min-width:900px){ .v6-arrows{display:flex} .v6-rail{margin:0;padding-left:4px} .v6-rail .v6-pc{flex-basis:280px} }
.v6-a-center .v6-arrows { justify-content: center; }
.v6-a-right .v6-arrows { justify-content: flex-end; }

/* Product Card */
.v6-pc { position: relative; display: flex; flex-direction: column; border-radius: 22px; overflow: hidden; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.28); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); box-shadow: 0 14px 40px rgba(0,0,0,.3); transition: transform .3s; }
@supports not (backdrop-filter:blur(1px)){ .v6-pc { background: rgba(10,40,25,.88); } }
.v6-pc:hover { transform: translateY(-6px); }
.v6-pc-img { position: relative; display: block; aspect-ratio: 4/5; overflow: hidden; background: #0b2a1c; }
.v6-pc-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s; }
.v6-pc:hover .v6-pc-img img { transform: scale(1.06); }
.v6-badge { position: absolute; left: 10px; top: 10px; padding: 4px 10px; border-radius: 999px; background: #ef4444; color: #fff; font-size: 11px; font-weight: 900; }
.v6-wish { position: absolute; right: 10px; top: 10px; width: 36px; height: 36px; border-radius: 50%; border: 0; background: rgba(255,255,255,.9); color: #6b7280; display: flex; align-items: center; justify-content: center; cursor: pointer; }
.v6-wish:hover { color: #ef4444; }
.v6-pc-body { padding: 14px; display: flex; flex-direction: column; gap: 6px; flex: 1; }
.v6-pc-cat { font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #fcd34d; }
.v6-pc-name { color: #fff; text-decoration: none; font-weight: 700; font-size: 15px; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.6em; }
.v6-pc-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: auto; padding-top: 6px; }
.v6-price b { font-size: 18px; font-family: Georgia, serif; }
.v6-price s { margin-left: 6px; font-size: 12px; opacity: .65; }
.v6-add { padding: 9px 18px; border: 0; border-radius: 999px; background: #f59e0b; color: #1c1204; font-weight: 900; font-size: 13px; cursor: pointer; transition: background .2s, transform .15s; }
.v6-add:hover { background: #fbbf24; }
.v6-add:active { transform: scale(.95); }

/* Featured Spotlight Grid */
.v6-spot { display: grid; gap: 16px; grid-template-columns: 1fr; }
@media(min-width:800px){ .v6-spot { grid-template-columns: 1.25fr 1fr 1fr; grid-template-rows: auto auto; } .v6-spot .v6-pc-big { grid-row: 1 / span 2; } }
.v6-spot .v6-pc-big .v6-pc-img { aspect-ratio: auto; flex: 1; min-height: 280px; }
.v6-spot .v6-pc-big .v6-pc-name { font-size: clamp(1.2rem, 2.4vw, 1.7rem); font-family: Georgia, serif; }
.v6-spot .v6-pc-big .v6-pc-body { padding: 20px; }
@media(min-width:800px){ .v6-spot .v6-pc:not(.v6-pc-big) .v6-pc-img { aspect-ratio: 4/3; } }

/* Artisans */
.v6-art { display: grid; gap: 56px 18px; grid-template-columns: 1fr; padding-top: 44px; }
@media(min-width:640px){ .v6-art { grid-template-columns: repeat(2, 1fr); } }
@media(min-width:1000px){ .v6-art { grid-template-columns: repeat(4, 1fr); } }
.v6-ac { position: relative; padding: 58px 20px 22px; border-radius: 26px; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.28); backdrop-filter: blur(14px); text-align: center; box-shadow: 0 14px 40px rgba(0,0,0,.3); }
.v6-ac:nth-child(even) { transform: translateY(22px); }
@media(max-width:999px){ .v6-ac:nth-child(even) { transform: none; } }
.v6-ac img, .v6-ac .v6-av { position: absolute; top: -42px; left: 50%; transform: translateX(-50%); width: 84px; height: 84px; border-radius: 50%; object-fit: cover; border: 4px solid #f59e0b; box-shadow: 0 8px 24px rgba(0,0,0,.4); background: #14532d; }
.v6-ac .v6-av { display: flex; align-items: center; justify-content: center; font-size: 30px; font-weight: 900; font-family: Georgia, serif; }
.v6-ac h3 { margin: 0; font-family: Georgia, serif; font-size: 19px; }
.v6-ac .v6-loc { margin: 6px 0 10px; font-size: 12px; color: #fcd34d; font-weight: 800; letter-spacing: .05em; }
.v6-ac p { margin: 0; font-size: 13.5px; line-height: 1.55; opacity: .92; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; }

/* Trust */
.v6-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 8px 0 26px; }
.v6-stat { padding: 18px 8px; border-radius: 20px; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.25); backdrop-filter: blur(10px); text-align: center; }
.v6-stat b { display: block; font-family: Georgia, serif; font-size: clamp(1.5rem, 5vw, 2.6rem); color: #fcd34d; }
.v6-stat span { font-size: 12px; opacity: .9; }
.v6-trust { display: grid; gap: 12px; grid-template-columns: 1fr; margin-bottom: 8px; }
@media(min-width:760px){ .v6-trust { grid-template-columns: repeat(3, 1fr); } }
.v6-tr { padding: 18px; border-radius: 20px; background: rgba(0,0,0,.28); border: 1px solid rgba(255,255,255,.18); text-align: left; }
.v6-tr b { display: block; margin-bottom: 4px; font-size: 16px; }
.v6-tr span { font-size: 13.5px; opacity: .88; line-height: 1.5; }

/* Reveal Animation */
.v6-rv { opacity: 0; transform: translateY(26px); transition: opacity .8s ease, transform .8s ease; }
.v6-rv.v6-in { opacity: 1; transform: none; }

/* Strip Under Categories */
.v6-strip { margin-top: 34px; }
.v6-strip-t { margin: 0 0 12px; font-family: Georgia, serif; font-size: clamp(1.2rem, 3vw, 1.7rem); text-shadow: 0 2px 12px rgba(0,0,0,.45); }
.v6-a-center .v6-strip-t { text-align: center; }
.v6-strip .v6-rail { margin-bottom: 0; }

/* Compact Bottom Sections */
.v6-sec.v6-compact { min-height: auto; }
.v6-compact .v6-wrap { padding-top: 72px; padding-bottom: 64px; }

/* Blog */
.v6-blog { display: grid; gap: 16px; grid-template-columns: 1fr; }
@media(min-width:700px){ .v6-blog { grid-template-columns: repeat(2, 1fr); } }
@media(min-width:1000px){ .v6-blog { grid-template-columns: repeat(3, 1fr); } }
.v6-bc { display: flex; flex-direction: column; border-radius: 22px; overflow: hidden; color: #fff; text-decoration: none; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.28); backdrop-filter: blur(14px); box-shadow: 0 14px 40px rgba(0,0,0,.3); transition: transform .3s; }
@supports not (backdrop-filter:blur(1px)){ .v6-bc { background: rgba(10,40,25,.88); } }
.v6-bc:hover, .v6-bc:focus-visible { transform: translateY(-6px); }
.v6-bc:focus-visible { outline: 3px solid #fcd34d; outline-offset: 3px; }
.v6-bc-img { display: block; aspect-ratio: 16/10; background: linear-gradient(135deg, #14532d, #7c2d12); overflow: hidden; }
.v6-bc-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s; }
.v6-bc:hover .v6-bc-img img { transform: scale(1.06); }
.v6-bc-body { display: flex; flex-direction: column; gap: 8px; padding: 18px; }
.v6-bc-body b { font-family: Georgia, serif; font-size: 18px; line-height: 1.25; }
.v6-bc-ex { font-size: 13.5px; line-height: 1.55; opacity: .9; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
.v6-bc-more { font-size: 13px; font-weight: 800; color: #fcd34d; }
.v6-soon { padding: 22px; border-radius: 20px; background: rgba(0,0,0,.3); border: 1px solid rgba(255,255,255,.2); max-width: 560px; }

/* Newsletter */
.v6-nl { display: flex; flex-direction: column; gap: 12px; max-width: 560px; margin: 0 auto; }
.v6-a-left .v6-nl { margin-left: 0; }
.v6-a-right .v6-nl { margin-right: 0; }
@media(min-width:600px){ .v6-nl { flex-direction: row; } }
.v6-nl input[type=email] { flex: 1; min-width: 0; padding: 15px 22px; border-radius: 9999px; border: 1.5px solid rgba(255,255,255,.45); background: rgba(255,255,255,.14); color: #fff; font-size: 15px; outline: none; backdrop-filter: blur(8px); }
.v6-nl input[type=email]::placeholder { color: rgba(255,255,255,.7); }
.v6-nl input[type=email]:focus { background: rgba(255,255,255,.26); border-color: #fcd34d; }
.v6-nl button { justify-content: center; border: 0; cursor: pointer; font-family: inherit; }
.v6-nl button[disabled] { opacity: .6; cursor: wait; }
.v6-hp { position: absolute !important; left: -9999px; width: 1px; height: 1px; opacity: 0; }
.v6-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
.v6-nlmsg { min-height: 1.4em; margin: 12px 0 0; font-weight: 700; text-align: center; }
.v6-nlmsg.err { color: #fecaca; }
.v6-nlmsg.ok { color: #bbf7d0; }
.v6-nlnote { margin: 6px 0 0; font-size: 12px; opacity: .75; text-align: center; }
@media(prefers-reduced-motion:reduce){ .v6-rv{opacity:1;transform:none;transition:none} .v6-scrollhint::after{animation:none} .v6-tile, .v6-pc, .v6-btn{transition:none} html.v6-snap{scroll-snap-type:none} }
</style>

<?php
// ---------- Header (transparent overlay variant) ----------
$v6_logo = $active_site_logo ?? '/logo.png';
$v6_cart = function_exists('get_cart_count') ? (int)get_cart_count() : 0;
$v6_wish = function_exists('get_wishlist_count') ? (int)get_wishlist_count() : 0;
$v6_logged = function_exists('is_logged_in') && is_logged_in();
if ($v6g['header_transparent']):
?>
<div class="v6-hdr" id="v6Hdr">
  <a class="v6-logo" href="/" aria-label="<?= v6e(get_company_name()) ?> home">
    <img src="<?= v6e($v6_logo) ?>" alt="<?= v6e(get_company_name()) ?>" onerror="this.onerror=null;this.src='/logo.png'">
  </a>
  <nav class="v6-nav" aria-label="Main">
    <a href="/products.php"><span class="lang-en">Shop</span><span class="lang-hi hidden">दुकान</span></a>
    <a href="/artisans.php"><span class="lang-en">Artisans</span><span class="lang-hi hidden">कारीगर</span></a>
    <a href="/seller-register.php"><span class="lang-en">Sell with us</span><span class="lang-hi hidden">विक्रेता बनें</span></a>
    <a href="/contact.php"><span class="lang-en">Contact</span><span class="lang-hi hidden">संपर्क</span></a>
  </nav>
  <form class="v6-search" action="/products.php" method="GET" role="search">
    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
    <input type="search" name="q" placeholder="Search tools, pulses, pickles..." aria-label="Search products" maxlength="100" autocomplete="off">
  </form>
  <div class="v6-right">
    <button type="button" class="v6-ico v6-sbtn" id="v6SearchBtn" aria-label="Search"><svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg></button>
    <a class="v6-ico" href="/wishlist.php" aria-label="Wishlist"><svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8z"/></svg><span class="v6-badge-n" id="v6Wish" <?= $v6_wish ? '' : 'hidden' ?>><?= $v6_wish ?></span></a>
    <a class="v6-ico" href="/cart.php" aria-label="Cart"><svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2 3h3l2.7 12.4a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L21 7H6"/></svg><span class="v6-badge-n" id="v6Cart" <?= $v6_cart ? '' : 'hidden' ?>><?= $v6_cart ?></span></a>
    <a class="v6-ico" href="<?= $v6_logged ? '/account.php' : '/login.php?tab=shopper' ?>" aria-label="<?= $v6_logged ? 'My account' : 'Log in' ?>"><svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg></a>
    <button type="button" class="v6-ico v6-burger" id="v6Burger" aria-label="Open menu"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
  </div>
</div>
<form class="v6-msearch" id="v6MSearch" action="/products.php" method="GET" role="search"><input type="search" name="q" placeholder="Search products..." aria-label="Search products" maxlength="100"></form>
<?php endif; ?>

<?php if ($v6g['dots'] && count($v6_enabled) > 1): ?>
<nav class="v6-dots" id="v6Dots" aria-label="Page sections">
  <?php foreach ($v6_enabled as $s): ?><a href="#v6-s<?= (int)$s['n'] ?>" aria-label="<?= v6e($s['name']) ?>"><span><?= v6e($s['name']) ?></span></a><?php endforeach; ?>
</nav>
<?php endif; ?>

<?php
foreach ($v6 as $n => $s):
    if (!$s['enabled']) continue;
    v6_open($s, $n === $v6_first_n);
?>
  <div class="v6-wrap">
<?php if ($n === 1): // ---------------- 1. HERO SECTION ---------------- ?>
    <div class="v6-rv">
      <?php v6_head($s, true); ?>
      <?= v6_buttons($s) ?>
      <div class="v6-chips">
        <?php for ($i = 1; $i <= 3; $i++): 
          $s_num = get_setting("hero_stat{$i}_num", ['100%', '40+', '₹499+'][$i - 1]);
          $s_lbl = get_setting("hero_stat{$i}_label", ['Forged Iron & Pure Dal', 'Village Artisan Hubs', 'Free India Delivery'][$i - 1]);
          $s_lbl_hi = ['लोहा शिल्प व शुद्ध दालें', 'ग्रामीण कारीगर केंद्र', 'पूरे भारत में मुफ़्त डिलीवरी'][$i - 1];
        ?>
          <div class="v6-chip">
            <b><?= v6e($s_num) ?></b>
            <span>
              <span class="lang-en"><?= v6e($s_lbl) ?></span>
              <span class="lang-hi hidden"><?= v6e($s_lbl_hi) ?></span>
            </span>
          </div>
        <?php endfor; ?>
      </div>
    </div>
    <span class="v6-scrollhint" aria-hidden="true"></span>

<?php elseif ($n === 2): // ---------------- 2. CATEGORIES SECTION ---------------- ?>
    <?php
      $cats = $categories ?? [];
      if ($s['cat_source'] === 'selected' && trim($s['cat_ids']) !== '') {
          $want = array_filter(array_map('intval', explode(',', $s['cat_ids'])));
          $cats = array_values(array_filter($cats, function ($c) use ($want) { return in_array((int)$c['id'], $want, true); }));
      }
      $cats = array_slice($cats, 0, (int)$s['limit']);
    ?>
    <div class="v6-rv"><?php v6_head($s); ?></div>
    <div class="v6-tiles v6-rv">
      <?php foreach ($cats as $i => $c): $cnt = $v6_cat_counts[(int)$c['id']] ?? 0; ?>
        <a class="v6-tile" style="--tc:<?= $gpal[$i % count($gpal)] ?>" href="/products.php?category=<?= v6e(urlencode($c['slug'])) ?>">
          <?php if (!empty($c['image'])): ?><img src="<?= v6e($c['image']) ?>" alt="" loading="lazy" decoding="async"><?php endif; ?>
          <i aria-hidden="true">&rarr;</i>
          <div>
            <b>
              <span class="lang-en"><?= v6e($c['name']) ?></span>
              <?php if (!empty($c['hindi_name'])): ?><span class="lang-hi hidden"><?= v6e($c['hindi_name']) ?></span><?php endif; ?>
            </b>
            <small>
              <span class="lang-en"><?= $cnt ?> product<?= $cnt === 1 ? '' : 's' ?></span>
              <span class="lang-hi hidden"><?= $cnt ?> उत्पाद</span>
            </small>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <?php
      $strip = [];
      if ($s['show_products'] !== '0') {
          $strip = v6_products($pdo, 'newest', max(1, min(12, (int)$s['prod_limit'])), '', []);
          foreach ($strip as $it) $v6_used[] = (int)$it['id'];
      }
    ?>
    <?php if ($strip): ?>
      <div class="v6-strip v6-rv">
        <h3 class="v6-strip-t">
          <span class="lang-en">New arrivals</span>
          <span class="lang-hi hidden">नए आगमन</span>
        </h3>
        <div class="v6-rail"><?php foreach ($strip as $p) v6_card($p); ?></div>
      </div>
    <?php endif; ?>
    <div class="v6-rv"><?= v6_buttons($s) ?></div>

<?php elseif ($n === 3): // ---------------- 3. BEST SELLERS RAIL ---------------- ?>
    <?php $items = v6_products($pdo, $s['prod_source'], $s['limit'], $s['prod_cat'], $v6_used); foreach ($items as $it) $v6_used[] = (int)$it['id']; ?>
    <div class="v6-rv"><?php v6_head($s); ?></div>
    <div class="v6-arrows v6-rv" data-rail="r<?= $n ?>"><button type="button" data-d="-1" aria-label="Previous">&larr;</button><button type="button" data-d="1" aria-label="Next">&rarr;</button></div>
    <div class="v6-rail v6-rv" id="r<?= $n ?>">
      <?php foreach ($items as $p) v6_card($p); ?>
    </div>
    <div class="v6-rv"><?= v6_buttons($s) ?></div>

<?php elseif ($n === 4): // ---------------- 4. FEATURED SPOTLIGHT ---------------- ?>
    <?php $items = v6_products($pdo, $s['prod_source'], $s['limit'], $s['prod_cat'], $v6_used); ?>
    <div class="v6-rv"><?php v6_head($s); ?></div>
    <div class="v6-spot v6-rv">
      <?php foreach ($items as $i => $p) v6_card($p, $i === 0); ?>
    </div>
    <div class="v6-rv"><?= v6_buttons($s) ?></div>

<?php elseif ($n === 5): // ---------------- 5. ARTISANS ---------------- ?>
    <?php $arts = array_slice($all_vendors ?? [], 0, (int)$s['limit']); ?>
    <div class="v6-rv"><?php v6_head($s); ?></div>
    <div class="v6-art v6-rv">
      <?php foreach ($arts as $v):
          $vn = (string)($v['name'] ?? ''); $loc = trim(($v['village'] ?? '') . ((!empty($v['village']) && !empty($v['district'])) ? ', ' : '') . ($v['district'] ?? '')); ?>
        <article class="v6-ac">
          <?php if (!empty($v['avatar'])): ?><img src="<?= v6e($v['avatar']) ?>" alt="<?= v6e($vn) ?>" loading="lazy" width="84" height="84"><?php else: ?><span class="v6-av" aria-hidden="true"><?= v6e(function_exists('mb_substr') ? mb_strtoupper(mb_substr($vn, 0, 1)) : strtoupper(substr($vn, 0, 1))) ?></span><?php endif; ?>
          <h3><?= v6e($vn) ?></h3>
          <?php if ($loc !== ''): ?><div class="v6-loc"><?= v6e($loc) ?></div><?php endif; ?>
          <?php if (!empty($v['story'])): ?><p><?= v6e($v['story']) ?></p><?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="v6-rv"><?= v6_buttons($s) ?></div>

<?php elseif ($n === 6): // ---------------- 6. TRUST + CTA ---------------- ?>
    <?php
      $pc = 0;
      try { $pc = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE stock > 0")->fetchColumn(); } catch (Throwable $e) {}
    ?>
    <div class="v6-rv"><?php v6_head($s); ?></div>
    <div class="v6-stats v6-rv">
      <div class="v6-stat"><b data-count="<?= $pc ?>"><?= $pc ?></b><span><span class="lang-en">Products in stock</span><span class="lang-hi hidden">उपलब्ध उत्पाद</span></span></div>
      <div class="v6-stat"><b data-count="<?= count($all_vendors ?? []) ?>"><?= count($all_vendors ?? []) ?></b><span><span class="lang-en">Verified artisans</span><span class="lang-hi hidden">सत्यापित कारीगर</span></span></div>
      <div class="v6-stat"><b data-count="<?= count($categories ?? []) ?>"><?= count($categories ?? []) ?></b><span><span class="lang-en">Categories</span><span class="lang-hi hidden">श्रेणियां</span></span></div>
    </div>
    <div class="v6-trust v6-rv">
      <div class="v6-tr">
        <b><span class="lang-en">Direct from the maker</span><span class="lang-hi hidden">सीधे निर्माता से</span></b>
        <span><span class="lang-en">Every product ships from the artisan or producer. No middlemen.</span><span class="lang-hi hidden">प्रत्येक उत्पाद सीधे कारीगर या निर्माता से भेजा जाता है। कोई बिचौलिया नहीं।</span></span>
      </div>
      <div class="v6-tr">
        <b><span class="lang-en">Free delivery <?= v6e(get_setting('hero_stat3_num', '₹499+')) ?></span><span class="lang-hi hidden">मुफ़्त डिलीवरी <?= v6e(get_setting('hero_stat3_num', '₹499+')) ?></span></b>
        <span><span class="lang-en">Pan-India delivery with order tracking.</span><span class="lang-hi hidden">ट्रैकिंग के साथ पूरे भारत में डिलीवरी।</span></span>
      </div>
      <div class="v6-tr">
        <b><span class="lang-en">Secure payments</span><span class="lang-hi hidden">सुरक्षित भुगतान</span></b>
        <span><span class="lang-en">UPI, cards, net banking or Cash on Delivery.</span><span class="lang-hi hidden">UPI, कार्ड, नेट बैंकिंग या कैश ऑन डिलीवरी।</span></span>
      </div>
    </div>
    <div class="v6-rv"><?= v6_buttons($s) ?></div>

<?php elseif ($n === 7): // ---------------- 7. BLOG ---------------- ?>
    <?php
      $posts = [];
      try {
          $st = $pdo->prepare("SELECT id, title, slug, excerpt, category, image, created_at FROM blogs WHERE status = 'published' ORDER BY id DESC LIMIT " . max(1, min(12, (int)$s['limit'])));
          $st->execute();
          $posts = $st->fetchAll();
      } catch (Throwable $e) { error_log('layout_6 blog: ' . $e->getMessage()); }
    ?>
    <div class="v6-rv"><?php v6_head($s); ?></div>
    <?php if ($posts): ?>
      <div class="v6-blog v6-rv">
        <?php foreach ($posts as $b):
            $ex = trim(strip_tags((string)($b['excerpt'] ?? '')));
            if ($ex === '') $ex = trim(strip_tags((string)($b['title'] ?? '')));
            $ex = function_exists('mb_substr') ? mb_substr($ex, 0, 130) : substr($ex, 0, 130);
            $ts = !empty($b['created_at']) ? strtotime($b['created_at']) : false; ?>
          <a class="v6-bc" href="/blog-detail.php?slug=<?= v6e(urlencode($b['slug'])) ?>">
            <span class="v6-bc-img"><?php if (!empty($b['image'])): ?><img src="<?= v6e($b['image']) ?>" alt="" loading="lazy" decoding="async" width="400" height="260"><?php endif; ?></span>
            <span class="v6-bc-body">
              <span class="v6-pc-cat"><?= v6e($b['category'] ?? 'Journal') ?><?= $ts ? ' &middot; ' . v6e(date('d M Y', $ts)) : '' ?></span>
              <b><?= v6e($b['title']) ?></b>
              <span class="v6-bc-ex"><?= v6e($ex) ?></span>
              <span class="v6-bc-more"><span class="lang-en">Read more &rarr;</span><span class="lang-hi hidden">और पढ़ें &rarr;</span></span>
            </span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="v6-soon v6-rv">
        <span class="lang-en">New articles are on the way. Please check back soon.</span>
        <span class="lang-hi hidden">नए लेख जल्द ही आ रहे हैं। कृपया कुछ समय बाद देखें।</span>
      </div>
    <?php endif; ?>
    <div class="v6-rv"><?= v6_buttons($s) ?></div>

<?php elseif ($n === 8): // ---------------- 8. NEWSLETTER ---------------- ?>
    <div class="v6-rv"><?php v6_head($s); ?></div>
    <div class="v6-rv">
      <form class="v6-nl" id="v6NlForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?= v6e(csrf_token()) ?>">
        <input type="text" name="website" class="v6-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
        <label class="v6-sr" for="v6NlEmail">Email address</label>
        <input id="v6NlEmail" type="email" name="email" required maxlength="190" placeholder="you@example.com" autocomplete="email" inputmode="email">
        <button type="submit" class="v6-btn v6-btn-pri">
          <span><?= v6e($s['btn1_text'] !== '' ? $s['btn1_text'] : 'Subscribe') ?></span>
        </button>
      </form>
      <p class="v6-nlmsg" id="v6NlMsg" role="status" aria-live="polite"></p>
      <p class="v6-nlnote">
        <span class="lang-en">No spam. We never share your email.</span>
        <span class="lang-hi hidden">कोई स्पैम नहीं। हम आपका ईमेल किसी से साझा नहीं करते।</span>
      </p>
    </div>
<?php endif; ?>
  </div>
</section>
<?php endforeach; ?>

<script>
(function () {
  'use strict';
  var d = document, root = d.documentElement, body = d.body;
  body.classList.add('v6-active');
  <?php if ($v6g['snap']): ?>root.classList.add('v6-snap');<?php endif; ?>

  /* Strict Bilingual Language Sync */
  try {
    var savedLang = localStorage.getItem('ggh_site_lang');
    if (savedLang === 'hi') {
      d.querySelectorAll('.lang-en').forEach(function(el) { el.classList.add('hidden'); });
      d.querySelectorAll('.lang-hi').forEach(function(el) { el.classList.remove('hidden'); });
    } else {
      d.querySelectorAll('.lang-hi').forEach(function(el) { el.classList.add('hidden'); });
      d.querySelectorAll('.lang-en').forEach(function(el) { el.classList.remove('hidden'); });
    }
  } catch(e) {}

  var secs = [].slice.call(d.querySelectorAll('.v6-sec'));
  if (!secs.length) return;

  /* ---- header: solid on scroll, mobile menu + search ---- */
  var hdr = d.getElementById('v6Hdr');
  function onScroll() { if (hdr) hdr.classList.toggle('v6-solid', (window.scrollY || root.scrollTop) > 80); }
  onScroll(); window.addEventListener('scroll', onScroll, { passive: true });

  var burger = d.getElementById('v6Burger');
  if (burger) burger.addEventListener('click', function () { var b = d.getElementById('mobile-menu-open'); if (b) b.click(); });
  var sb = d.getElementById('v6SearchBtn'), ms = d.getElementById('v6MSearch');
  if (sb && ms) sb.addEventListener('click', function () { ms.classList.toggle('open'); if (ms.classList.contains('open')) ms.querySelector('input').focus(); });

  /* mirror cart / wishlist counters updated by main.js on the (hidden) default header */
  function mirror(srcId, dstId) {
    var src = d.getElementById(srcId), dst = d.getElementById(dstId);
    if (!src || !dst) return;
    var sync = function () { var t = (src.textContent || '').trim(); dst.textContent = t; dst.hidden = !t || t === '0'; };
    new MutationObserver(sync).observe(src, { childList: true, characterData: true, subtree: true, attributes: true });
  }
  mirror('header-cart-count', 'v6Cart'); mirror('header-wishlist-count', 'v6Wish');

  /* ---- product rail arrows ---- */
  [].forEach.call(d.querySelectorAll('.v6-arrows'), function (a) {
    var rail = d.getElementById(a.getAttribute('data-rail'));
    a.addEventListener('click', function (e) {
      var b = e.target.closest('button'); if (!b || !rail) return;
      rail.scrollBy({ left: (+b.getAttribute('data-d')) * Math.max(280, rail.clientWidth * 0.8), behavior: 'smooth' });
    });
  });

  /* ---- newsletter form ---- */
  var nl = d.getElementById('v6NlForm'), nlMsg = d.getElementById('v6NlMsg');
  if (nl) nl.addEventListener('submit', function (e) {
    e.preventDefault();
    var em = nl.elements.email.value.trim(), btn = nl.querySelector('button');
    nlMsg.className = 'v6-nlmsg';
    if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(em)) { nlMsg.textContent = 'Please enter a valid email address.'; nlMsg.classList.add('err'); return; }
    btn.disabled = true;
    fetch('/ajax/newsletter_subscribe.php', { method: 'POST', body: new FormData(nl), credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': nl.elements.csrf_token.value } })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        nlMsg.textContent = j.message || (j.success ? 'Thank you for subscribing!' : 'Something went wrong. Please try again.');
        nlMsg.classList.add(j.success ? 'ok' : 'err');
        if (j.success) nl.reset();
      })
      .catch(function () { nlMsg.textContent = 'Network error. Please try again.'; nlMsg.classList.add('err'); })
      .then(function () { btn.disabled = false; });
  });

  /* ---- reveal + dots + count-up ---- */
  var dots = [].slice.call(d.querySelectorAll('#v6Dots a'));
  var active = 0;
  function countUp(sec) {
    [].forEach.call(sec.querySelectorAll('[data-count]'), function (el) {
      var to = +el.getAttribute('data-count') || 0, t0 = null;
      if (matchMedia('(prefers-reduced-motion: reduce)').matches || to < 2) return;
      (function step(ts) { t0 = t0 || ts; var p = Math.min(1, (ts - t0) / 1200); el.textContent = Math.round(to * p); if (p < 1) requestAnimationFrame(step); })(performance.now());
    });
  }

  /* ---- video engine (YouTube + MP4, Muted Loop Background, No Sound Toggle) ---- */
  var reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  var conn = navigator.connection || {};
  var posterOnly = reduce || conn.saveData === true || /(^|-)2g$/.test(conn.effectiveType || '');
  var live = new Map();               // section -> {kind, p, ready, wantPlay}
  var apiState = 0, apiWait = [];

  function size(sec) {
    var box = sec.querySelector('.v6-player'); if (!box) return;
    var w = sec.clientWidth, h = sec.clientHeight;
    var vw = Math.max(w, h * 16 / 9), vh = Math.max(h, w * 9 / 16);
    box.style.setProperty('--vw', vw + 'px'); box.style.setProperty('--vh', vh + 'px');
  }
  if ('ResizeObserver' in window) { var ro = new ResizeObserver(function (es) { es.forEach(function (e) { size(e.target); }); }); secs.forEach(function (s) { ro.observe(s); }); }
  secs.forEach(size); window.addEventListener('resize', function () { secs.forEach(size); });

  function loadApi(cb) {
    if (apiState === 2) return cb();
    if (apiState < 0) return;
    apiWait.push(cb);
    if (apiState === 1) return;
    apiState = 1;
    var to = setTimeout(function () { if (apiState !== 2) apiState = -1; }, 7000);
    window.onYouTubeIframeAPIReady = function () { clearTimeout(to); apiState = 2; apiWait.splice(0).forEach(function (f) { f(); }); };
    var s = d.createElement('script'); s.src = 'https://www.youtube.com/iframe_api'; s.async = true;
    s.onerror = function () { apiState = -1; }; d.head.appendChild(s);
  }

  function mkYT(sec) {
    var id = sec.getAttribute('data-yt'), st = +sec.getAttribute('data-start') || 0, en = +sec.getAttribute('data-end') || 0;
    if (!id) return;
    var holder = d.createElement('div'); sec.querySelector('.v6-player').appendChild(holder);
    var rec = { kind: 'yt', p: null, ready: false };
    var vars = { autoplay: 1, mute: 1, controls: 0, loop: 1, playlist: id, playsinline: 1, modestbranding: 1, rel: 0, disablekb: 1, iv_load_policy: 3, fs: 0, cc_load_policy: 0, start: st, origin: location.origin };
    if (en > st) vars.end = en;
    try {
      rec.p = new YT.Player(holder, {
        host: 'https://www.youtube-nocookie.com', videoId: id, playerVars: vars,
        events: {
          onReady: function (e) {
            rec.ready = true; e.target.mute();
            try { e.target.setPlaybackQuality('hd720'); } catch (x) {}
            var f = e.target.getIframe(); f.setAttribute('tabindex', '-1'); f.setAttribute('aria-hidden', 'true'); f.setAttribute('title', 'Background video');
            f.setAttribute('allow', 'autoplay; encrypted-media; picture-in-picture');
            if (secs.indexOf(sec) === active) e.target.playVideo(); else e.target.pauseVideo();
          },
          onStateChange: function (e) {
            if (e.data === 1) sec.classList.add('v6-playing');
            if (e.data === 0) { e.target.seekTo(st, true); e.target.playVideo(); }
          },
          onError: function () { destroy(sec); }
        }
      });
      live.set(sec, rec);
    } catch(err) {
      // Fallback direct iframe if YT API fails
      var iframe = d.createElement('iframe');
      iframe.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(id) + '?autoplay=1&mute=1&controls=0&loop=1&playlist=' + encodeURIComponent(id) + '&playsinline=1&rel=0';
      iframe.setAttribute('allow', 'autoplay; encrypted-media; picture-in-picture');
      iframe.setAttribute('tabindex', '-1');
      iframe.setAttribute('aria-hidden', 'true');
      holder.appendChild(iframe);
      sec.classList.add('v6-playing');
    }
  }

  function mkMP4(sec) {
    var v = d.createElement('video'); v.muted = true; v.loop = true; v.autoplay = true; v.playsInline = true; v.setAttribute('playsinline', ''); v.preload = 'auto'; v.setAttribute('tabindex', '-1');
    v.addEventListener('playing', function () { sec.classList.add('v6-playing'); });
    v.src = sec.getAttribute('data-mp4'); sec.querySelector('.v6-player').appendChild(v);
    var p = v.play();
    if (p && p.catch) p.catch(function () {});
    live.set(sec, { kind: 'mp4', p: v, ready: true });
  }

  function ensure(sec) {
    if (posterOnly || live.has(sec)) return;
    var src = sec.getAttribute('data-src');
    if (src === 'mp4') { mkMP4(sec); return; }
    if (src === 'yt') loadApi(function () { if (!live.has(sec)) mkYT(sec); });
  }
  function play(sec) {
    var r = live.get(sec); if (!r || !r.ready) return;
    try { if (r.kind === 'yt') r.p.playVideo(); else { var pr = r.p.play(); if (pr && pr.catch) pr.catch(function () {}); } } catch (x) {}
  }
  function pause(sec) {
    var r = live.get(sec); if (!r || !r.ready) return;
    try { if (r.kind === 'yt') r.p.pauseVideo(); else r.p.pause(); } catch (x) {}
  }
  function destroy(sec) {
    var r = live.get(sec); if (!r) return;
    try { if (r.kind === 'yt') r.p.destroy(); else { r.p.removeAttribute('src'); r.p.load(); } } catch (x) {}
    var box = sec.querySelector('.v6-player'); if (box) box.innerHTML = '';
    sec.classList.remove('v6-playing'); live.delete(sec);
  }
  function focusSection(i) {
    active = i;
    secs.forEach(function (s, j) {
      if (j === i) { ensure(s); play(s); }
      else if (Math.abs(j - i) > 1) destroy(s);
      else pause(s);
    });
    if (secs[i + 1]) setTimeout(function () { if (active === i) ensure(secs[i + 1]); }, 2500);
  }

  /* Intersection Observer for active section */
  var io = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      if (!e.isIntersecting) return;
      var i = secs.indexOf(e.target);
      if (dots.length) dots.forEach(function (a, j) { a.classList.toggle('on', j === i); });
      focusSection(i);
    });
  }, { rootMargin: '-50% 0px -50% 0px', threshold: 0 });
  secs.forEach(function (s) { io.observe(s); });

  /* Reveal every block on its own as soon as 8% of it is visible */
  var rv = [].slice.call(d.querySelectorAll('.v6-rv'));
  if ('IntersectionObserver' in window) {
    var rio = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (!e.isIntersecting) return;
        e.target.classList.add('v6-in'); rio.unobserve(e.target);
        if (e.target.querySelector('[data-count]')) countUp(e.target);
      });
    }, { threshold: 0.08 });
    rv.forEach(function (el) { rio.observe(el); });
  } else { rv.forEach(function (el) { el.classList.add('v6-in'); }); }

  d.addEventListener('visibilitychange', function () {
    if (d.hidden) live.forEach(function (r, s) { pause(s); }); else play(secs[active]);
  });

  /* Load video engine on idle */
  function boot() { if (!posterOnly) { ensure(secs[0]); } }
  if (d.readyState === 'complete') (window.requestIdleCallback || setTimeout)(boot, 50);
  else window.addEventListener('load', function () { (window.requestIdleCallback || setTimeout)(boot, 50); });
})();
</script>
