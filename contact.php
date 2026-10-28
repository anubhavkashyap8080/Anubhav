<?php
$page_title = 'संपर्क करें (Contact Us) — GaunGaliHat';
require_once __DIR__ . '/includes/header.php';

$sent = false;
$ticket_created_num = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Item 16: CSRF Check
    csrf_protect();

    $sender_name = clean_input($_POST['name'] ?? '');
    $sender_phone = clean_input($_POST['phone'] ?? '');
    $sender_email = clean_input($_POST['email'] ?? '');
    $subject_key = clean_input($_POST['subject'] ?? 'order');
    $sender_message = clean_input($_POST['message'] ?? '');

    // Item 24: Rate limiting per IP and phone
    $client_ip = get_client_ip();
    if (!check_rate_limit('contact_ip', $client_ip, 5, 900) || !check_rate_limit('contact_phone', $sender_phone, 3, 900)) {
        set_flash('error', 'बहुत अधिक संदेश अनुरोध। कृपया 15 मिनट बाद पुनः प्रयास करें। (Rate limit exceeded)');
        header('Location: /contact.php');
        exit;
    }

    // Item 24: Turnstile CAPTCHA verification
    if (!verify_turnstile_captcha()) {
        set_flash('error', 'CAPTCHA सुरक्षा सत्यापन विफल रहा। कृपया पुनः प्रयास करें।');
        header('Location: /contact.php');
        exit;
    }

    $cat_map = [
        'order' => 'Order Tracking',
        'bulk' => 'Bulk Purchase / Wholesale',
        'vendor' => 'Artisan Onboarding',
        'other' => 'General Inquiry'
    ];
    $category = $cat_map[$subject_key] ?? 'General Inquiry';
    $subject_label = "वेबसाइट संपर्क फॉर्म: " . $category;

    if (!empty($sender_name) && !empty($sender_phone) && !empty($sender_message)) {
        try {
            $user_id = null;
            $role = 'visitor';
            if (is_logged_in()) {
                $cuser = get_logged_in_user();
                if ($cuser) {
                    $user_id = $cuser['id'];
                    $role = $cuser['role'];
                    if (empty($sender_email)) {
                        $sender_email = $cuser['email'];
                    }
                }
            }

            $email_val = !empty($sender_email) ? $sender_email : ($sender_phone . '@visitor.ganvgalihat.com');
            $ticket_num = 'TKT-' . date('ymd') . '-' . random_int(1000, 9999);
            
            $stmt_tkt = $pdo->prepare("
                INSERT INTO tickets (ticket_number, user_id, name, email, phone, role, subject, category, priority, status, message, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Medium', 'Open', ?, CURRENT_TIMESTAMP)
            ");
            $stmt_tkt->execute([
                $ticket_num,
                $user_id,
                $sender_name,
                $email_val,
                $sender_phone,
                $role,
                $subject_label,
                $category,
                $sender_message
            ]);

            $ticket_created_num = $ticket_num;
            set_flash('success', "धन्यवाद $sender_name! आपका संदेश सपोर्ट टिकट #$ticket_num के रूप में दर्ज कर लिया गया है। हमारी टीम 24 घंटे के भीतर संपर्क करेगी।");
            $sent = true;
        } catch (Exception $e) {
            set_flash('error', 'संदेश भेजने में त्रुटि: ' . $e->getMessage());
        }
    }
}
?>

<div class="bg-stone-50 min-h-screen py-12">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="text-center mb-10">
      <span class="text-xs font-bold text-emerald-800 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
        सहायता एवं ग्राहक सेवा
      </span>
      <h1 class="text-3xl sm:text-4xl font-black text-gray-900 mt-2">हमसे संपर्क करें (Get in Touch)</h1>
      <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-lg mx-auto">
        आर्डर से संबंधित सहायता, थोक ऑर्डर, या कारीगर साझेदारी हेतु हमसे सीधे बात करें।
      </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      
      <!-- Left: Contact Details (5 cols) -->
      <div class="lg:col-span-5 space-y-6">
        
        <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm space-y-5">
          <h3 class="text-base font-bold text-gray-900 pb-3 border-b border-gray-100 flex items-center gap-2">
            <i class="fa-solid fa-headset text-brand-green"></i>
            सीधा संपर्क सूत्र
          </h3>

          <div class="space-y-4 text-xs">
            <div class="flex items-start gap-3.5">
              <div class="w-9 h-9 rounded-xl bg-emerald-50 text-brand-green flex items-center justify-center flex-shrink-0 text-sm">
                <i class="fa-solid fa-phone"></i>
              </div>
              <div>
                <span class="block text-gray-400 font-medium">फ़ोन / व्हाट्सएप सहायता:</span>
                <a href="tel:<?= APP_PHONE ?>" class="text-sm font-bold text-gray-900 hover:text-brand-green"><?= APP_PHONE ?></a>
                <p class="text-[11px] text-gray-500 mt-0.5">सोमवार से शनिवार (प्रातः 9:00 से सायं 7:00 बजे)</p>
              </div>
            </div>

            <div class="flex items-start gap-3.5">
              <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 text-sm">
                <i class="fa-solid fa-envelope"></i>
              </div>
              <div>
                <span class="block text-gray-400 font-medium">ईमेल सहायता:</span>
                <a href="mailto:<?= APP_EMAIL ?>" class="text-sm font-bold text-gray-900 hover:text-brand-green"><?= APP_EMAIL ?></a>
                <p class="text-[11px] text-gray-500 mt-0.5">24 घंटे के भीतर उत्तर दिया जाएगा</p>
              </div>
            </div>

            <div class="flex items-start gap-3.5">
              <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0 text-sm">
                <i class="fa-solid fa-industry"></i>
              </div>
              <div>
                <span class="block text-gray-400 font-medium">कारखाना एवं निर्माण केंद्र:</span>
                <p class="font-bold text-gray-900">एस. एस. इंडस्ट्रीज</p>
                <p class="text-gray-600 leading-relaxed mt-0.5"><?= APP_ADDRESS ?></p>
              </div>
            </div>

            <div class="flex items-start gap-3.5">
              <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0 text-sm">
                <i class="fa-solid fa-building"></i>
              </div>
              <div>
                <span class="block text-gray-400 font-medium">प्रधान कार्यालय (UPSIDA):</span>
                <p class="font-bold text-gray-900">G145 Phase 2 Industrial Area</p>
                <p class="text-gray-600 leading-relaxed mt-0.5">UPSIDA Sandila, Hardoi, Uttar Pradesh - 241204</p>
              </div>
            </div>
          </div>
        </div>

        <!-- India Post Pickup Guarantee -->
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 text-xs text-red-950 flex items-start gap-3">
          <i class="fa-solid fa-truck-fast text-red-600 text-lg mt-0.5"></i>
          <div>
            <h4 class="font-bold">स्पीड पोस्ट डिलीवरी गारंटी</h4>
            <p class="text-gray-600 text-[11px] mt-0.5">भारतीय डाक के माध्यम से दूर-दराज के गाँवों एवं कस्बों तक सुरक्षित डिलीवरी।</p>
          </div>
        </div>

      </div>

      <!-- Right: Contact Form (7 cols) -->
      <div class="lg:col-span-7 bg-white p-6 sm:p-8 rounded-3xl border border-gray-200 shadow-sm">
        <h3 class="text-base font-bold text-gray-900 pb-3 mb-6 border-b border-gray-100 flex items-center gap-2">
          <i class="fa-solid fa-paper-plane text-brand-green"></i>
          संदेश भेजें (Send us a Message)
        </h3>

        <form action="/contact.php" method="POST" class="space-y-4">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">आपका नाम <span class="text-red-500">*</span></label>
              <input type="text" name="name" required placeholder="उदा. सुरेश वर्मा"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">मोबाइल नंबर <span class="text-red-500">*</span></label>
              <input type="tel" name="phone" required pattern="[0-9]{10}" maxlength="10" placeholder="10-अंकीय नंबर"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green transition">
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">ईमेल पता</label>
            <input type="email" name="email" placeholder="name@example.com"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">विषय (Subject)</label>
            <select name="subject" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green transition">
              <option value="order">आर्डर या डिलीवरी स्थिति संबंधित</option>
              <option value="bulk">थोक खरीद एवं कृषि उपकरण सप्लाई (Bulk Order)</option>
              <option value="vendor">कारीगर / SHG विक्रेता ऑनबोर्डिंग</option>
              <option value="other">अन्य सहायता</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">आपका संदेश <span class="text-red-500">*</span></label>
            <textarea name="message" required rows="4" placeholder="कृपया अपना प्रश्न या आवश्यकता यहाँ लिखें..."
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green transition"></textarea>
          </div>

          <?= render_turnstile_widget() ?>

          <button type="submit"
            class="px-8 py-3 bg-brand-green hover:bg-emerald-800 text-white font-bold rounded-xl text-xs transition shadow-md flex items-center justify-center gap-2">
            <i class="fa-solid fa-paper-plane"></i> संदेश भेजें (Submit Message)
          </button>
        </form>
      </div>

    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
