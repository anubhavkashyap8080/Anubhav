<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_login();
$admin_user = get_logged_in_user();

// Clearance: super_admin, admin, support role or Support department
$is_support_authorized = in_array($admin_user['role'], ['super_admin', 'admin', 'support']) ||
                         strpos(strtolower($admin_user['department'] ?? ''), 'support') !== false;

if (!$is_support_authorized) {
    set_flash('error', 'लाइव चैट कंसोल केवल सुपर एडमिन, एडमिन और सपोर्ट टीम के लिए अधिकृत है।');
    header('Location: /admin/index.php');
    exit;
}

$page_title = 'Live Support Desk — GaunGaliHat';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title Bar -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-headset text-brand-green"></i>
      Live Customer Support Desk
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      Chat real-time one-on-one with shoppers and rural buyers to provide instant resolutions
    </p>
  </div>

  <div class="flex items-center gap-2">
    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-full text-xs font-semibold">
      <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
      Active Support Agent: <strong><?= htmlspecialchars($admin_user['name']) ?></strong> (<?= htmlspecialchars(ucfirst($admin_user['role'])) ?>)
    </span>
  </div>
</div>

<!-- Main Live Chat Application Frame -->
<div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-12" style="height: 640px;">
  
  <!-- Left Side: Sessions Directory (4 cols) -->
  <div class="lg:col-span-4 border-r border-gray-200 flex flex-col h-full bg-stone-50/50">
    
    <!-- Directory Header & Search -->
    <div class="p-4 border-b border-gray-200 space-y-3 bg-white">
      <div class="flex items-center justify-between">
        <h3 class="font-bold text-xs text-gray-800 flex items-center gap-1.5">
          <i class="fa-solid fa-comments text-brand-green"></i> Chat Sessions
        </h3>
        <span id="unreadTotalBadge" class="hidden px-2 py-0.5 bg-red-500 text-white rounded-full text-[10px] font-bold">
          0 new
        </span>
      </div>

      <div class="relative">
        <input type="text" id="chatSearchInput" onkeyup="filterSessionsList()" placeholder="Search by name, phone or email..."
          class="w-full pl-8 pr-3 py-1.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green">
        <i class="fa-solid fa-magnifying-glass text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 text-xs"></i>
      </div>

      <!-- Filters -->
      <div class="flex items-center gap-1.5 text-[11px] font-bold">
        <button type="button" onclick="setSessionFilter('all', this)" class="session-filter-btn px-2.5 py-1 rounded-lg bg-indigo-600 text-white shadow-xs">All</button>
        <button type="button" onclick="setSessionFilter('unread', this)" class="session-filter-btn px-2.5 py-1 rounded-lg text-gray-600 hover:bg-stone-200">Unread</button>
        <button type="button" onclick="setSessionFilter('active', this)" class="session-filter-btn px-2.5 py-1 rounded-lg text-gray-600 hover:bg-stone-200">Active</button>
        <button type="button" onclick="setSessionFilter('resolved', this)" class="session-filter-btn px-2.5 py-1 rounded-lg text-gray-600 hover:bg-stone-200">Resolved</button>
      </div>
    </div>

    <!-- Sessions List (Scrollable) -->
    <div id="sessionsListContainer" class="flex-1 overflow-y-auto divide-y divide-gray-100">
      <div class="p-8 text-center text-xs text-gray-400">Loading chat sessions...</div>
    </div>

  </div>

  <!-- Right Side: Active Chat Stream & Reply Box (8 cols) -->
  <div class="lg:col-span-8 flex flex-col h-full bg-white">
    
    <!-- Active Chat Header -->
    <div id="chatActiveHeader" class="p-4 border-b border-gray-200 flex items-center justify-between bg-stone-50/60">
      <div class="flex items-center gap-3">
        <div id="activeAvatarBox" class="w-10 h-10 rounded-2xl bg-brand-green text-white font-bold flex items-center justify-center text-sm shadow-xs">
          ?
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h3 id="activeCustomerName" class="font-bold text-sm text-gray-900">Please select a chat session</h3>
            <span id="activeStatusBadge" class="hidden px-2 py-0.2 rounded-full text-[10px] font-bold">Active</span>
          </div>
          <p id="activeCustomerContact" class="text-xs text-gray-400 mt-0.5">Click any chat thread from the left panel to begin replying</p>
        </div>
      </div>

      <div id="activeHeaderActions" class="hidden flex items-center gap-2">
        <button type="button" id="toggleStatusBtn" onclick="toggleActiveSessionStatus()"
          class="px-3 py-1.5 bg-white border border-gray-200 hover:bg-stone-100 text-gray-700 font-bold text-xs rounded-xl transition flex items-center gap-1.5 shadow-xs">
          <i class="fa-solid fa-check"></i> <span>Mark Resolved</span>
        </button>
      </div>
    </div>

    <!-- Message Bubbles Thread Area -->
    <div id="adminChatMessages" class="flex-1 p-5 overflow-y-auto space-y-3 bg-stone-50/30 text-xs">
      <div class="h-full flex flex-col items-center justify-center text-center text-gray-400 space-y-2">
        <div class="w-14 h-14 rounded-3xl bg-stone-100 flex items-center justify-center text-2xl text-stone-300">
          <i class="fa-solid fa-comments"></i>
        </div>
        <p class="font-bold text-sm text-gray-600">No Chat Selected</p>
        <p class="text-xs max-w-xs text-gray-400">Select a conversation from the left to view customer inquiries.</p>
      </div>
    </div>

    <!-- Canned Quick Responses Bar -->
    <div id="cannedResponseBar" class="hidden px-4 py-2 bg-stone-100 border-t border-gray-200 flex items-center gap-2 text-xs">
      <span class="text-[11px] font-bold text-gray-500 flex items-center gap-1 shrink-0">
        <i class="fa-solid fa-bolt text-amber-500"></i> Canned Responses:
      </span>
      <select onchange="insertCannedResponse(this)" class="flex-1 px-2.5 py-1 bg-white border border-gray-200 rounded-lg text-xs outline-none">
        <option value="">-- Select quick canned response --</option>
        <option value="Hello! Welcome to GaunGaliHat Support Desk. How may we assist you today?">Welcome greeting...</option>
        <option value="Your India Post parcel tracking status is being verified. Please give us 2 minutes.">Checking India Post parcel tracking...</option>
        <option value="Free all-India shipping is automatically applied on all orders of ₹499 and above.">Free shipping threshold (₹499)...</option>
        <option value="All our tools and produce are 100% authentic, hand-forged in Hardoi foundries and directly harvested.">Product quality & authenticity assurance...</option>
        <option value="Please share your 6-digit delivery PIN code so we can calculate exact arrival time.">Request delivery pincode...</option>
      </select>
    </div>

    <!-- Admin Reply Input Form -->
    <form id="adminReplyForm" onsubmit="handleSendAdminReply(event)" class="p-3.5 bg-white border-t border-gray-200 flex items-center gap-2">
      <input type="text" id="adminReplyInput" disabled placeholder="Select a chat session first..."
        class="flex-1 px-4 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green transition">
      <button type="submit" id="adminSendBtn" disabled
        class="px-5 py-2.5 bg-brand-green hover:bg-emerald-800 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5 shadow-md">
        <i class="fa-solid fa-paper-plane text-xs"></i> <span>Send</span>
      </button>
    </form>

  </div>

</div>

<script>
let currentSessionId = 0;
let currentFilter = 'all';
let sessionListTimer = null;
let threadTimer = null;
let lastAdminMsgId = 0;
let allSessionsCache = [];

function setSessionFilter(filter, btn) {
  currentFilter = filter;
  document.querySelectorAll('.session-filter-btn').forEach(b => {
    b.className = 'session-filter-btn px-2.5 py-1 rounded-lg text-gray-600 hover:bg-stone-200';
  });
  btn.className = 'session-filter-btn px-2.5 py-1 rounded-lg bg-indigo-600 text-white shadow-xs';
  loadAdminSessions();
}

function loadAdminSessions() {
  fetch('/ajax/chat_api.php?action=admin_sessions&filter=' + currentFilter)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        allSessionsCache = data.sessions || [];
        renderSessions(allSessionsCache);

        const unreadBadge = document.getElementById('unreadTotalBadge');
        if (data.total_unread > 0) {
          unreadBadge.innerText = data.total_unread + ' नए';
          unreadBadge.classList.remove('hidden');
        } else {
          unreadBadge.classList.add('hidden');
        }
      }
    });
}

function renderSessions(list) {
  const container = document.getElementById('sessionsListContainer');
  if (!list || list.length === 0) {
    container.innerHTML = '<div class="p-8 text-center text-xs text-gray-400">कोई सत्र नहीं मिला।</div>';
    return;
  }

  let html = '';
  list.forEach(s => {
    const isSelected = (s.id == currentSessionId);
    const hasUnread = (s.unread_admin > 0);
    const initial = s.user_name ? s.user_name.charAt(0).toUpperCase() : 'C';

    html += `
      <div onclick="selectChatSession(${s.id})"
        class="p-3.5 cursor-pointer transition flex items-start gap-3 ${isSelected ? 'bg-indigo-50/80 border-l-4 border-indigo-600' : (hasUnread ? 'bg-amber-50/40 hover:bg-stone-100' : 'hover:bg-stone-100')}">
        <div class="w-9 h-9 rounded-xl ${hasUnread ? 'bg-amber-500 text-stone-950 font-black' : 'bg-brand-green text-white'} flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
          ${initial}
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center justify-between mb-0.5">
            <span class="font-bold text-xs ${hasUnread ? 'text-stone-900 font-black' : 'text-gray-800'} truncate">${escapeHtml(s.user_name)}</span>
            <span class="text-[10px] text-gray-400 shrink-0">${formatTime(s.updated_at)}</span>
          </div>
          <p class="text-[11px] ${hasUnread ? 'text-stone-900 font-bold' : 'text-gray-500'} truncate">${escapeHtml(s.last_message || 'कोई संदेश नहीं')}</p>
          <div class="flex items-center gap-1.5 mt-1">
            <span class="text-[9px] px-1.5 py-0.2 rounded font-semibold ${s.status === 'resolved' ? 'bg-gray-200 text-gray-700' : 'bg-emerald-100 text-emerald-800'}">${s.status}</span>
            ${hasUnread ? `<span class="text-[9px] px-1.5 py-0.2 rounded-full bg-red-500 text-white font-bold">${s.unread_admin} नया</span>` : ''}
          </div>
        </div>
      </div>
    `;
  });
  container.innerHTML = html;
}

function filterSessionsList() {
  const query = document.getElementById('chatSearchInput').value.toLowerCase();
  if (!query) {
    renderSessions(allSessionsCache);
    return;
  }
  const filtered = allSessionsCache.filter(s => 
    (s.user_name && s.user_name.toLowerCase().includes(query)) ||
    (s.user_phone && s.user_phone.includes(query)) ||
    (s.user_email && s.user_email.toLowerCase().includes(query))
  );
  renderSessions(filtered);
}

function selectChatSession(id) {
  currentSessionId = id;
  lastAdminMsgId = 0;
  loadAdminSessions();

  document.getElementById('adminReplyInput').disabled = false;
  document.getElementById('adminSendBtn').disabled = false;
  document.getElementById('adminReplyInput').placeholder = 'यहाँ ग्राहक को उत्तर लिखें...';
  document.getElementById('cannedResponseBar').classList.remove('hidden');
  document.getElementById('activeHeaderActions').classList.remove('hidden');

  pollThread(true);

  if (threadTimer) clearInterval(threadTimer);
  threadTimer = setInterval(() => pollThread(false), 2500);
}

function pollThread(fullReload = false) {
  if (!currentSessionId) return;

  fetch(`/ajax/chat_api.php?action=admin_get_thread&session_id=${currentSessionId}&last_id=${lastAdminMsgId}`)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const s = data.session;
        document.getElementById('activeCustomerName').innerText = s.user_name;
        document.getElementById('activeCustomerContact').innerText = (s.user_phone ? '📞 ' + s.user_phone : '') + (s.user_email ? ' • ✉️ ' + s.user_email : '');
        document.getElementById('activeAvatarBox').innerText = s.user_name.charAt(0).toUpperCase();

        const badge = document.getElementById('activeStatusBadge');
        badge.innerText = s.status;
        badge.className = 'px-2 py-0.2 rounded-full text-[10px] font-bold ' + (s.status === 'resolved' ? 'bg-gray-200 text-gray-700' : 'bg-emerald-100 text-emerald-800');
        badge.classList.remove('hidden');

        const toggleBtn = document.getElementById('toggleStatusBtn');
        toggleBtn.innerHTML = s.status === 'resolved' ? '<i class="fa-solid fa-rotate-left"></i> Reopen Session' : '<i class="fa-solid fa-check"></i> Mark Resolved';

        const area = document.getElementById('adminChatMessages');
        if (fullReload) {
          area.innerHTML = '';
        }

        if (data.messages && data.messages.length > 0) {
          let newAppended = false;
          data.messages.forEach(m => {
            lastAdminMsgId = Math.max(lastAdminMsgId, parseInt(m.id));

            // Strict de-duplication check: avoid double bubbles
            if (document.getElementById('admin-msg-' + m.id)) {
              return;
            }

            const isAgent = (m.sender_type !== 'user');
            const isSystem = (m.sender_type === 'system');
            const div = document.createElement('div');
            div.id = 'admin-msg-' + m.id;

            if (isSystem) {
              div.className = 'text-center py-1';
              div.innerHTML = `<span class="text-[10px] text-gray-400 italic bg-white px-2.5 py-0.5 rounded-full border border-gray-200">${escapeHtml(m.message)}</span>`;
            } else {
              div.className = isAgent ? 'flex justify-end' : 'flex justify-start';
              div.innerHTML = `
                <div class="max-w-[75%] p-3 rounded-2xl ${isAgent ? 'bg-indigo-600 text-white rounded-br-xs shadow-xs' : 'bg-white border border-gray-200 text-gray-800 rounded-bl-xs shadow-xs'}">
                  <div class="flex items-center justify-between gap-3 text-[10px] ${isAgent ? 'text-indigo-200' : 'text-emerald-800'} font-bold mb-1">
                    <span>${escapeHtml(m.sender_name)}</span>
                    <span class="${isAgent ? 'text-indigo-200' : 'text-gray-400'}">${formatTime(m.created_at)}</span>
                  </div>
                  <div class="leading-relaxed whitespace-pre-line text-xs">${escapeHtml(m.message)}</div>
                </div>
              `;
            }

            area.appendChild(div);
            newAppended = true;
          });

          if (newAppended) {
            area.scrollTop = area.scrollHeight;
          }
        }
      }
    });
}

let isSendingAdmin = false;
function handleSendAdminReply(e) {
  e.preventDefault();
  if (isSendingAdmin) return;

  const input = document.getElementById('adminReplyInput');
  const btn = document.getElementById('adminSendBtn');
  const msg = input.value.trim();
  if (!msg || !currentSessionId) return;

  isSendingAdmin = true;
  if (btn) btn.disabled = true;

  const formData = new FormData();
  formData.append('session_id', currentSessionId);
  formData.append('message', msg);

  input.value = '';

  fetch('/ajax/chat_api.php?action=admin_reply', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      pollThread(false);
      loadAdminSessions();
    }
  })
  .finally(() => {
    isSendingAdmin = false;
    if (btn) btn.disabled = false;
    input.focus();
  });
}

function insertCannedResponse(select) {
  if (select.value) {
    document.getElementById('adminReplyInput').value = select.value;
    select.value = '';
    document.getElementById('adminReplyInput').focus();
  }
}

function toggleActiveSessionStatus() {
  if (!currentSessionId) return;
  const badge = document.getElementById('activeStatusBadge');
  const newStatus = (badge.innerText === 'resolved') ? 'active' : 'resolved';

  const formData = new FormData();
  formData.append('session_id', currentSessionId);
  formData.append('status', newStatus);

  fetch('/ajax/chat_api.php?action=admin_toggle_status', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      pollThread(false);
      loadAdminSessions();
    }
  });
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.innerText = text;
  return div.innerHTML;
}

function formatTime(isoStr) {
  if (!isoStr) return '';
  const d = new Date(isoStr.replace(' ', 'T'));
  return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

// Initial boot
loadAdminSessions();
sessionListTimer = setInterval(loadAdminSessions, 4000);
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
