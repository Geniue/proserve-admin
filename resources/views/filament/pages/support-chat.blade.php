<x-filament-panels::page>
  <div
    x-data="supportChat()"
    x-init="initChat()"
    class="flex h-[calc(100vh-180px)] rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700"
  >
    {{-- Left: Chat list --}}
    <div class="w-[320px] border-e border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-y-auto flex flex-col">
      {{-- Search --}}
      <div class="p-3 border-b border-gray-200 dark:border-gray-700">
        <input
          x-model="searchQuery"
          type="text"
          placeholder="Search by name or phone..."
          class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white px-3 py-2 text-sm focus:ring-primary-500 focus:border-primary-500"
        />
        <div class="flex gap-1 mt-2">
          <button
            @click="statusFilter = ''"
            :class="statusFilter === '' ? 'bg-primary-500 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'"
            class="text-xs px-2 py-1 rounded-full transition"
          >All</button>
          <button
            @click="statusFilter = 'active'"
            :class="statusFilter === 'active' ? 'bg-primary-500 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'"
            class="text-xs px-2 py-1 rounded-full transition"
          >Active</button>
          <button
            @click="statusFilter = 'waiting'"
            :class="statusFilter === 'waiting' ? 'bg-primary-500 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'"
            class="text-xs px-2 py-1 rounded-full transition"
          >Waiting</button>
          <button
            @click="statusFilter = 'closed'"
            :class="statusFilter === 'closed' ? 'bg-primary-500 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'"
            class="text-xs px-2 py-1 rounded-full transition"
          >Closed</button>
        </div>
      </div>

      {{-- Chat list --}}
      <div class="flex-1 overflow-y-auto">
        <template x-for="chat in filteredChats" :key="chat.id">
          <div
            @click="selectChat(chat)"
            :class="{ 'bg-primary-50 dark:bg-primary-950': selectedChatId === chat.id }"
            class="flex items-center gap-3 p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 border-b border-gray-100 dark:border-gray-800 transition"
          >
            <div class="relative flex-shrink-0">
              <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                <span class="text-primary-600 dark:text-primary-400 font-bold text-sm" x-text="chat.userName?.charAt(0)?.toUpperCase() || '?'"></span>
              </div>
              <div
                x-show="chat.unreadByAdmin > 0"
                class="absolute -top-1 -right-1 w-5 h-5 bg-danger-500 rounded-full text-white text-xs flex items-center justify-center font-bold"
                x-text="chat.unreadByAdmin"
              ></div>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-center">
                <span class="font-medium text-sm truncate text-gray-900 dark:text-white" x-text="chat.userName || chat.userPhone || 'Unknown'"></span>
                <span class="text-[10px] text-gray-400 flex-shrink-0 ms-1" x-text="formatTime(chat.lastMessageAt)"></span>
              </div>
              <div class="flex justify-between items-center mt-0.5">
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="chat.lastMessage || 'No messages yet'"></p>
                <span
                  x-show="chat.status"
                  :class="{
                    'bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-400': chat.status === 'active',
                    'bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-400': chat.status === 'waiting',
                    'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-500': chat.status === 'closed',
                  }"
                  class="text-[10px] px-1.5 py-0.5 rounded-full flex-shrink-0 ms-1"
                  x-text="chat.status"
                ></span>
              </div>
            </div>
          </div>
        </template>

        <div x-show="filteredChats.length === 0" class="p-6 text-center text-gray-400 dark:text-gray-500 text-sm">
          No conversations found
        </div>
      </div>
    </div>

    {{-- Right: Messages --}}
    <div class="flex-1 flex flex-col bg-gray-50 dark:bg-gray-950">
      <template x-if="selectedChatId">
        <div class="flex flex-col h-full">
          {{-- Header --}}
          <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white" x-text="selectedChat?.userName || 'Unknown'"></h3>
              <div class="flex items-center gap-2 mt-0.5">
                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="selectedChat?.userPhone || ''"></p>
                <span
                  x-show="selectedChat?.userType"
                  class="text-[10px] px-1.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400"
                  x-text="selectedChat?.userType"
                ></span>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <select
                x-model="chatStatus"
                @change="updateChatStatus()"
                class="text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white px-2 py-1 focus:ring-primary-500 focus:border-primary-500"
              >
                <option value="active">Active</option>
                <option value="waiting">Waiting</option>
                <option value="closed">Closed</option>
              </select>
            </div>
          </div>

          {{-- Messages --}}
          <div x-ref="messageContainer" class="flex-1 overflow-y-auto p-4 space-y-3">
            <template x-for="msg in messages" :key="msg.id">
              <div :class="msg.senderType === 'admin' ? 'flex justify-end' : 'flex justify-start'">
                <div
                  :class="msg.senderType === 'admin'
                    ? 'bg-primary-500 text-white rounded-2xl rounded-br-md'
                    : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-2xl rounded-bl-md shadow-sm'"
                  class="max-w-[70%] px-4 py-2.5"
                >
                  <template x-if="msg.imageUrl">
                    <img :src="msg.imageUrl" class="max-w-[240px] rounded-lg mb-1.5 cursor-pointer" @click="window.open(msg.imageUrl, '_blank')" />
                  </template>
                  <p x-show="msg.message" class="text-sm leading-relaxed" x-text="msg.message"></p>
                  <div class="flex items-center gap-1 mt-1" :class="msg.senderType === 'admin' ? 'justify-end' : 'justify-start'">
                    <span x-show="msg.senderType === 'admin' && msg.senderName" class="text-[10px] opacity-50" x-text="msg.senderName"></span>
                    <span class="text-[10px] opacity-50" x-text="formatTime(msg.timestamp)"></span>
                  </div>
                </div>
              </div>
            </template>

            <div x-show="messages.length === 0" class="flex items-center justify-center h-full text-gray-400 dark:text-gray-500 text-sm">
              No messages in this conversation
            </div>
          </div>

          {{-- Input --}}
          <div class="p-3 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 flex gap-2">
            <input
              x-model="newMessage"
              @keydown.enter="sendMessage()"
              type="text"
              placeholder="Type a message..."
              class="flex-1 rounded-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white px-4 py-2 text-sm focus:ring-primary-500 focus:border-primary-500"
            />
            <button
              @click="sendMessage()"
              :disabled="!newMessage.trim()"
              class="bg-primary-500 hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-full px-5 py-2 text-sm font-medium transition"
            >
              Send
            </button>
          </div>
        </div>
      </template>

      <template x-if="!selectedChatId">
        <div class="flex-1 flex items-center justify-center text-gray-400 dark:text-gray-500">
          <div class="text-center">
            <svg class="w-16 h-16 mx-auto mb-3 opacity-30" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
            </svg>
            <p class="text-sm">Select a conversation to start chatting</p>
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
