import re

path = r'C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat\index.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

target = '''} elseif ($hero_theme === 'indigo') {
    $hero_bg_class = 'from-slate-950 via-indigo-950 to-blue-950';
    $hero_badge_class = 'bg-indigo-900/80 border-indigo-500/60 text-amber-300';
    $hero_accent_text = 'text-amber-300';
}
?>


// Determine Active Homepage Layout (5 Distinct Handcrafted Layouts)
// Supports admin setting and real-time live preview via ?layout=layout_X or ?theme=X
 = ['layout_1', 'layout_2', 'layout_3', 'layout_4', 'layout_5'];
 = ['layout'] ?? ['theme'] ?? null;

if () {
    if (in_array(, ['1', '2', '3', '4', '5'])) {
         = 'layout_' . ;
    }
}

if ( && in_array(, )) {
     = ;
} else {
     = get_setting('homepage_layout', 'layout_1');
    if (!in_array(, )) {
         = 'layout_1';
    }
}

 = __DIR__ . '/includes/home_layouts/' .  . '.php';
if (file_exists()) {
    include ;
} else {
    include __DIR__ . '/includes/home_layouts/layout_1.php';
}
?>'''

replacement = '''} elseif ($hero_theme === 'indigo') {
    $hero_bg_class = 'from-slate-950 via-indigo-950 to-blue-950';
    $hero_badge_class = 'bg-indigo-900/80 border-indigo-500/60 text-amber-300';
    $hero_accent_text = 'text-amber-300';
}

// Determine Active Homepage Layout (5 Distinct Handcrafted Layouts)
// Supports admin setting and real-time live preview via ?layout=layout_X or ?theme=X
$valid_layouts = ['layout_1', 'layout_2', 'layout_3', 'layout_4', 'layout_5'];
$requested_layout = $_GET['layout'] ?? $_GET['theme'] ?? null;

if ($requested_layout) {
    if (in_array($requested_layout, ['1', '2', '3', '4', '5'])) {
        $requested_layout = 'layout_' . $requested_layout;
    }
}

if ($requested_layout && in_array($requested_layout, $valid_layouts)) {
    $active_layout = $requested_layout;
} else {
    $active_layout = get_setting('homepage_layout', 'layout_1');
    if (!in_array($active_layout, $valid_layouts)) {
        $active_layout = 'layout_1';
    }
}

$layout_file = __DIR__ . '/includes/home_layouts/' . $active_layout . '.php';
if (file_exists($layout_file)) {
    include $layout_file;
} else {
    include __DIR__ . '/includes/home_layouts/layout_1.php';
}
?>'''

# Normalize newlines for matching
content_norm = content.replace('\r\n', '\n')
target_norm = target.replace('\r\n', '\n')
replacement_norm = replacement.replace('\r\n', '\n')

if target_norm in content_norm:
    new_content = content_norm.replace(target_norm, replacement_norm)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(new_content)
    print("Fixed index.php router successfully!")
else:
    print("Could not find target block")
