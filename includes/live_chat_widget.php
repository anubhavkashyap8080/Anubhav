<!-- GaunGaliHat Real-time Customer Live Chat Support Widget -->
<div id="liveChatWidgetContainer" class="fixed bottom-5 right-4 z-50 flex flex-col items-end gap-2.5 font-sans">

  <!-- Live Chat Trigger Pill -->
  <button type="button" id="openLiveChatBtn"
    onclick="toggleLiveChatBox()"
    class="flex items-center gap-2.5 px-4 py-2.5 bg-gradient-to-r from-emerald-800 to-brand-green text-white rounded-full shadow-2xl hover:shadow-emerald-900/30 transition-all hover:scale-105 active:scale-95 text-xs font-bold border border-emerald-500/40 relative">
    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping absolute top-2 left-2"></span>
    <i class="fa-solid fa-headset text-base"></i>
    <span>Live Chat Support</span>
    <span id="chatCustomerUnread" class="hidden px-1.5 py-0.2 bg-red-500 text-white text-[10px] font-black rounded-full shadow-xs">1</span>
  </button>

  <!-- Live Chat Popup Card (Responsive, Never Cut Off) -->
  <div id="liveChatPopupBox"
    class="hidden w-[92vw] sm:w-96 max-w-sm bg-white rounded-3xl border border-gray-200 shadow-2xl flex flex-col overflow-hidden animate-in fade-in slide-in-from-bottom-5 duration-200"
    style="height: 490px; max-height: 80vh;">
    
    <!-- Chat Header with Minimize and Cancel Options -->
    <div class="bg-gradient-to-r from-emerald-950 via-slate-900 to-emerald-900 text-white p-3.5 flex items-center justify-between">
      <div class="flex items-center gap-2.5 min-w-0">
        <div class="w-8 h-8 rounded-xl bg-emerald-800 text-white flex items-center justify-center font-bold text-sm shrink-0">
          🌾
        </div>
        <div class="min-w-0">
          <h4 class="text-xs font-bold truncate">GaunGaliHat Support</h4>
          <span class="text-[10px] text-emerald-300 flex items-center gap-1 font-semibold truncate">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Online • Direct Help Desk
          </span>
        </div>
      </div>

      <!-- Action Controls: Minimize & Cancel / End Chat -->
      <div class="flex items-center gap-1.5 shrink-0">
        <!-- Minimize Button -->
        <button type="button" onclick="minimizeLiveChat()"
          class="flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold text-gray-200 hover:text-white bg-white/10 hover:bg-white/20 rounded-lg transition"
          title="Minimize Chat">
          <i class="fa-solid fa-minus text-[10px]"></i>
          <span>Minimize</span>
        </button>

        <!-- Cancel / End Chat Button -->
        <button type="button" onclick="cancelLiveChat()"
          class="flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold text-red-200 hover:text-white bg-red-500/20 hover:bg-red-600 rounded-lg transition"
          title="End / Cancel Chat">
          <i class="fa-solid fa-xmark text-xs"></i>
          <span>Cancel</span>
        </button>
      </div>
    </div>

    <!-- Messages Container -->
    <div id="chatMessagesArea" class="flex-1 p-3.5 overflow-y-auto space-y-3 bg-stone-50 text-xs">
      <div class="text-center py-2">
        <span class="text-[10px] text-gray-400 bg-white px-2.5 py-1 rounded-full border border-gray-200">Today's Conversation</span>
      </div>
    </div>

    <!-- Guest Identity Form (If not logged in and first time) -->
    <?php if (!is_logged_in() && empty($_SESSION['chat_guest_name'])): ?>
      <div id="chatGuestFields" class="p-2.5 bg-amber-50 border-t border-amber-100 flex gap-1.5 text-xs">
        <input type="text" id="chatGuestName" placeholder="Your Name *" required
          class="w-1/2 px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-xs outline-none focus:border-brand-green">
        <input type="tel" id="chatGuestPhone" placeholder="Mobile Number *" required maxlength="10"
          class="w-1/2 px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-xs outline-none focus:border-brand-green">
      </div>
    <?php endif; ?>

    <!-- Input Box -->
    <form id="liveChatSendForm" onsubmit="handleSendChatMessage(event)" class="p-2.5 bg-white border-t border-gray-200 flex items-center gap-2">
      <input type="text" id="chatInputMessage" required placeholder="Type your message or order ID here..."
        class="flex-1 px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green transition">
      <button type="submit" id="chatSendBtn"
        class="w-9 h-9 bg-brand-green hover:bg-emerald-800 disabled:bg-gray-300 disabled:cursor-not-allowed text-white rounded-xl flex items-center justify-center transition shadow-xs">
        <i class="fa-solid fa-paper-plane text-xs"></i>
      </button>
    </form>
  </div>

</div>

<script>
let lastChatMsgId = 0;
let chatPollTimer = null;
let isChatOpen = false;
let isSendingChat = false;
let isPollingChat = false;

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
    setTimeout(() => {
      document.getElementById('chatInputMessage')?.focus();
    }, 150);
  } else {
    box.classList.add('hidden');
    if (chatPollTimer) {
      clearInterval(chatPollTimer);
      chatPollTimer = null;
    }
  }
}

// 1. Minimize live chat (keeps conversation in memory & active)
function minimizeLiveChat() {
  const box = document.getElementById('liveChatPopupBox');
  box.classList.add('hidden');
  isChatOpen = false;
  // Polling continues if timer active, keeping state alive
}

// 2. Cancel / End chat session
function cancelLiveChat() {
  if (confirm('Are you sure you want to end this chat session?')) {
    fetch('/ajax/chat_api.php?action=customer_end_chat', { method: 'POST' })
      .then(res => res.json())
      .then(() => {
        const box = document.getElementById('liveChatPopupBox');
        box.classList.add('hidden');
        isChatOpen = false;
        lastChatMsgId = 0;
        if (chatPollTimer) {
          clearInterval(chatPollTimer);
          chatPollTimer = null;
        }
        const area = document.getElementById('chatMessagesArea');
        if (area) {
          area.innerHTML = `
            <div class="text-center py-4">
              <span class="text-[10px] text-gray-400 bg-white px-2.5 py-1 rounded-full border border-gray-200">Chat session ended</span>
              <p class="text-[11px] text-gray-500 mt-2">Click below to start a new chat whenever you need assistance.</p>
            </div>
          `;
        }
      })
      .catch(() => {
        const box = document.getElementById('liveChatPopupBox');
        box.classList.add('hidden');
        isChatOpen = false;
      });
  }
}

// 3. Poll Customer Chat with strict de-duplication
function pollCustomerChat() {
  if (isPollingChat) return;
  isPollingChat = true;

  fetch('/ajax/chat_api.php?action=customer_poll&last_id=' + lastChatMsgId)
    .then(res => res.json())
    .then(data => {
      if (data.success && data.messages && data.messages.length > 0) {
        const area = document.getElementById('chatMessagesArea');
        let newAppended = false;

        data.messages.forEach(m => {
          lastChatMsgId = Math.max(lastChatMsgId, parseInt(m.id));

          // Strict de-duplication check: Skip if element with this ID already exists
          if (document.getElementById('chat-msg-' + m.id)) {
            return;
          }

          const isUser = (m.sender_type === 'user');
          const isSystem = (m.sender_type === 'system');
          const row = document.createElement('div');
          row.id = 'chat-msg-' + m.id;

          if (isSystem) {
            row.className = 'text-center py-1';
            row.innerHTML = `<span class="text-[10px] text-gray-400 italic bg-white px-2 py-0.5 rounded-full border border-gray-200">${escapeHtml(m.message)}</span>`;
          } else {
            row.className = isUser ? 'flex justify-end' : 'flex justify-start';
            row.innerHTML = `
              <div class="max-w-[82%] p-2.5 rounded-2xl ${isUser ? 'bg-brand-green text-white rounded-br-xs' : 'bg-white border border-gray-200 text-gray-800 rounded-bl-xs shadow-xs'}">
                ${!isUser ? `<div class="text-[10px] font-bold text-emerald-800 mb-0.5">${escapeHtml(m.sender_name)}</div>` : ''}
                <div class="leading-relaxed whitespace-pre-line">${escapeHtml(m.message)}</div>
                <div class="text-[9px] ${isUser ? 'text-emerald-200' : 'text-gray-400'} text-right mt-1">${formatTime(m.created_at)}</div>
              </div>
            `;
          }

          area.appendChild(row);
          newAppended = true;
        });

        if (newAppended) {
          area.scrollTop = area.scrollHeight;
        }
      }
    })
    .catch(err => console.log('Chat poll notice:', err))
    .finally(() => {
      isPollingChat = false;
    });
}

// 4. Send Customer Chat Message with Anti-Double-Submit Guard
function handleSendChatMessage(e) {
  e.preventDefault();
  if (isSendingChat) return;

  const input = document.getElementById('chatInputMessage');
  const btn = document.getElementById('chatSendBtn');
  const msg = input.value.trim();
  if (!msg) return;

  const formData = new FormData();
  formData.append('message', msg);

  const guestNameEl = document.getElementById('chatGuestName');
  const guestPhoneEl = document.getElementById('chatGuestPhone');
  if (guestNameEl && guestPhoneEl) {
    if (!guestNameEl.value.trim() || !guestPhoneEl.value.trim()) {
      alert('Please enter your name and phone number to start chatting.');
      return;
    }
    formData.append('guest_name', guestNameEl.value.trim());
    formData.append('guest_phone', guestPhoneEl.value.trim());
  }

  // Lock form to avoid double clicks or accidental re-submits
  isSendingChat = true;
  if (btn) btn.disabled = true;
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
  })
  .catch(err => {
    console.error('Send error:', err);
  })
  .finally(() => {
    isSendingChat = false;
    if (btn) btn.disabled = false;
    input.focus();
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
