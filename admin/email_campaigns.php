<?php
/**
 * GaunGaliHat Email & Marketing Campaign Center
 * Send Welcome Emails, Seasonal Promotions, and Broadcast Updates
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strict Admin Authorization
require_admin();

$page_title = 'Email Campaigns & Marketing Desk — Super Admin';
$flash = get_flash();

$test_email_recipient = defined('OWNER_ALERT_EMAIL') && OWNER_ALERT_EMAIL ? OWNER_ALERT_EMAIL : 'info@ganvgalihat.com';

// Handle Campaign Dispatch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security token invalid. Please refresh the page and try again.');
        header('Location: /admin/email_campaigns.php');
        exit;
    }

    $action = clean_input($_POST['action']);
    $subject = clean_input($_POST['subject'] ?? '');
    $headline = clean_input($_POST['headline'] ?? '');
    $content_body = trim((string)($_POST['content_body'] ?? ''));
    $cta_text = clean_input($_POST['cta_text'] ?? 'Shop Handcrafted Products');
    $cta_url = clean_input($_POST['cta_url'] ?? (defined('SITE_URL') ? SITE_URL : 'https://ganvgalihat.com'));
    $template_type = clean_input($_POST['template_type'] ?? 'marketing');
    $target_audience = clean_input($_POST['target_audience'] ?? 'test');

    if (empty($subject) || empty($content_body)) {
        set_flash('error', 'Subject line and message body cannot be empty.');
        header('Location: /admin/email_campaigns.php');
        exit;
    }

    // Build Email HTML Template
    $site_url = defined('SITE_URL') ? SITE_URL : 'https://ganvgalihat.com';
    $site_host = parse_url($site_url, PHP_URL_HOST) ?: 'ganvgalihat.com';
    $brand_name = defined('APP_NAME') ? APP_NAME : 'GaunGaliHat';

    $accent_color = '#059669'; // brand green
    $badge_text = 'Authentic Rural Commerce';
    if ($template_type === 'festival') {
        $accent_color = '#d97706';
        $badge_text = 'Festival & Seasonal Special';
    } elseif ($template_type === 'announcement') {
        $accent_color = '#4f46e5';
        $badge_text = 'Official Notice & Update';
    }

    $email_html = "
    <!DOCTYPE html>
    <html>
    <head><meta charset='utf-8'></head>
    <body style='margin:0; padding:0; background-color:#f9fafb; font-family:-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica, Arial, sans-serif;'>
      <div style='max-width:600px; margin:24px auto; background:#ffffff; border-radius:18px; border:1px solid #e5e7eb; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.05);'>
        
        <!-- Header Banner -->
        <div style='background-color:#022c22; padding:32px 24px; text-align:center;'>
          <h1 style='color:#ffffff; font-size:26px; margin:0 0 6px 0; font-weight:800; letter-spacing:-0.5px;'>{$brand_name}</h1>
          <p style='color:#fde68a; font-size:13px; font-weight:bold; margin:0;'>Hunar desh ka, ganv se ghar tak</p>
        </div>

        <!-- Body Content -->
        <div style='padding:32px 28px; color:#1f2937;'>
          <div style='display:inline-block; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:1px; color:{$accent_color}; background-color:#ecfdf5; padding:4px 10px; border-radius:999px; margin-bottom:16px;'>
            {$badge_text}
          </div>

          <h2 style='font-size:20px; font-weight:800; color:#111827; margin:0 0 16px 0;'>
            " . htmlspecialchars($headline ?: $subject) . "
          </h2>

          <div style='font-size:14px; line-height:1.7; color:#374151; margin-bottom:28px;'>
            " . nl2br(htmlspecialchars($content_body)) . "
          </div>

          <!-- Call to Action -->
          <div style='text-align:center; margin:32px 0 20px 0;'>
            <a href='" . htmlspecialchars($cta_url) . "' style='display:inline-block; background-color:{$accent_color}; color:#ffffff; font-size:14px; font-weight:bold; text-decoration:none; padding:14px 32px; border-radius:12px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);'>
              " . htmlspecialchars($cta_text) . "
            </a>
          </div>
        </div>

        <!-- Footer -->
        <div style='background-color:#f8fafc; border-top:1px solid #e2e8f0; padding:24px 28px; text-align:center; font-size:11px; color:#64748b;'>
          <p style='margin:0 0 8px 0; font-weight:600;'>Kalyani agrofood pvt ltd • Hardoi & Awadh Rural Artisan Network</p>
          <p style='margin:0 0 12px 0;'>You received this email because you are a registered user of GaunGaliHat marketplace.</p>
          <p style='margin:0;'>© " . date('Y') . " {$brand_name}. All rights reserved.</p>
        </div>

      </div>
    </body>
    </html>";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$brand_name} <updates@{$site_host}>\r\n";
    $headers .= "Reply-To: info@{$site_host}\r\n";

    if ($action === 'send_test') {
        $test_email = clean_input($_POST['test_email_address'] ?? $test_email_recipient);
        if (!filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
            set_flash('error', 'Invalid test email address.');
            header('Location: /admin/email_campaigns.php');
            exit;
        }

        @mail($test_email, "[TEST] " . $subject, $email_html, $headers);
        log_audit_action('email_campaign_test', 'email', 'campaign', null, 'Sent test email campaign to ' . $test_email);
        set_flash('success', 'Test email dispatched to ' . htmlspecialchars($test_email) . '! Check your inbox.');
        header('Location: /admin/email_campaigns.php');
        exit;
    }

    if ($action === 'broadcast_live') {
        $target_sql = "SELECT email, name FROM users WHERE status = 'active' AND email IS NOT NULL AND email != ''";
        if ($target_audience === 'customers') {
            $target_sql .= " AND role = 'customer'";
        } elseif ($target_audience === 'sellers') {
            $target_sql .= " AND role = 'vendor'";
        }

        try {
            $stmt = $pdo->query($target_sql);
            $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $count = 0;

            foreach ($recipients as $recipient) {
                if (filter_var($recipient['email'], FILTER_VALIDATE_EMAIL)) {
                    @mail($recipient['email'], $subject, $email_html, $headers);
                    $count++;
                }
            }

            log_audit_action('email_campaign_broadcast', 'email', 'campaign', null, "Broadcast email '{$subject}' sent to {$count} recipients in audience '{$target_audience}'");
            set_flash('success', "Campaign broadcast completed! Successfully dispatched to {$count} recipients.");
            header('Location: /admin/email_campaigns.php');
            exit;
        } catch (Throwable $e) {
            set_flash('error', 'Broadcast error: ' . $e->getMessage());
            header('Location: /admin/email_campaigns.php');
            exit;
        }
    }
}

// Fetch stats for recipient counts
$total_active_users = 0;
$total_customers = 0;
$total_sellers = 0;
try {
    $total_active_users = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
    $total_customers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active' AND role = 'customer'")->fetchColumn();
    $total_sellers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active' AND role = 'vendor'")->fetchColumn();
} catch (Throwable $e) {}

require_once __DIR__ . '/admin_header.php';
?>

<div class="space-y-6">

  <!-- Header Banner -->
  <div class="bg-gradient-to-r from-emerald-950 via-teal-900 to-indigo-950 text-white rounded-3xl p-6 sm:p-8 shadow-xl border border-emerald-900/50 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div class="space-y-2">
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400/20 text-amber-300 text-xs font-black uppercase tracking-wider border border-amber-400/30">
        <i class="fa-solid fa-paper-plane"></i> Email & Marketing Desk
      </div>
      <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">Broadcast & Promotional Campaigns</h1>
      <p class="text-emerald-200 text-xs sm:text-sm max-w-2xl leading-relaxed">
        Send transactional welcome notifications, marketing campaigns, festive offers, and artisan updates to registered shoppers and artisans.
      </p>
    </div>

    <!-- Audience Stat Chips -->
    <div class="grid grid-cols-3 gap-3 text-center">
      <div class="bg-white/10 rounded-2xl p-3 border border-white/10">
        <div class="text-lg font-black text-amber-300"><?= $total_active_users ?></div>
        <div class="text-[10px] uppercase font-bold text-emerald-200">Total Users</div>
      </div>
      <div class="bg-white/10 rounded-2xl p-3 border border-white/10">
        <div class="text-lg font-black text-white"><?= $total_customers ?></div>
        <div class="text-[10px] uppercase font-bold text-emerald-200">Customers</div>
      </div>
      <div class="bg-white/10 rounded-2xl p-3 border border-white/10">
        <div class="text-lg font-black text-white"><?= $total_sellers ?></div>
        <div class="text-[10px] uppercase font-bold text-emerald-200">Sellers</div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
    
    <!-- Left Composer Form (7 cols) -->
    <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-8 border border-gray-200 shadow-sm space-y-6">
      <h2 class="text-base font-extrabold text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-4">
        <i class="fa-solid fa-pen-nib text-brand-green"></i> Compose Campaign Message
      </h2>

      <form method="POST" id="campaignForm" class="space-y-4">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="text-xs font-bold uppercase tracking-wider text-gray-600 block mb-1">
              Campaign Template
            </label>
            <select name="template_type" id="template_type" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs font-bold text-gray-800">
              <option value="marketing">Standard Marketing & Promotions</option>
              <option value="festival">Festival Offer & Harvest Special</option>
              <option value="announcement">Platform Announcement & Policy Notice</option>
            </select>
          </div>

          <div>
            <label class="text-xs font-bold uppercase tracking-wider text-gray-600 block mb-1">
              Target Audience
            </label>
            <select name="target_audience" id="target_audience" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs font-bold text-gray-800">
              <option value="all">All Active Users (<?= $total_active_users ?>)</option>
              <option value="customers" selected>Registered Shoppers (<?= $total_customers ?>)</option>
              <option value="sellers">Artisans & Sellers (<?= $total_sellers ?>)</option>
            </select>
          </div>
        </div>

        <div>
          <label class="text-xs font-bold uppercase tracking-wider text-gray-600 block mb-1">
            Email Subject Line <span class="text-red-500">*</span>
          </label>
          <input type="text" name="subject" id="subject_input" required placeholder="e.g. Special Village Harvest: Fresh Stone-Ground Awadh Staples Now Available"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs font-bold text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <div>
          <label class="text-xs font-bold uppercase tracking-wider text-gray-600 block mb-1">
            Headline Banner
          </label>
          <input type="text" name="headline" id="headline_input" placeholder="e.g. Authentic Rural Staples Direct From Awadh Farmers"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs font-bold text-gray-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <div>
          <label class="text-xs font-bold uppercase tracking-wider text-gray-600 block mb-1">
            Message Body (Supports Paragraphs) <span class="text-red-500">*</span>
          </label>
          <textarea name="content_body" id="body_input" rows="7" required placeholder="Dear Patron, we are thrilled to bring you hand-picked seasonal products..."
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs leading-relaxed text-gray-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="text-xs font-bold uppercase tracking-wider text-gray-600 block mb-1">
              Button Label (CTA)
            </label>
            <input type="text" name="cta_text" id="cta_text_input" value="Shop Now at GaunGaliHat"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs font-bold text-gray-800">
          </div>

          <div>
            <label class="text-xs font-bold uppercase tracking-wider text-gray-600 block mb-1">
              Button URL (Destination)
            </label>
            <input type="text" name="cta_url" id="cta_url_input" value="<?= htmlspecialchars(defined('SITE_URL') ? SITE_URL : 'https://ganvgalihat.com') ?>/products.php"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs font-mono text-gray-800">
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
          <div class="flex items-center gap-2 w-full sm:w-auto">
            <input type="email" name="test_email_address" value="<?= htmlspecialchars($test_email_recipient) ?>" placeholder="Test email"
              class="px-3 py-2 bg-stone-50 border border-gray-300 rounded-xl text-xs w-48 font-mono">
            <button type="submit" name="action" value="send_test"
              class="px-4 py-2.5 bg-stone-800 hover:bg-black text-white font-bold text-xs rounded-xl transition shadow-xs flex items-center gap-1.5 whitespace-nowrap">
              <i class="fa-solid fa-vial"></i> Send Test
            </button>
          </div>

          <button type="submit" name="action" value="broadcast_live" onclick="return confirm('Are you sure you want to broadcast this email campaign to all selected recipients?');"
            class="w-full sm:w-auto px-6 py-2.5 bg-brand-green hover:bg-emerald-800 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-md transition flex items-center justify-center gap-2">
            <i class="fa-solid fa-paper-plane"></i> Broadcast Campaign
          </button>
        </div>
      </form>
    </div>

    <!-- Right Preview Box (5 cols) -->
    <div class="lg:col-span-5 bg-white rounded-3xl p-6 sm:p-8 border border-gray-200 shadow-sm space-y-4">
      <div class="flex items-center justify-between border-b border-gray-100 pb-3">
        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 flex items-center gap-2">
          <i class="fa-solid fa-eye text-emerald-600"></i> Live Email Preview
        </h3>
        <span class="text-[10px] bg-stone-100 text-gray-500 px-2 py-0.5 rounded-full font-bold">Desktop & Mobile View</span>
      </div>

      <!-- Mock Email Frame -->
      <div class="border border-gray-200 rounded-2xl overflow-hidden shadow-xs bg-stone-50">
        <!-- Mock Window Bar -->
        <div class="bg-gray-200 px-3 py-2 flex items-center gap-1.5 text-[10px] text-gray-500">
          <span class="w-2 h-2 rounded-full bg-red-400"></span>
          <span class="w-2 h-2 rounded-full bg-amber-400"></span>
          <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
          <span class="ml-2 font-mono text-gray-600" id="preview_subject_header">Preview: Subject</span>
        </div>

        <div class="p-4 bg-white space-y-4">
          <div class="bg-emerald-950 text-white text-center py-4 px-3 rounded-xl">
            <div class="font-extrabold text-base tracking-tight">GaunGaliHat</div>
            <div class="text-[10px] text-amber-300 font-bold">Hunar desh ka, ganv se ghar tak</div>
          </div>

          <div>
            <div class="text-xs font-black text-gray-900" id="preview_headline">Headline will appear here</div>
            <p class="text-[11px] text-gray-600 leading-relaxed mt-2" id="preview_body">
              Your message body preview will dynamically render here as you type in the composer form.
            </p>
          </div>

          <div class="text-center pt-2">
            <span class="inline-block px-4 py-2 bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-xs" id="preview_btn">
              Shop Now at GaunGaliHat
            </span>
          </div>

          <div class="text-center text-[10px] text-gray-400 pt-3 border-t border-gray-100">
            © <?= date('Y') ?> GaunGaliHat • A Unit of Kalyani agrofood pvt ltd
          </div>
        </div>
      </div>
    </div>

  </div>

</div>

<script>
// Dynamic live preview bindings
const subjIn = document.getElementById('subject_input');
const headIn = document.getElementById('headline_input');
const bodyIn = document.getElementById('body_input');
const ctaIn  = document.getElementById('cta_text_input');

const prevSubj = document.getElementById('preview_subject_header');
const prevHead = document.getElementById('preview_headline');
const prevBody = document.getElementById('preview_body');
const prevBtn  = document.getElementById('preview_btn');

function updateLivePreview() {
  prevSubj.innerText = subjIn.value || 'Preview: Subject';
  prevHead.innerText = headIn.value || subjIn.value || 'Campaign Headline';
  prevBody.innerText = bodyIn.value || 'Your message body preview will dynamically render here as you type in the composer form.';
  prevBtn.innerText  = ctaIn.value || 'Shop Now at GaunGaliHat';
}

subjIn.addEventListener('input', updateLivePreview);
headIn.addEventListener('input', updateLivePreview);
bodyIn.addEventListener('input', updateLivePreview);
ctaIn.addEventListener('input', updateLivePreview);
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
