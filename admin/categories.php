<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
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
        set_flash('success', 'नई श्रेणी "' . htmlspecialchars($name) . '" सफलतापूर्वक जोड़ी गई!');
        header('Location: /admin/categories.php');
        exit;
    } catch (Exception $e) {
        set_flash('error', 'त्रुटि: ' . $e->getMessage());
    }
}

// Handle Delete Category
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$del_id]);
    set_flash('success', 'श्रेणी हटा दी गई।');
    header('Location: /admin/categories.php');
    exit;
}

// Handle Edit Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
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
            set_flash('success', 'श्रेणी जानकारी सफलतापूर्वक अपडेट की गई!');
            header('Location: /admin/categories.php');
            exit;
        } catch (Exception $e) {
            set_flash('error', 'त्रुटि: ' . $e->getMessage());
        }
    }
}

// Fetch all categories with product count
$stmt_cats = $pdo->query("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.sort_order ASC, c.id ASC
");
$categories = $stmt_cats->fetchAll();

$page_title = 'श्रेणी प्रबंधन (Category Management)';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title & Action Bar -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-tags text-indigo-600"></i>
      उत्पाद श्रेणी प्रबंधन (Categories)
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      जैविक दालें, बनारसी अचार, कृषि उपकरण, जूट बैग एवं हथकरघा श्रेणियां
    </p>
  </div>

  <button onclick="document.getElementById('addCategoryModal').classList.remove('hidden')"
    class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
    <i class="fa-solid fa-plus"></i> नई श्रेणी जोड़ें (Add Category)
  </button>
</div>

<!-- Categories Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
  <?php foreach ($categories as $cat): ?>
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col justify-between">
      <div class="relative h-36 bg-stone-100 overflow-hidden">
        <img src="<?= htmlspecialchars($cat['image'] ?? 'https://images.unsplash.com/photo-1589923188900-85dae523342b?w=600&q=80') ?>"
          alt="<?= htmlspecialchars($cat['name']) ?>" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
        <div class="absolute bottom-3 left-4 right-4 text-white">
          <span class="text-[10px] bg-white/20 backdrop-blur-sm px-2 py-0.5 rounded font-bold uppercase tracking-wider">
            <?= $cat['product_count'] ?> उत्पाद
          </span>
          <h3 class="text-base font-bold mt-1"><?= htmlspecialchars($cat['hindi_name'] ?? $cat['name']) ?></h3>
          <p class="text-[11px] opacity-80"><?= htmlspecialchars($cat['name']) ?></p>
        </div>
      </div>

      <div class="p-4 flex-1 flex flex-col justify-between text-xs text-gray-600">
        <p class="line-clamp-2 text-gray-500 mb-3"><?= htmlspecialchars($cat['description'] ?? '') ?></p>
        
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
            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' class="text-stone-500 hover:text-indigo-600 font-bold p-1 cursor-pointer transition" title="संपादित करें">
              <i class="fa-solid fa-pen-to-square"></i>
            </button>
            <a href="/products.php?category=<?= htmlspecialchars($cat['slug']) ?>" target="_blank" class="text-indigo-600 hover:underline font-bold">
              देखें →
            </a>
            <a href="/admin/categories.php?delete_id=<?= $cat['id'] ?>" onclick="return confirm('क्या आप इस श्रेणी को हटाना चाहते हैं?')" class="text-red-500 hover:text-red-700">
              <i class="fa-regular fa-trash-can"></i>
            </a>
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
        नई श्रेणी जोड़ें
      </h3>
      <button onclick="document.getElementById('addCategoryModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/admin/categories.php" method="POST" class="space-y-4">
      <input type="hidden" name="add_category" value="1">

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">श्रेणी का नाम (English) <span class="text-red-500">*</span></label>
        <input type="text" name="name" required placeholder="उदा. Ayurvedic Herbal Honey"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">हिंदी नाम (Hindi Name) <span class="text-red-500">*</span></label>
        <input type="text" name="hindi_name" required placeholder="उदा. आयुर्वेदिक जंगली शहद"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">URL Slug</label>
        <input type="text" name="slug" placeholder="ayurvedic-herbal-honey"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">बैनर चित्र URL</label>
        <input type="url" name="image" placeholder="https://..."
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">विवरण (Description)</label>
        <textarea name="description" rows="2" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition"></textarea>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" onclick="document.getElementById('addCategoryModal').classList.add('hidden')" class="px-4 py-2 bg-stone-100 text-stone-700 font-bold rounded-xl text-xs">
          रद्द करें
        </button>
        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition">
          श्रेणी सहेजें
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
        श्रेणी संपादित करें (Edit Category)
      </h3>
      <button type="button" onclick="document.getElementById('editCategoryModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/admin/categories.php" method="POST" class="space-y-4">
      <input type="hidden" name="edit_category" value="1">
      <input type="hidden" name="edit_cat_id" id="edit_cat_id" value="">

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">श्रेणी का नाम (English) <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="edit_cat_name" required
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">हिंदी नाम (Hindi Name)</label>
        <input type="text" name="hindi_name" id="edit_cat_hindi"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">URL Slug</label>
          <input type="text" name="slug" id="edit_cat_slug"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">क्रम (Sort Order)</label>
          <input type="number" name="sort_order" id="edit_cat_order" value="0"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">बैनर चित्र URL</label>
        <input type="url" name="image" id="edit_cat_image" placeholder="https://..."
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">विवरण (Description)</label>
        <textarea name="description" id="edit_cat_desc" rows="2" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition"></textarea>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" onclick="document.getElementById('editCategoryModal').classList.add('hidden')" class="px-4 py-2 bg-stone-100 text-stone-700 font-bold rounded-xl text-xs">
          रद्द करें
        </button>
        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition">
          अपडेट सहेजें (Save Changes)
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditCategoryModal(cat) {
  document.getElementById('edit_cat_id').value = cat.id || '';
  document.getElementById('edit_cat_name').value = cat.name || '';
  document.getElementById('edit_cat_hindi').value = cat.hindi_name || '';
  document.getElementById('edit_cat_slug').value = cat.slug || '';
  document.getElementById('edit_cat_order').value = cat.sort_order || 0;
  document.getElementById('edit_cat_image').value = cat.image || '';
  document.getElementById('edit_cat_desc').value = cat.description || '';
  document.getElementById('editCategoryModal').classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
