import re

customizer_path = r'C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat\admin\hero_customizer.php'

with open(customizer_path, 'r', encoding='utf-8') as f:
    cust = f.read()

# 1. Update $keys
old_keys = """    $keys = [
        'hero_festive_preset', 'hero_theme_color', 'hero_badge_text',"""

new_keys = """    $keys = [
        'homepage_layout', 'homepage_youtube_url',
        'hero_festive_preset', 'hero_theme_color', 'hero_badge_text',"""

cust = cust.replace(old_keys, new_keys)

# 2. Add Section 0 right after <input type="hidden" name="hero_festive_preset" ...>
target_point = """  <input type="hidden" name="hero_festive_preset" id="hero_festive_preset" value="<?= htmlspecialchars(get_h_val('hero_festive_preset', 'general')) ?>">"""

section_0 = """  <input type="hidden" name="hero_festive_preset" id="hero_festive_preset" value="<?= htmlspecialchars(get_h_val('hero_festive_preset', 'general')) ?>">

  <!-- Section 0: Homepage Layout & Multi-Design Selector (5 Distinct Designs) -->
  <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100">
      <div>
        <h3 class="text-base font-extrabold text-gray-900 flex items-center gap-2">
          <i class="fa-solid fa-layer-group text-indigo-600"></i>
          0. Homepage Layout & Multi-Design Selector (5 Unique Experiences)
        </h3>
        <p class="text-xs text-gray-500 mt-1">Select which handcrafted design to serve on the homepage. You can preview all 5 options before saving.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="/" target="_blank" class="px-3.5 py-2 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 font-bold text-xs rounded-xl transition flex items-center gap-1.5 border border-emerald-200">
          <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> View Live Store
        </a>
      </div>
    </div>

    <?php $current_layout = get_h_val('homepage_layout', 'layout_1'); ?>

    <!-- 5 Distinct Layout Options -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      
      <!-- Option 1: Classic Rural Marketplace & 4-Box Department Grid -->
      <label class="relative flex flex-col justify-between p-4 rounded-2xl border-2 transition cursor-pointer <?= $current_layout === 'layout_1' ? 'border-indigo-600 bg-indigo-50/30 ring-2 ring-indigo-600/20' : 'border-gray-200 bg-white hover:border-gray-300' ?>">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="px-2 py-0.5 bg-gray-100 text-gray-800 font-bold text-[10px] rounded-md uppercase">Layout 1</span>
            <input type="radio" name="homepage_layout" value="layout_1" <?= $current_layout === 'layout_1' ? 'checked' : '' ?> class="text-indigo-600 focus:ring-indigo-500">
          </div>
          <h4 class="font-extrabold text-sm text-gray-900 mb-1">Classic Rural Marketplace</h4>
          <p class="text-xs text-gray-500 leading-relaxed mb-3">
            Features circular visual categories, 4-box department grid (tools, pulses, pickles, crafts), and Haversine nearest forge locator.
          </p>
        </div>
        <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
          <span class="text-[11px] font-bold text-gray-400">Department Grid</span>
          <a href="/?layout=layout_1" target="_blank" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
            <span>Preview</span> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
          </a>
        </div>
      </label>

      <!-- Option 2: Modern Animated Slider Hero with Live Flash Deals Timer -->
      <label class="relative flex flex-col justify-between p-4 rounded-2xl border-2 transition cursor-pointer <?= $current_layout === 'layout_2' ? 'border-indigo-600 bg-indigo-50/30 ring-2 ring-indigo-600/20' : 'border-gray-200 bg-white hover:border-gray-300' ?>">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="px-2 py-0.5 bg-amber-100 text-amber-800 font-bold text-[10px] rounded-md uppercase">Layout 2</span>
            <input type="radio" name="homepage_layout" value="layout_2" <?= $current_layout === 'layout_2' ? 'checked' : '' ?> class="text-indigo-600 focus:ring-indigo-500">
          </div>
          <h4 class="font-extrabold text-sm text-gray-900 mb-1">Animated Slider & Flash Deals</h4>
          <p class="text-xs text-gray-500 leading-relaxed mb-3">
            Features dynamic auto-rotating 3-slide hero slider with navigation dots, live countdown flash deals timer (HH:MM:SS), and urgent dispatch indicators.
          </p>
        </div>
        <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
          <span class="text-[11px] font-bold text-amber-600">Hero Slider + Deals</span>
          <a href="/?layout=layout_2" target="_blank" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
            <span>Preview</span> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
          </a>
        </div>
      </label>

      <!-- Option 3: Cinematic YouTube Video Hero & Rural Craft Documentary -->
      <label class="relative flex flex-col justify-between p-4 rounded-2xl border-2 transition cursor-pointer <?= $current_layout === 'layout_3' ? 'border-indigo-600 bg-indigo-50/30 ring-2 ring-indigo-600/20' : 'border-gray-200 bg-white hover:border-gray-300' ?>">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="px-2 py-0.5 bg-red-100 text-red-800 font-bold text-[10px] rounded-md uppercase">Layout 3</span>
            <input type="radio" name="homepage_layout" value="layout_3" <?= $current_layout === 'layout_3' ? 'checked' : '' ?> class="text-indigo-600 focus:ring-indigo-500">
          </div>
          <h4 class="font-extrabold text-sm text-gray-900 mb-1">Cinematic YouTube Video Hero</h4>
          <p class="text-xs text-gray-500 leading-relaxed mb-3">
            Features full-width responsive 16:9 embedded YouTube documentary hero, behind-the-scenes forge stories, and rural craft maker highlights.
          </p>
        </div>
        <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
          <span class="text-[11px] font-bold text-red-600">YouTube Video Hero</span>
          <a href="/?layout=layout_3" target="_blank" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
            <span>Preview</span> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
          </a>
        </div>
      </label>

      <!-- Option 4: Modern Bento Grid Catalog & Seasonal Harvest Fair -->
      <label class="relative flex flex-col justify-between p-4 rounded-2xl border-2 transition cursor-pointer <?= $current_layout === 'layout_4' ? 'border-indigo-600 bg-indigo-50/30 ring-2 ring-indigo-600/20' : 'border-gray-200 bg-white hover:border-gray-300' ?>">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold text-[10px] rounded-md uppercase">Layout 4</span>
            <input type="radio" name="homepage_layout" value="layout_4" <?= $current_layout === 'layout_4' ? 'checked' : '' ?> class="text-indigo-600 focus:ring-indigo-500">
          </div>
          <h4 class="font-extrabold text-sm text-gray-900 mb-1">Modern Bento Grid Catalog</h4>
          <p class="text-xs text-gray-500 leading-relaxed mb-3">
            Contemporary bento-card hero showcasing farm-fresh mustard oil, Women SHG handicrafts, live dispatch metrics, and transparent fair-value price breakdown.
          </p>
        </div>
        <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
          <span class="text-[11px] font-bold text-emerald-600">Bento Grid + FAQ</span>
          <a href="/?layout=layout_4" target="_blank" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
            <span>Preview</span> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
          </a>
        </div>
      </label>

      <!-- Option 5: Artisan Split Showcase & Regional Forge Hub with Video Spotlight -->
      <label class="relative flex flex-col justify-between p-4 rounded-2xl border-2 transition cursor-pointer <?= $current_layout === 'layout_5' ? 'border-indigo-600 bg-indigo-50/30 ring-2 ring-indigo-600/20' : 'border-gray-200 bg-white hover:border-gray-300' ?>">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="px-2 py-0.5 bg-purple-100 text-purple-800 font-bold text-[10px] rounded-md uppercase">Layout 5</span>
            <input type="radio" name="homepage_layout" value="layout_5" <?= $current_layout === 'layout_5' ? 'checked' : '' ?> class="text-indigo-600 focus:ring-indigo-500">
          </div>
          <h4 class="font-extrabold text-sm text-gray-900 mb-1">Artisan Split & Regional Hub</h4>
          <p class="text-xs text-gray-500 leading-relaxed mb-3">
            Dual-column split hero with interactive YouTube video spotlight card, regional artisan clusters directory (Sandila, Mallawan, Varanasi), and heavy implements showcase.
          </p>
        </div>
        <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
          <span class="text-[11px] font-bold text-purple-600">Split Hero + Hubs</span>
          <a href="/?layout=layout_5" target="_blank" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
            <span>Preview</span> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
          </a>
        </div>
      </label>

    </div>

    <!-- YouTube Video URL Setting -->
    <div class="pt-5 border-t border-gray-100">
      <label class="block text-xs font-bold text-gray-800 mb-1.5 flex items-center gap-2">
        <i class="fa-brands fa-youtube text-red-600 text-sm"></i>
        <span>YouTube Video URL or Video ID (Used by Layout 3 and Layout 5)</span>
      </label>
      <div class="flex flex-col sm:flex-row gap-2">
        <input type="text" name="homepage_youtube_url" id="homepage_youtube_url"
          value="<?= htmlspecialchars(get_h_val('homepage_youtube_url', 'https://www.youtube.com/watch?v=k4m3y1eH5Ww')) ?>"
          placeholder="e.g. https://www.youtube.com/watch?v=k4m3y1eH5Ww or https://youtu.be/..."
          class="flex-1 px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono">
      </div>
      <p class="text-[11px] text-gray-500 mt-1.5">
        Supports full URLs (<code class="text-stone-700">youtube.com/watch?v=...</code>), short URLs (<code class="text-stone-700">youtu.be/...</code>), embed URLs, and standalone video IDs. Fully responsive with zero overflow.
      </p>
    </div>

  </div>"""

cust = cust.replace(target_point, section_0)

with open(customizer_path, 'w', encoding='utf-8') as f:
    f.write(cust)

print('Updated admin/hero_customizer.php successfully.')

# 3. Update admin/site_settings.php $allowed_keys
site_settings_path = r'C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat\admin\site_settings.php'
with open(site_settings_path, 'r', encoding='utf-8') as f:
    ss = f.read()

old_allowed = """        $allowed_keys = [
            'brand_name', 'brand_tagline', 'company_name',"""

new_allowed = """        $allowed_keys = [
            'homepage_layout', 'homepage_youtube_url',
            'brand_name', 'brand_tagline', 'company_name',"""

if old_allowed in ss:
    ss = ss.replace(old_allowed, new_allowed)
    with open(site_settings_path, 'w', encoding='utf-8') as f:
        f.write(ss)
    print('Updated admin/site_settings.php $allowed_keys successfully.')
else:
    print('Could not find allowed_keys in site_settings.php')
