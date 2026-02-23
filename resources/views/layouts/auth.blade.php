<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Auth') | {{ config('app.name') }}</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary":          "#9a28eb",
                        "accent-pink":      "#CC66DA",
                        "background-dark":  "#000000",
                        "card-dark":        "#0a0a0a",
                        "border-dark":      "#1f1f1f",
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
                },
            },
        }
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .auth-card { background: rgba(10,10,10,0.9); backdrop-filter: blur(12px); border: 1px solid #1f1f1f; }
        .input-dark {
            background: #0a0a0a !important;
            border-color: #1f1f1f !important;
            color: #f1f5f9 !important;
        }
        .input-dark::placeholder { color: #64748b !important; }
        .input-dark:focus {
            border-color: #9a28eb !important;
            box-shadow: 0 0 0 3px rgba(154,40,235,0.15) !important;
            outline: none !important;
        }
        .btn-primary {
            background: linear-gradient(135deg, #9a28eb 0%, #CC66DA 100%);
            transition: opacity 0.2s;
        }
        .btn-primary:hover { opacity: 0.9; }
        .glow-bg {
            background: radial-gradient(ellipse at 50% 0%, rgba(154,40,235,0.15) 0%, transparent 70%);
        }
        [x-cloak] { display: none !important; }
    </style>

    @stack('styles')
</head>
<body class="bg-background-dark text-slate-100 min-h-screen flex items-center justify-center relative overflow-hidden">

    {{-- Ambient glow background --}}
    <div class="glow-bg absolute inset-0 pointer-events-none"></div>

    {{-- Subtle grid pattern --}}
    <div class="absolute inset-0 pointer-events-none opacity-[0.03]"
         style="background-image: linear-gradient(#9a28eb 1px,transparent 1px),linear-gradient(90deg,#9a28eb 1px,transparent 1px);
                background-size: 40px 40px;"></div>

    <div class="relative w-full max-w-md px-4 py-12">

        {{-- Logo --}}
        <div class="flex items-center justify-center gap-3 mb-8">
            <div class="size-10 bg-primary rounded-xl flex items-center justify-center text-white shadow-lg shadow-primary/30">
                <span class="material-symbols-outlined">link</span>
            </div>
            <span class="text-xl font-semibold tracking-tight">{{ config('app.name') }}</span>
        </div>

        {{-- Card --}}
        <div class="auth-card rounded-2xl p-8">

            {{-- Flash messages --}}
            @if(session('status'))
                <div class="mb-5 rounded-lg bg-emerald-500/10 border border-emerald-500/30 px-4 py-3 text-sm text-emerald-400">
                    {{ session('status') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 rounded-lg bg-red-500/10 border border-red-500/30 px-4 py-3 text-sm text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>

        {{-- Footer links --}}
        <div class="mt-6 text-center text-xs text-slate-600">
            @yield('footer')
        </div>
    </div>

    @stack('scripts')
</body>
</html>
