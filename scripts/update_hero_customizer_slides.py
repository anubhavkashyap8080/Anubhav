# -*- coding: utf-8 -*-
with open(r'C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat\admin\hero_customizer.php', 'r', encoding='utf-8') as f:
    code = f.read()

# 1. Add slide keys to $keys
old_keys = """    $keys = [
        'homepage_layout', 'homepage_youtube_url',"""

new_keys = """    $keys = [
        'homepage_layout', 'homepage_youtube_url',
        'slider_slide1_badge', 'slider_slide1_title', 'slider_slide1_subtitle', 'slider_slide1_btn1_text', 'slider_slide1_btn1_link', 'slider_slide1_btn2_text', 'slider_slide1_btn2_link', 'slider_slide1_image',
        'slider_slide2_badge', 'slider_slide2_title', 'slider_slide2_subtitle', 'slider_slide2_btn1_text', 'slider_slide2_btn1_link', 'slider_slide2_btn2_text', 'slider_slide2_btn2_link', 'slider_slide2_image',
        'slider_slide3_badge', 'slider_slide3_title', 'slider_slide3_subtitle', 'slider_slide3_btn1_text', 'slider_slide3_btn1_link', 'slider_slide3_btn2_text', 'slider_slide3_btn2_link', 'slider_slide3_image',"""

code = code.replace(old_keys, new_keys)

# 2. Add slide image upload handlers
upload_target = """        if ($upload_res['success']) {
            $image_url = $upload_res['path'];
        }
    }"""

upload_replacement = """        if ($upload_res['success']) {
            $image_url = $upload_res['path'];
        }
    }

    // Handle Slide 1-3 Image Uploads
    for ($s = 1; $s <= 3; $s++) {
        $f_key = "slider_slide{$s}_file";
        if (isset($_FILES[$f_key]) && $_FILES[$f_key]['error'] === UPLOAD_ERR_OK) {
            $up = secure_upload_image($f_key, 'uploads/banners/');
            if ($up['success']) {
                $_POST["slider_slide{$s}_image"] = $up['path'];
            }
        }
    }"""

code = code.replace(upload_target, upload_replacement)

# 3. Add Section 0B UI after Section 0
sec0_end = """      <p class="text-[11px] text-gray-500 mt-1.5">
        Supports full URLs (<code class="text-stone-700">youtube.com/watch?v=...</code>), short URLs (<code class="text-stone-700">youtu.be/...</code>), embed URLs, and standalone video IDs. Fully responsive with zero overflow.
      </p>
    </div>

  </div>"""

sec0b_ui = """      <p class="text-[11px] text-gray-500 mt-1.5">
        Supports full URLs (<code class="text-stone-700">youtube.com/watch?v=...</code>), short URLs (<code class="text-stone-700">youtu.be/...</code>), embed URLs, and standalone video IDs. The video plays in the background automatically, muted, without YouTube controls and with a clean dark overlay.
      </p>
    </div>

  </div>

  <!-- Section 0B: Multi-Slide Carousel Customizer (3 Slides for Layout 2) -->
  <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <div class="pb-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h3 class="text-base font-extrabold text-gray-900 flex items-center gap-2">
          <i class="fa-solid fa-sliders text-amber-600"></i>
          0B. Multi-Slide Hero Carousel (3 Slides for Layout 2)
        </h3>
        <p class="text-xs text-gray-500 mt-1">
          Customize all 3 slides that auto-rotate on Homepage Layout 2. Each slide has its own badge, title, subtitle, 2 CTA buttons, and background banner image.
        </p>
      </div>
      <a href="/?layout=layout_2" target="_blank" class="px-3.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs rounded-xl border border-amber-200 flex items-center gap-1.5 transition">
        <span>Preview Slider Live</span> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
      </a>
    </div>

    <!-- 3 Slides Accordion / Cards Grid -->
    <div class="space-y-6">
      
      <!-- Slide 1 Customizer -->
      <div class="p-5 bg-stone-50 rounded-2xl border border-gray-200 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-gray-200">
          <span class="px-3 py-1 bg-amber-600 text-white font-black text-xs rounded-lg uppercase tracking-wider">Slide 1 (Default Hero)</span>
          <span class="text-xs text-gray-500 font-medium">Primary Featured Product or Guild</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Badge Text (Top Pill)</label>
            <input type="text" name="slider_slide1_badge" 
              value="<?= htmlspecialchars(get_h_val('slider_slide1_badge', 'Village Forge Heritage • Kalyani agrofood pvt ltd')) ?>"
              class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Slide Heading (Title)</label>
            <input type="text" name="slider_slide1_title" 
              value="<?= htmlspecialchars(get_h_val('slider_slide1_title', 'Hand-Forged Tempered Steel Agricultural Tools')) ?>"
              class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold">
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Slide Subtitle / Description</label>
          <textarea name="slider_slide1_subtitle" rows="2"
            class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs leading-relaxed"><?= htmlspecialchars(get_h_val('slider_slide1_subtitle', 'Crafted from high-grade railway spring steel by generational masters of Hardoi. Razor-sharp sickles, heavy axes, and durable khurpis made for lifelong farming.')) ?></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 1 Text</label>
            <input type="text" name="slider_slide1_btn1_text" 
              value="<?= htmlspecialchars(get_h_val('slider_slide1_btn1_text', 'Shop Forged Tools')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 1 Link URL</label>
            <input type="text" name="slider_slide1_btn1_link" 
              value="<?= htmlspecialchars(get_h_val('slider_slide1_btn1_link', '/products.php?category=traditional-tools')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 2 Text</label>
            <input type="text" name="slider_slide1_btn2_text" 
              value="<?= htmlspecialchars(get_h_val('slider_slide1_btn2_text', 'Meet Blacksmiths')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 2 Link URL</label>
            <input type="text" name="slider_slide1_btn2_link" 
              value="<?= htmlspecialchars(get_h_val('slider_slide1_btn2_link', '/artisans.php')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Background Image URL</label>
            <input type="text" name="slider_slide1_image" 
              value="<?= htmlspecialchars(get_h_val('slider_slide1_image', 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=1600&q=80')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Upload New Slide 1 Image</label>
            <input type="file" name="slider_slide1_file" accept="image/*"
              class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200">
          </div>
        </div>
      </div>

      <!-- Slide 2 Customizer -->
      <div class="p-5 bg-stone-50 rounded-2xl border border-gray-200 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-gray-200">
          <span class="px-3 py-1 bg-emerald-700 text-white font-black text-xs rounded-lg uppercase tracking-wider">Slide 2</span>
          <span class="text-xs text-gray-500 font-medium">Cold-Pressed Oils, Pulses & Pickles</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Badge Text (Top Pill)</label>
            <input type="text" name="slider_slide2_badge" 
              value="<?= htmlspecialchars(get_h_val('slider_slide2_badge', '100% Pure Cold-Pressed Organic Staples')) ?>"
              class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Slide Heading (Title)</label>
            <input type="text" name="slider_slide2_title" 
              value="<?= htmlspecialchars(get_h_val('slider_slide2_title', 'Raw Kachi Ghani Mustard Oil & Desi Pickles')) ?>"
              class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold">
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Slide Subtitle / Description</label>
          <textarea name="slider_slide2_subtitle" rows="2"
            class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs leading-relaxed"><?= htmlspecialchars(get_h_val('slider_slide2_subtitle', 'Extracted through traditional wooden expellers without chemical refining. Paired with grandma-recipe sun-cured Awadhi mango, amla, and stuffed red chili achar.')) ?></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 1 Text</label>
            <input type="text" name="slider_slide2_btn1_text" 
              value="<?= htmlspecialchars(get_h_val('slider_slide2_btn1_text', 'Explore Natural Staples')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 1 Link URL</label>
            <input type="text" name="slider_slide2_btn1_link" 
              value="<?= htmlspecialchars(get_h_val('slider_slide2_btn1_link', '/products.php?category=pulses-and-oils')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 2 Text</label>
            <input type="text" name="slider_slide2_btn2_text" 
              value="<?= htmlspecialchars(get_h_val('slider_slide2_btn2_text', 'View All Foods')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 2 Link URL</label>
            <input type="text" name="slider_slide2_btn2_link" 
              value="<?= htmlspecialchars(get_h_val('slider_slide2_btn2_link', '/products.php')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Background Image URL</label>
            <input type="text" name="slider_slide2_image" 
              value="<?= htmlspecialchars(get_h_val('slider_slide2_image', 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=1600&q=80')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Upload New Slide 2 Image</label>
            <input type="file" name="slider_slide2_file" accept="image/*"
              class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-100 file:text-emerald-800 hover:file:bg-emerald-200">
          </div>
        </div>
      </div>

      <!-- Slide 3 Customizer -->
      <div class="p-5 bg-stone-50 rounded-2xl border border-gray-200 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-gray-200">
          <span class="px-3 py-1 bg-purple-700 text-white font-black text-xs rounded-lg uppercase tracking-wider">Slide 3</span>
          <span class="text-xs text-gray-500 font-medium">Handlooms, Clay Pottery & Women SHGs</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Badge Text (Top Pill)</label>
            <input type="text" name="slider_slide3_badge" 
              value="<?= htmlspecialchars(get_h_val('slider_slide3_badge', 'Women Self-Help Groups • Fair Trade Direct')) ?>"
              class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Slide Heading (Title)</label>
            <input type="text" name="slider_slide3_title" 
              value="<?= htmlspecialchars(get_h_val('slider_slide3_title', 'Handloom Textiles, Clay Pottery & Jute Crafts')) ?>"
              class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold">
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Slide Subtitle / Description</label>
          <textarea name="slider_slide3_subtitle" rows="2"
            class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs leading-relaxed"><?= htmlspecialchars(get_h_val('slider_slide3_subtitle', 'Support rural craftswomen across Uttar Pradesh. 96% of sales price directly credits to artisan bank accounts via India Post payments bank.')) ?></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 1 Text</label>
            <input type="text" name="slider_slide3_btn1_text" 
              value="<?= htmlspecialchars(get_h_val('slider_slide3_btn1_text', 'Support Craftswomen')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 1 Link URL</label>
            <input type="text" name="slider_slide3_btn1_link" 
              value="<?= htmlspecialchars(get_h_val('slider_slide3_btn1_link', '/products.php?category=handicrafts')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 2 Text</label>
            <input type="text" name="slider_slide3_btn2_text" 
              value="<?= htmlspecialchars(get_h_val('slider_slide3_btn2_text', 'Join as Seller (5-Step)')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 2 Link URL</label>
            <input type="text" name="slider_slide3_btn2_link" 
              value="<?= htmlspecialchars(get_h_val('slider_slide3_btn2_link', '/seller-register.php')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Background Image URL</label>
            <input type="text" name="slider_slide3_image" 
              value="<?= htmlspecialchars(get_h_val('slider_slide3_image', 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=1600&q=80')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Upload New Slide 3 Image</label>
            <input type="file" name="slider_slide3_file" accept="image/*"
              class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-100 file:text-purple-800 hover:file:bg-purple-200">
          </div>
        </div>
      </div>

    </div>
  </div>"""

code = code.replace(sec0_end, sec0b_ui)

with open(r'C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat\admin\hero_customizer.php', 'w', encoding='utf-8') as f:
    f.write(code)

print("Updated admin/hero_customizer.php with 3-slide customizer!")
