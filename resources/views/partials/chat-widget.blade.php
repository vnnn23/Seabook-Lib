@auth
    @if(Auth::user()->isUser() && Auth::user()->isAktif())
    <!-- Floating Chat Widget SyBook -->
    <div id="sybookChatWidget" style="position: fixed !important; bottom: 24px !important; right: 24px !important; z-index: 99999 !important; font-family: 'Plus Jakarta Sans', sans-serif;">
        <!-- Chat Toggle Button -->
        <button id="chatToggleBtn" onclick="toggleSybookChat()" style="width: 58px; height: 58px; border-radius: 50%; background: #2563eb; color: white; border: none; box-shadow: 0 8px 24px rgba(37,99,235,0.35); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative;">
            <i id="chatIconOpen" class="fa-solid fa-comments"></i>
            <i id="chatIconClose" class="fa-solid fa-xmark" style="display: none; font-size: 1.3rem;"></i>
            <span id="chatUnreadDot" style="display: none; position: absolute; top: 2px; right: 2px; width: 14px; height: 14px; background: #ef4444; border-radius: 50%; border: 2px solid white;"></span>
        </button>

        <!-- Chat Popover Window -->
        <div id="chatWindow" style="display: none; position: absolute; bottom: 70px; right: 0; width: 375px; height: 520px; background: #ffffff; border-radius: 16px; box-shadow: 0 16px 40px rgba(15,23,42,0.18); border: 1px solid #e2e8f0; flex-direction: column; overflow: hidden; animation: chatFadeUp 0.25s ease-out;">
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; padding: 14px 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 36px; height: 36px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                            <i id="headerModeIcon" class="fa-solid fa-robot"></i>
                        </div>
                        <div>
                            <div style="font-weight: 700; font-size: 0.95rem; line-height: 1.2;">Layanan Chat {{ config('app.name', 'SeaBook') }}</div>
                            <div id="chatStatusText" style="font-size: 0.75rem; color: #bfdbfe; display: flex; align-items: center; gap: 5px;">
                                <span style="width: 7px; height: 7px; background: #22c55e; border-radius: 50%; display: inline-block;"></span>
                                Asisten AI Siaga
                            </div>
                        </div>
                    </div>
                    <button onclick="toggleSybookChat()" style="background: transparent; border: none; color: white; cursor: pointer; font-size: 1.1rem; opacity: 0.8; padding: 4px;">
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                </div>

                <!-- Mode Switcher Tabs -->
                <div style="display: flex; background: rgba(0,0,0,0.18); border-radius: 8px; padding: 3px; gap: 4px;">
                    <button id="btnModeAi" onclick="switchChatMode('ai')" style="flex: 1; padding: 6px 10px; border-radius: 6px; border: none; font-size: 0.78rem; font-weight: 700; cursor: pointer; background: white; color: #2563eb; transition: all 0.2s;">
                        <i class="fa-solid fa-wand-magic-sparkles" style="margin-right: 4px;"></i> Asisten AI
                    </button>
                    <button id="btnModePetugas" onclick="switchChatMode('petugas')" style="flex: 1; padding: 6px 10px; border-radius: 6px; border: none; font-size: 0.78rem; font-weight: 600; cursor: pointer; background: transparent; color: rgba(255,255,255,0.85); transition: all 0.2s;">
                        <i class="fa-solid fa-headset" style="margin-right: 4px;"></i> Hubungi Petugas
                    </button>
                </div>
            </div>

            <!-- Pemberitahuan Privasi -->
            <div style="padding: 7px 14px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 0.68rem; color: #64748b; line-height: 1.35; display: flex; align-items: flex-start; gap: 6px;">
                <i class="fa-solid fa-shield-halved" style="color: #64748b; margin-top: 1px; font-size: 0.75rem; flex-shrink: 0;"></i>
                <span><strong>Pemberitahuan Privasi:</strong> Pesan pada mode AI diproses layanan pihak ketiga; percakapan dengan petugas dapat dibaca oleh staf dan administrator perpustakaan.</span>
            </div>

            <!-- Messages Stream Area -->
            <div id="chatMessages" style="flex: 1; padding: 14px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; background: #f8fafc;" aria-busy="true">
                <!-- Initial skeleton state on first load -->
                <div id="chatLoadingState" class="chat-skeleton-container" aria-hidden="true" style="display: flex; flex-direction: column; gap: 12px; padding: 10px 0;">
                    <div style="display: flex; gap: 8px; align-items: flex-start;">
                        <div class="skeleton skeleton-circle" style="width: 28px; height: 28px; flex-shrink: 0;"></div>
                        <div style="flex: 1; max-width: 80%;">
                            <div class="skeleton skeleton-text" style="width: 85%; height: 16px;"></div>
                            <div class="skeleton skeleton-text" style="width: 60%; height: 14px;"></div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                        <div style="width: 65%;">
                            <div class="skeleton skeleton-text" style="width: 100%; height: 16px; margin-left: auto;"></div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: flex-start;">
                        <div class="skeleton skeleton-circle" style="width: 28px; height: 28px; flex-shrink: 0;"></div>
                        <div style="flex: 1; max-width: 85%;">
                            <div class="skeleton skeleton-text" style="width: 90%; height: 16px;"></div>
                            <div class="skeleton skeleton-text" style="width: 45%; height: 14px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Typing indicator -->
            <div id="chatTypingIndicator" style="display: none; padding: 6px 16px; background: #f8fafc; font-size: 0.78rem; color: #64748b;">
                <span id="typingSender">Asisten AI</span> sedang mengetik <span class="typing-dots"><span>.</span><span>.</span><span>.</span></span>
            </div>

            <!-- Input Footer -->
            <form id="chatForm" onsubmit="handleSendChat(event)" style="padding: 10px 12px; background: white; border-top: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 4px;">
                @csrf
                <div style="display: flex; align-items: center; gap: 8px;">
                    <input type="text" id="chatInput" placeholder="Tanyakan seputar buku atau peminjaman..." maxlength="500" required
                        style="flex: 1; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 24px; font-size: 0.88rem; outline: none; transition: border-color 0.2s;"
                        onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#cbd5e1'" oninput="updateCharCount(this)">
                    <button type="submit" id="chatSendBtn" style="width: 40px; height: 40px; border-radius: 50%; background: #2563eb; color: white; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.95rem; transition: background 0.2s;">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.7rem; color: #94a3b8; padding: 0 6px;">
                    <span id="chatModeHelp">Mode AI: Jawaban instan otomatis</span>
                    <span id="chatCharCounter">0/500</span>
                </div>
            </form>
        </div>
    </div>

    <style>
        @keyframes chatFadeUp {
            from { opacity: 0; transform: translateY(12px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .chat-msg-row { display: flex; width: 100%; margin-bottom: 6px; align-items: flex-end; }
        .chat-msg-user { justify-content: flex-end; }
        .chat-msg-other { justify-content: flex-start; }
        .chat-bubble { max-width: 82%; width: fit-content; padding: 8px 12px; border-radius: 14px; font-size: 0.86rem; word-wrap: break-word; overflow-wrap: anywhere; box-sizing: border-box; }
        .chat-msg-text { white-space: pre-wrap; word-break: break-word; overflow-wrap: anywhere; line-height: 1.45; }
        .bubble-user { background: #2563eb; color: white; border-bottom-right-radius: 3px; }
        .bubble-ai { background: #ffffff; color: #1e293b; border: 1px solid #e2e8f0; border-bottom-left-radius: 3px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        .bubble-staff { background: #ecfdf5; color: #064e3b; border: 1px solid #a7f3d0; border-bottom-left-radius: 3px; }
        .msg-meta { font-size: 0.68rem; margin-top: 3px; opacity: 0.75; text-align: right; }
        .typing-dots span { animation: blink 1.4s infinite both; }
        .typing-dots span:nth-child(2) { animation-delay: .2s; }
        .typing-dots span:nth-child(3) { animation-delay: .4s; }
        @keyframes blink { 0% { opacity: .2; } 20% { opacity: 1; } 100% { opacity: .2; } }
    </style>

    <script>
    (function() {
        let chatState = {
            isOpen: false,
            percakapanId: null,
            currentMode: 'ai',
            lastMessageId: 0,
            pollingTimer: null,
            isSending: false
        };

        const csrfToken = '{{ csrf_token() }}';

        window.toggleSybookChat = function() {
            chatState.isOpen = !chatState.isOpen;
            const windowEl = document.getElementById('chatWindow');
            const iconOpen = document.getElementById('chatIconOpen');
            const iconClose = document.getElementById('chatIconClose');
            const toggleBtn = document.getElementById('chatToggleBtn');

            if (chatState.isOpen) {
                windowEl.style.display = 'flex';
                iconOpen.style.display = 'none';
                iconClose.style.display = 'block';
                toggleBtn.style.transform = 'scale(0.95)';
                document.getElementById('chatUnreadDot').style.display = 'none';

                if (!chatState.percakapanId) {
                    loadActiveConversation();
                } else {
                    startPolling();
                    scrollChatToBottom();
                }
                setTimeout(() => document.getElementById('chatInput').focus(), 150);
            } else {
                windowEl.style.display = 'none';
                iconOpen.style.display = 'block';
                iconClose.style.display = 'none';
                toggleBtn.style.transform = 'scale(1)';
                stopPolling();
            }
        };

        // Hentikan polling jika tab tidak aktif / user beralih aplikasi
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopPolling();
            } else if (chatState.isOpen && chatState.percakapanId) {
                startPolling();
            }
        });

        function startPolling() {
            stopPolling();
            chatState.pollingTimer = setInterval(pollNewMessages, 3500);
        }

        function stopPolling() {
            if (chatState.pollingTimer) {
                clearInterval(chatState.pollingTimer);
                chatState.pollingTimer = null;
            }
        }

        function loadActiveConversation() {
            const container = document.getElementById('chatMessages');
            container.setAttribute('aria-busy', 'true');
            if (!container.querySelector('.chat-skeleton-container')) {
                container.innerHTML = `
                    <div class="chat-skeleton-container" aria-hidden="true" style="display: flex; flex-direction: column; gap: 12px; padding: 10px 0;">
                        <div style="display: flex; gap: 8px; align-items: flex-start;">
                            <div class="skeleton skeleton-circle" style="width: 28px; height: 28px; flex-shrink: 0;"></div>
                            <div style="flex: 1; max-width: 80%;">
                                <div class="skeleton skeleton-text" style="width: 85%; height: 16px;"></div>
                                <div class="skeleton skeleton-text" style="width: 60%; height: 14px;"></div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <div style="width: 65%;">
                                <div class="skeleton skeleton-text" style="width: 100%; height: 16px; margin-left: auto;"></div>
                            </div>
                        </div>
                    </div>`;
            }

            fetch('{{ route("chat.user.active") }}', {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('HTTP ' + res.status);
                }
                return res.json();
            })
            .then(data => {
                if (data && data.success) {
                    chatState.percakapanId = data.percakapan.id;
                    chatState.currentMode = data.percakapan.mode;
                    updateModeUI(data.percakapan.mode, data.percakapan.petugas);

                    container.removeAttribute('aria-busy');
                    container.innerHTML = '';

                    if (data.pesan && data.pesan.length > 0) {
                        data.pesan.forEach(p => appendMessageUI(p));
                        chatState.lastMessageId = data.pesan[data.pesan.length - 1].id;
                    }
                    scrollChatToBottom();
                    startPolling();
                } else {
                    throw new Error(data && data.message ? data.message : 'Gagal memuat sesi chat.');
                }
            })
            .catch(err => {
                console.error('Error loading chat:', err);
                container.removeAttribute('aria-busy');
                container.innerHTML = '<div style="text-align:center;color:#ef4444;font-size:0.85rem;padding:25px 15px;">' +
                    '<i class="fa-solid fa-triangle-exclamation" style="font-size:1.5rem;margin-bottom:8px;display:block;"></i>' +
                    '<b>Gagal memuat sesi chat.</b><br>' +
                    '<span style="font-size:0.75rem;color:#64748b;display:block;margin-top:4px;">Pastikan tabel database chat sudah dimigrasi (<code>php artisan migrate</code>).</span>' +
                    '<button onclick="loadActiveConversation()" style="margin-top:10px;padding:5px 12px;background:#2563eb;color:#fff;border:none;border-radius:6px;font-size:0.75rem;cursor:pointer;">Coba Lagi</button>' +
                    '</div>';
            });
        }

        window.switchChatMode = function(targetMode) {
            if (targetMode === chatState.currentMode) return;
            if (!chatState.percakapanId) return;

            fetch(`/chat/switch-mode/${chatState.percakapanId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ mode: targetMode })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    chatState.currentMode = data.mode;
                    updateModeUI(data.mode);
                    pollNewMessages();
                }
            });
        };

        function updateModeUI(mode, petugas = null) {
            const btnAi = document.getElementById('btnModeAi');
            const btnPetugas = document.getElementById('btnModePetugas');
            const statusText = document.getElementById('chatStatusText');
            const modeIcon = document.getElementById('headerModeIcon');
            const helpText = document.getElementById('chatModeHelp');

            if (mode === 'petugas') {
                btnPetugas.style.background = 'white';
                btnPetugas.style.color = '#2563eb';
                btnPetugas.style.fontWeight = '700';

                btnAi.style.background = 'transparent';
                btnAi.style.color = 'rgba(255,255,255,0.85)';
                btnAi.style.fontWeight = '600';

                modeIcon.className = 'fa-solid fa-headset';
                if (petugas) {
                    statusText.innerHTML = `<span style="width: 7px; height: 7px; background: #22c55e; border-radius: 50%; display: inline-block;"></span> Terhubung: ${escapeHtml(petugas.nama)}`;
                } else {
                    statusText.innerHTML = `<span style="width: 7px; height: 7px; background: #eab308; border-radius: 50%; display: inline-block;"></span> Menunggu Petugas...`;
                }
                helpText.innerText = 'Mode Petugas: Pesan dijawab oleh staf perpustakaan';
            } else {
                btnAi.style.background = 'white';
                btnAi.style.color = '#2563eb';
                btnAi.style.fontWeight = '700';

                btnPetugas.style.background = 'transparent';
                btnPetugas.style.color = 'rgba(255,255,255,0.85)';
                btnPetugas.style.fontWeight = '600';

                modeIcon.className = 'fa-solid fa-robot';
                statusText.innerHTML = `<span style="width: 7px; height: 7px; background: #22c55e; border-radius: 50%; display: inline-block;"></span> Asisten AI Siaga`;
                helpText.innerText = 'Mode AI: Jawaban instan otomatis';
            }
        }

        window.handleSendChat = function(e) {
            e.preventDefault();
            if (chatState.isSending) return;
            if (!chatState.percakapanId) {
                alert('Sesi chat belum siap atau sedang dimuat. Harap muat ulang halaman atau tunggu sampai sesi berhasil terhubung.');
                return;
            }

            const input = document.getElementById('chatInput');
            const text = input.value.trim();
            if (!text) return;

            chatState.isSending = true;
            document.getElementById('chatSendBtn').disabled = true;

            // Optimistic render pesan user
            const tempUserMsg = {
                id: 'temp-' + Date.now(),
                pengirim_tipe: 'user',
                isi: text,
                waktu: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})
            };
            appendMessageUI(tempUserMsg);
            input.value = '';
            updateCharCount(input);
            scrollChatToBottom();

            // Tampilkan typing indicator
            const typingInd = document.getElementById('chatTypingIndicator');
            document.getElementById('typingSender').innerText = chatState.currentMode === 'ai' ? 'Asisten AI' : 'Layanan';
            typingInd.style.display = 'block';

            fetch('{{ route("chat.user.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    percakapan_id: chatState.percakapanId,
                    pesan: text
                })
            })
            .then(res => res.json())
            .then(data => {
                typingInd.style.display = 'none';
                chatState.isSending = false;
                document.getElementById('chatSendBtn').disabled = false;

                if (data.success) {
                    if (data.user_message && data.user_message.id) {
                        chatState.lastMessageId = Math.max(chatState.lastMessageId, data.user_message.id);
                    }
                    if (data.ai_reply) {
                        appendMessageUI(data.ai_reply);
                        chatState.lastMessageId = Math.max(chatState.lastMessageId, data.ai_reply.id);
                    }
                    scrollChatToBottom();
                } else {
                    appendMessageUI({
                        pengirim_tipe: 'ai',
                        isi: data.message || 'Gagal mengirim pesan. Silakan coba kembali.',
                        waktu: ''
                    });
                }
            })
            .catch(err => {
                typingInd.style.display = 'none';
                chatState.isSending = false;
                document.getElementById('chatSendBtn').disabled = false;
                appendMessageUI({
                    pengirim_tipe: 'ai',
                    isi: 'Terjadi gangguan jaringan saat mengirim pesan. Silakan coba lagi.',
                    waktu: ''
                });
            });
        };

        function pollNewMessages() {
            if (!chatState.percakapanId || chatState.isSending) return;

            fetch(`/chat/messages/${chatState.percakapanId}?after_id=${chatState.lastMessageId}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.petugas || data.mode) {
                        updateModeUI(data.mode, data.petugas);
                    }
                    if (data.messages && data.messages.length > 0) {
                        data.messages.forEach(msg => {
                            appendMessageUI(msg);
                            chatState.lastMessageId = Math.max(chatState.lastMessageId, msg.id);
                        });
                        scrollChatToBottom();
                    }
                }
            })
            .catch(err => console.debug('Polling background tick:', err));
        }

        function appendMessageUI(msg) {
            const container = document.getElementById('chatMessages');
            const isUser = msg.pengirim_tipe === 'user';
            const isStaff = msg.pengirim_tipe === 'petugas' || msg.pengirim_tipe === 'admin';

            const row = document.createElement('div');
            row.className = 'chat-msg-row ' + (isUser ? 'chat-msg-user' : 'chat-msg-other');

            let bubbleClass = 'bubble-ai';
            let senderLabel = '<i class="fa-solid fa-robot" style="margin-right:3px;"></i> AI';
            if (isUser) {
                bubbleClass = 'bubble-user';
                senderLabel = '';
            } else if (isStaff) {
                bubbleClass = 'bubble-staff';
                senderLabel = '<i class="fa-solid fa-headset" style="margin-right:3px;"></i> Petugas';
            }

            const headerHtml = (!isUser && senderLabel)
                ? `<div style="font-size: 0.7rem; font-weight: 700; margin-bottom: 3px; color: ${isStaff ? '#065f46' : '#2563eb'};">${senderLabel}</div>`
                : '';

            const safeContent = escapeHtml(msg.isi);
            const timeHtml = msg.waktu ? `<div class="msg-meta">${escapeHtml(msg.waktu)}</div>` : '';

            row.innerHTML = `<div class="chat-bubble ${bubbleClass}">${headerHtml}<div class="chat-msg-text">${safeContent}</div>${timeHtml}</div>`;

            container.appendChild(row);
        }

        function scrollChatToBottom() {
            const container = document.getElementById('chatMessages');
            container.scrollTop = container.scrollHeight;
        }

        window.updateCharCount = function(input) {
            const counter = document.getElementById('chatCharCounter');
            counter.innerText = input.value.length + '/500';
            if (input.value.length >= 480) {
                counter.style.color = '#ef4444';
            } else {
                counter.style.color = '#94a3b8';
            }
        };

        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    })();
    </script>
    @endif
@endauth

