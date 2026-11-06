<!DOCTYPE html>
<html lang="vi" class="dark scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $siteName = \App\Models\Setting::getValue('site_name', 'GameHub');
        $pageTitle = isset($title) ? "{$title} | {$siteName}" : "{$siteName} - Cổng Game HTML5 Trực Tuyến Chuẩn Quốc Tế";
        $pageDescription = $metaDescription ?? \App\Models\Setting::getValue('site_description', 'Chơi hàng trăm tựa game HTML5 2D miễn phí tốc độ cao ngay trên trình duyệt PC và Mobile tại GameHub.');
        $canonicalUrl = $canonical ?? url()->current();
        $ogImage = $metaImage ?? asset('images/default-game-thumb.svg');
        $navCategories = \App\Models\Category::where('is_active', true)->orderBy('position')->limit(8)->get();
    @endphp

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:site_name" content="{{ $siteName }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.4/howler.min.js"></script>
    <script>window.GAMEHUB_AUDIO_API_URL = @json(route('audio.settings.show'));</script>
    <script defer src="{{ asset('js/gamehub-audio.js') }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                    },
                    colors: {
                        gh: {
                            bg: '#10121B',
                            secondary: '#181B28',
                            card: '#202437',
                            primary: '#7C5CFF',
                            accent: '#00D4FF',
                            highlight: '#FFB84D',
                            muted: '#A5ABC0',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #10121B;
            color: #FFFFFF;
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            overflow-x: hidden;
        }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #10121B; }
        ::-webkit-scrollbar-thumb { background: #202437; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #7C5CFF; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(124, 92, 255, 0.55), 0 10px 25px -5px rgba(0, 212, 255, 0.4); }
            50% { box-shadow: 0 0 0 10px rgba(124, 92, 255, 0), 0 15px 35px -5px rgba(124, 92, 255, 0.65); }
        }
        .btn-play-pulse {
            animation: pulseGlow 2.4s infinite;
        }
        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-10px) scale(1.03); }
        }
        .animate-float-slow {
            animation: floatSlow 6s ease-in-out infinite;
        }
        .kbd-key {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            padding: 0.25rem 0.6rem;
            border-radius: 0.5rem;
            background: linear-gradient(180deg, #282D46 0%, #181B28 100%);
            border: 1px solid rgba(255, 255, 255, 0.16);
            box-shadow: 0 3px 0 #0B0D14;
            font-size: 0.75rem;
            font-weight: 800;
            color: #FFFFFF;
            letter-spacing: 0.02em;
        }
    </style>
</head>
<body
    data-audio-zone="{{ request()->routeIs('games.play') ? 'game-player' : (request()->routeIs('games.explore', 'games.category') ? 'explore' : 'home') }}"
    class="bg-[#10121B] text-white min-h-screen flex flex-col pb-16 lg:pb-0 selection:bg-[#7C5CFF] selection:text-white"
    x-data="{ mobileMenuOpen: false, mobileSearchOpen: false }"
    @keydown.window.ctrl.k.prevent="$refs.globalSearchInput ? $refs.globalSearchInput.focus() : (mobileSearchOpen = true)"
    @keydown.window.meta.k.prevent="$refs.globalSearchInput ? $refs.globalSearchInput.focus() : (mobileSearchOpen = true)"
>

    <!-- Top Header Navigation (CrazyGames / Poki Standard) -->
    <header class="sticky top-0 z-50 bg-[#181B28]/95 backdrop-blur-xl border-b border-white/10 shadow-lg shadow-black/30">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 h-18 py-3 flex items-center justify-between gap-3 xl:gap-6">
            <!-- Brand Logo & Primary Navigation -->
            <div class="flex items-center gap-4 xl:gap-7 shrink-0">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 group focus:outline-none">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#7C5CFF] to-[#00D4FF] flex items-center justify-center shadow-lg shadow-[#7C5CFF]/30 group-hover:scale-105 group-hover:shadow-[0_0_24px_rgba(124,92,255,0.75)] transition-all duration-300">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-xl font-black tracking-tight bg-gradient-to-r from-white via-white to-[#00D4FF] bg-clip-text text-transparent group-hover:drop-shadow-[0_0_12px_rgba(0,212,255,0.45)] transition">GameHub</span>
                        <span class="hidden sm:inline-block ml-1 text-[10px] font-extrabold uppercase px-1.5 py-0.5 rounded-md bg-[#7C5CFF]/20 text-[#00D4FF] border border-[#7C5CFF]/40">PORTAL</span>
                    </div>
                </a>

                <!-- Desktop Navigation Menu -->
                <nav class="hidden xl:flex items-center gap-1">
                    <a href="{{ route('games.explore') }}" class="px-3.5 py-2 rounded-xl text-sm font-bold transition {{ request()->routeIs('games.explore') && !request('sort') ? 'bg-[#7C5CFF] text-white shadow-md shadow-[#7C5CFF]/30' : 'text-[#A5ABC0] hover:text-white hover:bg-[#202437]' }}">
                        🧭 Khám phá
                    </a>

                    <!-- Category Dropdown -->
                    <div class="relative" x-data="{ catOpen: false }" @mouseenter="catOpen = true" @mouseleave="catOpen = false">
                        <button @click="catOpen = !catOpen" type="button" class="px-3.5 py-2 rounded-xl text-sm font-bold flex items-center gap-1.5 transition {{ request()->routeIs('games.category') ? 'bg-[#7C5CFF] text-white shadow-md shadow-[#7C5CFF]/30' : 'text-[#A5ABC0] hover:text-white hover:bg-[#202437]' }}">
                            <span>🗂️ Danh mục</span>
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="catOpen ? 'rotate-180 text-[#00D4FF]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="catOpen" x-transition.opacity.duration.150ms class="absolute left-0 mt-1.5 w-64 rounded-2xl bg-[#181B28] border border-white/10 shadow-2xl p-2 z-50 grid grid-cols-1 gap-1" style="display: none;">
                            @foreach($navCategories as $navCat)
                                <a href="{{ route('games.category', $navCat->slug) }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold text-[#A5ABC0] hover:text-white hover:bg-[#202437] transition">
                                    <span class="flex items-center gap-2">
                                        <span class="text-[#00D4FF]">🎮</span>
                                        <span>{{ $navCat->name }}</span>
                                    </span>
                                    <span class="text-[10px] text-[#A5ABC0]/70">→</span>
                                </a>
                            @endforeach
                            <div class="border-t border-white/10 mt-1 pt-1">
                                <a href="{{ route('games.explore') }}" class="block text-center py-1.5 text-xs font-extrabold text-[#00D4FF] hover:underline">
                                    Xem tất cả danh mục →
                                </a>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('games.explore', ['sort' => 'newest']) }}" class="px-3.5 py-2 rounded-xl text-sm font-bold transition {{ request('sort') === 'newest' ? 'bg-[#7C5CFF] text-white shadow-md shadow-[#7C5CFF]/30' : 'text-[#A5ABC0] hover:text-white hover:bg-[#202437]' }}">
                        ✨ Game mới
                    </a>
                    <a href="{{ route('games.explore', ['sort' => 'popular']) }}" class="px-3.5 py-2 rounded-xl text-sm font-bold transition {{ request('sort') === 'popular' ? 'bg-[#7C5CFF] text-white shadow-md shadow-[#7C5CFF]/30' : 'text-[#A5ABC0] hover:text-white hover:bg-[#202437]' }}">
                        🔥 Phổ biến
                    </a>
                    <a href="{{ route('games.random') }}" class="px-3.5 py-2 rounded-xl text-sm font-bold text-[#A5ABC0] hover:text-[#FFB84D] hover:bg-[#202437] transition flex items-center gap-1.5" title="Chơi một tựa game ngẫu nhiên">
                        <span>🎲</span> Ngẫu nhiên
                    </a>
                </nav>
            </div>

            <!-- Center Smart Instant Search Bar (With Ctrl + K Shortcut) -->
            <div class="flex-1 max-w-xl hidden md:block relative"
                 x-data="{
                    q: @js(request('q', '')),
                    open: false,
                    loading: false,
                    results: [],
                    async search() {
                        if (this.q.trim().length < 2) { this.results = []; this.loading = false; return; }
                        this.loading = true;
                        try {
                            const res = await fetch('{{ route('games.instant-search') }}?q=' + encodeURIComponent(this.q.trim()));
                            this.results = await res.json();
                            this.open = true;
                        } catch (e) {} finally {
                            this.loading = false;
                        }
                    }
                 }"
                 @click.outside="open = false">
                <form action="{{ route('games.explore') }}" method="GET" class="relative group">
                    <input
                        x-ref="globalSearchInput"
                        type="text"
                        name="q"
                        x-model="q"
                        @input.debounce.220ms="search()"
                        @focus="if (q.trim().length >= 2) open = true"
                        @keydown.escape="open = false; $el.blur()"
                        placeholder="Tìm kiếm game, thể loại (Nhấn Ctrl + K)..."
                        autocomplete="off"
                        class="w-full rounded-2xl bg-[#202437] border border-white/10 group-hover:border-white/20 pl-11 pr-20 py-2.5 text-sm text-white placeholder-[#A5ABC0] focus:outline-none focus:border-[#00D4FF] focus:ring-2 focus:ring-[#7C5CFF]/35 transition-all"
                    >
                    <svg class="w-4 h-4 text-[#00D4FF] absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>

                    <!-- Clear or Ctrl + K Badge -->
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1.5">
                        <button type="button" x-show="q.length > 0" @click="q = ''; results = []; open = false; $refs.globalSearchInput.focus()" class="text-[#A5ABC0] hover:text-white p-1" style="display: none;">
                            ✕
                        </button>
                        <kbd x-show="q.length === 0" class="hidden sm:inline-flex items-center gap-0.5 px-2 py-0.5 rounded-lg bg-[#181B28] border border-white/10 text-[10px] font-bold text-[#A5ABC0] pointer-events-none">
                            Ctrl K
                        </kbd>
                    </div>
                </form>

                <!-- Instant Search Dropdown Results -->
                <div x-show="open && q.trim().length >= 2" x-transition.opacity.duration.150ms class="absolute mt-2 w-full rounded-2xl bg-[#181B28] border border-[#7C5CFF]/40 shadow-2xl shadow-black/80 overflow-hidden z-50" style="display: none;">
                    <template x-if="loading">
                        <div class="p-4 space-y-2.5">
                            <div class="h-11 rounded-xl bg-[#202437] animate-pulse"></div>
                            <div class="h-11 rounded-xl bg-[#202437] animate-pulse"></div>
                        </div>
                    </template>
                    <template x-if="!loading && results.length === 0">
                        <div class="p-5 text-center text-xs text-[#A5ABC0]">
                            Không tìm thấy tựa game nào khớp với "<span class="text-white font-bold" x-text="q"></span>"
                        </div>
                    </template>
                    <template x-if="!loading && results.length > 0">
                        <div>
                            <div class="px-3.5 py-2 bg-[#10121B]/60 border-b border-white/5 flex items-center justify-between text-[11px] font-bold text-[#A5ABC0]">
                                <span>GỢI Ý TỨC THÌ</span>
                                <span x-text="results.length + ' kết quả'"></span>
                            </div>
                            <div class="divide-y divide-white/5 max-h-80 overflow-y-auto">
                                <template x-for="item in results" :key="item.slug">
                                    <a :href="item.url" class="flex items-center gap-3.5 p-3 hover:bg-[#202437] transition group">
                                        <img :src="item.thumbnail_url" :alt="item.title" class="w-16 h-10 rounded-xl object-cover shrink-0 bg-[#10121B] border border-white/10 group-hover:border-[#00D4FF]/60">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-extrabold text-white group-hover:text-[#00D4FF] truncate transition" x-text="item.title"></p>
                                            <p class="text-xs text-[#A5ABC0] truncate">
                                                <span class="text-[#00D4FF] font-semibold" x-text="item.category"></span> • <span x-text="item.play_count"></span> lượt chơi
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span class="text-xs font-extrabold text-[#FFB84D] bg-[#10121B] px-2 py-1 rounded-lg">★ <span x-text="item.avg_rating"></span></span>
                                            <span class="px-2.5 py-1 rounded-lg bg-[#7C5CFF] text-white text-[11px] font-extrabold opacity-0 group-hover:opacity-100 transition">Chơi</span>
                                        </div>
                                    </a>
                                </template>
                            </div>
                            <a :href="'{{ route('games.explore') }}?q=' + encodeURIComponent(q)" class="block text-center py-2.5 bg-[#202437] hover:bg-[#282d46] text-xs font-extrabold text-[#00D4FF] transition">
                                Xem toàn bộ kết quả tìm kiếm →
                            </a>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Right User Area & Mobile Controls -->
            <div class="flex items-center gap-2.5 shrink-0">
                <!-- Global Audio Settings Control Panel (Howler.js + Game Sync) -->
                <div class="relative"
                     x-data="{
                        audioOpen: false,
                        s: {
                            master_volume: 0.80,
                            music_volume: 0.50,
                            ui_volume: 0.70,
                            game_volume: 0.85,
                            mute_all: false,
                            hover_sound_enabled: true,
                            bgm_enabled: false
                        },
                        init() {
                            if (window.GameHubAudio) {
                                this.s = { ...window.GameHubAudio.settings };
                            }
                            window.addEventListener('gamehub:audio-updated', (e) => {
                                if (e.detail) this.s = { ...e.detail };
                            });
                        },
                        sync() {
                            if (window.GameHubAudio) {
                                window.GameHubAudio.updateSettings(this.s);
                            }
                        },
                        togglePopover() {
                            this.audioOpen = !this.audioOpen;
                            if (window.GameHubAudio) {
                                this.audioOpen ? window.GameHubAudio.modalOpen() : window.GameHubAudio.modalClose();
                            }
                        }
                     }">
                    <button
                        type="button"
                        @click="togglePopover()"
                        class="w-11 h-11 rounded-xl bg-[#202437] hover:bg-[#282d46] border border-white/10 hover:border-[#00D4FF]/60 flex items-center justify-center transition"
                        :class="s.mute_all ? 'text-rose-400 border-rose-500/40' : 'text-[#00D4FF]'"
                        title="Cài đặt Âm thanh Toàn hệ thống (Global Audio Settings)"
                        aria-label="Cài đặt Âm thanh"
                    >
                        <span x-text="s.mute_all ? '🔇' : '🔊'" class="text-base">🔊</span>
                    </button>

                    <!-- Audio Settings Dropdown Panel -->
                    <div
                        x-show="audioOpen"
                        x-transition.opacity.duration.150ms
                        @click.outside="if (audioOpen) { audioOpen = false; if (window.GameHubAudio) window.GameHubAudio.modalClose(); }"
                        class="absolute right-0 mt-2 w-80 rounded-2xl bg-[#181B28] border border-[#7C5CFF]/50 shadow-2xl shadow-black/90 p-4 z-50 space-y-4"
                        style="display: none;"
                    >
                        <div class="flex items-center justify-between pb-2.5 border-b border-white/10">
                            <div>
                                <h3 class="text-sm font-extrabold text-white flex items-center gap-1.5">
                                    <span>🎛️</span> Cài Đặt Âm Thanh
                                </h3>
                                <p class="text-[11px] text-[#A5ABC0]">Đồng bộ tự động giữa Website & Game</p>
                            </div>
                            <button
                                type="button"
                                @click="s.mute_all = !s.mute_all; sync()"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-extrabold transition"
                                :class="s.mute_all ? 'bg-rose-500 text-white' : 'bg-[#202437] text-[#A5ABC0] hover:text-white'"
                            >
                                <span x-text="s.mute_all ? '🔇 Đang tắt' : '🔊 Mute All'"></span>
                            </button>
                        </div>

                        <div class="space-y-3 text-xs">
                            <!-- Master Volume -->
                            <div>
                                <div class="flex justify-between font-bold mb-1">
                                    <span class="text-white">Âm lượng tổng (Master)</span>
                                    <span class="text-[#00D4FF]" x-text="Math.round(s.master_volume * 100) + '%'"></span>
                                </div>
                                <input type="range" min="0" max="1" step="0.05" x-model.number="s.master_volume" @input="sync()" class="w-full accent-[#7C5CFF] cursor-pointer">
                            </div>

                            <!-- Background Music Volume -->
                            <div>
                                <div class="flex justify-between font-bold mb-1">
                                    <span class="text-white">Nhạc nền (BGM Volume)</span>
                                    <span class="text-[#7C5CFF]" x-text="Math.round(s.music_volume * 100) + '%'"></span>
                                </div>
                                <input type="range" min="0" max="1" step="0.05" x-model.number="s.music_volume" @input="sync()" class="w-full accent-[#7C5CFF] cursor-pointer">
                            </div>

                            <!-- UI Sound Volume -->
                            <div>
                                <div class="flex justify-between font-bold mb-1">
                                    <span class="text-white">Âm thanh Giao diện (UI)</span>
                                    <span class="text-[#00D4FF]" x-text="Math.round(s.ui_volume * 100) + '%'"></span>
                                </div>
                                <input type="range" min="0" max="1" step="0.05" x-model.number="s.ui_volume" @input="sync()" class="w-full accent-[#00D4FF] cursor-pointer">
                            </div>

                            <!-- Game Sound Volume -->
                            <div>
                                <div class="flex justify-between font-bold mb-1">
                                    <span class="text-white">Âm thanh trong Game</span>
                                    <span class="text-[#FFB84D]" x-text="Math.round(s.game_volume * 100) + '%'"></span>
                                </div>
                                <input type="range" min="0" max="1" step="0.05" x-model.number="s.game_volume" @input="sync()" class="w-full accent-[#FFB84D] cursor-pointer">
                            </div>
                        </div>

                        <div class="pt-2.5 border-t border-white/10 space-y-2 text-xs">
                            <label class="flex items-center justify-between cursor-pointer">
                                <span class="text-[#A5ABC0] font-semibold">🎵 Nhạc nền Trang chủ</span>
                                <input type="checkbox" x-model="s.bgm_enabled" @change="sync()" class="rounded bg-[#202437] border-white/20 text-[#7C5CFF]">
                            </label>
                            <label class="flex items-center justify-between cursor-pointer">
                                <span class="text-[#A5ABC0] font-semibold">✨ Âm thanh lướt chuột (Hover)</span>
                                <input type="checkbox" x-model="s.hover_sound_enabled" @change="sync()" class="rounded bg-[#202437] border-white/20 text-[#00D4FF]">
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Mobile Search Toggle Button -->
                <button @click="mobileSearchOpen = !mobileSearchOpen" type="button" class="md:hidden w-11 h-11 rounded-xl bg-[#202437] border border-white/10 flex items-center justify-center text-[#A5ABC0] hover:text-white" aria-label="Tìm kiếm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>

                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-extrabold uppercase tracking-wider bg-[#FFB84D]/15 text-[#FFB84D] border border-[#FFB84D]/40 hover:bg-[#FFB84D]/25 transition">
                            <span>⚡</span> Quản trị
                        </a>
                    @endif

                    <!-- Authenticated User Pill & Dropdown -->
                    <div class="relative" x-data="{ userMenu: false }">
                        <button @click="userMenu = !userMenu" @click.outside="userMenu = false" class="flex items-center gap-2.5 bg-[#202437] hover:bg-[#282d45] border border-white/10 hover:border-[#7C5CFF]/60 rounded-xl px-2.5 sm:px-3 py-1.5 transition min-h-[44px]">
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-7 h-7 rounded-lg object-cover ring-2 ring-[#7C5CFF]/50">
                            <div class="text-left hidden sm:block">
                                <p class="text-xs font-extrabold text-white leading-tight">{{ Str::limit(auth()->user()->name, 15) }}</p>
                                <p class="text-[10px] text-[#FFB84D] font-extrabold flex items-center gap-1">
                                    <span>🪙</span> {{ number_format(auth()->user()->total_points) }} XP
                                </p>
                            </div>
                            <svg class="w-4 h-4 text-[#A5ABC0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div x-show="userMenu" x-transition class="absolute right-0 mt-2 w-60 rounded-2xl bg-[#181B28] border border-white/10 shadow-2xl py-2 z-50" style="display: none;">
                            <div class="px-4 py-2.5 border-b border-white/10 flex items-center justify-between">
                                <div class="min-w-0">
                                    <p class="text-sm font-extrabold text-white truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-[#A5ABC0] truncate">{{ auth()->user()->email }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded-lg bg-[#FFB84D]/20 text-[#FFB84D] text-[11px] font-black shrink-0">
                                    {{ number_format(auth()->user()->total_points) }} XP
                                </span>
                            </div>
                            <a href="{{ route('user.profile') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-[#A5ABC0] hover:text-white hover:bg-[#202437] transition">
                                <span>👤</span> Hồ sơ & Thành tích
                            </a>
                            <a href="{{ route('user.favorites') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-[#A5ABC0] hover:text-white hover:bg-[#202437] transition">
                                <span>❤️</span> Game yêu thích
                            </a>
                            <a href="{{ route('user.history') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-[#A5ABC0] hover:text-white hover:bg-[#202437] transition">
                                <span>🕒</span> Lịch sử chơi game
                            </a>
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-bold text-[#FFB84D] hover:bg-[#202437] transition">
                                    <span>⚡</span> Trung tâm Quản trị
                                </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="border-t border-white/10 mt-1 pt-1">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 text-sm font-bold text-rose-400 hover:bg-rose-500/10 transition">
                                    <span>🚪</span> Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <!-- Guest Buttons: Outline Login & Primary #7C5CFF Register -->
                    <a href="{{ route('login') }}" class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white border border-white/20 hover:border-[#00D4FF] hover:text-[#00D4FF] bg-[#202437]/60 transition min-h-[44px] inline-flex items-center">
                        Đăng nhập
                    </a>
                    <a href="{{ route('register') }}" class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-extrabold bg-[#7C5CFF] hover:bg-[#6b49ff] text-white shadow-lg shadow-[#7C5CFF]/35 hover:shadow-[#7C5CFF]/55 transition min-h-[44px] inline-flex items-center">
                        Đăng ký
                    </a>
                @endauth

                <!-- Mobile Hamburger Button (44x44px Touch Target) -->
                <button @click="mobileMenuOpen = true" type="button" class="xl:hidden w-11 h-11 rounded-xl bg-[#202437] border border-white/10 flex items-center justify-center text-[#A5ABC0] hover:text-white" aria-label="Mở menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Instant Search Bar Collapse -->
        <div x-show="mobileSearchOpen" x-transition class="md:hidden bg-[#181B28] border-t border-white/10 px-4 py-3" style="display: none;">
            <form action="{{ route('games.explore') }}" method="GET" class="flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Nhập tên game cần tìm..." class="flex-1 rounded-xl bg-[#202437] border border-white/10 px-4 py-2.5 text-sm text-white placeholder-[#A5ABC0] focus:outline-none focus:border-[#00D4FF]">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#7C5CFF] text-white text-sm font-extrabold">Tìm</button>
            </form>
        </div>
    </header>

    <!-- Slide-out Mobile Drawer Menu -->
    <div x-show="mobileMenuOpen" class="fixed inset-0 z-50 xl:hidden" style="display: none;">
        <div x-show="mobileMenuOpen" x-transition.opacity class="fixed inset-0 bg-black/75 backdrop-blur-sm" @click="mobileMenuOpen = false"></div>
        <aside x-show="mobileMenuOpen"
               x-transition:enter="transition ease-out duration-300 transform"
               x-transition:enter-start="translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200 transform"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="translate-x-full"
               class="fixed right-0 top-0 bottom-0 w-80 max-w-[85vw] bg-[#181B28] border-l border-white/10 shadow-2xl flex flex-col justify-between p-5 overflow-y-auto">
            <div class="space-y-5">
                <div class="flex items-center justify-between pb-4 border-b border-white/10">
                    <span class="text-lg font-black text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-[#7C5CFF] text-white flex items-center justify-center text-sm">G</span>
                        Menu GameHub
                    </span>
                    <button @click="mobileMenuOpen = false" class="w-10 h-10 rounded-xl bg-[#202437] text-[#A5ABC0] hover:text-white flex items-center justify-center">✕</button>
                </div>

                <div class="space-y-1.5">
                    <p class="text-[11px] font-extrabold uppercase tracking-wider text-[#A5ABC0] px-2 mb-2">Điều hướng chính</p>
                    <a href="{{ route('home') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-xl bg-[#202437] text-sm font-bold text-white">🏠 Trang chủ</a>
                    <a href="{{ route('games.explore') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-xl hover:bg-[#202437] text-sm font-bold text-[#A5ABC0] hover:text-white">🧭 Khám phá kho game</a>
                    <a href="{{ route('games.explore', ['sort' => 'newest']) }}" class="flex items-center gap-3 px-3.5 py-3 rounded-xl hover:bg-[#202437] text-sm font-bold text-[#A5ABC0] hover:text-white">✨ Game mới ra mắt</a>
                    <a href="{{ route('games.explore', ['sort' => 'popular']) }}" class="flex items-center gap-3 px-3.5 py-3 rounded-xl hover:bg-[#202437] text-sm font-bold text-[#A5ABC0] hover:text-white">🔥 Game thịnh hành</a>
                    <a href="{{ route('games.random') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-xl hover:bg-[#202437] text-sm font-bold text-[#FFB84D]">🎲 Chơi game ngẫu nhiên</a>
                </div>

                <div class="space-y-1.5 pt-2 border-t border-white/10">
                    <p class="text-[11px] font-extrabold uppercase tracking-wider text-[#A5ABC0] px-2 mb-2">Danh mục nổi bật</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($navCategories as $navCat)
                            <a href="{{ route('games.category', $navCat->slug) }}" class="px-3 py-2.5 rounded-xl bg-[#202437]/70 hover:bg-[#202437] text-xs font-bold text-white truncate">
                                🎮 {{ $navCat->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-white/10 text-xs text-[#A5ABC0] flex items-center justify-between">
                <a href="{{ route('pages.faq') }}" class="hover:text-white">Trợ giúp & FAQ</a>
                <a href="{{ route('pages.contact') }}" class="hover:text-white">Báo lỗi</a>
            </div>
        </aside>
    </div>

    <!-- Flash Notifications -->
    @if(session('success') || session('error') || session('warning') || $errors->any())
        <div class="max-w-[1440px] w-full mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            @if(session('success'))
                <div class="rounded-2xl bg-emerald-500/15 border border-emerald-500/40 px-4 py-3 text-sm text-emerald-300 flex items-center justify-between shadow-lg">
                    <span>✅ {{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="rounded-2xl bg-rose-500/15 border border-rose-500/40 px-4 py-3 text-sm text-rose-300 flex items-center justify-between shadow-lg">
                    <span>⚠️ {{ session('error') }}</span>
                </div>
            @endif
            @if(session('warning'))
                <div class="rounded-2xl bg-amber-500/15 border border-amber-500/40 px-4 py-3 text-sm text-amber-300 flex items-center justify-between shadow-lg">
                    <span>⚡ {{ session('warning') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="rounded-2xl bg-rose-500/15 border border-rose-500/40 px-4 py-3 text-sm text-rose-300 shadow-lg">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Modern Gaming Portal Footer -->
    <footer class="bg-[#181B28] border-t border-white/10 mt-16">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#7C5CFF] to-[#00D4FF] flex items-center justify-center shadow-md shadow-[#7C5CFF]/30">
                            <span class="font-black text-white">G</span>
                        </div>
                        <span class="text-xl font-extrabold text-white">GameHub</span>
                    </div>
                    <p class="text-sm text-[#A5ABC0] leading-relaxed">
                        Nền tảng cổng game HTML5 trực tuyến thế hệ mới. Chơi ngay lập tức hàng trăm trò chơi 2D hấp dẫn trên mọi thiết bị không cần cài đặt.
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white mb-4">Khám phá nhanh</h3>
                    <ul class="space-y-2.5 text-sm text-[#A5ABC0]">
                        <li><a href="{{ route('games.explore', ['sort' => 'popular']) }}" class="hover:text-[#00D4FF] transition">Game chơi nhiều nhất</a></li>
                        <li><a href="{{ route('games.explore', ['sort' => 'newest']) }}" class="hover:text-[#00D4FF] transition">Game mới cập nhật</a></li>
                        <li><a href="{{ route('games.explore', ['sort' => 'rating']) }}" class="hover:text-[#00D4FF] transition">Game đánh giá cao</a></li>
                        <li><a href="{{ route('games.random') }}" class="hover:text-[#FFB84D] transition">🎲 Chơi game ngẫu nhiên</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white mb-4">Thông tin & Hỗ trợ</h3>
                    <ul class="space-y-2.5 text-sm text-[#A5ABC0]">
                        <li><a href="{{ route('pages.about') }}" class="hover:text-[#00D4FF] transition">Giới thiệu về GameHub</a></li>
                        <li><a href="{{ route('pages.faq') }}" class="hover:text-[#00D4FF] transition">Câu hỏi thường gặp (FAQ)</a></li>
                        <li><a href="{{ route('pages.contact') }}" class="hover:text-[#00D4FF] transition">Liên hệ & Báo lỗi</a></li>
                        <li><a href="{{ route('sitemap') }}" class="hover:text-[#00D4FF] transition">Sơ đồ trang (Sitemap XML)</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white mb-4">Pháp lý & Bảo mật</h3>
                    <ul class="space-y-2.5 text-sm text-[#A5ABC0]">
                        <li><a href="{{ route('pages.privacy') }}" class="hover:text-[#00D4FF] transition">Chính sách bảo mật</a></li>
                        <li><a href="{{ route('pages.terms') }}" class="hover:text-[#00D4FF] transition">Điều khoản sử dụng</a></li>
                        <li class="pt-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#202437] text-xs text-[#00D4FF] border border-[#00D4FF]/30">
                                🛡️ Bảo vệ bởi Iframe Sandbox
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-white/10 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-[#A5ABC0] gap-4">
                <p>{{ \App\Models\Setting::getValue('footer_copyright', '© ' . date('Y') . ' GameHub Portal. Bảo lưu mọi quyền.') }}</p>
                <p>Thiết kế chuẩn Modern Dark Gaming UI (#10121B) • Tương thích 100% Desktop & Mobile</p>
            </div>
        </div>
    </footer>

    <!-- Mobile App-like Bottom Navigation Bar (< 1024px) -->
    <nav class="fixed bottom-0 inset-x-0 z-40 lg:hidden bg-[#181B28]/95 backdrop-blur-xl border-t border-white/10 px-2 py-1.5 flex items-center justify-around">
        <a href="{{ route('home') }}" class="flex flex-col items-center justify-center min-w-[56px] min-h-[44px] rounded-xl text-[11px] font-bold transition {{ request()->routeIs('home') ? 'text-[#00D4FF]' : 'text-[#A5ABC0] hover:text-white' }}">
            <span class="text-base leading-none">🏠</span>
            <span class="mt-1">Trang chủ</span>
        </a>
        <a href="{{ route('games.explore') }}" class="flex flex-col items-center justify-center min-w-[56px] min-h-[44px] rounded-xl text-[11px] font-bold transition {{ request()->routeIs('games.explore') ? 'text-[#00D4FF]' : 'text-[#A5ABC0] hover:text-white' }}">
            <span class="text-base leading-none">🧭</span>
            <span class="mt-1">Khám phá</span>
        </a>
        <a href="{{ route('games.random') }}" class="flex flex-col items-center justify-center min-w-[56px] min-h-[44px] -mt-4">
            <span class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#7C5CFF] to-[#00D4FF] text-white flex items-center justify-center shadow-lg shadow-[#7C5CFF]/40 text-lg">
                🎲
            </span>
            <span class="text-[10px] font-extrabold text-[#FFB84D] mt-0.5">Ngẫu nhiên</span>
        </a>
        @auth
            <a href="{{ route('user.favorites') }}" class="flex flex-col items-center justify-center min-w-[56px] min-h-[44px] rounded-xl text-[11px] font-bold transition {{ request()->routeIs('user.favorites') ? 'text-[#00D4FF]' : 'text-[#A5ABC0] hover:text-white' }}">
                <span class="text-base leading-none">❤️</span>
                <span class="mt-1">Yêu thích</span>
            </a>
        @else
            <a href="{{ route('login') }}" class="flex flex-col items-center justify-center min-w-[56px] min-h-[44px] rounded-xl text-[11px] font-bold transition {{ request()->routeIs('login') ? 'text-[#00D4FF]' : 'text-[#A5ABC0] hover:text-white' }}">
                <span class="text-base leading-none">👤</span>
                <span class="mt-1">Đăng nhập</span>
            </a>
        @endauth
        <button @click="mobileMenuOpen = true" type="button" class="flex flex-col items-center justify-center min-w-[56px] min-h-[44px] rounded-xl text-[11px] font-bold text-[#A5ABC0] hover:text-white">
            <span class="text-base leading-none">☰</span>
            <span class="mt-1">Danh mục</span>
        </button>
    </nav>

    @stack('scripts')
</body>
</html>
