<?php
/**
 * GanvGaliHat Super Admin Central — Policy & Dynamic CMS Pages Manager
 * Full Bilingual Editing (English & Hindi) with Direct Visual View + Raw HTML Source Mode
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strict Admin Authorization Check
require_admin();

// Central CSRF Protection for all Admin POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();
}

$page_title = 'Policy & Dynamic CMS Pages';
$pages_map = get_all_cms_pages();

// Handle Save Page (Add or Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_page') {
    $raw_slug = trim(strtolower($_POST['slug'] ?? ''));
    $slug = preg_replace('/[^a-z0-9_-]+/', '-', $raw_slug);
    $slug = trim($slug, '-');

    $title = clean_input($_POST['title'] ?? '');
    $hindi_title = clean_input($_POST['hindi_title'] ?? '');
    $meta_desc = clean_input($_POST['meta_description'] ?? '');
    $content_en = trim($_POST['content_en'] ?? '');
    $content_hi = trim($_POST['content_hi'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_system = isset($_POST['is_system']) && $_POST['is_system'] === '1' ? 1 : 0;

    if (empty($slug)) {
        set_flash('error', 'Page URL slug is required. (e.g. buyer-protection)');
    } elseif (empty($title)) {
        set_flash('error', 'Page title (English) is required.');
    } else {
        $save_data = [
            'slug' => $slug,
            'title' => $title,
            'hindi_title' => $hindi_title,
            'meta_description' => $meta_desc,
            'content_en' => $content_en,
            'content_hi' => $content_hi,
            'is_active' => $is_active,
            'is_system' => $is_system
        ];

        save_cms_page($slug, $save_data);
        if (function_exists('log_audit_action')) {
            log_audit_action('page_save', 'pages', 'page', 0, "Saved CMS page: {$slug} ({$title})");
        }
        set_flash('success', "Page '{$title}' ({$slug}) has been saved successfully!");
        header('Location: /admin/pages.php');
        exit;
    }
}

// Handle Delete Custom Page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_page') {
    $del_slug = trim(strtolower($_POST['slug'] ?? ''));
    if (!empty($del_slug)) {
        if (delete_cms_page($del_slug)) {
            if (function_exists('log_audit_action')) {
                log_audit_action('page_delete', 'pages', 'page', 0, "Deleted CMS page: {$del_slug}");
            }
            set_flash('success', "Page '{$del_slug}' deleted successfully.");
        } else {
            set_flash('error', "Core system policy pages cannot be deleted.");
        }
    }
    header('Location: /admin/pages.php');
    exit;
}

// Handle Toggle Active Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $tog_slug = trim(strtolower($_POST['slug'] ?? ''));
    if (!empty($tog_slug) && isset($pages_map[$tog_slug])) {
        $page = $pages_map[$tog_slug];
        $page['is_active'] = empty($page['is_active']) ? 1 : 0;
        save_cms_page($tog_slug, $page);
        set_flash('success', "Status updated for '{$page['title']}'.");
    }
    header('Location: /admin/pages.php');
    exit;
}

// Helper to determine public URL
function get_page_public_url($slug) {
    $system_routes = [
        'about-us' => '/about.php',
        'privacy-policy' => '/privacy-policy.php',
        'terms-conditions' => '/terms-conditions.php',
        'shipping-policy' => '/shipping-policy.php',
        'terms-of-supply' => '/terms-of-supply.php',
        'support' => '/contact.php'
    ];
    return $system_routes[$slug] ?? ('/page.php?slug=' . urlencode($slug));
}

require_once __DIR__ . '/admin_header.php';
?>

<div class="space-y-6">
  
  <!-- Header Title & Add Page Action -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-200 shadow-xs">
    <div>
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-bold uppercase tracking-wider mb-2">
        <i class="fa-solid fa-file-lines text-indigo-600"></i> Central Content Management
      </div>
      <h1 class="text-xl sm:text-2xl font-black text-gray-900">Policy & Dynamic CMS Pages</h1>
      <p class="text-xs text-gray-500 mt-1 max-w-2xl">
        Manage all core legal policies, About Us, Customer Support, and publish unlimited custom pages with dual English & Hindi direct visual view and raw HTML source editing.
      </p>
    </div>

    <div class="flex items-center gap-3">
      <button type="button" onclick="openPageModal()"
              class="px-4 py-2.5 bg-indigo-900 hover:bg-indigo-950 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-2 cursor-pointer">
        <i class="fa-solid fa-plus text-amber-300"></i>
        <span>Add New CMS Page</span>
      </button>
    </div>
  </div>

  <!-- Pages Table List -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
    <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between">
      <h2 class="font-bold text-sm text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-layer-group text-emerald-600"></i>
        <span>All Active Policy & Content Pages</span>
      </h2>
      <span class="text-xs text-gray-400 font-semibold"><?= count($pages_map) ?> Total Pages</span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-xs">
        <thead>
          <tr class="bg-stone-50 border-b border-gray-200 text-gray-500 font-bold uppercase text-[11px]">
            <th class="py-3.5 px-4 sm:px-6">Page Title & Hindi Name</th>
            <th class="py-3.5 px-4">URL Slug / Live Link</th>
            <th class="py-3.5 px-4">Page Type</th>
            <th class="py-3.5 px-4">Status</th>
            <th class="py-3.5 px-4 text-right sm:pr-6">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 font-medium text-gray-700">
          <?php if (empty($pages_map)): ?>
            <tr>
              <td colspan="5" class="py-12 text-center text-gray-400">
                <i class="fa-solid fa-file-circle-question text-3xl mb-2 block"></i>
                No pages found. Click "+ Add New CMS Page" to get started.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($pages_map as $p): 
              $live_url = get_page_public_url($p['slug']);
              $is_system = !empty($p['is_system']);
            ?>
              <tr class="hover:bg-stone-50/80 transition">
                <td class="py-4 px-4 sm:px-6">
                  <div class="font-bold text-sm text-gray-900"><?= htmlspecialchars($p['title']) ?></div>
                  <?php if (!empty($p['hindi_title'])): ?>
                    <div class="text-[11px] text-emerald-800 font-semibold mt-0.5"><?= htmlspecialchars($p['hindi_title']) ?></div>
                  <?php endif; ?>
                </td>

                <td class="py-4 px-4">
                  <a href="<?= htmlspecialchars($live_url) ?>" target="_blank"
                     class="inline-flex items-center gap-1.5 font-mono text-[11px] text-indigo-700 hover:text-indigo-900 hover:underline bg-indigo-50/60 px-2.5 py-1 rounded-lg border border-indigo-100">
                    <span><?= htmlspecialchars($live_url) ?></span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                  </a>
                </td>

                <td class="py-4 px-4">
                  <?php if ($is_system): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 font-bold text-[10px] border border-emerald-200">
                      <i class="fa-solid fa-shield-halved text-[9px]"></i> Core Policy
                    </span>
                  <?php else: ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 font-bold text-[10px] border border-purple-200">
                      <i class="fa-solid fa-file-lines text-[9px]"></i> Custom Page
                    </span>
                  <?php endif; ?>
                </td>

                <td class="py-4 px-4">
                  <form action="/admin/pages.php" method="POST" class="inline">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="slug" value="<?= htmlspecialchars($p['slug']) ?>">
                    <button type="submit" class="cursor-pointer" title="Click to toggle status">
                      <?php if (!empty($p['is_active'])): ?>
                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px] inline-flex items-center gap-1">
                          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                        </span>
                      <?php else: ?>
                        <span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 font-bold text-[10px] inline-flex items-center gap-1">
                          <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Draft
                        </span>
                      <?php endif; ?>
                    </button>
                  </form>
                </td>

                <td class="py-4 px-4 text-right sm:pr-6 whitespace-nowrap">
                  <div class="flex items-center justify-end gap-2">
                    <button type="button" onclick="editPage(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8') ?>)"
                            class="px-3 py-1.5 bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1 cursor-pointer">
                      <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                      <span>Edit</span>
                    </button>

                    <a href="<?= htmlspecialchars($live_url) ?>" target="_blank"
                       class="px-3 py-1.5 bg-stone-100 hover:bg-stone-200 text-gray-700 font-bold text-xs rounded-xl transition flex items-center gap-1">
                      <i class="fa-solid fa-eye text-[11px]"></i>
                      <span class="hidden sm:inline">View</span>
                    </a>

                    <?php if (!$is_system): ?>
                      <form action="/admin/pages.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to permanently delete this custom page?');">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="action" value="delete_page">
                        <input type="hidden" name="slug" value="<?= htmlspecialchars($p['slug']) ?>">
                        <button type="submit" class="p-1.5 text-stone-400 hover:text-red-600 transition cursor-pointer" title="Delete Page">
                          <i class="fa-solid fa-trash-can"></i>
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- ==================== PAGE EDITOR MODAL (Direct View + HTML Source) ==================== -->
<div id="pageEditorModal" class="fixed inset-0 z-50 bg-stone-950/70 backdrop-blur-xs hidden overflow-y-auto p-3 sm:p-6 flex items-start justify-center">
  <div class="bg-white rounded-3xl border border-gray-200 shadow-2xl w-full max-w-5xl my-6 overflow-hidden">
    
    <!-- Modal Header -->
    <div class="p-5 sm:p-6 bg-indigo-950 text-white flex items-center justify-between border-b border-indigo-900">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-amber-400 text-stone-950 flex items-center justify-center text-lg font-black">
          <i class="fa-solid fa-file-pen"></i>
        </div>
        <div>
          <h3 id="modalTitle" class="text-base sm:text-lg font-black">Edit CMS Page</h3>
          <p class="text-xs text-indigo-300">Direct Visual View + HTML Source Code Dual Mode</p>
        </div>
      </div>
      
      <button type="button" onclick="closePageModal()" class="w-9 h-9 rounded-xl bg-indigo-900 hover:bg-indigo-800 text-indigo-200 hover:text-white flex items-center justify-center transition cursor-pointer">
        <i class="fa-solid fa-xmark text-lg"></i>
      </button>
    </div>

    <!-- Modal Form -->
    <form id="cmsPageForm" action="/admin/pages.php" method="POST" onsubmit="prepareFormSubmission(event)" class="p-6 space-y-6">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="save_page">
      <input type="hidden" id="form_is_system" name="is_system" value="0">
      
      <!-- Hidden Content Inputs that receive the final clean HTML -->
      <input type="hidden" id="final_content_en" name="content_en" value="">
      <input type="hidden" id="final_content_hi" name="content_hi" value="">

      <!-- Basic Page Meta -->
      <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
        
        <div class="sm:col-span-6">
          <label class="block text-xs font-bold text-gray-700 mb-1">
            Page Title (English) <span class="text-red-500">*</span>
          </label>
          <input type="text" id="form_title" name="title" required
                 placeholder="e.g. Buyer Protection & Quality Guarantee"
                 class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-indigo-600 transition"
                 onkeyup="autoGenerateSlug(this.value)">
        </div>

        <div class="sm:col-span-6">
          <label class="block text-xs font-bold text-gray-700 mb-1">
            Page Title (Hindi / हिन्दी)
          </label>
          <input type="text" id="form_hindi_title" name="hindi_title"
                 placeholder="उदा. ग्राहक सुरक्षा एवं गुणवत्ता गारंटी"
                 class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>

        <div class="sm:col-span-6">
          <label class="block text-xs font-bold text-gray-700 mb-1">
            URL Slug <span class="text-red-500">*</span>
            <span class="text-gray-400 font-normal text-[10px]">(lowercase, e.g. buyer-protection)</span>
          </label>
          <div class="flex items-center">
            <span class="px-3 py-2.5 bg-stone-100 border border-r-0 border-gray-200 rounded-l-xl text-xs text-gray-500 font-mono">/page.php?slug=</span>
            <input type="text" id="form_slug" name="slug" required
                   placeholder="buyer-protection"
                   class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-r-xl text-xs font-mono font-bold focus:bg-white focus:outline-none focus:border-indigo-600 transition">
          </div>
        </div>

        <div class="sm:col-span-4">
          <label class="block text-xs font-bold text-gray-700 mb-1">
            Publication Status
          </label>
          <select id="form_is_active" name="is_active"
                  class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold focus:bg-white focus:outline-none focus:border-indigo-600 transition">
            <option value="1">Published / Active Live</option>
            <option value="0">Draft / Hidden</option>
          </select>
        </div>

        <div class="sm:col-span-12">
          <label class="block text-xs font-bold text-gray-700 mb-1">
            Meta Description (SEO & Social Sharing)
          </label>
          <input type="text" id="form_meta_description" name="meta_description"
                 placeholder="Brief 1-2 sentence description for search engines..."
                 class="w-full px-3.5 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>

      </div>

      <!-- Bilingual Tabs Navigation -->
      <div class="border-t border-gray-200 pt-5">
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-2">
            <button type="button" onclick="switchEditorTab('en')" id="tab_btn_en"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-indigo-900 text-white shadow-xs cursor-pointer">
              <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
              <span>English Content</span>
            </button>
            <button type="button" onclick="switchEditorTab('hi')" id="tab_btn_hi"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-stone-100 text-gray-600 hover:text-gray-900 hover:bg-stone-200 cursor-pointer">
              <span>Hindi Content (हिन्दी)</span>
            </button>
          </div>

          <!-- Direct View vs HTML Source Code Mode Switcher -->
          <div class="flex items-center gap-2">
            <button type="button" onclick="toggleSourceMode()" id="btn_mode_toggle"
                    class="px-3 py-1.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-stone-100 transition flex items-center gap-1.5 cursor-pointer">
              <i class="fa-solid fa-code text-indigo-600"></i>
              <span id="mode_label">Edit HTML Source</span>
            </button>
          </div>
        </div>

        <!-- Formatting Toolbar for Visual Direct View -->
        <div id="visual_toolbar" class="flex flex-wrap items-center gap-1.5 p-2 bg-stone-100 rounded-xl border border-gray-200 mb-3 text-xs">
          <button type="button" onclick="formatDoc('bold')" class="p-1.5 px-2.5 rounded-lg bg-white hover:bg-stone-200 font-bold shadow-xs cursor-pointer" title="Bold"><b>B</b></button>
          <button type="button" onclick="formatDoc('italic')" class="p-1.5 px-2.5 rounded-lg bg-white hover:bg-stone-200 italic shadow-xs cursor-pointer" title="Italic"><i>I</i></button>
          <button type="button" onclick="formatDoc('underline')" class="p-1.5 px-2.5 rounded-lg bg-white hover:bg-stone-200 underline shadow-xs cursor-pointer" title="Underline"><u>U</u></button>
          <span class="text-gray-300">|</span>
          <button type="button" onclick="formatDoc('formatBlock', '<h2>')" class="p-1.5 px-2.5 rounded-lg bg-white hover:bg-stone-200 font-bold shadow-xs cursor-pointer" title="Heading 2">H2</button>
          <button type="button" onclick="formatDoc('formatBlock', '<h3>')" class="p-1.5 px-2.5 rounded-lg bg-white hover:bg-stone-200 font-bold shadow-xs cursor-pointer" title="Heading 3">H3</button>
          <button type="button" onclick="formatDoc('formatBlock', '<p>')" class="p-1.5 px-2.5 rounded-lg bg-white hover:bg-stone-200 shadow-xs cursor-pointer" title="Paragraph">P</button>
          <span class="text-gray-300">|</span>
          <button type="button" onclick="formatDoc('insertUnorderedList')" class="p-1.5 px-2.5 rounded-lg bg-white hover:bg-stone-200 shadow-xs cursor-pointer" title="Bullet List"><i class="fa-solid fa-list-ul"></i></button>
          <button type="button" onclick="formatDoc('insertOrderedList')" class="p-1.5 px-2.5 rounded-lg bg-white hover:bg-stone-200 shadow-xs cursor-pointer" title="Numbered List"><i class="fa-solid fa-list-ol"></i></button>
          <button type="button" onclick="insertLinkPrompt()" class="p-1.5 px-2.5 rounded-lg bg-white hover:bg-stone-200 shadow-xs cursor-pointer" title="Insert Link"><i class="fa-solid fa-link"></i></button>
          <button type="button" onclick="formatDoc('removeFormat')" class="p-1.5 px-2.5 rounded-lg bg-white hover:bg-stone-200 text-stone-500 shadow-xs cursor-pointer" title="Clear Formatting"><i class="fa-solid fa-eraser"></i></button>
        </div>

        <!-- ================= ENGLISH TAB ================= -->
        <div id="tab_pane_en">
          <!-- Direct Visual View Container -->
          <div id="visual_container_en" class="editor-pane">
            <div id="visual_editor_en" contenteditable="true"
                 class="min-h-[350px] p-5 bg-white border border-gray-300 rounded-2xl focus:outline-none focus:border-indigo-600 prose max-w-none text-xs sm:text-sm"
                 oninput="syncVisualToSource('en')"></div>
          </div>

          <!-- Raw HTML Source Code Textarea -->
          <div id="source_container_en" class="editor-pane hidden">
            <textarea id="source_editor_en" rows="16"
                      class="w-full p-4 bg-stone-900 text-emerald-400 font-mono text-xs rounded-2xl border border-stone-800 focus:outline-none focus:border-amber-400 leading-relaxed"
                      placeholder="Type or paste raw HTML markup here..."
                      oninput="syncSourceToVisual('en')"></textarea>
          </div>
        </div>

        <!-- ================= HINDI TAB ================= -->
        <div id="tab_pane_hi" class="hidden">
          <!-- Direct Visual View Container -->
          <div id="visual_container_hi" class="editor-pane">
            <div id="visual_editor_hi" contenteditable="true"
                 class="min-h-[350px] p-5 bg-white border border-gray-300 rounded-2xl focus:outline-none focus:border-indigo-600 prose max-w-none text-xs sm:text-sm"
                 oninput="syncVisualToSource('hi')"></div>
          </div>

          <!-- Raw HTML Source Code Textarea -->
          <div id="source_container_hi" class="editor-pane hidden">
            <textarea id="source_editor_hi" rows="16"
                      class="w-full p-4 bg-stone-900 text-emerald-400 font-mono text-xs rounded-2xl border border-stone-800 focus:outline-none focus:border-amber-400 leading-relaxed"
                      placeholder="यहाँ हिंदी सामग्री का HTML कोड लिखें या पेस्ट करें..."
                      oninput="syncSourceToVisual('hi')"></textarea>
          </div>
        </div>

      </div>

      <!-- Action Buttons -->
      <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
        <button type="button" onclick="closePageModal()"
                class="px-5 py-2.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-stone-100 transition cursor-pointer">
          Cancel
        </button>
        <button type="submit"
                class="px-6 py-2.5 bg-emerald-800 hover:bg-emerald-900 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2 cursor-pointer">
          <i class="fa-solid fa-floppy-disk"></i>
          <span>Save CMS Page</span>
        </button>
      </div>

    </form>

  </div>
</div>

<script>
let currentTab = 'en';
let isSourceMode = false;

function openPageModal() {
  document.getElementById('modalTitle').textContent = 'Add New CMS Page';
  document.getElementById('form_title').value = '';
  document.getElementById('form_hindi_title').value = '';
  document.getElementById('form_slug').value = '';
  document.getElementById('form_slug').readOnly = false;
  document.getElementById('form_is_system').value = '0';
  document.getElementById('form_is_active').value = '1';
  document.getElementById('form_meta_description').value = '';

  document.getElementById('visual_editor_en').innerHTML = '<p>Write your English page content here...</p>';
  document.getElementById('source_editor_en').value = '<p>Write your English page content here...</p>';
  document.getElementById('visual_editor_hi').innerHTML = '<p>यहाँ अपनी हिन्दी सामग्री लिखें...</p>';
  document.getElementById('source_editor_hi').value = '<p>यहाँ अपनी हिन्दी सामग्री लिखें...</p>';

  switchEditorTab('en');
  setSourceMode(false);
  document.getElementById('pageEditorModal').classList.remove('hidden');
}

function editPage(page) {
  document.getElementById('modalTitle').textContent = 'Edit Page: ' + (page.title || page.slug);
  document.getElementById('form_title').value = page.title || '';
  document.getElementById('form_hindi_title').value = page.hindi_title || '';
  document.getElementById('form_slug').value = page.slug || '';
  document.getElementById('form_slug').readOnly = (page.is_system == 1);
  document.getElementById('form_is_system').value = page.is_system || '0';
  document.getElementById('form_is_active').value = page.is_active ? '1' : '0';
  document.getElementById('form_meta_description').value = page.meta_description || '';

  const c_en = page.content_en || '';
  const c_hi = page.content_hi || '';

  document.getElementById('visual_editor_en').innerHTML = c_en;
  document.getElementById('source_editor_en').value = c_en;
  document.getElementById('visual_editor_hi').innerHTML = c_hi;
  document.getElementById('source_editor_hi').value = c_hi;

  switchEditorTab('en');
  setSourceMode(false);
  document.getElementById('pageEditorModal').classList.remove('hidden');
}

function closePageModal() {
  document.getElementById('pageEditorModal').classList.add('hidden');
}

function switchEditorTab(lang) {
  currentTab = lang;
  if (lang === 'en') {
    document.getElementById('tab_pane_en').classList.remove('hidden');
    document.getElementById('tab_pane_hi').classList.add('hidden');
    document.getElementById('tab_btn_en').className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-indigo-900 text-white shadow-xs cursor-pointer';
    document.getElementById('tab_btn_hi').className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-stone-100 text-gray-600 hover:text-gray-900 hover:bg-stone-200 cursor-pointer';
  } else {
    document.getElementById('tab_pane_hi').classList.remove('hidden');
    document.getElementById('tab_pane_en').classList.add('hidden');
    document.getElementById('tab_btn_hi').className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-indigo-900 text-white shadow-xs cursor-pointer';
    document.getElementById('tab_btn_en').className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-stone-100 text-gray-600 hover:text-gray-900 hover:bg-stone-200 cursor-pointer';
  }
}

function toggleSourceMode() {
  setSourceMode(!isSourceMode);
}

function setSourceMode(showSource) {
  isSourceMode = showSource;
  const toolbar = document.getElementById('visual_toolbar');
  const label = document.getElementById('mode_label');

  if (isSourceMode) {
    syncVisualToSource('en');
    syncVisualToSource('hi');
    document.getElementById('visual_container_en').classList.add('hidden');
    document.getElementById('source_container_en').classList.remove('hidden');
    document.getElementById('visual_container_hi').classList.add('hidden');
    document.getElementById('source_container_hi').classList.remove('hidden');
    toolbar.classList.add('opacity-40', 'pointer-events-none');
    label.textContent = 'Direct View / Visual Edit';
  } else {
    syncSourceToVisual('en');
    syncSourceToVisual('hi');
    document.getElementById('source_container_en').classList.add('hidden');
    document.getElementById('visual_container_en').classList.remove('hidden');
    document.getElementById('source_container_hi').classList.add('hidden');
    document.getElementById('visual_container_hi').classList.remove('hidden');
    toolbar.classList.remove('opacity-40', 'pointer-events-none');
    label.textContent = 'Edit HTML Source';
  }
}

function syncVisualToSource(lang) {
  const visual = document.getElementById('visual_editor_' + lang);
  const source = document.getElementById('source_editor_' + lang);
  if (visual && source) {
    source.value = visual.innerHTML;
  }
}

function syncSourceToVisual(lang) {
  const visual = document.getElementById('visual_editor_' + lang);
  const source = document.getElementById('source_editor_' + lang);
  if (visual && source) {
    visual.innerHTML = source.value;
  }
}

function formatDoc(cmd, value = null) {
  const target = document.getElementById('visual_editor_' + currentTab);
  if (target) {
    target.focus();
    document.execCommand(cmd, false, value);
    syncVisualToSource(currentTab);
  }
}

function insertLinkPrompt() {
  const url = prompt('Enter web address / URL:');
  if (url) {
    formatDoc('createLink', url);
  }
}

function autoGenerateSlug(title) {
  const slugInput = document.getElementById('form_slug');
  const isSystem = document.getElementById('form_is_system').value;
  if (slugInput && isSystem !== '1' && !slugInput.readOnly) {
    const slug = title.toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
    slugInput.value = slug;
  }
}

function prepareFormSubmission(e) {
  if (isSourceMode) {
    syncSourceToVisual('en');
    syncSourceToVisual('hi');
  } else {
    syncVisualToSource('en');
    syncVisualToSource('hi');
  }
  document.getElementById('final_content_en').value = document.getElementById('source_editor_en').value;
  document.getElementById('final_content_hi').value = document.getElementById('source_editor_hi').value;
}
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
