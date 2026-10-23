<!-- GaunGaliHat Real-time Customer Live Chat & WhatsApp Widget -->
<div id="liveChatWidgetContainer" class="fixed bottom-5 right-4 z-50 flex flex-col items-end gap-2.5 font-sans">

  <!-- WhatsApp Quick Support Floating Button -->
  <a href="https://wa.me/917897492100?text=नमस्ते%20GaunGaliHat!%20मुझे%20सहायता%20चाहिए।"
     target="_blank" rel="noopener noreferrer"
     class="flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full shadow-lg transition-transform hover:scale-105 text-xs font-bold"
     title="सीधे व्हाट्सएप पर बात करें">
    <i class="fa-brands fa-whatsapp text-lg"></i>
    <span class="hidden sm:inline">WhatsApp सहायता</span>
  </a>

  <!-- Live Chat Trigger Pill -->
  <button type="button" id="openLiveChatBtn"
    onclick="toggleLiveChatBox()"
    class="flex items-center gap-2.5 px-4 py-2.5 bg-gradient-to-r from-emerald-800 to-brand-green text-white rounded-full shadow-2xl hover:shadow-emerald-900/30 transition-all hover:scale-105 active:scale-95 text-xs font-bold border border-emerald-500/40 relative">
    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping absolute top-2 left-2"></span>
    <i class="fa-solid fa-headset text-base"></i>
    <span>लाइव चैट (Live Support)</span>
    <span id="chatCustomerUnread" class="hidden px-1.5 py-0.2 bg-red-500 text-white text-[10px] font-black rounded-full shadow-xs">1</span>
  </button>

  <!-- Live Chat Popup Card (Responsive, Never Cut Off) -->
  <div id="liveChatPopupBox"
    class="hidden w-[92vw] sm:w-96 max-w-sm bg-white rounded-3xl border border-gray-200 shadow-2xl flex flex-col overflow-hidden animate-in fade-in slide-in-from-bottom-5 duration-200"
    style="height: 480px; max-height: 80vh;">
    
    <!-- Chat Header -->
    <div class="bg-gradient-to-r from-emerald-950 via-slate-900 to-emerald-900 text-white p-4 flex items-center justify-between">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-xl bg-emerald-800 text-white flex items-center justify-center font-bold text-sm">
          🌾
        </div>
        <div>
          <h4 class="text-xs font-bold">गाँव गली हाट लाइव सहायता</h4>
          <span class="text-[10px] text-emerald-300 flex items-center gap-1 font-semibold">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> ऑनलाइन • 2 मिनट में उत्तर
          </span>
        </div>
      </div>
      <button type="button" onclick="toggleLiveChatBox()" class="text-gray-400 hover:text-white p-1">
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>
    </div>

    <!-- Messages Container -->
    <div id="chatMessagesArea" class="flex-1 p-3.5 overflow-y-auto space-y-3 bg-stone-50 text-xs">
      <div class="text-center py-2">
        <span class="text-[10px] text-gray-400 bg-white px-2.5 py-1 rounded-full border border-gray-200">आज की बातचीत</span>
      </div>
    </div>

    <!-- Guest Identity Form (If not logged in and first time) -->
    <?php if (!is_logged_in() && empty($_SESSION['chat_guest_name'])): ?>
      <div id="chatGuestFields" class="p-2.5 bg-amber-50 border-t border-amber-100 flex gap-1.5 text-xs">
        <input type="text" id="chatGuestName" placeholder="आपका नाम *" required
          class="w-1/2 px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-xs outline-none focus:border-brand-green">
        <input type="tel" id="chatGuestPhone" placeholder="मोबाइल नंबर *" required maxlength="10"
          class="w-1/2 px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-xs outline-none focus:border-brand-green">
      </div>
    <?php endif; ?>

    <!-- Input Box -->
    <form id="liveChatSendForm" onsubmit="handleSendChatMessage(event)" class="p-2.5 bg-white border-t border-gray-200 flex items-center gap-2">
      <input type="text" id="chatInputMessage" required placeholder="यहाँ अपना संदेश या आर्डर सं. लिखें..."
        class="flex-1 px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green transition">
      <button type="submit" id="chatSendBtn"
        class="w-9 h-9 bg-brand-green hover:bg-emerald-800 text-white rounded-xl flex items-center justify-center transition shadow-xs">
        <i class="fa-solid fa-paper-plane text-xs"></i>
      </button>
    </form>
  </div>

</div>

<script>
let lastChatMsgId = 0;
let chatPollTimer = null;
let isChatOpen = false;

function toggleLiveChatBox() {
  const box = document.getElementById('liveChatPopupBox');
  isChatOpen = !isChatOpen;
  if (isChatOpen) {
    box.classList.remove('hidden');
    document.getElementById('chatCustomerUnread').classList.add('hidden');
    pollCustomerChat();
    if (!chatPollTimer) {
      chatPollTimer = setInterval(pollCustomerChat, 3000);
    }
  } else {
    box.classList.add('hidden');
    if (chatPollTimer) {
      clearInterval(chatPollTimer);
      chatPollTimer = null;
    }
  }
}

function pollCustomerChat() {
  fetch('/ajax/chat_api.php?action=customer_poll&last_id=' + lastChatMsgId)
    .then(res => res.json())
    .then(data => {
      if (data.success && data.messages && data.messages.length > 0) {
        const area = document.getElementById('chatMessagesArea');
        data.messages.forEach(m => {
          lastChatMsgId = Math.max(lastChatMsgId, m.id);
          const isUser = (m.sender_type === 'user');
          const row = document.createElement('div');
          row.className = isUser ? 'flex justify-end' : 'flex justify-start';

          row.innerHTML = `
            <div class="max-w-[82%] p-2.5 rounded-2xl ${isUser ? 'bg-brand-green text-white rounded-br-xs' : 'bg-white border border-gray-200 text-gray-800 rounded-bl-xs shadow-xs'}">
              ${!isUser ? `<div class="text-[10px] font-bold text-emerald-800 mb-0.5">${escapeHtml(m.sender_name)}</div>` : ''}
              <div class="leading-relaxed whitespace-pre-line">${escapeHtml(m.message)}</div>
              <div class="text-[9px] ${isUser ? 'text-emerald-200' : 'text-gray-400'} text-right mt-1">${formatTime(m.created_at)}</div>
            </div>
          `;
          area.appendChild(row);
        });
        area.scrollTop = area.scrollHeight;
      }
    })
    .catch(err => console.log('Chat poll notice:', err));
}

function handleSendChatMessage(e) {
  e.preventDefault();
  const input = document.getElementById('chatInputMessage');
  const msg = input.value.trim();
  if (!msg) return;

  const formData = new FormData();
  formData.append('message', msg);

  const guestNameEl = document.getElementById('chatGuestName');
  const guestPhoneEl = document.getElementById('chatGuestPhone');
  if (guestNameEl && guestPhoneEl) {
    if (!guestNameEl.value.trim() || !guestPhoneEl.value.trim()) {
      alert('कृपया पहली बार चैट करने हेतु अपना नाम व फ़ोन नंबर लिखें।');
      return;
    }
    formData.append('guest_name', guestNameEl.value.trim());
    formData.append('guest_phone', guestPhoneEl.value.trim());
  }

  input.value = '';

  fetch('/ajax/chat_api.php?action=customer_send', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      const gBox = document.getElementById('chatGuestFields');
      if (gBox) gBox.style.display = 'none';
      pollCustomerChat();
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
</script>
