<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Docker Generator' }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --pg-bg: #f1f4fa;
            --pg-bg-soft: #e7edf8;
            --pg-surface: rgba(255, 255, 255, 0.9);
            --pg-border: #d8e0ef;
            --pg-text-main: #0f172a;
            --pg-text-soft: #56637a;
            --pg-accent: #1f4e8c;
            --pg-accent-dark: #173b6a;
            --pg-gold: #eab346;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            color: var(--pg-text-main);
            background:
                radial-gradient(circle at 8% -8%, rgba(31, 78, 140, 0.18), transparent 34%),
                radial-gradient(circle at 94% 3%, rgba(234, 179, 70, 0.12), transparent 30%),
                linear-gradient(180deg, var(--pg-bg-soft), var(--pg-bg));
        }

        .main-wrapper {
            margin-left: 280px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }

        .content { flex: 1; padding: 24px; }

        .backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(8, 14, 26, 0.58);
            z-index: 999;
            backdrop-filter: blur(4px);
        }
        .backdrop.active { display: block; }

        /* ── HEADER ── */
        .header {
            height: 74px;
            background: var(--pg-surface);
            border-bottom: 1px solid var(--pg-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.07);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(8px);
        }
        .header-left { display: flex; align-items: center; gap: 20px; }
        .menu-toggle {
            display: none;
            background: linear-gradient(145deg, #ffffff, #f2f5fb);
            border: 1px solid var(--pg-border);
            width: 40px;
            height: 40px;
            border-radius: 12px;
            font-size: 18px;
            cursor: pointer;
            color: var(--pg-accent-dark);
        }
        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: #0b1222;
            letter-spacing: -0.02em;
        }
        .header-right { display: flex; align-items: center; gap: 20px; }
        .search-box {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f8fbff;
            border: 1px solid var(--pg-border);
            padding: 10px 14px;
            border-radius: 14px;
            width: 340px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .search-box:focus-within {
            border-color: rgba(31, 78, 140, 0.42);
            box-shadow: 0 0 0 4px rgba(31, 78, 140, 0.12);
        }
        .search-box i { color: #6b7891; }
        .search-box input {
            border: none;
            background: none;
            outline: none;
            width: 100%;
            font-size: 14px;
            color: #182338;
            font-weight: 500;
        }
        .search-box input::placeholder {
            color: #8893a7;
        }
        .search-pill {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            color: var(--pg-accent-dark);
            background: rgba(31, 78, 140, 0.1);
            border: 1px solid rgba(31, 78, 140, 0.2);
            border-radius: 999px;
            padding: 4px 8px;
        }
        .header-actions { display: flex; align-items: center; gap: 15px; }
        .header-btn {
            position: relative;
            background: linear-gradient(145deg, #ffffff, #f2f5fb);
            border: 1px solid var(--pg-border);
            width: 40px;
            height: 40px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4a5670;
        }
        .header-btn:hover {
            color: #fff;
            background: linear-gradient(145deg, var(--pg-accent), var(--pg-accent-dark));
            border-color: transparent;
            transform: translateY(-1px);
            box-shadow: 0 10px 18px rgba(31, 78, 140, 0.28);
        }

        /* Chat button glows */
        .header-btn.chat-btn {
            background: linear-gradient(145deg, var(--pg-accent), var(--pg-accent-dark));
            color: white;
            border-color: transparent;
            box-shadow: 0 10px 20px rgba(31, 78, 140, 0.35);
        }
        .header-btn.chat-btn:hover {
            box-shadow: 0 12px 24px rgba(31, 78, 140, 0.48);
            transform: translateY(-1px);
        }

        .notif-badge {
            position: absolute; top: -5px; right: -5px;
            background: #b42318;
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            border: 1px solid #ffffff;
        }
        .user-menu {
            display: flex; align-items: center; gap: 10px;
            cursor: pointer;
            padding: 6px 10px;
            border-radius: 12px;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }
        .user-menu:hover {
            background: #f8fbff;
            border-color: var(--pg-border);
        }
        .user-menu img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            border: 2px solid rgba(234, 179, 70, 0.5);
        }

        /* ══════════════════════════════════════
           DARK PREMIUM CHAT PANEL
        ══════════════════════════════════════ */
        .chat-panel {
            position: fixed;
            top: 0; right: -480px;
            width: 420px; height: 100vh;
            background: #0f0f1a;
            box-shadow: -8px 0 40px rgba(0,0,0,0.6);
            z-index: 1001;
            transition: right 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            display: flex;
            flex-direction: column;
            border-left: 1px solid rgba(255,255,255,0.06);
        }
        .chat-panel.show { right: 0; }

        /* Glowing top border */
        .chat-panel::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #667eea);
            background-size: 200% 100%;
            animation: borderFlow 3s linear infinite;
        }
        @keyframes borderFlow {
            0% { background-position: 0% 0%; }
            100% { background-position: 200% 0%; }
        }

        /* ── Panel Header ── */
        .cp-header {
            padding: 0 20px;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #13131f;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            flex-shrink: 0;
        }
        .cp-header-left { display: flex; align-items: center; gap: 12px; }
        .cp-ai-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #b90c0c, #510101);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(102,126,234,0.3);
        }
        .cp-title { color: #f0f0ff; font-size: 15px; font-weight: 600; letter-spacing: 0.3px; }
        .cp-subtitle {
            color: #6b7280; font-size: 11px; margin-top: 1px;
            display: flex; align-items: center; gap: 5px;
        }
        .online-dot {
            width: 6px; height: 6px;
            background: #22c55e; border-radius: 50%;
            animation: blink 2s ease-in-out infinite;
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        .cp-close {
            width: 34px; height: 34px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 8px;
            color: #9ca3af; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; transition: all 0.2s;
        }
        .cp-close:hover { background: rgba(239,68,68,0.15); color: #ef4444; border-color: rgba(239,68,68,0.3); }

        /* ── Messages ── */
        .cp-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px 16px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            background: #0f0f1a;

            /* Custom scrollbar */
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.1) transparent;
        }
        .cp-messages::-webkit-scrollbar { width: 4px; }
        .cp-messages::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

        /* Empty State */
        .cp-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
            padding: 30px 20px;
            gap: 12px;
        }
        .cp-empty-orb {
            width: 80px; height: 80px;
            background: linear-gradient(135deg, rgba(102,126,234,0.2), rgba(118,75,162,0.2));
            border: 1px solid rgba(102,126,234,0.3);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 32px;
            box-shadow: 0 0 30px rgba(102,126,234,0.2);
        }
        .cp-empty h4 { color: #e2e8f0; font-size: 15px; font-weight: 600; }
        .cp-empty p { color: #6b7280; font-size: 13px; }
        .cp-suggestion-chips {
            display: flex; flex-direction: column; gap: 8px; width: 100%; margin-top: 8px;
        }
        .chip {
            background: rgba(102,126,234,0.1);
            border: 1px solid rgba(102,126,234,0.2);
            color: #a5b4fc;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: left;
        }
        .chip:hover {
            background: rgba(102,126,234,0.2);
            border-color: rgba(102,126,234,0.4);
            color: #c7d2fe;
            transform: translateX(3px);
        }

        /* ── Bubbles ── */
        .msg-row {
            display: flex;
            align-items: flex-end;
            gap: 10px;
        }
        .msg-row.user { flex-direction: row-reverse; }
        .msg-row.ai { flex-direction: row; }

        .msg-avatar {
            width: 28px; height: 28px;
            border-radius: 8px;
            flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px;
        }
        .msg-avatar.ai {
            background: linear-gradient(135deg, 135deg, #b90c0c, #510101);
            color: white;
        }
        .msg-avatar.user {
            background: linear-gradient(135deg, #06b6d4, #3b82f6);
            color: white;
        }

        .msg-bubble {
            max-width: 78%;
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 13.5px;
            line-height: 1.6;
            word-wrap: break-word;
            position: relative;
        }

        /* User bubble */
        .msg-bubble.user {
            background: linear-gradient(135deg, #b90c0c, #510101);
            color: #fff;
            border-bottom-right-radius: 4px;
            box-shadow: 0 4px 15px rgba(102,126,234,0.3);
        }

        /* AI bubble */
        .msg-bubble.ai {
            background: #1e1e30;
            color: #e2e8f0;
            border: 1px solid rgba(255,255,255,0.07);
            border-bottom-left-radius: 4px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .msg-time {
            font-size: 10px;
            margin-top: 6px;
            opacity: 0.5;
            text-align: right;
        }

        /* ── Typing Indicator ── */
        .cp-typing {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            padding: 0 16px 4px;
        }
        .typing-bubble {
            background: #1e1e30;
            border: 1px solid rgba(255,255,255,0.07);
            padding: 12px 16px;
            border-radius: 16px;
            border-bottom-left-radius: 4px;
            display: flex; gap: 5px; align-items: center;
        }
        .typing-bubble span {
            width: 7px; height: 7px;
            background: #667eea;
            border-radius: 50%;
            animation: typingBounce 1.4s ease-in-out infinite;
        }
        .typing-bubble span:nth-child(2) { animation-delay: 0.2s; }
        .typing-bubble span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes typingBounce {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.5; }
            30% { transform: translateY(-6px); opacity: 1; }
        }

        /* ── Smart Suggestions ── */
        .cp-suggestions {
            padding: 0 16px 10px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .cp-suggestions .chip {
            background: rgba(102,126,234,0.14);
            border: 1px solid rgba(102,126,234,0.35);
            color: #cbd5ff;
            padding: 8px 12px;
            border-radius: 12px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .cp-suggestions .chip:hover {
            background: rgba(102,126,234,0.28);
            transform: translateY(-1px);
        }

        /* ── Input Bar ── */
        .cp-input-bar {
            padding: 14px 16px;
            background: #13131f;
            border-top: 1px solid rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .cp-input {
            flex: 1;
            background: #1e1e30;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            padding: 11px 16px;
            color: #e2e8f0;
            font-size: 13.5px;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .cp-input::placeholder { color: #4b5563; }
        .cp-input:focus {
            border-color: rgba(102,126,234,0.5);
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        .cp-input:disabled { opacity: 0.5; }

        .cp-send-btn {
            width: 42px; height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            color: white;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            font-size: 15px;
            transition: all 0.2s;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(102,126,234,0.35);
        }
        .cp-send-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(204, 208, 230, 0.5);
        }
        .cp-send-btn:disabled {
            background: #2a2a3d;
            box-shadow: none;
            cursor: not-allowed;
            transform: none;
        }

        /* Footer hint */
        .cp-footer-hint {
            text-align: center;
            padding: 6px 16px 10px;
            font-size: 10px;
            color: #374151;
            background: #13131f;
        }

        @media (max-width: 768px) {
            .main-wrapper { margin-left: 0; }
            .menu-toggle { display: block; }
            .search-box { display: none; }
            .chat-panel { width: 100%; right: -100%; }
        }
    </style>
    {{ $styles ?? '' }}
</head>
<body>
    <x-partials.sidebar />

    <div class="main-wrapper">
        <header class="header">
            <div class="header-left">
                <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
                <h1 class="page-title">{{ $pageTitle ?? 'Dashboard' }}</h1>
            </div>
            <div class="header-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Filter projects, status, services...">
                    <span class="search-pill">Filter</span>
                </div>
                <div class="header-actions">
                    <button class="header-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notif-badge">3</span>
                    </button>
                    <button class="header-btn chat-btn" id="chatToggle" title="AI Assistant">
                        <i class="fas fa-robot"></i>
                    </button>

                    <button class="header-btn" id="themeToggle">
                        <i class="fas fa-moon"></i>
                    </button>
                    <div class="user-menu">
                        <img src="https://ui-avatars.com/api/?name=Developer&background=667eea&color=fff" alt="User">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
            </div>
        </header>

        <main class="content">{{ $slot }}</main>
        <x-partials.footer />
    </div>

    <div id="chatPanel" class="chat-panel" x-data="chatApp()">

        <div class="cp-header">
            <div class="cp-header-left">
                <div class="cp-ai-icon">🤖</div>
                <div>
                    <div class="cp-title">AI Docker Assistant</div>
                    <div class="cp-subtitle">
                        <span class="online-dot"></span>
                        Connected to your database
                    </div>
                </div>
            </div>
            <button class="cp-close" id="chatClose"><i class="fas fa-times"></i></button>
        </div>

        <div class="cp-messages" x-ref="messagesContainer">
            <div class="cp-empty" x-show="messages.length === 0">
                <div class="cp-empty-orb">🧠</div>
                <h4>Ask anything about your projects</h4>
                <p>Powered by AI + your live database</p>
                {{-- <div class="cp-suggestion-chips">
                    <div class="chip" @click="quickAsk('How many projects have been created?')">
                        💬 How many projects created?
                    </div>
                    <div class="chip" @click="quickAsk('Show all active projects')">
                        🟢 Show all active projects
                    </div>
                    <div class="chip" @click="quickAsk('What is the latest project?')">
                        🆕 What is the latest project?
                    </div>
                    <div class="chip" @click="quickAsk('How many failed builds?')">
                        ❌ How many failed builds?
                    </div>
                </div> --}}
            </div>
            <template x-for="(msg, index) in messages" :key="index">
                <div class="msg-row" :class="msg.role">
                    <div class="msg-avatar" :class="msg.role">
                        <i :class="msg.role === 'user' ? 'fas fa-user' : 'fas fa-robot'"></i>
                    </div>
                    <div class="msg-bubble" :class="msg.role">
                        <span x-text="msg.content"></span>
                        <div class="msg-time" x-text="msg.time"></div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Typing Indicator -->
        <div class="cp-typing" x-show="loading">
            <div class="msg-avatar ai"><i class="fas fa-robot"></i></div>
            <div class="typing-bubble">
                <span></span><span></span><span></span>
            </div>
        </div>

        <!-- Proactive Suggestions -->
        <div class="cp-suggestions" x-show="suggestions.length">
            <template x-for="(sug, i) in suggestions" :key="i">
                <div class="chip" @click="quickAsk(sug)" x-text="sug"></div>
            </template>
        </div>

        <!-- Input -->
        <div class="cp-input-bar">
            <input
                class="cp-input"
                type="text"
                x-model="question"
                placeholder="Ask about your Docker projects..."
                @keyup.enter="sendMessage"
                :disabled="loading"
            >
            <button
                class="cp-send-btn"
                @click="sendMessage"
                :disabled="!question.trim() || loading"
            >
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
        <div class="cp-footer-hint">⚡ Queries your live database in real-time</div>
    </div>

    <div class="backdrop" id="backdrop"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const menuToggle = document.getElementById('menuToggle');
        const closeSidebar = document.getElementById('closeSidebar');
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('backdrop');
        const themeToggle = document.getElementById('themeToggle');
        let isDark = false;

        menuToggle?.addEventListener('click', () => { sidebar?.classList.add('active'); backdrop?.classList.add('active'); });
        closeSidebar?.addEventListener('click', () => { sidebar?.classList.remove('active'); backdrop?.classList.remove('active'); });
        backdrop?.addEventListener('click', () => { sidebar?.classList.remove('active'); backdrop?.classList.remove('active'); closeChat(); });
        document.querySelectorAll('.has-submenu').forEach(i => i.addEventListener('click', e => { e.preventDefault(); i.classList.toggle('active'); }));
        themeToggle?.addEventListener('click', () => {
            isDark = !isDark;
            document.body.style.background = isDark ? '#1a1a2e' : '#f5f7fa';
            document.body.style.color = isDark ? '#fff' : '#333';
            themeToggle.querySelector('i').className = isDark ? 'fas fa-sun' : 'fas fa-moon';
        });

        const chatToggle = document.getElementById('chatToggle');
        const chatClose = document.getElementById('chatClose');
        const chatPanel = document.getElementById('chatPanel');

        chatToggle?.addEventListener('click', () => {
            chatPanel.classList.add('show');
            backdrop.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        chatClose?.addEventListener('click', closeChat);
        function closeChat() {
            chatPanel?.classList.remove('show');
            backdrop?.classList.remove('active');
            document.body.style.overflow = '';
        }

        function chatApp() {
            return {
                messages: [],
                question: '',
                loading: false,
                suggestions: [],

                scrollBottom() {
                    this.$nextTick(() => {
                        const el = this.$refs.messagesContainer;
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                },

                quickAsk(q) {
                    this.question = q;
                    this.sendMessage();
                },

                async sendMessage() {
                    if (!this.question.trim() || this.loading) return;

                    const now = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                    const userQuestion = this.question.trim(); 
                    this.messages.push({ role: 'user', content: userQuestion, time: now });
                    this.question = '';
                    this.loading = true;
                    this.scrollBottom();

                    try {
                        const response = await fetch('/chat-ask', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ question: userQuestion })
                        });

                        const data = await response.json();
                        const now2 = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

                        this.messages.push({
                            role: 'ai',
                            content: data.answer ?? '⚠️ No response received.',
                            time: now2
                        });

                        this.suggestions = (data.suggestions || []).slice(0, 3);

                    } catch (error) {
                        console.error('Chat error:', error);
                        const now2 = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                        this.messages.push({
                            role: 'ai',
                            content: '❌ Connection error. Is your server running on port 8677?',
                            time: now2
                        });
                    }

                    this.loading = false;
                    this.scrollBottom();
                }
            }
        }
    </script>
    {{ $scripts ?? '' }}
</body>
</html>
