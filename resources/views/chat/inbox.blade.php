<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Chat Layanan Pemustaka - {{ config('app.name', 'SeaBook') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Collapsible Sidebar Style & Staff Layout -->
    <link rel="stylesheet" href="{{ asset('css/sidebar-collapsible.css') }}">

    <script>
        if (localStorage.getItem('sybook_sidebar_collapsed') === 'true' && window.innerWidth >= 992) {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #eff6ff;
            --bg-body: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-sub: #64748b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: var(--bg-body); color: var(--text-main); height: 100vh; overflow: hidden; }

        .chat-app-container {
            flex: 1;
            display: flex;
            overflow: hidden;
            height: calc(100vh - 70px);
        }

        /* Sidebar Inbox List */
        .chat-sidebar {
            width: 360px;
            border-right: 1px solid var(--border);
            background: var(--surface);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .inbox-header {
            padding: 16px 20px 10px;
            border-bottom: 1px solid var(--border);
        }
        .inbox-title { font-size: 1.1rem; font-weight: 800; display: flex; align-items: center; justify-content: space-between; }

        .inbox-tabs {
            display: flex;
            gap: 4px;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 8px;
            margin-top: 12px;
        }
        .tab-btn {
            flex: 1;
            padding: 8px 10px;
            border: none;
            background: transparent;
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            color: var(--text-sub);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .tab-btn.active {
            background: white;
            color: var(--primary);
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            font-weight: 700;
        }
        .tab-badge {
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 800;
        }
        .badge-warning { background: #fef08a; color: #854d0e; }
        .badge-primary { background: #dbeafe; color: #1e40af; }
        .badge-gray { background: #e2e8f0; color: #475569; }

        .conversation-list {
            flex: 1;
            overflow-y: auto;
        }
        .conv-item {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            display: flex;
            gap: 12px;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            transition: background 0.15s;
        }
        .conv-item:hover { background: #f8fafc; }
        .conv-item.active { background: var(--primary-light); border-left: 4px solid var(--primary); }

        .conv-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #e0f2fe;
            color: #0369a1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            flex-shrink: 0;
            font-size: 1.1rem;
        }
        .conv-info { flex: 1; min-width: 0; }
        .conv-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
        .conv-name { font-weight: 700; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .conv-time { font-size: 0.72rem; color: var(--text-sub); }
        .conv-preview { font-size: 0.8rem; color: var(--text-sub); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .unread-indicator { width: 18px; height: 18px; background: #ef4444; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.68rem; font-weight: 800; }

        /* Main Chat Window */
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #ffffff;
        }
        .chat-main-header {
            padding: 14px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            min-height: 64px;
        }
        .chat-thread {
            flex: 1;
            padding: 24px;
            overflow-y: auto;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .bubble-row { display: flex; width: 100%; align-items: flex-end; }
        .bubble-row.staff { justify-content: flex-end; }
        .bubble-row.user { justify-content: flex-start; }
        .bubble-row.ai { justify-content: flex-start; }

        .bubble {
            max-width: 65%;
            width: fit-content;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 0.9rem;
            word-wrap: break-word;
            overflow-wrap: anywhere;
            box-sizing: border-box;
        }
        .bubble-text { white-space: pre-wrap; word-break: break-word; overflow-wrap: anywhere; line-height: 1.45; }
        .bubble-staff-ui { background: var(--primary); color: white; border-bottom-right-radius: 2px; }
        .bubble-user-ui { background: white; color: var(--text-main); border: 1px solid var(--border); border-bottom-left-radius: 2px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
        .bubble-ai-ui { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; border-bottom-left-radius: 2px; font-style: italic; }

        .chat-main-footer {
            padding: 14px 24px;
            background: white;
            border-top: 1px solid var(--border);
        }
        .chat-input-form {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .chat-text-input {
            flex: 1;
            padding: 12px 18px;
            border: 1px solid var(--border);
            border-radius: 24px;
            font-size: 0.92rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .chat-text-input:focus { border-color: var(--primary); }
        .btn-send-staff {
            padding: 12px 24px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 24px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-send-staff:hover { background: var(--primary-dark); }

        .empty-placeholder {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-sub);
            gap: 14px;
            padding: 40px;
            text-align: center;
        }
        .btn-action-sm {
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }
        .btn-take { background: #16a34a; color: white; }
        .btn-take:hover { background: #15803d; }
        .btn-close-chat { background: #ef4444; color: white; }
        .btn-close-chat:hover { background: #dc2626; }
    </style>
    @include('partials.motion')
</head>
<body class="staff-layout" style="height: 100vh; overflow: hidden;">

    <!-- Collapsible Staff Sidebar -->
    @include('partials.staff-sidebar', ['active' => 'chat'])

    <!-- Staff Main Content -->
    <div class="staff-main-content" style="height: 100vh; display: flex; flex-direction: column; overflow: hidden;">
        <header class="staff-topbar">
            <div class="staff-topbar-left">
                <h2 class="staff-page-title" style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-headset" style="color: #2563eb;"></i> Live Chat Layanan Pemustaka
                </h2>
            </div>
            <div class="staff-topbar-right">
                <a href="{{ route('dashboard.index') }}" style="font-weight: 600; color: var(--text-sub); font-size: 0.88rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-chart-pie" style="color: #2563eb;"></i> Statistik
                </a>
                <span class="role-pill {{ Auth::user()->isAdmin() ? 'role-admin' : 'role-petugas' }}">
                    <i class="fa-solid {{ Auth::user()->isAdmin() ? 'fa-user-shield' : 'fa-id-badge' }}"></i>
                    {{ ucfirst(Auth::user()->role) }}
                </span>
            </div>
        </header>

        <div class="chat-app-container">
        <!-- Sidebar Daftar Percakapan -->
        <aside class="chat-sidebar">
            <div class="inbox-header">
                <div class="inbox-title">
                    <span>Kotak Masuk Chat</span>
                    <span style="font-size: 0.75rem; color: var(--text-sub); font-weight: 600;">
                        Auto-Refresh: Aktif
                    </span>
                </div>
                <div class="inbox-tabs">
                    <button class="tab-btn active" onclick="switchInboxTab('menunggu', this)">
                        Menunggu
                        <span class="tab-badge {{ $menungguList->count() > 0 ? 'badge-warning' : 'badge-gray' }}">{{ $menungguList->count() }}</span>
                    </button>
                    <button class="tab-btn" onclick="switchInboxTab('aktif', this)">
                        Aktif
                        <span class="tab-badge badge-primary">{{ $aktifList->count() }}</span>
                    </button>
                    <button class="tab-btn" onclick="switchInboxTab('selesai', this)">
                        Selesai
                        <span class="tab-badge badge-gray">{{ $selesaiList->count() }}</span>
                    </button>
                </div>
            </div>

            <!-- Tab Content: Menunggu -->
            <div id="tab-content-menunggu" class="conversation-list tab-view">
                @forelse($menungguList as $conv)
                    <a href="{{ route('chat.inbox', ['id' => $conv->id]) }}" class="conv-item {{ $selectedPercakapan && $selectedPercakapan->id === $conv->id ? 'active' : '' }}">
                        <div class="conv-avatar">
                            {{ substr($conv->user->nama ?? 'U', 0, 1) }}
                        </div>
                        <div class="conv-info">
                            <div class="conv-top">
                                <span class="conv-name">{{ $conv->user->nama ?? 'Pengguna' }}</span>
                                <span class="conv-time">{{ $conv->terakhir_aktivitas ? $conv->terakhir_aktivitas->format('H:i') : '' }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div class="conv-preview">{{ $conv->pesanTerbaru ? $conv->pesanTerbaru->isi : 'Meminta bantuan petugas...' }}</div>
                                @if($conv->unread_count > 0)
                                    <span class="unread-indicator">{{ $conv->unread_count }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div style="text-align: center; padding: 40px 20px; color: var(--text-sub); font-size: 0.85rem;">
                        <i class="fa-solid fa-circle-check" style="font-size: 1.8rem; color: #16a34a; margin-bottom: 8px;"></i>
                        <div>Tidak ada percakapan dalam antrean.</div>
                    </div>
                @endforelse
            </div>

            <!-- Tab Content: Aktif -->
            <div id="tab-content-aktif" class="conversation-list tab-view" style="display: none;">
                @forelse($aktifList as $conv)
                    <a href="{{ route('chat.inbox', ['id' => $conv->id]) }}" class="conv-item {{ $selectedPercakapan && $selectedPercakapan->id === $conv->id ? 'active' : '' }}">
                        <div class="conv-avatar" style="background: #dcfce7; color: #166534;">
                            {{ substr($conv->user->nama ?? 'U', 0, 1) }}
                        </div>
                        <div class="conv-info">
                            <div class="conv-top">
                                <span class="conv-name">{{ $conv->user->nama ?? 'Pengguna' }}</span>
                                <span class="conv-time">{{ $conv->terakhir_aktivitas ? $conv->terakhir_aktivitas->format('H:i') : '' }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div class="conv-preview">{{ $conv->pesanTerbaru ? $conv->pesanTerbaru->isi : 'Percakapan berlangsung' }}</div>
                                @if($conv->unread_count > 0)
                                    <span class="unread-indicator">{{ $conv->unread_count }}</span>
                                @endif
                            </div>
                            @if($isAdmin && $conv->petugas)
                                <div style="font-size: 0.72rem; color: #0369a1; margin-top: 3px;">
                                    <i class="fa-solid fa-user-shield"></i> Ditangani: {{ $conv->petugas->nama }}
                                </div>
                            @endif
                        </div>
                    </a>
                @empty
                    <div style="text-align: center; padding: 40px 20px; color: var(--text-sub); font-size: 0.85rem;">
                        <i class="fa-solid fa-comments" style="font-size: 1.8rem; color: #94a3b8; margin-bottom: 8px;"></i>
                        <div>Belum ada percakapan aktif.</div>
                    </div>
                @endforelse
            </div>

            <!-- Tab Content: Selesai -->
            <div id="tab-content-selesai" class="conversation-list tab-view" style="display: none;">
                @forelse($selesaiList as $conv)
                    <a href="{{ route('chat.inbox', ['id' => $conv->id]) }}" class="conv-item {{ $selectedPercakapan && $selectedPercakapan->id === $conv->id ? 'active' : '' }}">
                        <div class="conv-avatar" style="background: #f1f5f9; color: #64748b;">
                            {{ substr($conv->user->nama ?? 'U', 0, 1) }}
                        </div>
                        <div class="conv-info">
                            <div class="conv-top">
                                <span class="conv-name">{{ $conv->user->nama ?? 'Pengguna' }}</span>
                                <span class="conv-time">{{ $conv->terakhir_aktivitas ? $conv->terakhir_aktivitas->format('d M') : '' }}</span>
                            </div>
                            <div class="conv-preview" style="color: #94a3b8;">Selesai dilayani</div>
                        </div>
                    </a>
                @empty
                    <div style="text-align: center; padding: 40px 20px; color: var(--text-sub); font-size: 0.85rem;">
                        <div>Belum ada arsip percakapan.</div>
                    </div>
                @endforelse
            </div>
        </aside>

        <!-- Area Percakapan Utama -->
        <main class="chat-main">
            @if($selectedPercakapan)
                <!-- Header Percakapan -->
                <div class="chat-main-header">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div class="conv-avatar">
                            {{ substr($selectedPercakapan->user->nama ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 1.05rem;">{{ $selectedPercakapan->user->nama ?? 'Pengguna' }}</div>
                            <div style="font-size: 0.78rem; color: var(--text-sub); display: flex; align-items: center; gap: 8px;">
                                <span><i class="fa-solid fa-envelope"></i> {{ $selectedPercakapan->user->email }}</span>
                                <span>•</span>
                                <span>Poin Pelanggaran: <strong>{{ $selectedPercakapan->user->poin_pelanggaran ?? 0 }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px;">
                        @if($selectedPercakapan->status === 'menunggu')
                            <form action="{{ route('chat.take', $selectedPercakapan->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-action-sm btn-take">
                                    <i class="fa-solid fa-hand-holding-hand"></i> Ambil Percakapan
                                </button>
                            </form>
                        @elseif($selectedPercakapan->status === 'aktif')
                            <form action="{{ route('chat.close', $selectedPercakapan->id) }}" method="POST" onsubmit="return confirm('Tandai percakapan ini sebagai selesai?');">
                                @csrf
                                <button type="submit" class="btn-action-sm btn-close-chat">
                                    <i class="fa-solid fa-circle-check"></i> Selesaikan Sesi
                                </button>
                            </form>
                        @else
                            <span style="background: #e2e8f0; color: #475569; padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;">
                                Percakapan Selesai
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Thread Pesan -->
                <div id="staffChatThread" class="chat-thread">
                    @forelse($selectedPercakapan->pesan as $p)
                        @php
                            $isStaff = $p->isFromStaff();
                            $isAi = $p->isFromAi();
                            $rowClass = $isStaff ? 'staff' : ($isAi ? 'ai' : 'user');
                            $bubbleClass = $isStaff ? 'bubble-staff-ui' : ($isAi ? 'bubble-ai-ui' : 'bubble-user-ui');
                        @endphp
                        <div class="bubble-row {{ $rowClass }}" data-id="{{ $p->id }}">
                            <div class="bubble {{ $bubbleClass }}"><div style="font-size: 0.72rem; font-weight: 700; margin-bottom: 3px; opacity: 0.85;">@if($isStaff)<i class="fa-solid fa-headset"></i> {{ $p->pengirim->nama ?? 'Petugas' }}@elseif($isAi)<i class="fa-solid fa-robot"></i> Asisten AI@else{{ $selectedPercakapan->user->nama ?? 'Pemustaka' }}@endif</div><div class="bubble-text">{{ $p->isi }}</div><div style="font-size: 0.68rem; margin-top: 4px; text-align: right; opacity: 0.75;">{{ $p->created_at ? $p->created_at->format('H:i') : '' }}</div></div>
                        </div>
                    @empty
                        <div style="text-align: center; color: var(--text-sub); margin: auto;">Belum ada riwayat pesan.</div>
                    @endforelse
                </div>

                <!-- Input Footer -->
                @if($selectedPercakapan->status !== 'selesai')
                    <div class="chat-main-footer">
                        <form id="staffReplyForm" onsubmit="handleStaffReply(event)" class="chat-input-form">
                            @csrf
                            <input type="text" id="staffInput" placeholder="Ketik balasan untuk pemustaka..." maxlength="500" required class="chat-text-input">
                            <button type="submit" id="staffSendBtn" class="btn-send-staff">
                                <i class="fa-solid fa-paper-plane"></i> Kirim
                            </button>
                        </form>
                    </div>
                @endif

            @else
                <!-- Kosong / Belum ada percakapan terpilih -->
                <div class="empty-placeholder">
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: #eff6ff; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 2.2rem;">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main);">Kotak Layanan Live Chat Pemustaka</h3>
                    <p style="max-width: 420px; line-height: 1.6;">
                        Silakan pilih percakapan dari daftar di sebelah kiri untuk berinteraksi langsung dengan pemustaka yang memerlukan bantuan petugas perpustakaan.
                    </p>
                </div>
            @endif
        </main>
    </div>

    <script>
        function switchInboxTab(tabName, btnEl) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-view').forEach(view => view.style.display = 'none');

            btnEl.classList.add('active');
            document.getElementById('tab-content-' + tabName).style.display = 'block';
        }

        // Tampilkan tab yang sesuai jika selected conversation ada
        @if($selectedPercakapan)
            @if($selectedPercakapan->status === 'aktif')
                switchInboxTab('aktif', document.querySelectorAll('.tab-btn')[1]);
            @elseif($selectedPercakapan->status === 'selesai')
                switchInboxTab('selesai', document.querySelectorAll('.tab-btn')[2]);
            @endif
        @endif

        // Polling antrean inbox tiap 5 detik (berhenti saat tab tidak aktif)
        let inboxPollTimer = null;
        function pollInbox() {
            if (document.hidden) return;
            fetch('{{ route("chat.inbox.poll") }}', {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.counts) {
                    const tabBadges = document.querySelectorAll('.inbox-tabs .tab-badge');
                    if (tabBadges[0]) tabBadges[0].innerText = data.counts.menunggu;
                    if (tabBadges[1]) tabBadges[1].innerText = data.counts.aktif;
                }
            })
            .catch(err => console.debug('Inbox poll error:', err));
        }

        function startInboxPolling() {
            if (!inboxPollTimer) inboxPollTimer = setInterval(pollInbox, 5000);
        }
        function stopInboxPolling() {
            if (inboxPollTimer) { clearInterval(inboxPollTimer); inboxPollTimer = null; }
        }

        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopInboxPolling();
            } else {
                startInboxPolling();
            }
        });
        startInboxPolling();

        // Scroll thread to bottom
        const thread = document.getElementById('staffChatThread');
        if (thread) {
            thread.scrollTop = thread.scrollHeight;
        }

        @if($selectedPercakapan && $selectedPercakapan->status !== 'selesai')
            let lastMessageId = 0;
            const messageNodes = document.querySelectorAll('#staffChatThread [data-id]');
            if (messageNodes.length > 0) {
                lastMessageId = parseInt(messageNodes[messageNodes.length - 1].getAttribute('data-id')) || 0;
            }

            function pollStaffMessages() {
                if (document.hidden) return;

                fetch(`/staff/chat/{{ $selectedPercakapan->id }}/messages?after_id=${lastMessageId}`, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.messages && data.messages.length > 0) {
                        data.messages.forEach(msg => {
                            appendStaffMessageUI(msg);
                            lastMessageId = Math.max(lastMessageId, msg.id);
                        });
                        thread.scrollTop = thread.scrollHeight;
                    }
                })
                .catch(err => console.debug('Staff poll error:', err));
            }

            // Polling pesan tiap 5 detik dengan pause saat tab tidak aktif
            let msgPollTimer = null;
            function startMsgPolling() {
                if (!msgPollTimer) msgPollTimer = setInterval(pollStaffMessages, 5000);
            }
            function stopMsgPolling() {
                if (msgPollTimer) { clearInterval(msgPollTimer); msgPollTimer = null; }
            }

            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    stopMsgPolling();
                } else {
                    startMsgPolling();
                }
            });
            startMsgPolling();

            function handleStaffReply(e) {
                e.preventDefault();
                const input = document.getElementById('staffInput');
                const text = input.value.trim();
                if (!text) return;

                const sendBtn = document.getElementById('staffSendBtn');
                sendBtn.disabled = true;

                fetch('{{ route("chat.reply", $selectedPercakapan->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ pesan: text })
                })
                .then(res => res.json())
                .then(data => {
                    sendBtn.disabled = false;
                    input.value = '';
                    if (data.success && data.message) {
                        appendStaffMessageUI(data.message);
                        lastMessageId = Math.max(lastMessageId, data.message.id);
                        thread.scrollTop = thread.scrollHeight;
                    }
                })
                .catch(err => {
                    sendBtn.disabled = false;
                    alert('Gagal mengirim pesan balasan.');
                });
            }

            function appendStaffMessageUI(msg) {
                const row = document.createElement('div');
                const isStaff = msg.is_staff || msg.pengirim_tipe === 'petugas' || msg.pengirim_tipe === 'admin';
                const isAi = msg.pengirim_tipe === 'ai';

                row.className = 'bubble-row ' + (isStaff ? 'staff' : (isAi ? 'ai' : 'user'));
                row.setAttribute('data-id', msg.id);

                const bubbleClass = isStaff ? 'bubble-staff-ui' : (isAi ? 'bubble-ai-ui' : 'bubble-user-ui');
                const senderName = isStaff ? '<i class="fa-solid fa-headset"></i> Anda (Petugas)' : (isAi ? '<i class="fa-solid fa-robot"></i> Asisten AI' : '{{ $selectedPercakapan->user->nama ?? "Pemustaka" }}');

                row.innerHTML = `<div class="bubble ${bubbleClass}"><div style="font-size: 0.72rem; font-weight: 700; margin-bottom: 3px; opacity: 0.85;">${senderName}</div><div class="bubble-text">${escapeHtml(msg.isi)}</div><div style="font-size: 0.68rem; margin-top: 4px; text-align: right; opacity: 0.75;">${escapeHtml(msg.waktu || '')}</div></div>`;
                thread.appendChild(row);
            }

            function escapeHtml(text) {
                if (!text) return '';
                const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
                return String(text).replace(/[&<>"']/g, m => map[m]);
            }
        @endif
    </script>
    </div> <!-- Close staff-main-content -->
    <script src="{{ asset('js/sidebar-collapsible.js') }}" defer></script>
</body>
</html>

