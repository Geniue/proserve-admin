<x-filament-panels::page>
  <style>
    .sc-wrap { display:flex; height:calc(100vh - 180px); border-radius:0.75rem; overflow:hidden; border:1px solid #e5e7eb; }
    .dark .sc-wrap { border-color:#374151; }
    .sc-left { width:320px; min-width:320px; border-right:1px solid #e5e7eb; background:#fff; display:flex; flex-direction:column; overflow:hidden; }
    .dark .sc-left { border-color:#374151; background:#111827; }
    .sc-right { flex:1; display:flex; flex-direction:column; background:#f9fafb; }
    .dark .sc-right { background:#030712; }
    .sc-search { padding:0.75rem; border-bottom:1px solid #e5e7eb; }
    .dark .sc-search { border-color:#374151; }
    .sc-search input { width:100%; border-radius:0.5rem; border:1px solid #d1d5db; padding:0.5rem 0.75rem; font-size:0.875rem; outline:none; }
    .dark .sc-search input { background:#1f2937; border-color:#4b5563; color:#fff; }
    .sc-search input:focus { border-color:#f59e0b; box-shadow:0 0 0 2px rgba(245,158,11,0.2); }
    .sc-filters { display:flex; gap:4px; margin-top:0.5rem; }
    .sc-filter-btn { font-size:0.75rem; padding:2px 8px; border-radius:9999px; cursor:pointer; border:none; transition:all 0.15s; }
    .sc-filter-btn.active { background:#f59e0b; color:#fff; }
    .sc-filter-btn:not(.active) { background:#f3f4f6; color:#6b7280; }
    .dark .sc-filter-btn:not(.active) { background:#1f2937; color:#9ca3af; }
    .sc-chatlist { flex:1; overflow-y:auto; }
    .sc-chatitem { display:flex; align-items:center; gap:0.75rem; padding:0.75rem; cursor:pointer; border-bottom:1px solid #f3f4f6; transition:background 0.15s; }
    .dark .sc-chatitem { border-color:#1f2937; }
    .sc-chatitem:hover { background:#f9fafb; }
    .dark .sc-chatitem:hover { background:#1f2937; }
    .sc-chatitem.selected { background:#fffbeb; }
    .dark .sc-chatitem.selected { background:#1c1917; }
    .sc-avatar { width:40px; height:40px; border-radius:50%; background:#fef3c7; display:flex; align-items:center; justify-content:center; flex-shrink:0; position:relative; }
    .dark .sc-avatar { background:#78350f; }
    .sc-avatar span { color:#d97706; font-weight:700; font-size:0.875rem; }
    .dark .sc-avatar span { color:#fbbf24; }
    .sc-badge { position:absolute; top:-4px; right:-4px; width:20px; height:20px; background:#ef4444; border-radius:50%; color:#fff; font-size:0.625rem; display:flex; align-items:center; justify-content:center; font-weight:700; }
    .sc-chatmeta { flex:1; min-width:0; }
    .sc-chatname { font-weight:500; font-size:0.875rem; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .dark .sc-chatname { color:#fff; }
    .sc-chattime { font-size:10px; color:#9ca3af; white-space:nowrap; margin-left:4px; }
    .sc-chatpreview { font-size:0.75rem; color:#6b7280; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:2px; }
    .dark .sc-chatpreview { color:#9ca3af; }
    .sc-status-pill { font-size:10px; padding:1px 6px; border-radius:9999px; white-space:nowrap; margin-left:4px; }
    .sc-status-active { background:#dcfce7; color:#15803d; }
    .dark .sc-status-active { background:#14532d; color:#4ade80; }
    .sc-status-waiting { background:#fef9c3; color:#a16207; }
    .dark .sc-status-waiting { background:#713f12; color:#facc15; }
    .sc-status-closed { background:#f3f4f6; color:#6b7280; }
    .dark .sc-status-closed { background:#1f2937; color:#6b7280; }
    .sc-empty { padding:1.5rem; text-align:center; color:#9ca3af; font-size:0.875rem; }
    .sc-header { display:flex; align-items:center; justify-content:space-between; padding:1rem; background:#fff; border-bottom:1px solid #e5e7eb; }
    .dark .sc-header { background:#111827; border-color:#374151; }
    .sc-header h3 { font-weight:600; color:#111827; margin:0; font-size:1rem; }
    .dark .sc-header h3 { color:#fff; }
    .sc-header-sub { display:flex; align-items:center; gap:0.5rem; margin-top:2px; font-size:0.75rem; color:#6b7280; }
    .dark .sc-header-sub { color:#9ca3af; }
    .sc-header select { font-size:0.875rem; border-radius:0.5rem; border:1px solid #d1d5db; padding:4px 8px; }
    .dark .sc-header select { background:#1f2937; border-color:#4b5563; color:#fff; }
    .sc-messages { flex:1; overflow-y:auto; padding:1rem; display:flex; flex-direction:column; gap:0.75rem; }
    .sc-msg-row { display:flex; }
    .sc-msg-row.from-admin { justify-content:flex-end; }
    .sc-msg-row.from-user { justify-content:flex-start; }
    .sc-bubble { max-width:70%; padding:0.625rem 1rem; border-radius:1rem; }
    .sc-bubble.admin { background:#f59e0b; color:#fff; border-bottom-right-radius:0.375rem; }
    .sc-bubble.user { background:#fff; color:#374151; border-bottom-left-radius:0.375rem; box-shadow:0 1px 2px rgba(0,0,0,0.05); }
    .dark .sc-bubble.user { background:#1f2937; color:#e5e7eb; }
    .sc-bubble p { font-size:0.875rem; line-height:1.5; margin:0; }
    .sc-bubble img { max-width:240px; border-radius:0.5rem; margin-bottom:0.375rem; cursor:pointer; }
    .sc-bubble-meta { font-size:10px; opacity:0.6; margin-top:4px; }
    .sc-input-bar { display:flex; gap:0.5rem; padding:0.75rem; background:#fff; border-top:1px solid #e5e7eb; }
    .dark .sc-input-bar { background:#111827; border-color:#374151; }
    .sc-input-bar input { flex:1; border-radius:9999px; border:1px solid #d1d5db; padding:0.5rem 1rem; font-size:0.875rem; outline:none; }
    .dark .sc-input-bar input { background:#1f2937; border-color:#4b5563; color:#fff; }
    .sc-input-bar input:focus { border-color:#f59e0b; box-shadow:0 0 0 2px rgba(245,158,11,0.2); }
    .sc-send-btn { background:#f59e0b; color:#fff; border:none; border-radius:9999px; padding:0.5rem 1.25rem; font-size:0.875rem; font-weight:500; cursor:pointer; transition:background 0.15s; }
    .sc-send-btn:hover { background:#d97706; }
    .sc-send-btn:disabled { opacity:0.5; cursor:not-allowed; }
    .sc-placeholder { flex:1; display:flex; align-items:center; justify-content:center; color:#9ca3af; }
    .sc-placeholder svg { width:64px; height:64px; opacity:0.3; margin-bottom:0.75rem; }
    .sc-placeholder p { font-size:0.875rem; }
    .sc-type-pill { font-size:10px; padding:1px 6px; border-radius:9999px; background:#f3f4f6; color:#6b7280; }
    .dark .sc-type-pill { background:#1f2937; color:#9ca3af; }
  </style>

  <div x-data="supportChat()" x-init="initChat()" class="sc-wrap">
    {{-- Left: Chat list --}}
    <div class="sc-left">
      <div class="sc-search">
        <input x-model="searchQuery" type="text" placeholder="Search by name or phone..." />
        <div class="sc-filters">
          <button @click="statusFilter = ''" class="sc-filter-btn" :class="{ active: statusFilter === '' }">All</button>
          <button @click="statusFilter = 'active'" class="sc-filter-btn" :class="{ active: statusFilter === 'active' }">Active</button>
          <button @click="statusFilter = 'waiting'" class="sc-filter-btn" :class="{ active: statusFilter === 'waiting' }">Waiting</button>
          <button @click="statusFilter = 'closed'" class="sc-filter-btn" :class="{ active: statusFilter === 'closed' }">Closed</button>
        </div>
      </div>

      <div class="sc-chatlist">
        <template x-for="chat in filteredChats" :key="chat.id">
          <div @click="selectChat(chat)" class="sc-chatitem" :class="{ selected: selectedChatId === chat.id }">
            <div class="sc-avatar">
              <span x-text="chat.userName?.charAt(0)?.toUpperCase() || '?'"></span>
              <div x-show="chat.unreadByAdmin > 0" class="sc-badge" x-text="chat.unreadByAdmin"></div>
            </div>
            <div class="sc-chatmeta">
              <div style="display:flex; justify-content:space-between; align-items:center;">
                <span class="sc-chatname" x-text="chat.userName || chat.userPhone || 'Unknown'"></span>
                <span class="sc-chattime" x-text="formatTime(chat.lastMessageAt)"></span>
              </div>
              <div style="display:flex; justify-content:space-between; align-items:center;">
                <p class="sc-chatpreview" x-text="chat.lastMessage || 'No messages yet'"></p>
                <span
                  x-show="chat.status"
                  class="sc-status-pill"
                  :class="{
                    'sc-status-active': chat.status === 'active',
                    'sc-status-waiting': chat.status === 'waiting',
                    'sc-status-closed': chat.status === 'closed',
                  }"
                  x-text="chat.status"
                ></span>
              </div>
            </div>
          </div>
        </template>

        <div x-show="filteredChats.length === 0" class="sc-empty">
          No conversations found
        </div>
      </div>
    </div>

    {{-- Right: Messages --}}
    <div class="sc-right">
      <template x-if="selectedChatId">
        <div style="display:flex; flex-direction:column; height:100%;">
          <div class="sc-header">
            <div>
              <h3 x-text="selectedChat?.userName || 'Unknown'"></h3>
              <div class="sc-header-sub">
                <span x-text="selectedChat?.userPhone || ''"></span>
                <span x-show="selectedChat?.userType" class="sc-type-pill" x-text="selectedChat?.userType"></span>
              </div>
            </div>
            <div>
              <select x-model="chatStatus" @change="updateChatStatus()">
                <option value="active">Active</option>
                <option value="waiting">Waiting</option>
                <option value="closed">Closed</option>
              </select>
            </div>
          </div>

          <div x-ref="messageContainer" class="sc-messages">
            <template x-for="msg in messages" :key="msg.id">
              <div class="sc-msg-row" :class="msg.senderType === 'admin' ? 'from-admin' : 'from-user'">
                <div class="sc-bubble" :class="msg.senderType === 'admin' ? 'admin' : 'user'">
                  <template x-if="msg.imageUrl">
                    <img :src="msg.imageUrl" @click="window.open(msg.imageUrl, '_blank')" />
                  </template>
                  <p x-show="msg.message" x-text="msg.message"></p>
                  <div class="sc-bubble-meta" :style="msg.senderType === 'admin' ? 'text-align:right' : 'text-align:left'">
                    <span x-show="msg.senderType === 'admin' && msg.senderName" x-text="msg.senderName + ' · '"></span>
                    <span x-text="formatTime(msg.timestamp)"></span>
                  </div>
                </div>
              </div>
            </template>

            <div x-show="messages.length === 0" style="flex:1; display:flex; align-items:center; justify-content:center; color:#9ca3af; font-size:0.875rem;">
              No messages in this conversation
            </div>
          </div>

          <div class="sc-input-bar">
            <input x-model="newMessage" @keydown.enter="sendMessage()" type="text" placeholder="Type a message..." />
            <button @click="sendMessage()" :disabled="!newMessage.trim()" class="sc-send-btn">Send</button>
          </div>
        </div>
      </template>

      <template x-if="!selectedChatId">
        <div class="sc-placeholder">
          <div style="text-align:center;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:64px; height:64px; margin:0 auto 12px; opacity:0.3;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
            </svg>
            <p>Select a conversation to start chatting</p>
          </div>
        </div>
      </template>
    </div>
  </div>

  {{-- Firebase JS SDK --}}
  <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore-compat.js"></script>

  <script>
    function supportChat() {
      return {
        chats: [],
        messages: [],
        selectedChatId: null,
        selectedChat: null,
        chatStatus: 'active',
        searchQuery: '',
        statusFilter: '',
        newMessage: '',
        db: null,
        unsubChats: null,
        unsubMessages: null,

        get filteredChats() {
          let result = this.chats;
          if (this.statusFilter) {
            result = result.filter(c => c.status === this.statusFilter);
          }
          if (this.searchQuery) {
            const q = this.searchQuery.toLowerCase();
            result = result.filter(
              c => (c.userName || '').toLowerCase().includes(q)
                || (c.userPhone || '').includes(q)
            );
          }
          return result;
        },

        initChat() {
          const config = @json($firebaseConfig);
          if (!config.apiKey || !config.projectId) {
            console.error('Firebase config missing. Set FIREBASE_WEB_API_KEY and FIREBASE_WEB_PROJECT_ID in .env');
            return;
          }
          firebase.initializeApp(config);
          this.db = firebase.firestore();
          this.listenChats();
        },

        listenChats() {
          this.unsubChats = this.db
            .collection('support_chats')
            .orderBy('lastMessageAt', 'desc')
            .onSnapshot(snap => {
              this.chats = snap.docs.map(d => ({ id: d.id, ...d.data() }));
              if (this.selectedChatId) {
                this.selectedChat = this.chats.find(c => c.id === this.selectedChatId) || null;
              }
            });
        },

        selectChat(chat) {
          this.selectedChatId = chat.id;
          this.selectedChat = chat;
          this.chatStatus = chat.status || 'active';
          this.listenMessages(chat.id);

          if (chat.unreadByAdmin > 0) {
            this.db.collection('support_chats').doc(chat.id).update({
              unreadByAdmin: 0
            });
          }
        },

        listenMessages(userId) {
          if (this.unsubMessages) this.unsubMessages();
          this.unsubMessages = this.db
            .collection('support_chats')
            .doc(userId)
            .collection('messages')
            .orderBy('timestamp')
            .onSnapshot(snap => {
              this.messages = snap.docs.map(d => ({ id: d.id, ...d.data() }));
              this.$nextTick(() => {
                const c = this.$refs.messageContainer;
                if (c) c.scrollTop = c.scrollHeight;
              });

              // Mark unread user messages as read
              snap.docs.forEach(d => {
                const data = d.data();
                if (data.senderType === 'user' && !data.isRead) {
                  d.ref.update({ isRead: true });
                }
              });
            });
        },

        async sendMessage() {
          const text = this.newMessage.trim();
          if (!text || !this.selectedChatId) return;
          this.newMessage = '';

          const adminName = @json($adminName);

          await this.db
            .collection('support_chats')
            .doc(this.selectedChatId)
            .collection('messages')
            .add({
              senderId: 'admin',
              senderType: 'admin',
              senderName: adminName,
              message: text,
              imageUrl: null,
              timestamp: firebase.firestore.FieldValue.serverTimestamp(),
              isRead: false,
            });

          await this.db
            .collection('support_chats')
            .doc(this.selectedChatId)
            .update({
              lastMessage: text,
              lastMessageAt: firebase.firestore.FieldValue.serverTimestamp(),
              unreadByUser: firebase.firestore.FieldValue.increment(1),
              status: this.chatStatus === 'closed' ? 'active' : this.chatStatus,
            });
        },

        updateChatStatus() {
          if (!this.selectedChatId) return;
          this.db.collection('support_chats').doc(this.selectedChatId).update({
            status: this.chatStatus,
          });
        },

        formatTime(ts) {
          if (!ts) return '';
          const d = ts.toDate ? ts.toDate() : new Date(ts.seconds * 1000);
          const now = new Date();
          const diff = now - d;
          if (diff < 86400000 && d.getDate() === now.getDate()) {
            return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
          }
          if (diff < 604800000) {
            return d.toLocaleDateString([], { weekday: 'short' }) + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
          }
          return d.toLocaleDateString([], { month: 'short', day: 'numeric' });
        },
      };
    }
  </script>
</x-filament-panels::page>
