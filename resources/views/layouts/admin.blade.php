<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - Quản trị GameHub' : 'Hệ thống Quản trị GameHub' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #10121B; color: #FFFFFF; font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#10121B] text-white min-h-screen flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-[#181B28] border-r border-white/10 flex flex-col shrink-0">
        <div class="p-5 border-b border-white/10 flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#7C5CFF] to-[#00D4FF] flex items-center justify-center font-black text-white shadow-lg shadow-[#7C5CFF]/30">
                    G
                </div>
                <div>
                    <span class="font-extrabold text-white tracking-tight block leading-none">GameHub</span>
                    <span class="text-[10px] font-bold text-[#00D4FF] uppercase tracking-wider">Admin Center</span>
                </div>
            </a>
        </div>

        <nav class="flex-1 p-4 space-y-1.5 text-sm font-medium overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-[#7C5CFF] text-white shadow-lg shadow-[#7C5CFF]/25' : 'text-[#A5ABC0] hover:bg-[#202437] hover:text-white' }}">
                <span>📊</span> Bảng điều khiển
            </a>
            <a href="{{ route('admin.games.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.games.*') ? 'bg-[#7C5CFF] text-white shadow-lg shadow-[#7C5CFF]/25' : 'text-[#A5ABC0] hover:bg-[#202437] hover:text-white' }}">
                <span>🎮</span> Quản lý Game & ZIP
            </a>
            <a href="{{ route('admin.audio.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.audio.*') ? 'bg-[#7C5CFF] text-white shadow-lg shadow-[#7C5CFF]/25' : 'text-[#A5ABC0] hover:bg-[#202437] hover:text-white' }}">
                <span>🔊</span> Quản lý Âm thanh (Audio)
            </a>
            <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.categories.*') ? 'bg-[#7C5CFF] text-white shadow-lg shadow-[#7C5CFF]/25' : 'text-[#A5ABC0] hover:bg-[#202437] hover:text-white' }}">
                <span>🗂️</span> Danh mục Game
            </a>
            <a href="{{ route('admin.tags.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.tags.*') ? 'bg-[#7C5CFF] text-white shadow-lg shadow-[#7C5CFF]/25' : 'text-[#A5ABC0] hover:bg-[#202437] hover:text-white' }}">
                <span>🏷️</span> Quản lý Tags
            </a>
            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.users.*') ? 'bg-[#7C5CFF] text-white shadow-lg shadow-[#7C5CFF]/25' : 'text-[#A5ABC0] hover:bg-[#202437] hover:text-white' }}">
                <span>👥</span> Người dùng & RBAC
            </a>
            <a href="{{ route('admin.reviews.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.reviews.*') ? 'bg-[#7C5CFF] text-white shadow-lg shadow-[#7C5CFF]/25' : 'text-[#A5ABC0] hover:bg-[#202437] hover:text-white' }}">
                <span>⭐</span> Đánh giá (Reviews)
            </a>
            <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.reports.*') ? 'bg-[#7C5CFF] text-white shadow-lg shadow-[#7C5CFF]/25' : 'text-[#A5ABC0] hover:bg-[#202437] hover:text-white' }}">
                <span>🚩</span> Báo cáo lỗi & Vi phạm
            </a>
            <a href="{{ route('admin.cms.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.cms.*') ? 'bg-[#7C5CFF] text-white shadow-lg shadow-[#7C5CFF]/25' : 'text-[#A5ABC0] hover:bg-[#202437] hover:text-white' }}">
                <span>🎨</span> Giao diện & Banner
            </a>
            <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.settings.*') ? 'bg-[#7C5CFF] text-white shadow-lg shadow-[#7C5CFF]/25' : 'text-[#A5ABC0] hover:bg-[#202437] hover:text-white' }}">
                <span>⚙️</span> Cài đặt hệ thống
            </a>
            <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.audit-logs.*') ? 'bg-[#7C5CFF] text-white shadow-lg shadow-[#7C5CFF]/25' : 'text-[#A5ABC0] hover:bg-[#202437] hover:text-white' }}">
                <span>🛡️</span> Nhật ký Audit Logs
            </a>
        </nav>

        <div class="p-4 border-t border-white/10 space-y-2">
            <a href="{{ route('home') }}" class="flex items-center justify-center gap-2 w-full px-4 py-2 rounded-xl bg-[#202437] hover:bg-[#282d45] text-xs font-bold text-[#00D4FF] transition">
                🌐 Xem Trang chủ Public
            </a>
        </div>
    </aside>

    <!-- Main Admin Area -->
    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-16 bg-[#181B28] border-b border-white/10 px-6 flex items-center justify-between">
            <h1 class="text-lg font-extrabold text-white">{{ $headerTitle ?? 'Quản trị hệ thống GameHub' }}</h1>
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.games.create') }}" class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-[#7C5CFF] to-[#00D4FF] text-xs font-bold text-white shadow-md shadow-[#7C5CFF]/20 hover:opacity-95 transition">
                    + Tải lên Game ZIP mới
                </a>
                <span class="text-xs text-[#A5ABC0]">Xin chào, <strong class="text-white">{{ auth()->user()->name }}</strong></span>
            </div>
        </header>

        <main class="flex-1 p-6 overflow-y-auto">
            @if(session('success'))
                <div class="mb-6 rounded-2xl bg-emerald-500/15 border border-emerald-500/40 px-4 py-3 text-sm text-emerald-300">
                    ✅ {{ session('success') }}
                </div>
            @endif
            @if(session('warning'))
                <div class="mb-6 rounded-2xl bg-amber-500/15 border border-amber-500/40 px-4 py-3 text-sm text-amber-300">
                    ⚡ {{ session('warning') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-6 rounded-2xl bg-rose-500/15 border border-rose-500/40 px-4 py-3 text-sm text-rose-300">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

</body>
</html>

