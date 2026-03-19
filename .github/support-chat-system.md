# Customer Support Chat — Server Implementation Spec

**For:** Laravel Filament Dashboard on `pumpnow.app`
**Date:** March 19, 2026

---

## Overview

Real-time customer support chat between app users and admin CS agents. The Flutter app writes to **Firebase Firestore**, the admin dashboard reads/writes via the **Firebase Admin SDK**.

---

## Firestore Schema

### Collection: `/support_chats/{userId}`

The parent document — one per user, created by the app on first chat open.

```typescript
{
  userId: string,           // Firebase Auth UID (document ID)
  userName: string,         // "First Last"
  userPhone: string,        // e.g. "0551234567"
  userType: 'customer' | 'technician',
  lastMessage: string,      // Preview text for admin list
  lastMessageAt: Timestamp,
  unreadByAdmin: number,    // Incremented by app, reset by admin
  unreadByUser: number,     // Incremented by admin, reset by app
  status: 'active' | 'closed' | 'waiting',
  assignedAgent?: string,   // admin_users.id of assigned CS agent
  tags?: string[],          // e.g. ['billing', 'urgent']
  createdAt?: Timestamp
}
```

### Sub-collection: `/support_chats/{userId}/messages/{messageId}`

Individual messages in the conversation.

```typescript
{
  senderId: string,         // userId or admin userId
  senderType: 'user' | 'admin',
  senderName?: string,      // Admin display name (for admin messages)
  message: string,          // Text content (empty if image-only)
  imageUrl?: string,        // Firebase Storage URL
  timestamp: Timestamp,
  isRead: boolean           // Read by the OTHER side
}
```

---

## Admin Dashboard (Filament 3)

### 1. Live Chat Page — `SupportChatPage`

A custom Filament page (not a resource), showing a **split-panel layout**:

| Left Panel (30%) | Right Panel (70%) |
|---|---|
| Chat list sorted by `lastMessageAt` desc | Active conversation messages |
| Unread badge per chat | Input bar + attachment |
| Search by name/phone | User info sidebar |
| Filter: active / waiting / closed | Quick actions |

**Route:** `/admin/support-chat`
**Nav Icon:** `heroicon-o-chat-bubble-left-right`
**Nav Group:** Support

### 2. Implementation

#### Filament Custom Page

```php
// app/Filament/Pages/SupportChatPage.php
namespace App\Filament\Pages;

use Filament\Pages\Page;

class SupportChatPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Support';
    protected static ?string $title = 'Live Chat';
    protected static string $view = 'filament.pages.support-chat';
    protected static ?int $navigationSort = 1;

    public function getViewData(): array
    {
        return [
            'firebaseConfig' => [
                'projectId' => config('firebase.project_id'),
                'apiKey' => config('firebase.api_key'),
            ],
        ];
    }
}
```

#### Livewire / Alpine.js Real-time Component

The chat UI uses **Firebase JS SDK** directly from the browser for real-time updates:

```html
<!-- resources/views/filament/pages/support-chat.blade.php -->
<x-filament-panels::page>
  <div
    x-data="supportChat()"
    x-init="init()"
    class="flex h-[calc(100vh-180px)] rounded-xl overflow-hidden border"
  >
    {{-- Left: Chat list --}}
    <div class="w-[320px] border-r bg-white overflow-y-auto flex flex-col">
      <div class="p-3 border-b">
        <input
          x-model="searchQuery"
          type="text"
          placeholder="Search by name or phone..."
          class="w-full rounded-lg border px-3 py-2 text-sm"
        />
      </div>
      <template x-for="chat in filteredChats" :key="chat.id">
        <div
          @click="selectChat(chat)"
          :class="{ 'bg-primary-50': selectedChatId === chat.id }"
          class="flex items-center gap-3 p-3 cursor-pointer hover:bg-gray-50 border-b"
        >
          <div class="relative">
            <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
              <span class="text-primary-600 font-bold text-sm" x-text="chat.userName?.charAt(0) || '?'"></span>
            </div>
            <div
              x-show="chat.unreadByAdmin > 0"
              class="absolute -top-1 -right-1 w-5 h-5 bg-danger-500 rounded-full text-white text-xs flex items-center justify-center"
              x-text="chat.unreadByAdmin"
            ></div>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex justify-between">
              <span class="font-medium text-sm truncate" x-text="chat.userName || chat.userPhone"></span>
              <span class="text-xs text-gray-400" x-text="formatTime(chat.lastMessageAt)"></span>
            </div>
            <p class="text-xs text-gray-500 truncate" x-text="chat.lastMessage"></p>
          </div>
        </div>
      </template>
    </div>

    {{-- Right: Messages --}}
    <div class="flex-1 flex flex-col bg-gray-50">
      <template x-if="selectedChatId">
        <div class="flex flex-col h-full">
          {{-- Header --}}
          <div class="flex items-center justify-between p-4 bg-white border-b">
            <div>
              <h3 class="font-semibold" x-text="selectedChat?.userName"></h3>
              <p class="text-xs text-gray-500" x-text="selectedChat?.userPhone"></p>
            </div>
            <div class="flex gap-2">
              <select
                x-model="selectedChat.status"
                @change="updateChatStatus()"
                class="text-sm rounded-lg border px-2 py-1"
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
                    ? 'bg-primary-500 text-white rounded-t-2xl rounded-bl-2xl'
                    : 'bg-white text-gray-800 rounded-t-2xl rounded-br-2xl shadow-sm'"
                  class="max-w-[70%] px-4 py-2"
                >
                  <template x-if="msg.imageUrl">
                    <img :src="msg.imageUrl" class="max-w-[200px] rounded-lg mb-1" />
                  </template>
                  <p class="text-sm" x-text="msg.message"></p>
                  <span class="text-[10px] opacity-60" x-text="formatTime(msg.timestamp)"></span>
                </div>
              </div>
            </template>
          </div>

          {{-- Input --}}
          <div class="p-3 bg-white border-t flex gap-2">
            <input
              x-model="newMessage"
              @keydown.enter="sendMessage()"
              type="text"
              placeholder="Type a message..."
              class="flex-1 rounded-full border px-4 py-2 text-sm"
            />
            <button
              @click="sendMessage()"
              class="bg-primary-500 hover:bg-primary-600 text-white rounded-full px-4 py-2 text-sm font-medium"
            >
              Send
            </button>
          </div>
        </div>
      </template>

      <template x-if="!selectedChatId">
        <div class="flex-1 flex items-center justify-center text-gray-400">
          <div class="text-center">
            <x-heroicon-o-chat-bubble-left-right class="w-16 h-16 mx-auto mb-3 opacity-30" />
            <p>Select a conversation</p>
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
        searchQuery: '',
        newMessage: '',
        db: null,
        unsubChats: null,
        unsubMessages: null,

        get filteredChats() {
          if (!this.searchQuery) return this.chats;
          const q = this.searchQuery.toLowerCase();
          return this.chats.filter(
            c => (c.userName || '').toLowerCase().includes(q)
              || (c.userPhone || '').includes(q)
          );
        },

        init() {
          firebase.initializeApp(@json($firebaseConfig));
          this.db = firebase.firestore();
          this.listenChats();
        },

        listenChats() {
          this.unsubChats = this.db
            .collection('support_chats')
            .orderBy('lastMessageAt', 'desc')
            .onSnapshot(snap => {
              this.chats = snap.docs.map(d => ({ id: d.id, ...d.data() }));
              // Update selected chat ref
              if (this.selectedChatId) {
                this.selectedChat = this.chats.find(c => c.id === this.selectedChatId);
              }
            });
        },

        selectChat(chat) {
          this.selectedChatId = chat.id;
          this.selectedChat = chat;
          this.listenMessages(chat.id);

          // Reset unread count
          this.db.collection('support_chats').doc(chat.id).update({
            unreadByAdmin: 0
          });
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

              // Mark user messages as read
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

          const adminName = @json(auth()->user()?->name ?? 'Support');

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
              unreadByUser: firebase.firestore.Increment(1),
            });
        },

        updateChatStatus() {
          if (!this.selectedChatId || !this.selectedChat) return;
          this.db.collection('support_chats').doc(this.selectedChatId).update({
            status: this.selectedChat.status,
          });
        },

        formatTime(ts) {
          if (!ts) return '';
          const d = ts.toDate ? ts.toDate() : new Date(ts.seconds * 1000);
          return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },
      };
    }
  </script>
</x-filament-panels::page>
```

---

## PostgreSQL Cache Tables (Optional)

For analytics/reporting purposes, you can cache support chats:

```sql
CREATE TABLE support_chat_sessions (
    id BIGSERIAL PRIMARY KEY,
    firebase_user_id VARCHAR(255) NOT NULL,
    user_name VARCHAR(255),
    user_phone VARCHAR(50),
    user_type VARCHAR(50),
    status VARCHAR(50) DEFAULT 'active',
    assigned_agent_id BIGINT REFERENCES admin_users(id),
    message_count INTEGER DEFAULT 0,
    last_message_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    closed_at TIMESTAMP,

    INDEX idx_status (status),
    INDEX idx_user (firebase_user_id),
    INDEX idx_agent (assigned_agent_id)
);

CREATE TABLE support_chat_messages (
    id BIGSERIAL PRIMARY KEY,
    session_id BIGINT REFERENCES support_chat_sessions(id),
    firebase_message_id VARCHAR(255),
    sender_type VARCHAR(50) NOT NULL, -- 'user' or 'admin'
    sender_id VARCHAR(255),
    message TEXT,
    image_url TEXT,
    is_read BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT NOW(),

    INDEX idx_session (session_id)
);
```

---

## Firestore Security Rules

```javascript
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {

    // Support chat — user can only access their own chat
    match /support_chats/{userId} {
      allow read, write: if request.auth != null && request.auth.uid == userId;

      match /messages/{messageId} {
        allow read: if request.auth != null && request.auth.uid == userId;
        allow create: if request.auth != null && request.auth.uid == userId
                      && request.resource.data.senderType == 'user';
        allow update: if request.auth != null && request.auth.uid == userId;
      }
    }
  }
}
```

Admin SDK bypasses these rules (server-side).

---

## Push Notifications (Future Enhancement)

When an admin sends a support message, trigger an FCM notification to the user:

```php
// app/Observers/SupportMessageObserver.php (or Firebase Cloud Function)

// Option 1: Firebase Cloud Function (recommended)
exports.onSupportAdminReply = functions.firestore
  .document('support_chats/{userId}/messages/{messageId}')
  .onCreate(async (snap, context) => {
    const data = snap.data();
    if (data.senderType !== 'admin') return;

    const userId = context.params.userId;
    const userDoc = await admin.firestore().collection('users').doc(userId).get();
    const fcmToken = userDoc.data()?.fcmToken;

    if (fcmToken) {
      await admin.messaging().send({
        token: fcmToken,
        notification: {
          title: 'PUMP Support',
          body: data.message || 'New message from support',
        },
        data: {
          type: 'support_chat',
          userId: userId,
        },
      });
    }
  });
```

---

## Admin Dashboard Features

### Navigation Menu Item
```php
// In AdminPanelProvider.php
->pages([
    SupportChatPage::class,
])
```

### Unread Count Badge (Nav)
```php
// Use Filament's badge on navigation
public static function getNavigationBadge(): ?string
{
    // Query Firestore for total unreadByAdmin > 0
    $syncService = app(FirebaseSyncService::class);
    $chats = $syncService->fetchCollection('support_chats', [
        'unreadByAdmin' => ['>', 0]
    ]);
    $count = count($chats);
    return $count > 0 ? (string) $count : null;
}

public static function getNavigationBadgeColor(): ?string
{
    return 'danger';
}
```

---

## Summary

| Component | Technology | Location |
|---|---|---|
| User chat UI | Flutter + Firestore SDK | `support_chat_screen.dart` |
| Admin chat UI | Alpine.js + Firebase JS SDK | Filament custom page |
| Real-time sync | Firestore onSnapshot | Both sides |
| Data store | Firestore (`support_chats`) | Primary |
| Cache | PostgreSQL (optional) | Admin analytics |
| Push notifications | FCM via Cloud Function | Future |
| Security | Firestore rules + Admin SDK | Server |
