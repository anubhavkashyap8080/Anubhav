<?php
/**
 * GaunGaliHat 1-Click Database Setup & Status Verifier
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/settings.php';

$installed = false;
$msg = '';
$error = '';

if (isset($_POST['install_db'])) {
    $schema_file = __DIR__ . '/database/schema.sql';
    if (!file_exists($schema_file)) {
        $error = 'Database schema file database/schema.sql not found!';
    } else {
        try {
            $sql = file_get_contents($schema_file);
            // Execute schema commands
            $pdo->exec($sql);
            $installed = true;
            $msg = 'डेटाबेस स्कीमा एवं प्रारंभिक डेटा (दालें, अचार, औजार, विक्रेता, सुपर एडमिन) सफलतापूर्वक लोड किया गया!';
        } catch (Exception $e) {
            $error = 'डेटाबेस इंस्टालेशन में त्रुटि: ' . $e->getMessage();
        }
    }
}

// Check current counts
$cat_count = 0;
$prod_count = 0;
$vendor_count = 0;
$user_count = 0;
$order_count = 0;
$driver_name = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

try {
    $cat_count = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn() ?: 0;
    $prod_count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn() ?: 0;
    $vendor_count = $pdo->query("SELECT COUNT(*) FROM vendors")->fetchColumn() ?: 0;
    $user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?: 0;
    $order_count = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0;
} catch (Exception $e) {
    // Tables might not exist yet
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
  <meta charset="UTF-8">
  <title>Database Setup & Diagnostic — GaunGaliHat</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-stone-100 min-h-screen py-12 px-4">
  <div class="max-w-2xl mx-auto">
    
    <div class="text-center mb-8">
      <div class="w-14 h-14 rounded-2xl bg-emerald-800 text-white flex items-center justify-center mx-auto mb-3 shadow-lg text-2xl">
        <i class="fa-solid fa-database"></i>
      </div>
      <h1 class="text-2xl font-black text-gray-900">डेटाबेस इंस्टॉलर एवं सिस्टम स्थिति</h1>
      <p class="text-xs text-gray-500 mt-1">GaunGaliHat • S S Industries Enterprise</p>
    </div>

    <?php if ($msg): ?>
      <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-semibold flex items-center gap-2">
        <i class="fa-solid fa-circle-check text-base"></i>
        <span><?= htmlspecialchars($msg) ?></span>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-xs font-semibold flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation text-base"></i>
        <span><?= htmlspecialchars($error) ?></span>
      </div>
    <?php endif; ?>

    <!-- Database Driver Status Card -->
    <div class="bg-white rounded-3xl border border-gray-200 shadow-md p-6 sm:p-8 space-y-6">
      <div class="flex items-center justify-between pb-4 border-b border-gray-100">
        <div>
          <span class="text-xs text-gray-400 font-bold uppercase">सक्रिय PDO ड्राइवर</span>
          <h3 class="text-base font-bold text-gray-900 uppercase flex items-center gap-2 mt-0.5">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            PDO <?= strtoupper($driver_name) ?>
          </h3>
        </div>
        <span class="px-3 py-1 bg-emerald-50 text-emerald-800 rounded-full text-xs font-bold border border-emerald-200">
          कनेक्शन सक्रिय
        </span>
      </div>

      <!-- Current Database Entity Counts -->
      <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-center text-xs">
        <div class="p-3 bg-stone-50 rounded-xl border border-gray-100">
          <span class="text-xl font-black text-gray-900 block"><?= $cat_count ?></span>
          <span class="text-[11px] text-gray-500 font-medium">श्रेणियां</span>
        </div>
        <div class="p-3 bg-stone-50 rounded-xl border border-gray-100">
          <span class="text-xl font-black text-gray-900 block"><?= $prod_count ?></span>
          <span class="text-[11px] text-gray-500 font-medium">उत्पाद (SKU)</span>
        </div>
        <div class="p-3 bg-stone-50 rounded-xl border border-gray-100">
          <span class="text-xl font-black text-gray-900 block"><?= $vendor_count ?></span>
          <span class="text-[11px] text-gray-500 font-medium">कारीगर / SHG</span>
        </div>
        <div class="p-3 bg-stone-50 rounded-xl border border-gray-100">
          <span class="text-xl font-black text-gray-900 block"><?= $user_count ?></span>
          <span class="text-[11px] text-gray-500 font-medium">खाते</span>
        </div>
        <div class="p-3 bg-stone-50 rounded-xl border border-gray-100 col-span-2 sm:col-span-1">
          <span class="text-xl font-black text-gray-900 block"><?= $order_count ?></span>
          <span class="text-[11px] text-gray-500 font-medium">आर्डर</span>
        </div>
      </div>

      <!-- Seed Action Button -->
      <form action="/install.php" method="POST" class="pt-2">
        <input type="hidden" name="install_db" value="1">
        <button type="submit" onclick="return confirm('क्या आप डेटाबेस स्कीमा एवं डेमो डेटा को पुनः स्थापित करना चाहते हैं?')"
          class="w-full py-3 bg-emerald-800 hover:bg-emerald-900 text-white font-bold rounded-xl text-xs transition shadow-md flex items-center justify-center gap-2">
          <i class="fa-solid fa-arrows-rotate"></i>
          1-क्लिक डेटाबेस स्कीमा एवं डेमो डेटा इंस्टॉल करें (Reset / Install Schema)
        </button>
      </form>

      <!-- Portal Access Links -->
      <div class="pt-4 border-t border-gray-100 grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs font-bold">
        <a href="/" class="p-2.5 bg-stone-100 hover:bg-stone-200 text-stone-800 rounded-xl transition">
          <i class="fa-solid fa-store block mb-1"></i> होम स्टोर
        </a>
        <a href="/login.php" class="p-2.5 bg-stone-100 hover:bg-stone-200 text-stone-800 rounded-xl transition">
          <i class="fa-solid fa-right-to-bracket block mb-1"></i> लॉगिन पोर्टल
        </a>
        <a href="/seller/dashboard.php" class="p-2.5 bg-amber-50 hover:bg-amber-100 text-amber-900 rounded-xl transition">
          <i class="fa-solid fa-hammer block mb-1"></i> विक्रेता हब
        </a>
        <a href="/admin/index.php" class="p-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-900 rounded-xl transition">
          <i class="fa-solid fa-shield-halved block mb-1"></i> सुपर एडमिन
        </a>
      </div>

    </div>

  </div>
</body>
</html>
