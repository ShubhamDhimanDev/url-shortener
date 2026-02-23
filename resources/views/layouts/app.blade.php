<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Dashboard') | {{ config('app.name') }}</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet" />

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

    {{-- ══ Impersonation Banner ══════════════════════════════════════════════ --}}
    @if(session('impersonator_id'))
        <x-impersonation-banner />
    @endif

    {{-- ══ Sidebar ════════════════════════════════════════════════════════════ --}}
    <aside class="w-64 border-r border-slate-200 dark:border-border-dark flex flex-col h-screen sticky top-0 bg-white dark:bg-background-dark z-30 shrink-0">
        {{-- Logo --}}
        <div class="p-6 flex items-center gap-3">
            <div class="size-8 bg-primary rounded-lg flex items-center justify-center text-white">
                <span class="material-symbols-outlined !text-xl">link</span>
            </div>
            <div>
                <h1 class="text-sm font-bold tracking-tight leading-none">{{ config('app.name') }}</h1>
                <p class="text-[10px] text-slate-400 mt-0.5">URL Shortener</p>
            </div>
        </div>

        {{-- Team switcher --}}
        @php $activeTeamId = session('active_team_id'); @endphp
        <div class="px-3 pb-3">
            <form method="POST" action="{{ route('app.teams.switch-context') }}" id="team-switcher-form">
                @csrf
                <input type="hidden" name="team_id" id="team-switcher-value" value="{{ $activeTeamId ?? '' }}">
                <select
                    class="w-full text-xs bg-slate-100 dark:bg-card-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-1 focus:ring-primary"
                    onchange="document.getElementById('team-switcher-value').value=this.value; document.getElementById('team-switcher-form').submit();">
                    <option value="">Personal Workspace</option>
                    @foreach(auth()->user()->teamMemberships()->with('team')->get() as $membership)
                        <option value="{{ $membership->team_id }}" {{ $activeTeamId == $membership->team_id ? 'selected' : '' }}>
                            {{ $membership->team->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 space-y-1 overflow-y-auto pb-4">
            <div class="text-[10px] uppercase tracking-widest text-slate-400 dark:text-slate-500 font-bold px-3 py-4">Main</div>

            <x-app-nav-link :href="route('app.dashboard')" icon="grid_view" :active="request()->routeIs('app.dashboard')">Dashboard</x-app-nav-link>
            <x-app-nav-link :href="route('app.links.index')" icon="link" :active="request()->routeIs('app.links.*')">My Links</x-app-nav-link>
            <x-app-nav-link :href="route('app.domains.index')" icon="language" :active="request()->routeIs('app.domains.*')">Domains</x-app-nav-link>
            <x-app-nav-link :href="route('app.teams.index')" icon="groups" :active="request()->routeIs('app.teams.*')">Teams</x-app-nav-link>

            <div class="text-[10px] uppercase tracking-widest text-slate-400 dark:text-slate-500 font-bold px-3 pt-5 pb-2">Account</div>

            <x-app-nav-link :href="route('app.billing.index')" icon="credit_card" :active="request()->routeIs('app.billing.*')">Billing</x-app-nav-link>
            <x-app-nav-link :href="route('app.profile.edit')" icon="manage_accounts" :active="request()->routeIs('app.profile.*')">Profile</x-app-nav-link>

            @role('super_admin')
            <div class="text-[10px] uppercase tracking-widest text-slate-400 dark:text-slate-500 font-bold px-3 pt-5 pb-2">Admin</div>
            <x-app-nav-link :href="route('super-admin.dashboard')" icon="shield" :active="false">Super Admin</x-app-nav-link>
            @endrole
        </nav>

        {{-- User info --}}
        <div class="p-4 border-t border-slate-200 dark:border-border-dark">
            <div class="flex items-center gap-3">
                <div class="size-8 rounded-full bg-primary/20 flex items-center justify-center text-primary font-semibold text-sm shrink-0">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" class="size-8 rounded-full object-cover" alt="{{ auth()->user()->name }}">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate leading-none">{{ auth()->user()->name }}</p>
                    <p class="text-[11px] text-slate-400 truncate mt-0.5">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors" title="Log out">
                        <span class="material-symbols-outlined !text-lg">logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ══ Main Content ════════════════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Top bar --}}
        <header class="h-16 border-b border-slate-200 dark:border-border-dark flex items-center px-6 bg-white dark:bg-background-dark sticky top-0 z-20">
            <div class="flex-1">
                <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">@yield('page-title', 'Dashboard')</h2>
            </div>

            {{-- Flash messages ---}}
            @if(session('success'))
                <div class="mr-4 flex items-center gap-2 text-sm text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-3 py-1.5 rounded-lg border border-green-200 dark:border-green-800">
                    <span class="material-symbols-outlined !text-base">check_circle</span>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mr-4 flex items-center gap-2 text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 px-3 py-1.5 rounded-lg border border-red-200 dark:border-red-800">
                    <span class="material-symbols-outlined !text-base">error</span>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Plan badge --}}
            @if(isset($activePlan))
                <x-plan-badge :plan="$activePlan" />
            @endif

            {{-- New link button --}}
            <a href="{{ route('app.links.create') }}"
               class="ml-4 inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
                <span class="material-symbols-outlined !text-base">add</span>
                New Link
            </a>
        </header>

        {{-- Page body --}}
        <main class="flex-1 p-6 overflow-y-auto">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
