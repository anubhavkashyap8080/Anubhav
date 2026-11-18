<?php
/**
 * GanvGaliHat Rural Editorial & Blog CMS
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

// Security Audit H2: Enforce CSRF protection for all POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();
}

// Handle Add / Edit Blog
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_blog'])) {
    $blog_id = intval($_POST['blog_id'] ?? 0);
    $title = clean_input($_POST['title'] ?? '');
    $hindi_title = clean_input($_POST['hindi_title'] ?? '');
    $slug = strtolower(clean_input($_POST['slug'] ?? ''));
    if (empty($slug)) {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
    }
    $category = clean_input($_POST['category'] ?? 'Rural Craftsmanship');
    $author_name = clean_input($_POST['author_name'] ?? 'Anubhav Kashyap');
    $image = clean_input($_POST['image'] ?? 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=900&q=80');
    $excerpt = clean_input($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $tags = clean_input($_POST['tags'] ?? '');
    $status = clean_input($_POST['status'] ?? 'published');

    if (empty($title) || empty($content)) {
        set_flash('error', 'Please enter both the blog title and content.');
    } else {
        try {
            if ($blog_id > 0) {
                $stmt = $pdo->prepare("
                    UPDATE blogs 
                    SET title = ?, hindi_title = ?, slug = ?, category = ?, author_name = ?, 
                        image = ?, excerpt = ?, content = ?, tags = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([$title, $hindi_title, $slug, $category, $author_name, $image, $excerpt, $content, $tags, $status, $blog_id]);
                log_audit_action('blog_update', 'blogs', 'blog', $blog_id, "Updated blog: {$title}");
                set_flash('success', 'Blog post updated successfully.');
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO blogs (title, hindi_title, slug, category, author_name, image, excerpt, content, tags, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$title, $hindi_title, $slug, $category, $author_name, $image, $excerpt, $content, $tags, $status]);
                $new_id = $pdo->lastInsertId();
                log_audit_action('blog_create', 'blogs', 'blog', $new_id, "Created blog: {$title}");
                set_flash('success', 'New blog post published successfully.');
            }
            header('Location: /admin/blogs.php');
            exit;
        } catch (Exception $e) {
            set_flash('error', 'Error saving blog post. Please try again.');
        }
    }
}

// Handle Toggle Status (Strictly POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $bid = intval($_POST['blog_id'] ?? 0);
    $cur = $pdo->prepare("SELECT status FROM blogs WHERE id = ?");
    $cur->execute([$bid]);
    $st = $cur->fetchColumn();
    $new_st = ($st === 'published') ? 'draft' : 'published';
    $pdo->prepare("UPDATE blogs SET status = ? WHERE id = ?")->execute([$new_st, $bid]);
    log_audit_action('blog_toggle_status', 'blogs', 'blog', $bid, "Status changed to {$new_st}");
    set_flash('success', 'Blog status updated to ' . $new_st . '.');
    header('Location: /admin/blogs.php');
    exit;
}

// Handle Delete Blog (Strictly POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_blog') {
    $del_id = intval($_POST['delete_id'] ?? 0);
    $pdo->prepare("DELETE FROM blogs WHERE id = ?")->execute([$del_id]);
    log_audit_action('blog_delete', 'blogs', 'blog', $del_id, 'Blog post deleted');
    set_flash('success', 'Blog post deleted successfully.');
    header('Location: /admin/blogs.php');
    exit;
}

// Reject Insecure GET Mutating Requests
if (isset($_GET['delete_id']) || isset($_GET['toggle_status'])) {
    http_response_code(405);
    set_flash('error', 'Invalid request method. Only POST requests are allowed.');
    header('Location: /admin/blogs.php');
    exit;
}

// Fetch all blogs
$blogs = $pdo->query("SELECT * FROM blogs ORDER BY id DESC")->fetchAll();

$page_title = 'Blog & Editorial CMS — Super Admin';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title & Action Bar -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-newspaper text-indigo-600"></i>
      Blog & Editorial Management (Village Heritage Stories)
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      CMS for village artisan stories, organic farming, self-help groups, and rural craftsmanship
    </p>
  </div>

  <div class="flex items-center gap-2">
    <a href="/blogs.php" target="_blank" class="px-3.5 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-xl border border-stone-300 transition flex items-center gap-1.5 shadow-xs">
      <i class="fa-solid fa-arrow-up-right-from-square"></i> View Public Blogs
    </a>
    <button onclick="openBlogModal()"
      class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
      <i class="fa-solid fa-pen-nib"></i> Write New Blog Post
    </button>
  </div>
</div>

<!-- Blogs Table Card -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
      <thead class="bg-stone-50 border-b border-gray-200 text-gray-500 font-bold uppercase text-[10px]">
        <tr>
          <th class="px-4 py-3.5">Image</th>
          <th class="px-4 py-3.5">Title & Category</th>
          <th class="px-4 py-3.5">Author</th>
          <th class="px-4 py-3.5">Views</th>
          <th class="px-4 py-3.5">Status</th>
          <th class="px-4 py-3.5">Publish Date</th>
          <th class="px-4 py-3.5 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($blogs as $b): ?>
          <tr class="hover:bg-stone-50/50 transition">
            <td class="px-4 py-3.5">
              <img src="<?= htmlspecialchars($b['image'] ?: '/logo.png') ?>" alt="Blog"
                class="w-12 h-10 object-cover rounded-lg border border-gray-200 shadow-2xs">
            </td>

            <td class="px-4 py-3.5 max-w-sm">
              <div class="font-bold text-gray-900 line-clamp-1"><?= htmlspecialchars($b['title']) ?></div>
              <?php if (!empty($b['hindi_title'])): ?>
                <div class="text-[11px] text-gray-500 line-clamp-1"><?= htmlspecialchars($b['hindi_title']) ?></div>
              <?php endif; ?>
              <span class="inline-block mt-1 text-[10px] bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded font-semibold border border-indigo-200">
                <?= htmlspecialchars($b['category']) ?>
              </span>
            </td>

            <td class="px-4 py-3.5 font-medium text-gray-700">
              <?= htmlspecialchars($b['author_name']) ?>
            </td>

            <td class="px-4 py-3.5 font-mono text-gray-600">
              <i class="fa-regular fa-eye text-gray-400 mr-1"></i><?= number_format($b['views'] ?? 0) ?>
            </td>

            <td class="px-4 py-3.5">
              <form action="/admin/blogs.php" method="POST" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="toggle_status">
                <input type="hidden" name="blog_id" value="<?= $b['id'] ?>">
                <button type="submit"
                  class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold cursor-pointer <?= $b['status'] === 'published' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-stone-200 text-stone-700 hover:bg-stone-300' ?>"
                  title="Click to toggle status">
                  <span class="w-1.5 h-1.5 rounded-full <?= $b['status'] === 'published' ? 'bg-emerald-600' : 'bg-stone-500' ?>"></span>
                  <?= ucfirst($b['status']) ?>
                </button>
              </form>
            </td>

            <td class="px-4 py-3.5 text-gray-400 text-[11px]">
              <?= date('d M Y', strtotime($b['created_at'])) ?>
            </td>

            <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-1">
              <a href="/blog-detail.php?slug=<?= urlencode($b['slug']) ?>" target="_blank"
                class="p-1.5 text-gray-400 hover:text-indigo-600 rounded hover:bg-indigo-50 transition" title="View">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
              </a>
              <button onclick='editBlog(<?= json_encode($b) ?>)'
                class="p-1.5 text-gray-400 hover:text-indigo-600 rounded hover:bg-indigo-50 transition" title="Edit">
                <i class="fa-regular fa-pen-to-square"></i>
              </button>
              <form action="/admin/blogs.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this blog post? This cannot be undone.');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete_blog">
                <input type="hidden" name="delete_id" value="<?= $b['id'] ?>">
                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50 transition cursor-pointer" title="Delete">
                  <i class="fa-regular fa-trash-can"></i>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add / Edit Blog Modal -->
<div id="blogModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
  <div class="bg-white rounded-3xl border border-gray-200 shadow-2xl max-w-2xl w-full p-6 sm:p-8 max-h-[92vh] overflow-y-auto">
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
      <h3 class="text-base font-bold text-gray-900 flex items-center gap-2" id="blogModalTitle">
        <i class="fa-solid fa-pen-nib text-indigo-600"></i>
        Write New Blog Article
      </h3>
      <button onclick="document.getElementById('blogModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/admin/blogs.php" method="POST" class="space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="save_blog" value="1">
      <input type="hidden" name="blog_id" id="blog_id" value="0">

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Blog Title (English) <span class="text-red-500">*</span></label>
        <input type="text" name="title" id="blog_title" required
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Hindi Title (Localization)</label>
        <input type="text" name="hindi_title" id="blog_hindi_title"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">URL Slug</label>
          <input type="text" name="slug" id="blog_slug" placeholder="auto-generated-if-empty"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
          <select name="category" id="blog_category" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
            <option value="Rural Craftsmanship">Rural Craftsmanship</option>
            <option value="Organic Farming">Organic Farming</option>
            <option value="Village Women SHGs">Village Women SHGs</option>
            <option value="Artisan Stories">Artisan Stories</option>
            <option value="Tool Maintenance">Tool Maintenance</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Author Name</label>
          <input type="text" name="author_name" id="blog_author_name" value="Anubhav Kashyap"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Publish Status</label>
          <select name="status" id="blog_status" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
            <option value="published">Published</option>
            <option value="draft">Draft</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Cover Image URL (Featured Image)</label>
        <input type="url" name="image" id="blog_image" placeholder="https://images.unsplash.com/..."
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Short Excerpt (1-2 sentences)</label>
        <textarea name="excerpt" id="blog_excerpt" rows="2"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition"></textarea>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Full Article Content <span class="text-red-500">*</span></label>
        <textarea name="content" id="blog_content" rows="6" required
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition"></textarea>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Tags (Comma-separated)</label>
        <input type="text" name="tags" id="blog_tags" placeholder="e.g. Sickle, Blacksmith, Hardoi, Organic"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
        <button type="button" onclick="document.getElementById('blogModal').classList.add('hidden')"
          class="px-4 py-2 bg-stone-100 text-stone-700 font-bold rounded-xl text-xs">
          Cancel
        </button>
        <button type="submit"
          class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition">
          Save Article
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openBlogModal() {
  document.getElementById('blog_id').value = 0;
  document.getElementById('blog_title').value = '';
  document.getElementById('blog_hindi_title').value = '';
  document.getElementById('blog_slug').value = '';
  document.getElementById('blog_author_name').value = 'Anubhav Kashyap';
  document.getElementById('blog_image').value = 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=900&q=80';
  document.getElementById('blog_excerpt').value = '';
  document.getElementById('blog_content').value = '';
  document.getElementById('blog_tags').value = '';
  document.getElementById('blog_status').value = 'published';
  document.getElementById('blogModalTitle').innerHTML = '<i class="fa-solid fa-pen-nib text-indigo-600"></i> Write New Blog Article';
  document.getElementById('blogModal').classList.remove('hidden');
}

function editBlog(b) {
  document.getElementById('blog_id').value = b.id;
  document.getElementById('blog_title').value = b.title || '';
  document.getElementById('blog_hindi_title').value = b.hindi_title || '';
  document.getElementById('blog_slug').value = b.slug || '';
  document.getElementById('blog_category').value = b.category || 'Rural Craftsmanship';
  document.getElementById('blog_author_name').value = b.author_name || 'Anubhav Kashyap';
  document.getElementById('blog_image').value = b.image || '';
  document.getElementById('blog_excerpt').value = b.excerpt || '';
  document.getElementById('blog_content').value = b.content || '';
  document.getElementById('blog_tags').value = b.tags || '';
  document.getElementById('blog_status').value = b.status || 'published';
  document.getElementById('blogModalTitle').innerHTML = '<i class="fa-regular fa-pen-to-square text-indigo-600"></i> Edit Blog Article';
  document.getElementById('blogModal').classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
