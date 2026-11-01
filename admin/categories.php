<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

// Handle Save All Category Positions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category_order'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security token invalid. Please refresh the page and try again.');
        header('Location: /admin/categories.php');
        exit;
    }

    $positions = $_POST['category_order'] ?? [];
    if (is_array($positions)) {
        $stmt_upd = $pdo->prepare("UPDATE categories SET sort_order = ? WHERE id = ?");
        foreach ($positions as $cat_id => $pos) {
            $cat_id = (int)$cat_id;
            $pos = (int)$pos;
            if ($cat_id > 0) {
                $stmt_upd->execute([$pos, $cat_id]);
            }
        }
        log_audit_action('categories_reorder', 'categories', 'categories', null, 'Reordered categories positions');
        set_flash('success', 'Category display positions updated successfully! The new sequence is now active across all homepage layouts.');
        header('Location: /admin/categories.php');
        exit;
    }
}

// Handle 1-Click Move Up / Move Down
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['move_up', 'move_down'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security token invalid.');
        header('Location: /admin/categories.php');
        exit;
    }

    $target_id = (int)($_POST['category_id'] ?? 0);
    $direction = $_POST['action']; // 'move_up' or 'move_down'

    $all_cats = $pdo->query("SELECT id, sort_order FROM categories ORDER BY sort_order ASC, id ASC")->fetchAll();
    $idx = -1;
    foreach ($all_cats as $k => $c) {
        if ($c['id'] == $target_id) {
            $idx = $k;
            break;
        }
    }

    if ($idx !== -1) {
        $swap_idx = ($direction === 'move_up') ? ($idx - 1) : ($idx + 1);
        if ($swap_idx >= 0 && $swap_idx < count($all_cats)) {
            $cat_a = $all_cats[$idx];
            $cat_b = $all_cats[$swap_idx];

            $order_a = (int)$cat_a['sort_order'];
            $order_b = (int)$cat_b['sort_order'];
            if ($order_a === $order_b) {
                $order_a = $idx + 1;
                $order_b = $swap_idx + 1;
            }

            $stmt_swap = $pdo->prepare("UPDATE categories SET sort_order = ? WHERE id = ?");
            $stmt_swap->execute([$order_b, $cat_a['id']]);
            $stmt_swap->execute([$order_a, $cat_b['id']]);

            log_audit_action('category_move', 'categories', 'category', $target_id, "Moved category {$direction}");
            set_flash('success', 'Category position moved successfully!');
            header('Location: /admin/categories.php');
            exit;
        }
    }
}

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security token invalid.');
        header('Location: /admin/categories.php');
        exit;
    }
    $name = clean_input($_POST['name'] ?? '');
    $hindi_name = clean_input($_POST['hindi_name'] ?? '');
    $slug = strtolower(clean_input($_POST['slug'] ?? ''));
    if (empty($slug)) {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
    }
    $description = clean_input($_POST['description'] ?? '');
    $image = clean_input($_POST['image'] ?? 'https://images.unsplash.com/photo-1589923188900-85dae523342b?w=600&q=80');
    $sort_order = intval($_POST['sort_order'] ?? 0);

    try {
        $stmt = $pdo->prepare("INSERT INTO categories (name, hindi_name, slug, description, image, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $hindi_name, $slug, $description, $image, $sort_order]);
        $new_cat_id = $pdo->lastInsertId();
        log_audit_action('category_create', 'categories', 'category', $new_cat_id, "Created category: {$name}");
        set_flash('success', 'New category "' . htmlspecialchars($name) . '" created successfully!');
        header('Location: /admin/categories.php');
        exit;
    } catch (Exception $e) {
        set_flash('error', 'Error creating category: ' . $e->getMessage());
    }
}

// Handle Delete Category (Strictly POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_category') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security token invalid.');
        header('Location: /admin/categories.php');
        exit;
    }
    $del_id = intval($_POST['delete_id'] ?? 0);
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$del_id]);
    log_audit_action('category_delete', 'categories', 'category', $del_id, 'Category deleted');
    set_flash('success', 'Category deleted successfully.');
    header('Location: /admin/categories.php');
    exit;
}

// Reject Insecure GET Mutating Requests
if (isset($_GET['delete_id'])) {
    http_response_code(405);
    set_flash('error', 'Invalid request method (GET rejected). Please use buttons.');
    header('Location: /admin/categories.php');
    exit;
}

// Handle Edit Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security token invalid.');
        header('Location: /admin/categories.php');
        exit;
    }
    $cat_id = intval($_POST['edit_cat_id'] ?? 0);
    $name = clean_input($_POST['name'] ?? '');
    $hindi_name = clean_input($_POST['hindi_name'] ?? '');
    $slug = strtolower(clean_input($_POST['slug'] ?? ''));
    if (empty($slug)) {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
    }
    $description = clean_input($_POST['description'] ?? '');
    $image = clean_input($_POST['image'] ?? '');
    $sort_order = intval($_POST['sort_order'] ?? 0);

    if ($cat_id > 0 && !empty($name)) {
        try {
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, hindi_name = ?, slug = ?, description = ?, image = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$name, $hindi_name, $slug, $description, $image, $sort_order, $cat_id]);
            log_audit_action('admin_edit_category', 'categories', "Category #{$cat_id} edited");
            set_flash('success', 'Category "' . htmlspecialchars($name) . '" updated successfully!');
            header('Location: /admin/categories.php');
            exit;
        } catch (Exception $e) {
            set_flash('error', 'Error updating category: ' . $e->getMessage());
        }
    }
}

// Fetch all categories with product count ordered by sort_order
$stmt_cats = $pdo->query("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.sort_order ASC, c.id ASC
");
$categories = $stmt_cats->fetchAll();

$page_title = 'Category Management & Position Control';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title & Action Bar -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-tags text-indigo-600"></i>
      Product Categories & Position Arrangement
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      Manage category names, imagery, and control exact display positions across all homepage layouts.
    </p>
  </div>

  <button onclick="document.getElementById('addCategoryModal').classList.remove('hidden')"
    class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
    <i class="fa-solid fa-plus"></i> Add New Category
  </button>
</div>

<!-- ============================================================== -->
<!-- CATEGORY POSITION & SEQUENCE ARRANGEMENT CONTROLLER             -->
<!-- ============================================================== -->
<div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-5 sm:p-6 mb-8">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100">
    <div>
      <h2 class="text-base font-extrabold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-arrow-down-1-9 text-emerald-600"></i>
        Homepage Category Display Order (Sequence Control)
      </h2>
      <p class="text-xs text-gray-500 mt-0.5">
        Set the numeric position (1, 2, 3...) or use the Up/Down buttons. Categories are shown on the homepage circular tray and pill bar strictly in this order.
      </p>
    </div>
    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
      Total <?= count($categories) ?> Active Categories
    </span>
  </div>

  <form action="/admin/categories.php" method="POST" class="pt-4">
    <?= csrf_field() ?>
    <input type="hidden" name="save_category_order" value="1">

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead>
          <tr class="text-stone-400 font-bold border-b border-gray-100 uppercase text-[10px] tracking-wider">
            <th class="py-2.5 px-3">Position</th>
            <th class="py-2.5 px-3">Category</th>
            <th class="py-2.5 px-3">Slug</th>
            <th class="py-2.5 px-3">Products</th>
            <th class="py-2.5 px-3 text-center">Move (1-Click)</th>
            <th class="py-2.5 px-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach ($categories as $index => $cat): ?>
            <tr class="hover:bg-stone-50/80 transition">
              <!-- Position Number Input -->
              <td class="py-3 px-3 whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <span class="w-6 h-6 rounded-lg bg-stone-100 text-stone-700 font-black text-[11px] flex items-center justify-center">
                    #<?= $index + 1 ?>
                  </span>
                  <input type="number" name="category_order[<?= (int)$cat['id'] ?>]" 
                         value="<?= (int)$cat['sort_order'] ?>" min="0" max="999"
                         class="w-16 px-2 py-1.5 bg-stone-50 border border-gray-300 rounded-lg text-xs font-bold text-center focus:bg-white focus:border-indigo-600 focus:outline-none">
                </div>
              </td>

              <!-- Category Details -->
              <td class="py-3 px-3">
                <div class="flex items-center gap-3">
                  <img src="<?= htmlspecialchars($cat['image'] ?? '/assets/images/placeholder.jpg') ?>" 
                       alt="<?= htmlspecialchars($cat['name']) ?>" 
                       class="w-9 h-9 rounded-xl object-cover border border-gray-200 shadow-2xs">
                  <div>
                    <h3 class="font-extrabold text-xs text-gray-900"><?= htmlspecialchars($cat['name']) ?></h3>
                    <?php if (!empty($cat['hindi_name'])): ?>
                      <span class="text-[10px] text-gray-400"><?= htmlspecialchars($cat['hindi_name']) ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </td>

              <!-- Slug -->
              <td class="py-3 px-3 font-mono text-gray-500 text-[11px]">
                <?= htmlspecialchars($cat['slug']) ?>
              </td>

              <!-- Product Count -->
              <td class="py-3 px-3">
                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 font-bold text-[10px] rounded-md">
                  <?= (int)$cat['product_count'] ?> items
                </span>
              </td>

              <!-- Quick Move Up / Down Buttons -->
              <td class="py-3 px-3 text-center whitespace-nowrap">
                <div class="inline-flex items-center gap-1">
                  <!-- Move Up -->
                  <button type="submit" form="moveForm_up_<?= $cat['id'] ?>" 
                          class="w-7 h-7 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold flex items-center justify-center transition cursor-pointer"
                          title="Move Category Up" <?= $index === 0 ? 'disabled style="opacity:0.3;cursor:not-allowed;"' : '' ?>>
                    <i class="fa-solid fa-arrow-up text-[10px]"></i>
                  </button>
                  <!-- Move Down -->
                  <button type="submit" form="moveForm_down_<?= $cat['id'] ?>" 
                          class="w-7 h-7 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold flex items-center justify-center transition cursor-pointer"
                          title="Move Category Down" <?= $index === count($categories) - 1 ? 'disabled style="opacity:0.3;cursor:not-allowed;"' : '' ?>>
                    <i class="fa-solid fa-arrow-down text-[10px]"></i>
                  </button>
                </div>
              </td>

              <!-- Actions -->
              <td class="py-3 px-3 text-right whitespace-nowrap">
                <div class="inline-flex items-center gap-2">
                  <button type="button" onclick='openEditCategoryModal(<?= json_encode([
                      "id" => $cat["id"],
                      "name" => $cat["name"],
                      "hindi_name" => $cat["hindi_name"] ?? "",
                      "slug" => $cat["slug"],
                      "description" => $cat["description"] ?? "",
                      "image" => $cat["image"] ?? "",
                      "sort_order" => $cat["sort_order"] ?? 0
                  ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' class="px-2.5 py-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-[11px] rounded-lg transition" title="Edit">
                    <i class="fa-solid fa-pen-to-square"></i> Edit
                  </button>
                  <a href="/products.php?category=<?= htmlspecialchars($cat['slug']) ?>" target="_blank" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-[11px] rounded-lg transition">
                    View
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
      <span class="text-xs text-gray-500">
        💡 Tip: Lower position numbers appear first from left-to-right on desktop and mobile category trays.
      </span>
      <button type="submit" class="px-5 py-2.5 bg-emerald-800 hover:bg-emerald-900 text-white font-black text-xs rounded-xl shadow-md transition flex items-center gap-2 cursor-pointer">
        <i class="fa-solid fa-floppy-disk"></i>
        <span>Save All Category Positions</span>
      </button>
    </div>
  </form>
</div>

<!-- Hidden standalone forms for 1-click Move Up / Down -->
<?php foreach ($categories as $cat): ?>
  <form id="moveForm_up_<?= $cat['id'] ?>" action="/admin/categories.php" method="POST" style="display:none;">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="move_up">
    <input type="hidden" name="category_id" value="<?= (int)$cat['id'] ?>">
  </form>
  <form id="moveForm_down_<?= $cat['id'] ?>" action="/admin/categories.php" method="POST" style="display:none;">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="move_down">
    <input type="hidden" name="category_id" value="<?= (int)$cat['id'] ?>">
  </form>
<?php endforeach; ?>

<!-- Categories Card Grid -->
<h3 class="text-base font-extrabold text-gray-900 mb-4 flex items-center gap-2">
  <i class="fa-solid fa-grid-2 text-indigo-600"></i>
  Visual Category Cards
</h3>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
  <?php foreach ($categories as $cat): ?>
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col justify-between">
      <div class="relative h-36 bg-stone-100 overflow-hidden">
        <img src="<?= htmlspecialchars($cat['image'] ?? 'https://images.unsplash.com/photo-1589923188900-85dae523342b?w=600&q=80') ?>"
          alt="<?= htmlspecialchars($cat['name']) ?>" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"></div>
        <div class="absolute bottom-3 left-4 right-4 text-white">
          <div class="flex items-center gap-2 mb-1">
            <span class="text-[10px] bg-amber-500 text-stone-950 px-2 py-0.5 rounded font-black uppercase tracking-wider">
              Pos #<?= (int)$cat['sort_order'] ?>
            </span>
            <span class="text-[10px] bg-white/20 backdrop-blur-sm px-2 py-0.5 rounded font-bold uppercase tracking-wider">
              <?= $cat['product_count'] ?> products
            </span>
          </div>
          <h3 class="text-base font-bold"><?= htmlspecialchars($cat['name']) ?></h3>
          <?php if (!empty($cat['hindi_name'])): ?>
            <p class="text-[11px] opacity-80"><?= htmlspecialchars($cat['hindi_name']) ?></p>
          <?php endif; ?>
        </div>
      </div>

      <div class="p-4 flex-1 flex flex-col justify-between text-xs text-gray-600">
        <p class="line-clamp-2 text-gray-500 mb-3"><?= htmlspecialchars($cat['description'] ?? 'No description provided.') ?></p>
        
        <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-[11px]">
          <span class="font-mono text-gray-400">slug: <?= htmlspecialchars($cat['slug']) ?></span>
          <div class="flex items-center gap-2">
            <button type="button" onclick='openEditCategoryModal(<?= json_encode([
                "id" => $cat["id"],
                "name" => $cat["name"],
                "hindi_name" => $cat["hindi_name"] ?? "",
                "slug" => $cat["slug"],
                "description" => $cat["description"] ?? "",
                "image" => $cat["image"] ?? "",
                "sort_order" => $cat["sort_order"] ?? 0
            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' class="text-stone-600 hover:text-indigo-600 font-bold p-1 cursor-pointer transition" title="Edit">
              <i class="fa-solid fa-pen-to-square"></i>
            </button>
            <a href="/products.php?category=<?= htmlspecialchars($cat['slug']) ?>" target="_blank" class="text-indigo-600 hover:underline font-bold">
              View →
            </a>
            <form action="/admin/categories.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete_category">
              <input type="hidden" name="delete_id" value="<?= $cat['id'] ?>">
              <button type="submit" class="text-red-500 hover:text-red-700 cursor-pointer p-1" title="Delete">
                <i class="fa-regular fa-trash-can"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl border border-gray-200 shadow-2xl max-w-md w-full p-6 sm:p-8">
    <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
      <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-plus-circle text-indigo-600"></i>
        Add New Category
      </h3>
      <button onclick="document.getElementById('addCategoryModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/admin/categories.php" method="POST" class="space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="add_category" value="1">

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Category Name (English) <span class="text-red-500">*</span></label>
        <input type="text" name="name" required placeholder="e.g. Traditional Hand-Forged Tools"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Regional / Hindi Name (Optional)</label>
        <input type="text" name="hindi_name" placeholder="e.g. पारंपरिक लोहारी व औजार"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">URL Slug</label>
        <input type="text" name="slug" placeholder="traditional-tools"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Display Position (Sort Order)</label>
        <input type="number" name="sort_order" value="<?= count($categories) + 1 ?>" min="0" max="999"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Banner Image URL</label>
        <input type="url" name="image" placeholder="https://..."
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Description</label>
        <textarea name="description" rows="2" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition"></textarea>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" onclick="document.getElementById('addCategoryModal').classList.add('hidden')" class="px-4 py-2 bg-stone-100 text-stone-700 font-bold rounded-xl text-xs">
          Cancel
        </button>
        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition">
          Save Category
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl border border-gray-200 shadow-2xl max-w-md w-full p-6 sm:p-8">
    <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
      <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-pen-to-square text-indigo-600"></i>
        Edit Category
      </h3>
      <button type="button" onclick="document.getElementById('editCategoryModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/admin/categories.php" method="POST" class="space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="edit_category" value="1">
      <input type="hidden" name="edit_cat_id" id="edit_cat_id">

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Category Name (English) <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="edit_name" required
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Regional / Hindi Name</label>
        <input type="text" name="hindi_name" id="edit_hindi_name"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">URL Slug</label>
        <input type="text" name="slug" id="edit_slug"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Display Position (Sort Order)</label>
        <input type="number" name="sort_order" id="edit_sort_order" min="0" max="999"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Banner Image URL</label>
        <input type="url" name="image" id="edit_image"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Description</label>
        <textarea name="description" id="edit_description" rows="2" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition"></textarea>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" onclick="document.getElementById('editCategoryModal').classList.add('hidden')" class="px-4 py-2 bg-stone-100 text-stone-700 font-bold rounded-xl text-xs">
          Cancel
        </button>
        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition">
          Update Category
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditCategoryModal(cat) {
  document.getElementById('edit_cat_id').value = cat.id || '';
  document.getElementById('edit_name').value = cat.name || '';
  document.getElementById('edit_hindi_name').value = cat.hindi_name || '';
  document.getElementById('edit_slug').value = cat.slug || '';
  document.getElementById('edit_sort_order').value = cat.sort_order || 0;
  document.getElementById('edit_image').value = cat.image || '';
  document.getElementById('edit_description').value = cat.description || '';
  document.getElementById('editCategoryModal').classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
