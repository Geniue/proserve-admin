<x-filament-panels::page>
  <style>
    .otp-wrap { max-width:900px; margin:0 auto; }
    .otp-stats { display:flex; gap:1rem; margin-bottom:1.5rem; }
    .otp-stat-card { flex:1; background:#fff; border:1px solid #e5e7eb; border-radius:0.75rem; padding:1rem 1.25rem; }
    .dark .otp-stat-card { background:#111827; border-color:#374151; }
    .otp-stat-label { font-size:0.75rem; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em; }
    .dark .otp-stat-label { color:#9ca3af; }
    .otp-stat-value { font-size:1.5rem; font-weight:700; color:#111827; margin-top:0.25rem; }
    .dark .otp-stat-value { color:#fff; }
    .otp-stat-value.pending { color:#f59e0b; }
    .otp-stat-value.sent { color:#10b981; }
    .otp-stat-value.expired { color:#ef4444; }

    .otp-filters { display:flex; gap:0.5rem; margin-bottom:1rem; }
    .otp-filter-btn { font-size:0.8rem; padding:0.375rem 1rem; border-radius:9999px; cursor:pointer; border:1px solid #e5e7eb; background:#fff; color:#6b7280; transition:all 0.15s; }
    .dark .otp-filter-btn { background:#1f2937; border-color:#374151; color:#9ca3af; }
    .otp-filter-btn.active { background:#f59e0b; color:#fff; border-color:#f59e0b; }

    .otp-table { width:100%; background:#fff; border:1px solid #e5e7eb; border-radius:0.75rem; overflow:hidden; }
    .dark .otp-table { background:#111827; border-color:#374151; }
    .otp-table thead th { padding:0.75rem 1rem; text-align:left; font-size:0.75rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em; background:#f9fafb; border-bottom:1px solid #e5e7eb; }
    .dark .otp-table thead th { background:#1f2937; border-color:#374151; color:#9ca3af; }
    .otp-table tbody td { padding:0.75rem 1rem; font-size:0.875rem; color:#111827; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
    .dark .otp-table tbody td { color:#e5e7eb; border-color:#1f2937; }
    .otp-table tbody tr:last-child td { border-bottom:none; }
    .otp-table tbody tr:hover { background:#f9fafb; }
    .dark .otp-table tbody tr:hover { background:#1f2937; }

    .otp-code { font-family:monospace; font-size:1.125rem; font-weight:700; letter-spacing:0.15em; color:#111827; background:#f3f4f6; padding:0.25rem 0.625rem; border-radius:0.375rem; user-select:all; cursor:pointer; }
    .dark .otp-code { background:#374151; color:#fbbf24; }

    .otp-phone { font-weight:500; direction:ltr; }

    .otp-pill { display:inline-block; font-size:0.7rem; font-weight:600; padding:0.125rem 0.5rem; border-radius:9999px; text-transform:uppercase; }
    .otp-pill-pending { background:#fef3c7; color:#92400e; }
    .dark .otp-pill-pending { background:#78350f; color:#fbbf24; }
    .otp-pill-sent { background:#d1fae5; color:#065f46; }
    .dark .otp-pill-sent { background:#064e3b; color:#34d399; }
    .otp-pill-expired { background:#fee2e2; color:#991b1b; }
    .dark .otp-pill-expired { background:#7f1d1d; color:#fca5a5; }

    .otp-btn { font-size:0.8rem; padding:0.375rem 0.875rem; border-radius:0.5rem; cursor:pointer; border:none; font-weight:500; transition:all 0.15s; }
    .otp-btn-send { background:#10b981; color:#fff; }
    .otp-btn-send:hover { background:#059669; }
    .otp-btn-copy { background:#3b82f6; color:#fff; }
    .otp-btn-copy:hover { background:#2563eb; }
    .otp-btn:disabled { opacity:0.4; cursor:not-allowed; }

    .otp-empty { padding:3rem; text-align:center; color:#9ca3af; font-size:0.875rem; }
    .otp-empty svg { width:48px; height:48px; margin:0 auto 0.75rem; opacity:0.3; }

    .otp-time-ago { font-size:0.75rem; color:#9ca3af; }
    .dark .otp-time-ago { color:#6b7280; }

    .otp-toast { position:fixed; bottom:1.5rem; right:1.5rem; background:#10b981; color:#fff; padding:0.75rem 1.25rem; border-radius:0.75rem; font-size:0.875rem; font-weight:500; z-index:9999; box-shadow:0 4px 12px rgba(0,0,0,0.15); transition:opacity 0.3s; }

    .otp-sound-indicator { display:inline-block; width:8px; height:8px; border-radius:50%; margin-left:0.5rem; }
    .otp-sound-on { background:#10b981; }
    .otp-sound-off { background:#ef4444; }

    .otp-new-row { animation: otp-highlight 2s ease-out; }
    @keyframes otp-highlight {
      0% { background-color: #fef3c7; }
      100% { background-color: transparent; }
    }
    .dark .otp-new-row {
      animation: otp-highlight-dark 2s ease-out;
    }
    @keyframes otp-highlight-dark {
      0% { background-color: #78350f; }
      100% { background-color: transparent; }
    }
  </style>

  <div x-data="otpRequests()" x-init="init()" class="otp-wrap">
    {{-- Stats --}}
    <div class="otp-stats">
      <div class="otp-stat-card">
        <div class="otp-stat-label">Pending</div>
        <div class="otp-stat-value pending" x-text="stats.pending"></div>
      </div>
      <div class="otp-stat-card">
        <div class="otp-stat-label">Sent (today)</div>
        <div class="otp-stat-value sent" x-text="stats.sent"></div>
      </div>
      <div class="otp-stat-card">
        <div class="otp-stat-label">Expired</div>
        <div class="otp-stat-value expired" x-text="stats.expired"></div>
      </div>
      <div class="otp-stat-card">
        <div class="otp-stat-label">
          Sound
          <span class="otp-sound-indicator" :class="soundEnabled ? 'otp-sound-on' : 'otp-sound-off'"></span>
        </div>
        <div style="margin-top:0.25rem;">
          <button @click="soundEnabled = !soundEnabled" class="otp-btn" :class="soundEnabled ? 'otp-btn-send' : 'otp-btn-copy'" x-text="soundEnabled ? 'ON' : 'OFF'" style="font-size:0.75rem; padding:0.25rem 0.75rem;"></button>
        </div>
      </div>
    </div>

    {{-- Filters --}}
    <div class="otp-filters">
      <button @click="filter = 'all'" class="otp-filter-btn" :class="{ active: filter === 'all' }">All</button>
      <button @click="filter = 'pending'" class="otp-filter-btn" :class="{ active: filter === 'pending' }">Pending</button>
      <button @click="filter = 'sent'" class="otp-filter-btn" :class="{ active: filter === 'sent' }">Sent</button>
      <button @click="filter = 'expired'" class="otp-filter-btn" :class="{ active: filter === 'expired' }">Expired</button>
    </div>

    {{-- Table --}}
    <table class="otp-table">
      <thead>
        <tr>
          <th>Phone</th>
          <th>OTP Code</th>
          <th>Status</th>
          <th>Requested</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <template x-for="req in filteredRequests" :key="req.id">
          <tr :class="req.isNew ? 'otp-new-row' : ''">
            <td>
              <span class="otp-phone" x-text="req.phone"></span>
            </td>
            <td>
              <span class="otp-code" @click="copyOtp(req.otp)" :title="'Click to copy'" x-text="req.otp"></span>
            </td>
            <td>
              <span class="otp-pill"
                :class="{
                  'otp-pill-pending': req.status === 'pending',
                  'otp-pill-sent': req.status === 'sent',
                  'otp-pill-expired': req.status === 'expired',
                }"
                x-text="req.status"
              ></span>
            </td>
            <td>
              <span class="otp-time-ago" x-text="timeAgo(req.createdAt)"></span>
            </td>
            <td style="display:flex; gap:0.5rem;">
              <button
                @click="markSent(req)"
                class="otp-btn otp-btn-send"
                :disabled="req.status !== 'pending'"
                title="Mark as sent"
              >
                ✓ Sent
              </button>
              <button
                @click="copyOtp(req.otp)"
                class="otp-btn otp-btn-copy"
                title="Copy OTP code"
              >
                Copy
              </button>
            </td>
          </tr>
        </template>
      </tbody>
    </table>

    <div x-show="filteredRequests.length === 0" class="otp-empty">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
      </svg>
      <p x-text="filter === 'all' ? 'No OTP requests yet. They will appear here in real-time.' : 'No ' + filter + ' requests.'"></p>
    </div>

    {{-- Toast --}}
    <div x-show="toast" x-transition class="otp-toast" x-text="toastMessage"></div>
  </div>

  {{-- Firebase JS SDK --}}
  <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore-compat.js"></script>

  <script>
    function otpRequests() {
      return {
        requests: [],
        filter: 'pending',
        db: null,
        unsub: null,
        soundEnabled: true,
        toast: false,
        toastMessage: '',
        knownIds: new Set(),
        initialLoad: true,

        get stats() {
          const now = new Date();
          const todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate());
          return {
            pending: this.requests.filter(r => r.status === 'pending').length,
            sent: this.requests.filter(r => r.status === 'sent' && r.createdAt && r.createdAt.toDate() >= todayStart).length,
            expired: this.requests.filter(r => r.status === 'expired').length,
          };
        },

        get filteredRequests() {
          let result = this.requests;
          if (this.filter !== 'all') {
            result = result.filter(r => r.status === this.filter);
          }
          return result;
        },

        init() {
          const config = @json($firebaseConfig);
          if (!config.apiKey || !config.projectId) {
            console.error('Firebase config missing.');
            return;
          }
          if (!firebase.apps.length) {
            firebase.initializeApp(config);
          }
          this.db = firebase.firestore();
          this.listen();

          // Auto-expire old pending OTPs client-side every 30s
          setInterval(() => this.autoExpire(), 30000);
        },

        listen() {
          this.unsub = this.db
            .collection('otp_requests')
            .orderBy('createdAt', 'desc')
            .limit(100)
            .onSnapshot(snap => {
              const newRequests = [];
              snap.docs.forEach(doc => {
                const data = doc.data();
                const req = { id: doc.id, ...data, isNew: false };

                // Highlight new arrivals (not on initial load)
                if (!this.initialLoad && !this.knownIds.has(doc.id)) {
                  req.isNew = true;
                  if (this.soundEnabled && data.status === 'pending') {
                    this.playSound();
                  }
                  this.showToast('New OTP request from ' + (data.phone || 'unknown'));
                }
                this.knownIds.add(doc.id);
                newRequests.push(req);
              });
              this.requests = newRequests;
              this.initialLoad = false;
            });
        },

        async markSent(req) {
          if (!req.id || req.status !== 'pending') return;
          try {
            await this.db.collection('otp_requests').doc(req.id).update({
              status: 'sent',
              sentAt: firebase.firestore.FieldValue.serverTimestamp(),
              sentBy: 'admin',
            });
            this.showToast('Marked as sent');
          } catch (e) {
            console.error('Failed to update:', e);
            this.showToast('Error updating status');
          }
        },

        async copyOtp(code) {
          try {
            await navigator.clipboard.writeText(code);
            this.showToast('OTP copied: ' + code);
          } catch (e) {
            // Fallback
            const ta = document.createElement('textarea');
            ta.value = code;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            this.showToast('OTP copied: ' + code);
          }
        },

        autoExpire() {
          const fiveMinAgo = new Date(Date.now() - 5 * 60 * 1000);
          this.requests.forEach(req => {
            if (req.status === 'pending' && req.createdAt) {
              const created = req.createdAt.toDate ? req.createdAt.toDate() : new Date(req.createdAt);
              if (created < fiveMinAgo) {
                this.db.collection('otp_requests').doc(req.id).update({
                  status: 'expired',
                }).catch(e => console.warn('Auto-expire failed:', e));
              }
            }
          });
        },

        timeAgo(ts) {
          if (!ts) return '—';
          const date = ts.toDate ? ts.toDate() : new Date(ts);
          const diff = Math.floor((Date.now() - date.getTime()) / 1000);
          if (diff < 10) return 'just now';
          if (diff < 60) return diff + 's ago';
          if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
          if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
          return date.toLocaleDateString();
        },

        playSound() {
          try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.value = 880;
            osc.type = 'sine';
            gain.gain.value = 0.3;
            osc.start();
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
            osc.stop(ctx.currentTime + 0.3);
          } catch (e) { /* silent fail */ }
        },

        showToast(msg) {
          this.toastMessage = msg;
          this.toast = true;
          setTimeout(() => { this.toast = false; }, 3000);
        },
      };
    }
  </script>
</x-filament-panels::page>
