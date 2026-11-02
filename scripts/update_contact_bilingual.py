# -*- coding: utf-8 -*-
"""
Update contact.php to have English default language, main headlines in English,
and clean bilingual toggle with Hindi support.
"""

contact_code = """<?php
$page_title = 'Customer Support & Help Desk — GanvGaliHat';
$page_desc = 'Contact GanvGaliHat customer support desk. Inquiries regarding order tracking, artisan onboarding, and rural wholesale orders.';

require_once __DIR__ . '/includes/header.php';

$sent = false;
$ticket_created_num = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    csrf_protect();

    $sender_name = clean_input($_POST['name'] ?? '');
    $sender_phone = clean_input($_POST['phone'] ?? '');
    $sender_email = clean_input($_POST['email'] ?? '');
    $subject_key = clean_input($_POST['subject'] ?? 'order');
    $sender_message = clean_input($_POST['message'] ?? '');

    // Rate limiting per IP and phone
    $client_ip = get_client_ip();
    if (!check_rate_limit('contact_ip', $client_ip, 5, 900) || !check_rate_limit('contact_phone', $sender_phone, 3, 900)) {
        set_flash('error', 'Too many requests. Please try again after 15 minutes. / बहुत अधिक संदेश अनुरोध। कृपया 15 मिनट बाद पुनः प्रयास करें।');
        header('Location: /contact.php');
        exit;
    }

    // Turnstile CAPTCHA verification
    if (!verify_turnstile_captcha()) {
        set_flash('error', 'Security CAPTCHA verification failed. Please try again. / सुरक्षा सत्यापन विफल रहा।');
        header('Location: /contact.php');
        exit;
    }

    $cat_map = [
        'order' => 'Order Tracking & Delivery',
        'bulk' => 'Bulk Purchase / Wholesale',
        'vendor' => 'Artisan Onboarding',
        'other' => 'General Inquiry'
    ];
    $category = $cat_map[$subject_key] ?? 'General Inquiry';
    $subject_label = "Website Contact Form: " . $category;

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
            set_flash('success', "Thank you $sender_name! Your message has been logged as Support Ticket #$ticket_num. Our desk will contact you within 24 hours.");
            $sent = true;
        } catch (Exception $e) {
            set_flash('error', 'Error sending message: ' . $e->getMessage());
        }
    }
}
?>

<!-- Breadcrumbs & Language Switcher Bar -->
<div class="bg-stone-100 border-b border-stone-200 py-3">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
    <div class="flex items-center gap-2 text-gray-500">
      <a href="/" class="hover:text-emerald-800 font-medium">
        <span class="lang-en">Home</span>
        <span class="lang-hi hidden">होम</span>
      </a>
      <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400"></i>
      <span class="text-stone-900 font-bold">
        <span class="lang-en">Customer Support Desk</span>
        <span class="lang-hi hidden">ग्राहक सहायता एवं संपर्क</span>
      </span>
    </div>

    <!-- Bilingual Switcher Toggle (English Default) -->
    <div class="flex items-center gap-2">
      <span class="text-[11px] font-bold text-gray-400">Language:</span>
      <?= render_bilingual_toggle('en') ?>
    </div>
  </div>
</div>

<div class="bg-stone-50 min-h-screen py-10 sm:py-14">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="text-center mb-10">
      <span class="text-xs font-bold text-emerald-800 bg-emerald-50 px-3.5 py-1 rounded-full border border-emerald-200">
        <span class="lang-en">Help & Customer Support Desk</span>
        <span class="lang-hi hidden">सहायता एवं ग्राहक सेवा</span>
      </span>
      <h1 class="text-3xl sm:text-4xl font-black text-gray-900 mt-2">
        <span class="lang-en">Customer Support & Help Desk</span>
        <span class="lang-hi hidden">हमसे संपर्क करें (Get in Touch)</span>
      </h1>
      <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-lg mx-auto">
        <span class="lang-en">Have questions regarding order tracking, artisan onboarding, or bulk supply? We are here to assist you.</span>
        <span class="lang-hi hidden">आर्डर से संबंधित सहायता, थोक ऑर्डर, या कारीगर साझेदारी हेतु हमसे सीधे बात करें।</span>
      </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      
      <!-- Left: Contact Details (5 cols) -->
      <div class="lg:col-span-5 space-y-6">
        
        <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm space-y-5">
          <h3 class="text-base font-bold text-gray-900 pb-3 border-b border-gray-100 flex items-center gap-2">
            <i class="fa-solid fa-headset text-emerald-700"></i>
            <span class="lang-en">Direct Contact Channels</span>
            <span class="lang-hi hidden">सीधा संपर्क सूत्र</span>
          </h3>

          <div class="space-y-4 text-xs">
            <div class="flex items-start gap-3.5">
              <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0 text-sm">
                <i class="fa-solid fa-phone"></i>
              </div>
              <div>
                <span class="block text-gray-400 font-medium">
                  <span class="lang-en">Phone & WhatsApp Support:</span>
                  <span class="lang-hi hidden">फ़ोन / व्हाट्सएप सहायता:</span>
                </span>
                <a href="tel:<?= APP_PHONE ?>" class="text-sm font-bold text-gray-900 hover:text-emerald-700"><?= APP_PHONE ?></a>
                <p class="text-[11px] text-gray-500 mt-0.5">
                  <span class="lang-en">Monday to Saturday (9:00 AM – 7:00 PM IST)</span>
                  <span class="lang-hi hidden">सोमवार से शनिवार (प्रातः 9:00 से सायं 7:00 बजे)</span>
                </p>
              </div>
            </div>

            <div class="flex items-start gap-3.5">
              <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 text-sm">
                <i class="fa-solid fa-envelope"></i>
              </div>
              <div>
                <span class="block text-gray-400 font-medium">
                  <span class="lang-en">Official Email Support:</span>
                  <span class="lang-hi hidden">ईमेल सहायता:</span>
                </span>
                <a href="mailto:<?= APP_EMAIL ?>" class="text-sm font-bold text-gray-900 hover:text-emerald-700"><?= APP_EMAIL ?></a>
                <p class="text-[11px] text-gray-500 mt-0.5">
                  <span class="lang-en">Guaranteed response within 24 hours</span>
                  <span class="lang-hi hidden">24 घंटे के भीतर उत्तर दिया जाएगा</span>
                </p>
              </div>
            </div>

            <div class="flex items-start gap-3.5">
              <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0 text-sm">
                <i class="fa-solid fa-building"></i>
              </div>
              <div>
                <span class="block text-gray-400 font-medium">
                  <span class="lang-en">Head Office & Central Fulfillment Hub:</span>
                  <span class="lang-hi hidden">प्रधान कार्यालय (UPSIDA):</span>
                </span>
                <p class="font-bold text-gray-900">Kalyani agrofood pvt ltd</p>
                <p class="text-gray-600 leading-relaxed mt-0.5">G145 Phase 2 Industrial Area, UPSIDA Sandila, Hardoi, Uttar Pradesh - 241204</p>
              </div>
            </div>

            <div class="flex items-start gap-3.5">
              <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0 text-sm">
                <i class="fa-solid fa-industry"></i>
              </div>
              <div>
                <span class="block text-gray-400 font-medium">
                  <span class="lang-en">Forge & Manufacturing Works:</span>
                  <span class="lang-hi hidden">कारखाना एवं निर्माण केंद्र:</span>
                </span>
                <p class="font-bold text-gray-900">Kalyani agrofood pvt ltd (Nevada Works)</p>
                <p class="text-gray-600 leading-relaxed mt-0.5">Madhoganj Baghauli Road, Nevada, Hardoi, UP - 241126</p>
              </div>
            </div>
          </div>
        </div>

        <!-- India Post Pickup Guarantee -->
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 text-xs text-red-950 flex items-start gap-3">
          <i class="fa-solid fa-truck-fast text-red-600 text-lg mt-0.5"></i>
          <div>
            <h4 class="font-bold">
              <span class="lang-en">Speed Post Pan-India Dispatch</span>
              <span class="lang-hi hidden">स्पीड पोस्ट डिलीवरी गारंटी</span>
            </h4>
            <p class="text-gray-600 text-[11px] mt-0.5">
              <span class="lang-en">Direct parcel dispatch to 19,000+ PIN codes across all Indian states.</span>
              <span class="lang-hi hidden">भारतीय डाक के माध्यम से दूर-दराज के गाँवों एवं कस्बों तक सुरक्षित डिलीवरी।</span>
            </p>
          </div>
        </div>

      </div>

      <!-- Right: Contact Form (7 cols) -->
      <div class="lg:col-span-7 bg-white p-6 sm:p-8 rounded-3xl border border-gray-200 shadow-sm">
        <h3 class="text-base font-bold text-gray-900 pb-3 mb-6 border-b border-gray-100 flex items-center gap-2">
          <i class="fa-solid fa-paper-plane text-emerald-700"></i>
          <span class="lang-en">Send us a Message</span>
          <span class="lang-hi hidden">संदेश भेजें</span>
        </h3>

        <form action="/contact.php" method="POST" class="space-y-4">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                <span class="lang-en">Your Full Name <span class="text-red-500">*</span></span>
                <span class="lang-hi hidden">आपका नाम <span class="text-red-500">*</span></span>
              </label>
              <input type="text" name="name" required placeholder="e.g. Suresh Verma"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-emerald-700 transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                <span class="lang-en">Mobile Phone Number <span class="text-red-500">*</span></span>
                <span class="lang-hi hidden">मोबाइल नंबर <span class="text-red-500">*</span></span>
              </label>
              <input type="tel" name="phone" required pattern="[0-9]{10}" maxlength="10" placeholder="10-digit mobile"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-emerald-700 transition">
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">
              <span class="lang-en">Email Address</span>
              <span class="lang-hi hidden">ईमेल पता</span>
            </label>
            <input type="email" name="email" placeholder="name@example.com"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-emerald-700 transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">
              <span class="lang-en">Subject / Topic</span>
              <span class="lang-hi hidden">विषय (Subject)</span>
            </label>
            <select name="subject" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-emerald-700 transition">
              <option value="order">Order Tracking & Delivery Status</option>
              <option value="bulk">Bulk Order / Agricultural Tools Supply</option>
              <option value="vendor">Artisan / SHG Seller Onboarding</option>
              <option value="other">General Customer Support</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">
              <span class="lang-en">Your Message <span class="text-red-500">*</span></span>
              <span class="lang-hi hidden">आपका संदेश <span class="text-red-500">*</span></span>
            </label>
            <textarea name="message" required rows="4" placeholder="Please describe your query or requirement..."
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-emerald-700 transition"></textarea>
          </div>

          <?= render_turnstile_widget() ?>

          <button type="submit"
            class="px-8 py-3 bg-emerald-800 hover:bg-emerald-900 text-white font-bold rounded-xl text-xs transition shadow-md flex items-center justify-center gap-2">
            <i class="fa-solid fa-paper-plane"></i>
            <span class="lang-en">Submit Message</span>
            <span class="lang-hi hidden">संदेश भेजें</span>
          </button>
        </form>
      </div>

    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
"""

with open(r'C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat\contact.php', 'w', encoding='utf-8') as f:
    f.write(contact_code)

print("Updated contact.php")
