<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Super Admin') | {{ config('app.name') }}</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary":           "#9a28eb",
                        "accent-pink":       "#CC66DA",
                        "background-light":  "#f7f6f8",
                        "background-dark":   "#000000",
                        "card-dark":         "#0a0a0a",
                        "border-dark":       "#1f1f1f",
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
                },
            },
        }
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-item-active {
            background: linear-gradient(90deg, rgba(154,40,235,0.1) 0%, rgba(154,40,235,0) 100%);
            border-right: 2px solid #9a28eb;
        }
        .glass-card  { background: rgba(10,10,10,0.8); backdrop-filter: blur(10px); border: 1px solid #1f1f1f; }
        .neon-glow:hover { box-shadow: 0 0 15px rgba(154,40,235,0.3); }
        [x-cloak] { display: none !important; }
    </style>

    @stack('styles')
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 min-h-screen flex">

    {{-- ══ Sidebar ══════════════════════════════════════════════════════════ --}}
    <aside class="w-64 border-r border-slate-200 dark:border-border-dark flex flex-col h-screen sticky top-0 bg-white dark:bg-background-dark z-30 shrink-0">
        {{-- Logo --}}
        <div class="p-6 flex items-center gap-3">
            <div class="size-8 bg-primary rounded-lg flex items-center justify-center text-white">
                <span class="material-symbols-outlined !text-xl">shield</span>
            </div>
            <div>
                <h1 class="text-sm font-bold tracking-tight leading-none">Super Admin</h1>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ config('app.name') }}</p>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 space-y-1 overflow-y-auto pb-4">
            <div class="text-[10px] uppercase tracking-widest text-slate-400 dark:text-slate-500 font-bold px-3 py-4">Overview</div>

            <x-sa-nav-link :href="route('super-admin.dashboard')" icon="grid_view" :active="request()->routeIs('super-admin.dashboard')">Dashboard</x-sa-nav-link>

            <div class="text-[10px] uppercase tracking-widest text-slate-400 dark:text-slate-500 font-bold px-3 pt-5 pb-2">Users & Teams</div>

            <x-sa-nav-link :href="route('super-admin.users.index')" icon="person" :active="request()->routeIs('super-admin.users.*')">Users</x-sa-nav-link>
            <x-sa-nav-link :href="route('super-admin.teams.index')" icon="groups" :active="request()->routeIs('super-admin.teams.*')">Teams</x-sa-nav-link>

            <div class="text-[10px] uppercase tracking-widest text-slate-400 dark:text-slate-500 font-bold px-3 pt-5 pb-2">Billing</div>

            <x-sa-nav-link :href="route('super-admin.plans.index')" icon="layers" :active="request()->routeIs('super-admin.plans.*')">Plans</x-sa-nav-link>
            <x-sa-nav-link :href="route('super-admin.subscriptions.index')" icon="credit_card" :active="request()->routeIs('super-admin.subscriptions.*')">Subscriptions</x-sa-nav-link>
            <x-sa-nav-link :href="route('super-admin.invoices.index')" icon="receipt_long" :active="request()->routeIs('super-admin.invoices.*')">Invoices</x-sa-nav-link>

            <div class="text-[10px] uppercase tracking-widest text-slate-400 dark:text-slate-500 font-bold px-3 pt-5 pb-2">Platform</div>

            <x-sa-nav-link :href="route('super-admin.analytics.index')" icon="bar_chart" :active="request()->routeIs('super-admin.analytics.*')">Analytics</x-sa-nav-link>
            <x-sa-nav-link :href="route('super-admin.settings.index')" icon="settings" :active="request()->routeIs('super-admin.settings.*')">Settings</x-sa-nav-link>
        </nav>

        {{-- Bottom user info --}}
        <div class="p-4 border-t border-slate-200 dark:border-border-dark">
            <div class="flex items-center gap-3">
                <div class="size-8 rounded-full bg-primary/20 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined !text-base text-primary">person</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-red-400 transition-colors" title="Logout">
                        <span class="material-symbols-outlined !text-lg">logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ══ Main content ═════════════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Impersonation banner --}}
        <x-impersonation-banner />

        {{-- Top header --}}
        <header class="h-14 border-b border-slate-200 dark:border-border-dark bg-white dark:bg-background-dark flex items-center px-6 gap-4 shrink-0">
            <div class="flex-1">
                <h2 class="text-sm font-semibold text-slate-600 dark:text-slate-300">@yield('page-title')</h2>
            </div>
            <div class="flex items-center gap-2">
                @yield('header-actions')
            </div>
        </header>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="mx-6 mt-4 px-4 py-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center gap-2">
                <span class="material-symbols-outlined !text-lg">check_circle</span>
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mx-6 mt-4 px-4 py-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm flex items-center gap-2">
                <span class="material-symbols-outlined !text-lg">error</span>
                {{ session('error') }}
            </div>
        @endif
        @if (session('info'))
            <div class="mx-6 mt-4 px-4 py-3 rounded-lg bg-blue-500/10 border border-blue-500/20 text-blue-400 text-sm flex items-center gap-2">
                <span class="material-symbols-outlined !text-lg">info</span>
                {{ session('info') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mx-6 mt-4 px-4 py-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                <p class="font-medium mb-1">Please fix the following errors:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 p-6 overflow-y-auto">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
