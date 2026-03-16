<!DOCTYPE html>
<html class="dark scroll-smooth" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ config('app.name') }} — Shorten, Track & Grow</title>
    <meta name="description" content="The most powerful URL shortener for teams and individuals. Custom domains, deep analytics, QR codes, and more." />

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
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
                    fontFamily: { sans: ["Inter", "sans-serif"] },
                    animation: {
                        "fade-up":    "fadeUp 0.6s ease forwards",
                        "pulse-slow": "pulse 4s cubic-bezier(0.4,0,0.6,1) infinite",
                        "spin-slow":  "spin 12s linear infinite",
                        "float":      "float 6s ease-in-out infinite",
                    },
                    keyframes: {
                        fadeUp: { "0%": { opacity: "0", transform: "translateY(24px)" }, "100%": { opacity: "1", transform: "translateY(0)" } },
                        float: { "0%,100%": { transform: "translateY(0)" }, "50%": { transform: "translateY(-12px)" } },
                    },
                },
            },
        };
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; background: #000; }

        /* ── Gradient text ── */
        .grad-text {
            background: linear-gradient(135deg, #fff 0%, #CC66DA 45%, #9a28eb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .grad-text-subtle {
            background: linear-gradient(135deg, #9a28eb 0%, #CC66DA 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ── Glass card ── */
        .glass  { background: rgba(10,10,10,0.7); backdrop-filter: blur(16px); border: 1px solid #1f1f1f; }
        .glass-hover:hover { border-color: rgba(154,40,235,0.4); box-shadow: 0 0 30px rgba(154,40,235,0.08); }

        /* ── Grid background pattern ── */
        .grid-bg {
            background-image:
                linear-gradient(rgba(154,40,235,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(154,40,235,0.04) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        /* ── Glow backgrounds ── */
        .hero-glow {
            background: radial-gradient(ellipse 80% 50% at 50% -10%, rgba(154,40,235,0.18) 0%, transparent 70%);
        }
        .section-glow-left  { background: radial-gradient(ellipse 60% 60% at -10% 50%, rgba(154,40,235,0.1) 0%, transparent 70%); }
        .section-glow-right { background: radial-gradient(ellipse 60% 60% at 110% 50%, rgba(204,102,218,0.1) 0%, transparent 70%); }

        /* ── Feature icon gradient ── */
        .icon-grad { background: linear-gradient(135deg, rgba(154,40,235,0.2) 0%, rgba(204,102,218,0.1) 100%); }

        /* ── Primary button ── */
        .btn-primary {
            background: linear-gradient(135deg, #9a28eb 0%, #CC66DA 100%);
            transition: opacity 0.2s, transform 0.2s, box-shadow 0.2s;
        }
        .btn-primary:hover { opacity: 0.92; transform: translateY(-1px); box-shadow: 0 8px 32px rgba(154,40,235,0.35); }

        /* ── Outline button ── */
        .btn-outline {
            border: 1px solid #1f1f1f;
            background: rgba(255,255,255,0.03);
            transition: border-color 0.2s, background 0.2s, transform 0.2s;
        }
        .btn-outline:hover { border-color: rgba(154,40,235,0.5); background: rgba(154,40,235,0.06); transform: translateY(-1px); }

        /* ── Ticker strip ── */
        @keyframes ticker { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
        .ticker-inner { animation: ticker 28s linear infinite; }

        /* ── stat counter shimmer ── */
        .shimmer-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: linear-gradient(90deg, transparent 0%, rgba(154,40,235,0.06) 50%, transparent 100%);
            animation: shimmer 3s ease-in-out infinite;
        }
        @keyframes shimmer { 0%,100% { opacity: 0; } 50% { opacity: 1; } }

        /* ── Code block ── */
        .code-block {
            background: #050505;
            border: 1px solid #1a1a1a;
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
        }

        /* ── Pricing card popular ── */
        .popular-ring { box-shadow: 0 0 0 2px #9a28eb, 0 0 40px rgba(154,40,235,0.2); }

        /* ── Nav blur ── */
        .nav-blur { background: rgba(0,0,0,0.7); backdrop-filter: blur(20px); border-bottom: 1px solid #111; }

        /* ── FAQ ── */
        [x-cloak] { display: none !important; }

        /* ── Scroll indicators ── */
        .scroll-dot { transition: all 0.3s; }

        /* ── Animated border ── */
        .animated-border {
            position: relative;
        }
        .animated-border::before {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: inherit;
            padding: 1px;
            background: linear-gradient(135deg, #9a28eb, #CC66DA, #9a28eb);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            background-size: 200% 200%;
            animation: border-anim 4s linear infinite;
        }
        @keyframes border-anim { 0%,100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }

        /* Feature showcase tabs */
        .feature-tab-active {
            background: rgba(154,40,235,0.15);
            border-color: rgba(154,40,235,0.5);
            color: #CC66DA;
        }

        /* ── Link card mockup ── */
        .link-row:hover { background: rgba(154,40,235,0.05); }
    </style>
</head>
<body class="text-slate-100 overflow-x-hidden">

    {{-- ════════════════════════════════════════════════════════════
         NAVIGATION
    ════════════════════════════════════════════════════════════ --}}
    <nav x-data="{ open: false }" class="fixed top-0 inset-x-0 z-50 nav-blur">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                {{-- Logo --}}
                <a href="/" class="flex items-center gap-2.5">
                    <div class="size-8 bg-primary rounded-lg flex items-center justify-center text-white shadow-lg shadow-primary/30">
                        <span class="material-symbols-outlined !text-xl">link</span>
                    </div>
                    <span class="text-sm font-bold tracking-tight text-white">{{ config('app.name') }}</span>
                </a>

                {{-- Desktop nav --}}
                <div class="hidden md:flex items-center gap-6 text-sm text-slate-400">
                    <a href="#features"  class="hover:text-white transition-colors">Features</a>
                    <a href="#analytics" class="hover:text-white transition-colors">Analytics</a>
                    <a href="#pricing"   class="hover:text-white transition-colors">Pricing</a>
                    <a href="#faq"       class="hover:text-white transition-colors">FAQ</a>
                </div>

                {{-- CTA --}}
                <div class="hidden md:flex items-center gap-3">
                    <a href="{{ route('login') }}"    class="text-sm text-slate-400 hover:text-white transition-colors px-3 py-1.5">Sign in</a>
                    <a href="{{ route('register') }}" class="btn-primary text-white text-sm font-medium px-4 py-2 rounded-lg">Get Started Free</a>
                </div>

                {{-- Mobile burger --}}
                <button @click="open=!open" class="md:hidden text-slate-400 hover:text-white">
                    <span class="material-symbols-outlined" x-text="open ? 'close' : 'menu'">menu</span>
                </button>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div x-show="open" x-cloak x-transition class="md:hidden border-t border-white/5 bg-black/95 px-4 py-4 space-y-3">
            <a href="#features"  @click="open=false" class="block text-sm text-slate-400 hover:text-white py-1.5">Features</a>
            <a href="#analytics" @click="open=false" class="block text-sm text-slate-400 hover:text-white py-1.5">Analytics</a>
            <a href="#pricing"   @click="open=false" class="block text-sm text-slate-400 hover:text-white py-1.5">Pricing</a>
            <a href="#faq"       @click="open=false" class="block text-sm text-slate-400 hover:text-white py-1.5">FAQ</a>
            <div class="pt-2 flex flex-col gap-2">
                <a href="{{ route('login') }}"    class="block text-center text-sm btn-outline text-slate-300 px-4 py-2.5 rounded-lg">Sign in</a>
                <a href="{{ route('register') }}" class="block text-center btn-primary text-white text-sm font-medium px-4 py-2.5 rounded-lg">Get Started Free</a>
            </div>
        </div>
    </nav>


    {{-- ════════════════════════════════════════════════════════════
         HERO
    ════════════════════════════════════════════════════════════ --}}
    <section class="relative min-h-screen flex flex-col items-center justify-center pt-24 pb-16 overflow-hidden grid-bg">

        {{-- Ambient glow --}}
        <div class="hero-glow absolute inset-0 pointer-events-none"></div>

        {{-- Floating orbs --}}
        <div class="absolute top-1/4 -left-32 size-64 rounded-full bg-primary/10 blur-3xl animate-pulse-slow pointer-events-none"></div>
        <div class="absolute bottom-1/4 -right-32 size-80 rounded-full bg-accent-pink/8 blur-3xl animate-pulse-slow pointer-events-none" style="animation-delay:2s"></div>

        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 text-center">

            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 text-xs font-medium px-3 py-1.5 rounded-full glass border border-primary/20 text-primary mb-6 animate-fade-up">
                <span class="size-1.5 rounded-full bg-primary animate-ping inline-block"></span>
                Now with AI-powered link insights
            </div>

            {{-- Headline --}}
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight leading-[1.05] mb-6 animate-fade-up" style="animation-delay:0.1s">
                Short links that
                <span class="grad-text block">work smarter.</span>
            </h1>

            <p class="max-w-2xl mx-auto text-base sm:text-lg text-slate-400 leading-relaxed mb-8 animate-fade-up" style="animation-delay:0.2s">
                Shorten URLs, track every click with deep analytics, generate QR codes, add custom domains, and collaborate with your team — all in one place.
            </p>

            {{-- CTA row --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mb-12 animate-fade-up" style="animation-delay:0.3s">
                <a href="{{ route('register') }}" class="btn-primary w-full sm:w-auto inline-flex items-center justify-center gap-2 text-white font-semibold px-7 py-3.5 rounded-xl text-sm">
                    <span class="material-symbols-outlined !text-base">rocket_launch</span>
                    Start for free — no card needed
                </a>
                <a href="#features" class="btn-outline w-full sm:w-auto inline-flex items-center justify-center gap-2 text-slate-300 font-medium px-6 py-3.5 rounded-xl text-sm">
                    <span class="material-symbols-outlined !text-base">play_circle</span>
                    See how it works
                </a>
            </div>

            {{-- Social proof --}}
            <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs text-slate-500 animate-fade-up" style="animation-delay:0.4s">
                <span class="flex items-center gap-1.5"><span class="material-symbols-outlined !text-sm text-green-500">check_circle</span> No credit card required</span>
                <span class="flex items-center gap-1.5"><span class="material-symbols-outlined !text-sm text-green-500">check_circle</span> Free plan forever</span>
                <span class="flex items-center gap-1.5"><span class="material-symbols-outlined !text-sm text-green-500">check_circle</span> 5-minute setup</span>
            </div>

            {{-- Hero mockup ── dashboard preview ── --}}
            <div class="mt-16 relative animate-fade-up" style="animation-delay:0.5s">
                {{-- Glow behind mockup --}}
                <div class="absolute -inset-4 bg-gradient-to-b from-primary/10 to-transparent rounded-3xl blur-2xl pointer-events-none"></div>

                <div class="relative glass rounded-2xl overflow-hidden border border-white/5 shadow-2xl shadow-black max-w-4xl mx-auto">
                    {{-- Fake browser bar --}}
                    <div class="flex items-center gap-2 px-4 py-3 bg-card-dark border-b border-border-dark">
                        <span class="size-3 rounded-full bg-red-500/70"></span>
                        <span class="size-3 rounded-full bg-yellow-500/70"></span>
                        <span class="size-3 rounded-full bg-green-500/70"></span>
                        <div class="flex-1 mx-4 max-w-xs bg-black/50 rounded px-3 py-0.5 text-[11px] text-slate-500">app.{{ strtolower(config('app.name')) }}.io/dashboard</div>
                    </div>

                    {{-- Mock dashboard --}}
                    <div class="p-4 sm:p-6 bg-card-dark/50">

                        {{-- Stats row --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                            @php
                                $stats = [
                                    ['label'=>'Total Links',   'value'=>'1,284',  'icon'=>'link',        'color'=>'text-primary'],
                                    ['label'=>'Clicks Today',  'value'=>'8,492',  'icon'=>'ads_click',   'color'=>'text-accent-pink'],
                                    ['label'=>'Domains',       'value'=>'12',     'icon'=>'language',    'color'=>'text-blue-400'],
                                    ['label'=>'Conversion',    'value'=>'3.2%',   'icon'=>'trending_up', 'color'=>'text-emerald-400'],
                                ];
                            @endphp
                            @foreach($stats as $stat)
                            <div class="glass rounded-xl p-3 relative overflow-hidden shimmer-card">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <span class="material-symbols-outlined !text-base {{ $stat['color'] }}">{{ $stat['icon'] }}</span>
                                    <span class="text-[11px] text-slate-500">{{ $stat['label'] }}</span>
                                </div>
                                <p class="text-lg font-bold text-white">{{ $stat['value'] }}</p>
                            </div>
                            @endforeach
                        </div>

                        {{-- Link table mock --}}
                        <div class="glass rounded-xl overflow-hidden">
                            <div class="px-4 py-2.5 border-b border-border-dark flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-300">Recent Links</span>
                                <div class="flex gap-1.5">
                                    <div class="h-1.5 w-12 rounded-full bg-primary/30"></div>
                                    <div class="h-1.5 w-8 rounded-full bg-border-dark"></div>
                                </div>
                            </div>
                            @php
                                $mockLinks = [
                                    ['code'=>'promo24',   'dest'=>'campaign-landing-page.io/summer',     'clicks'=>'4.2k', 'badge'=>'Active',   'color'=>'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'],
                                    ['code'=>'docs',      'dest'=>'documentation.myproduct.com/getting…', 'clicks'=>'1.8k', 'badge'=>'Active',   'color'=>'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'],
                                    ['code'=>'yt-launch', 'dest'=>'youtube.com/watch?v=xXxXxXxX',        'clicks'=>'920',  'badge'=>'Expired',  'color'=>'bg-slate-500/10 text-slate-400 border-slate-500/20'],
                                ];
                            @endphp
                            @foreach($mockLinks as $link)
                            <div class="flex items-center gap-3 px-4 py-3 border-b border-border-dark/50 link-row transition-colors">
                                <div class="size-7 rounded-lg icon-grad flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined !text-sm text-primary">link</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-white truncate">lnk.io/<span class="text-primary">{{ $link['code'] }}</span></p>
                                    <p class="text-[10px] text-slate-500 truncate">{{ $link['dest'] }}</p>
                                </div>
                                <div class="hidden sm:flex items-center gap-3 shrink-0">
                                    <span class="text-xs text-slate-300 font-medium">{{ $link['clicks'] }}</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full border {{ $link['color'] }}">{{ $link['badge'] }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    {{-- ════════════════════════════════════════════════════════════
         TICKER STRIP
    ════════════════════════════════════════════════════════════ --}}
    <div class="border-y border-border-dark overflow-hidden py-3.5 bg-card-dark/40">
        <div class="ticker-inner flex whitespace-nowrap">
            @php
                $items = [
                    '⚡ Lightning Fast Redirects',
                    '🔐 Password Protected Links',
                    '🌍 Real-time Geo Analytics',
                    '📱 QR Code Generation',
                    '🎯 UTM Campaign Tracking',
                    '👥 Team Collaboration',
                    '🚀 Custom Domains',
                    '🤖 Bot Detection',
                    '📊 Click Heatmaps',
                    '🔗 Bulk Link Import',
                    '💳 Flexible Plans',
                    '🛡️ Spam Detection',
                ];
                $doubled = array_merge($items, $items);
            @endphp
            @foreach($doubled as $item)
            <span class="text-xs text-slate-500 mx-6">{{ $item }}</span>
            @endforeach
        </div>
    </div>


    {{-- ════════════════════════════════════════════════════════════
         STATS SECTION
    ════════════════════════════════════════════════════════════ --}}
    <section class="py-20 relative section-glow-left">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $bigStats = [
                        ['num'=>'50M+',   'label'=>'Links shortened',        'icon'=>'link',          'desc'=>'across all users'],
                        ['num'=>'2.4B+',  'label'=>'Clicks tracked',         'icon'=>'ads_click',     'desc'=>'and counting'],
                        ['num'=>'180+',   'label'=>'Countries reached',       'icon'=>'public',        'desc'=>'globally'],
                        ['num'=>'99.9%',  'label'=>'Uptime SLA',             'icon'=>'verified',      'desc'=>'guaranteed'],
                    ];
                @endphp
                @foreach($bigStats as $s)
                <div class="glass glass-hover rounded-2xl p-6 text-center transition-all duration-300 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <span class="material-symbols-outlined !text-2xl grad-text-subtle mb-3 block">{{ $s['icon'] }}</span>
                    <p class="text-3xl sm:text-4xl font-black grad-text-subtle mb-1">{{ $s['num'] }}</p>
                    <p class="text-sm font-semibold text-slate-200 mb-0.5">{{ $s['label'] }}</p>
                    <p class="text-xs text-slate-500">{{ $s['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>


    {{-- ════════════════════════════════════════════════════════════
         FEATURES GRID
    ════════════════════════════════════════════════════════════ --}}
    <section id="features" class="py-24 relative grid-bg">
        <div class="absolute inset-0 section-glow-right pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6">

            {{-- Header --}}
            <div class="text-center mb-16">
                <span class="text-xs font-semibold uppercase tracking-widest text-primary mb-3 block">Everything you need</span>
                <h2 class="text-3xl sm:text-5xl font-black tracking-tight mb-4">
                    Not just a shortener.<br>
                    <span class="grad-text">A growth engine.</span>
                </h2>
                <p class="max-w-xl mx-auto text-slate-400 text-base">Every feature is crafted to help you understand, optimize, and grow your traffic.</p>
            </div>

            {{-- Bento grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 auto-rows-auto">

                {{-- Feature 1 — Large: Analytics --}}
                <div class="lg:col-span-2 glass glass-hover rounded-2xl p-6 sm:p-8 transition-all duration-300 group relative overflow-hidden">
                    <div class="absolute top-0 right-0 size-64 bg-primary/5 rounded-full blur-3xl pointer-events-none group-hover:bg-primary/10 transition-colors"></div>
                    <div class="flex items-start gap-4 mb-6">
                        <div class="size-12 icon-grad rounded-xl flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined !text-2xl text-primary">bar_chart</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white mb-1">Deep Click Analytics</h3>
                            <p class="text-sm text-slate-400 leading-relaxed">Track every click with geo, device, browser, referrer and UTM data. Visualise traffic with charts you actually understand.</p>
                        </div>
                    </div>
                    {{-- Mini chart mockup --}}
                    <div class="glass rounded-xl p-4 relative overflow-hidden">
                        <div class="flex items-end gap-1.5 h-16">
                            @php $bars = [30,50,40,70,55,80,95,60,85,100,75,90]; @endphp
                            @foreach($bars as $h)
                            <div class="flex-1 rounded-sm bg-gradient-to-t from-primary to-accent-pink opacity-80 transition-all" style="height:{{ $h }}%"></div>
                            @endforeach
                        </div>
                        <div class="flex justify-between mt-2">
                            @foreach(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] as $m)
                            <span class="text-[9px] text-slate-600">{{ $m }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Feature 2 — Custom Domains --}}
                <div class="glass glass-hover rounded-2xl p-6 transition-all duration-300 group relative overflow-hidden">
                    <div class="size-12 icon-grad rounded-xl flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined !text-2xl text-primary">language</span>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Custom Domains</h3>
                    <p class="text-sm text-slate-400 leading-relaxed mb-4">Use your own branded domain for short links. Automatic SSL provisioning via Let's Encrypt.</p>
                    <div class="space-y-2">
                        @foreach(['go.yourband.com', 'links.acme.io'] as $d)
                        <div class="flex items-center gap-2 glass rounded-lg px-3 py-2">
                            <span class="size-2 rounded-full bg-emerald-400"></span>
                            <span class="text-xs text-slate-300 font-mono">{{ $d }}</span>
                            <span class="ml-auto text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">SSL ✓</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Feature 3 — QR Codes --}}
                <div class="glass glass-hover rounded-2xl p-6 transition-all duration-300 group relative overflow-hidden">
                    <div class="size-12 icon-grad rounded-xl flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined !text-2xl text-primary">qr_code_2</span>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">QR Code Generator</h3>
                    <p class="text-sm text-slate-400 leading-relaxed mb-5">Branded QR codes in PNG or SVG. Custom colors and logo. Download instantly.</p>
                    {{-- QR visual placeholder --}}
                    <div class="flex justify-center">
                        <div class="size-20 grid grid-cols-5 gap-0.5 opacity-70">
                            @php $qr = [1,1,1,1,1, 1,0,0,0,1, 1,0,1,0,1, 1,0,0,0,1, 1,1,1,1,1, 0,1,0,1,0, 1,0,1,0,1, 0,1,0,1,0, 1,0,1,0,1, 0,0,1,0,0, 1,1,1,1,1, 1,0,0,0,1, 1,0,1,0,1, 1,0,0,0,1, 1,1,1,1,1, 0,1,0,1,0, 1,0,1,0,1, 0,1,0,1,0, 1,0,1,0,1, 0,0,1,0,0, 1,1,1,1,1, 1,0,0,0,1, 1,0,1,0,1, 1,0,0,0,1, 1,1,1,1,1]; @endphp
                            @foreach($qr as $cell)
                            <div class="rounded-[1px] {{ $cell ? 'bg-primary' : 'bg-transparent' }}"></div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Feature 4 — Teams --}}
                <div class="glass glass-hover rounded-2xl p-6 transition-all duration-300 group relative overflow-hidden">
                    <div class="size-12 icon-grad rounded-xl flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined !text-2xl text-primary">groups</span>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Team Workspaces</h3>
                    <p class="text-sm text-slate-400 leading-relaxed mb-4">Invite team members, assign roles, share link quotas, and collaborate in dedicated workspaces.</p>
                    <div class="flex -space-x-2">
                        @php $avatarColors = ['bg-primary', 'bg-accent-pink', 'bg-blue-500', 'bg-emerald-500', 'bg-yellow-500']; @endphp
                        @foreach($avatarColors as $i => $color)
                        <div class="size-8 rounded-full {{ $color }} border-2 border-card-dark flex items-center justify-center text-[10px] font-bold text-white">{{ chr(65+$i) }}</div>
                        @endforeach
                        <div class="size-8 rounded-full glass border-2 border-card-dark flex items-center justify-center text-[10px] text-slate-400">+4</div>
                    </div>
                </div>

                {{-- Feature 5 — Large: Security & Link Features --}}
                <div class="lg:col-span-2 glass glass-hover rounded-2xl p-6 sm:p-8 transition-all duration-300 group relative overflow-hidden">
                    <div class="absolute bottom-0 left-0 size-48 bg-accent-pink/5 rounded-full blur-3xl pointer-events-none group-hover:bg-accent-pink/10 transition-colors"></div>
                    <div class="flex items-start gap-4 mb-6">
                        <div class="size-12 icon-grad rounded-xl flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined !text-2xl text-primary">shield_lock</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white mb-1">Smart Link Controls</h3>
                            <p class="text-sm text-slate-400 leading-relaxed">Full control over where and how your links behave. Protect, schedule, and track with precision.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @php
                            $controls = [
                                ['icon'=>'lock',            'label'=>'Password protection'],
                                ['icon'=>'schedule',        'label'=>'Link expiry dates'],
                                ['icon'=>'track_changes',   'label'=>'UTM parameters'],
                                ['icon'=>'smart_toy',       'label'=>'Bot detection'],
                                ['icon'=>'gpp_bad',         'label'=>'Spam detection'],
                                ['icon'=>'leak_add',        'label'=>'Meta Pixel & GTM'],
                            ];
                        @endphp
                        @foreach($controls as $c)
                        <div class="flex items-center gap-2 glass rounded-lg px-3 py-2.5">
                            <span class="material-symbols-outlined !text-base text-primary">{{ $c['icon'] }}</span>
                            <span class="text-xs text-slate-300">{{ $c['label'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Feature 6 — Geo Analytics --}}
                <div class="glass glass-hover rounded-2xl p-6 transition-all duration-300 group">
                    <div class="size-12 icon-grad rounded-xl flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined !text-2xl text-primary">travel_explore</span>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Geo Heatmaps</h3>
                    <p class="text-sm text-slate-400 leading-relaxed mb-4">Know exactly where your audience is — country, city, and region level.</p>
                    <div class="space-y-2">
                        @php $geos = [['🇮🇳','India','38%','bg-primary',38],['🇺🇸','United States','27%','bg-accent-pink',27],['🇬🇧','United Kingdom','15%','bg-blue-500',15]]; @endphp
                        @foreach($geos as $g)
                        <div>
                            <div class="flex justify-between text-[11px] mb-1">
                                <span class="text-slate-300">{{ $g[0] }} {{ $g[1] }}</span>
                                <span class="text-slate-500">{{ $g[2] }}</span>
                            </div>
                            <div class="h-1 bg-border-dark rounded-full overflow-hidden">
                                <div class="{{ $g[3] }}/50 h-full rounded-full" style="width:{{ $g[4] }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Feature 7 — Campaigns --}}
                <div class="glass glass-hover rounded-2xl p-6 transition-all duration-300 group">
                    <div class="size-12 icon-grad rounded-xl flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined !text-2xl text-primary">campaign</span>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Campaign Tracking</h3>
                    <p class="text-sm text-slate-400 leading-relaxed mb-4">Auto-append UTM parameters. See which campaigns drive the most traffic.</p>
                    <div class="code-block rounded-lg px-3 py-2.5 text-[11px] text-slate-400 leading-relaxed">
                        <span class="text-accent-pink">utm_source</span>=newsletter<br>
                        <span class="text-accent-pink">utm_medium</span>=email<br>
                        <span class="text-accent-pink">utm_campaign</span>=launch25
                    </div>
                </div>

                {{-- Feature 8 — Billing --}}
                <div class="glass glass-hover rounded-2xl p-6 transition-all duration-300 group">
                    <div class="size-12 icon-grad rounded-xl flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined !text-2xl text-primary">credit_card</span>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Subscription & Billing</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">Manage plans, view invoices and payment methods. Powered by Razorpay with gateway-agnostic architecture.</p>
                </div>

            </div>
        </div>
    </section>


    {{-- ════════════════════════════════════════════════════════════
         ANALYTICS SECTION
    ════════════════════════════════════════════════════════════ --}}
    <section id="analytics" class="py-24 relative overflow-hidden">
        <div class="absolute inset-0 section-glow-left pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="grid lg:grid-cols-2 gap-12 items-center">

                {{-- Left copy --}}
                <div>
                    <span class="text-xs font-semibold uppercase tracking-widest text-primary mb-3 block">Analytics</span>
                    <h2 class="text-3xl sm:text-5xl font-black tracking-tight mb-6">
                        Know your
                        <span class="grad-text">audience deeply.</span>
                    </h2>
                    <p class="text-slate-400 text-base leading-relaxed mb-8">From the moment someone clicks your link, we capture everything — device, location, browser, referrer, and UTMs — so you can make better decisions faster.</p>

                    <div class="space-y-4">
                        @php
                            $analyticsFeatures = [
                                ['icon'=>'schedule',     'title'=>'Real-time data',        'desc'=>'See clicks as they happen with live updates.'],
                                ['icon'=>'device_hub',   'title'=>'Device breakdown',      'desc'=>'Desktop, mobile, tablet — see every device type.'],
                                ['icon'=>'share',        'title'=>'Referrer tracking',     'desc'=>'Identify which sites and platforms send you traffic.'],
                                ['icon'=>'filter_list',  'title'=>'Date range filtering',  'desc'=>'Compare periods and spot trends over time.'],
                            ];
                        @endphp
                        @foreach($analyticsFeatures as $f)
                        <div class="flex gap-4">
                            <div class="size-9 icon-grad rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                                <span class="material-symbols-outlined !text-base text-primary">{{ $f['icon'] }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-white mb-0.5">{{ $f['title'] }}</p>
                                <p class="text-sm text-slate-400">{{ $f['desc'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Right mockup —  analytics card --}}
                <div class="relative animate-float">
                    <div class="absolute -inset-6 bg-primary/8 rounded-3xl blur-3xl pointer-events-none"></div>
                    <div class="relative glass rounded-2xl overflow-hidden border border-border-dark">
                        <div class="px-5 py-4 border-b border-border-dark flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-white">lnk.io/promo24</p>
                                <p class="text-[11px] text-slate-500">Last 30 days</p>
                            </div>
                            <div class="flex gap-1.5">
                                @foreach(['7D','30D','ALL'] as $i => $range)
                                <button class="text-[10px] px-2.5 py-1 rounded-lg border border-border-dark {{ $i===1 ? 'bg-primary/20 border-primary/40 text-primary' : 'text-slate-500' }}">{{ $range }}</button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Sparkline --}}
                        <div class="px-5 py-4">
                            <div class="flex items-end gap-1 h-24 mb-1">
                                @php $sparkline = [20,35,28,50,45,60,80,70,90,75,95,85,100,88,92,78,84,96,72,88,94,80,76,92,100,88,96,84,90,95]; @endphp
                                @foreach($sparkline as $v)
                                <div class="flex-1 rounded-sm bg-gradient-to-t from-primary/60 to-accent-pink/40" style="height:{{ $v }}%"></div>
                                @endforeach
                            </div>
                            <div class="flex justify-between text-[9px] text-slate-600 mb-4">
                                <span>Jun 1</span><span>Jun 10</span><span>Jun 20</span><span>Jun 30</span>
                            </div>

                            {{-- Metrics row --}}
                            <div class="grid grid-cols-3 gap-3">
                                @foreach([['8,492','Total Clicks'],['6,120','Unique'],['2.4%','CTR']] as $m)
                                <div class="glass rounded-xl p-3 text-center">
                                    <p class="text-base font-bold text-white">{{ $m[0] }}</p>
                                    <p class="text-[10px] text-slate-500">{{ $m[1] }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Country list --}}
                        <div class="px-5 pb-4 space-y-2">
                            @php $countries = [['🇮🇳','India','3,218'],['🇺🇸','USA','2,094'],['🇬🇧','UK','980'],['🇩🇪','Germany','680']]; @endphp
                            @foreach($countries as $c)
                            <div class="flex items-center gap-3 text-xs">
                                <span>{{ $c[0] }}</span>
                                <span class="text-slate-300 flex-1">{{ $c[1] }}</span>
                                <span class="text-slate-500">{{ $c[2] }} clicks</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    {{-- ════════════════════════════════════════════════════════════
         HOW IT WORKS
    ════════════════════════════════════════════════════════════ --}}
    <section class="py-24 relative grid-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-16">
                <span class="text-xs font-semibold uppercase tracking-widest text-primary mb-3 block">Simple workflow</span>
                <h2 class="text-3xl sm:text-5xl font-black tracking-tight">Up and running <span class="grad-text">in minutes.</span></h2>
            </div>

            <div class="grid sm:grid-cols-3 gap-6 relative">
                {{-- Connecting line --}}
                <div class="hidden sm:block absolute top-12 left-1/3 right-1/3 h-px bg-gradient-to-r from-transparent via-primary/30 to-transparent pointer-events-none"></div>

                @php
                    $steps = [
                        ['n'=>'01','icon'=>'person_add',      'title'=>'Create your account',  'desc'=>'Sign up free in seconds. No credit card required. Get instant access to all core features.'],
                        ['n'=>'02','icon'=>'add_link',        'title'=>'Shorten your first URL','desc'=>'Paste any URL, customise the slug, set options, and your branded short link is ready instantly.'],
                        ['n'=>'03','icon'=>'insights',        'title'=>'Watch analytics roll in','desc'=>'Share your link and watch real-time clicks, geo data, devices, and campaign metrics pour in.'],
                    ];
                @endphp
                @foreach($steps as $step)
                <div class="glass glass-hover rounded-2xl p-7 text-center transition-all duration-300 group relative">
                    <div class="inline-flex items-center justify-center size-14 icon-grad rounded-2xl mb-5 relative">
                        <span class="material-symbols-outlined !text-2xl text-primary">{{ $step['icon'] }}</span>
                        <span class="absolute -top-2 -right-2 size-5 bg-primary rounded-full text-[10px] font-black text-white flex items-center justify-center">{{ $step['n'][1] }}</span>
                    </div>
                    <h3 class="text-base font-bold text-white mb-2">{{ $step['title'] }}</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">{{ $step['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>


    {{-- ════════════════════════════════════════════════════════════
         PRICING
    ════════════════════════════════════════════════════════════ --}}
    <section id="pricing" class="py-24 relative overflow-hidden" x-data="{ yearly: false }">
        <div class="absolute inset-0 section-glow-right pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6">

            <div class="text-center mb-12">
                <span class="text-xs font-semibold uppercase tracking-widest text-primary mb-3 block">Pricing</span>
                <h2 class="text-3xl sm:text-5xl font-black tracking-tight mb-4">Plans that <span class="grad-text">scale with you.</span></h2>
                <p class="text-slate-400 max-w-md mx-auto mb-8">Start free and upgrade when you need more power. No hidden fees.</p>

                {{-- Toggle --}}
                <div class="inline-flex items-center gap-3 glass rounded-full px-4 py-2 border border-border-dark">
                    <span class="text-sm" :class="!yearly ? 'text-white font-semibold' : 'text-slate-500'">Monthly</span>
                    <button @click="yearly=!yearly" class="relative size-11 h-6 w-10 cursor-pointer transition-colors duration-300" :class="yearly ? 'bg-primary' : 'bg-border-dark'" style="border-radius:9999px">
                        <span class="absolute top-0.5 left-0.5 size-5 bg-white rounded-full transition-transform duration-300 shadow-md" :style="yearly ? 'transform:translateX(16px)' : ''"></span>
                    </button>
                    <span class="text-sm" :class="yearly ? 'text-white font-semibold' : 'text-slate-500'">Yearly <span class="text-[10px] text-emerald-400 font-bold">-20%</span></span>
                </div>
            </div>

            {{-- Plans --}}
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 items-start">
                @php
                    $plans = [
                        [
                            'name'=>'Free',
                            'desc'=>'Perfect for individuals getting started.',
                            'monthly'=>'₹0','yearly'=>'₹0',
                            'cta'=>'Get started free','cta_outline'=>true,'popular'=>false,
                            'features'=>['50 links / month','Basic click analytics','1 custom domain','QR code generation','Standard support'],
                        ],
                        [
                            'name'=>'Pro',
                            'desc'=>'For creators and marketers scaling up.',
                            'monthly'=>'₹499','yearly'=>'₹399',
                            'cta'=>'Start Pro trial','cta_outline'=>false,'popular'=>true,
                            'features'=>['2,000 links / month','Advanced analytics & geo','5 custom domains','Password & expiry controls','UTM & campaign tracking','Meta Pixel + Google Tag','Bot & spam detection','Priority support'],
                        ],
                        [
                            'name'=>'Team',
                            'desc'=>'Collaborate with your entire team.',
                            'monthly'=>'₹1,499','yearly'=>'₹1,199',
                            'cta'=>'Start Team trial','cta_outline'=>true,'popular'=>false,
                            'features'=>['Unlimited links','Everything in Pro','Unlimited custom domains','Team workspaces','Role-based access control','Bulk link import','Dedicated account manager','SLA uptime guarantee'],
                        ],
                    ];
                @endphp
                @foreach($plans as $plan)
                <div class="relative {{ $plan['popular'] ? 'popular-ring rounded-2xl' : '' }}">
                    @if($plan['popular'])
                    <div class="absolute -top-3.5 inset-x-0 flex justify-center">
                        <span class="btn-primary text-white text-[11px] font-bold px-4 py-1 rounded-full shadow-lg">Most Popular</span>
                    </div>
                    @endif

                    <div class="glass rounded-2xl p-6 sm:p-7 h-full flex flex-col {{ $plan['popular'] ? 'border-primary/20' : '' }}">
                        <div class="mb-6">
                            <h3 class="text-lg font-bold text-white mb-1">{{ $plan['name'] }}</h3>
                            <p class="text-xs text-slate-400 mb-4">{{ $plan['desc'] }}</p>
                            <div class="flex items-end gap-1">
                                <span class="text-4xl font-black text-white" x-text="yearly ? '{{ $plan['yearly'] }}' : '{{ $plan['monthly'] }}'">{{ $plan['monthly'] }}</span>
                                <span class="text-sm text-slate-500 mb-1.5">/mo</span>
                            </div>
                            <p x-show="yearly" class="text-xs text-emerald-400 mt-1">Billed annually</p>
                        </div>

                        <ul class="space-y-3 flex-1 mb-7">
                            @foreach($plan['features'] as $f)
                            <li class="flex items-start gap-2.5 text-sm text-slate-300">
                                <span class="material-symbols-outlined !text-base text-primary mt-0.5 shrink-0">check_circle</span>
                                {{ $f }}
                            </li>
                            @endforeach
                        </ul>

                        <a href="{{ route('register') }}"
                           class="{{ $plan['cta_outline'] && !$plan['popular'] ? 'btn-outline text-slate-300' : 'btn-primary text-white' }} w-full text-center py-3 rounded-xl text-sm font-semibold block">
                            {{ $plan['cta'] }}
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <p class="text-center text-xs text-slate-600 mt-8">All plans include a 14-day free trial. Cancel anytime. Prices in INR.</p>
        </div>
    </section>


    {{-- ════════════════════════════════════════════════════════════
         TESTIMONIALS
    ════════════════════════════════════════════════════════════ --}}
    <section class="py-24 relative grid-bg overflow-hidden">
        <div class="absolute inset-0 section-glow-left pointer-events-none"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-14">
                <span class="text-xs font-semibold uppercase tracking-widest text-primary mb-3 block">Testimonials</span>
                <h2 class="text-3xl sm:text-4xl font-black">Loved by <span class="grad-text">thousands of teams.</span></h2>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @php
                    $testimonials = [
                        ['name'=>'Priya Sharma',   'role'=>'Marketing Lead @ Bharat Startup','avatar'=>'P','color'=>'bg-primary',    'text'=>'The analytics are incredibly detailed. We switched all our campaign links to this platform and haven\'t looked back. The geo data and UTM tracking are game changers.'],
                        ['name'=>'Alex Chen',      'role'=>'Founder @ LaunchPad',            'avatar'=>'A','color'=>'bg-accent-pink', 'text'=>'Custom domains with automatic SSL was the deal-breaker for us. Setup took 5 minutes. Our click-through rates improved noticeably once we used branded links.'],
                        ['name'=>'Rohit Menon',    'role'=>'Growth Hacker @ ScaleUp',        'avatar'=>'R','color'=>'bg-blue-500',    'text'=>'Bot detection saved us from polluted analytics. The real traffic numbers we get now are so much more reliable for decision-making. Worth every penny.'],
                        ['name'=>'Sarah Williams', 'role'=>'Content Creator',                 'avatar'=>'S','color'=>'bg-emerald-500', 'text'=>'QR code generation is seamless. I print QR codes on physical flyers and can track exactly how many people scan them. Amazing for offline-to-online campaigns.'],
                        ['name'=>'Vikram Nair',    'role'=>'Tech Lead @ DevHouse',            'avatar'=>'V','color'=>'bg-yellow-500',  'text'=>'The team workspace feature is perfect. Multiple team members, role-based access, shared quota — everything we needed without the enterprise price tag.'],
                        ['name'=>'Diya Patel',     'role'=>'E-commerce Manager',              'avatar'=>'D','color'=>'bg-red-500',     'text'=>'Password-protected links for internal previews, expiry dates for limited offers — features I didn\'t know I needed until I had them. Super polished product.'],
                    ];
                @endphp
                @foreach($testimonials as $t)
                <div class="glass glass-hover rounded-2xl p-6 transition-all duration-300">
                    <div class="flex items-center gap-1.5 mb-4">
                        @for($s=0; $s<5; $s++)
                        <span class="material-symbols-outlined !text-sm text-yellow-400 !fill-current" style="-webkit-text-fill-color:#facc15">star</span>
                        @endfor
                    </div>
                    <p class="text-sm text-slate-300 leading-relaxed mb-5">"{{ $t['text'] }}"</p>
                    <div class="flex items-center gap-3">
                        <div class="size-9 rounded-full {{ $t['color'] }} flex items-center justify-center text-sm font-bold text-white">{{ $t['avatar'] }}</div>
                        <div>
                            <p class="text-sm font-semibold text-white">{{ $t['name'] }}</p>
                            <p class="text-[11px] text-slate-500">{{ $t['role'] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>


    {{-- ════════════════════════════════════════════════════════════
         FAQ
    ════════════════════════════════════════════════════════════ --}}
    <section id="faq" class="py-24" x-data="{ open: null }">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-14">
                <span class="text-xs font-semibold uppercase tracking-widest text-primary mb-3 block">FAQ</span>
                <h2 class="text-3xl sm:text-4xl font-black">Questions <span class="grad-text">answered.</span></h2>
            </div>

            @php
                $faqs = [
                    ['q'=>'Is there a free plan?', 'a'=>'Yes! Our free plan lets you create up to 50 short links per month, access basic analytics, and use one custom domain — forever. No credit card needed.'],
                    ['q'=>'Can I use my own domain?', 'a'=>'Absolutely. Add any domain you own (e.g. go.yourbrand.com) and we\'ll automatically provision a free SSL certificate via Let\'s Encrypt. DNS setup guide is included.'],
                    ['q'=>'How accurate is the analytics?', 'a'=>'We parse every click for device, OS, browser, country, city, referrer, and UTM parameters. We also detect and filter bot traffic to give you clean, actionable data.'],
                    ['q'=>'Can my team members access the same links?', 'a'=>'Yes. Team workspaces let you invite members, assign roles (owner, admin, member), and share a link quota. Perfect for marketing teams and agencies.'],
                    ['q'=>'What payment methods are accepted?', 'a'=>'We use Razorpay and accept all major Indian payment methods — credit/debit cards, UPI, net banking, and wallets.'],
                    ['q'=>'Can I cancel my subscription anytime?', 'a'=>'Yes. Cancel anytime from the billing page. Your plan remains active until the end of the billing period. No cancellation fees.'],
                    ['q'=>'Do you offer an API?', 'a'=>'A public REST API with full link management, analytics, and QR code endpoints is on our roadmap for Q2. Sign up for early access notifications.'],
                ];
            @endphp

            <div class="space-y-3">
                @foreach($faqs as $i => $faq)
                <div class="glass rounded-xl overflow-hidden border border-border-dark" :class="open === {{ $i }} ? 'border-primary/30' : ''">
                    <button @click="open = open === {{ $i }} ? null : {{ $i }}"
                            class="w-full flex items-center justify-between px-5 py-4 text-left">
                        <span class="text-sm font-semibold text-white">{{ $faq['q'] }}</span>
                        <span class="material-symbols-outlined !text-lg text-primary transition-transform shrink-0 ml-4"
                              :style="open === {{ $i }} ? 'transform:rotate(45deg)' : ''">add</span>
                    </button>
                    <div x-show="open === {{ $i }}" x-cloak x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                         class="px-5 pb-4 text-sm text-slate-400 leading-relaxed border-t border-border-dark pt-3">
                        {{ $faq['a'] }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>


    {{-- ════════════════════════════════════════════════════════════
         CTA BANNER
    ════════════════════════════════════════════════════════════ --}}
    <section class="py-20 px-4 sm:px-6">
        <div class="max-w-4xl mx-auto animated-border rounded-3xl overflow-hidden">
            <div class="glass text-center py-14 px-6 sm:py-20 sm:px-12 relative overflow-hidden rounded-3xl">
                <div class="absolute inset-0 bg-gradient-to-br from-primary/10 via-transparent to-accent-pink/10 pointer-events-none"></div>
                <div class="absolute top-0 left-1/2 -translate-x-1/2 size-48 bg-primary/15 rounded-full blur-3xl pointer-events-none"></div>

                <h2 class="relative text-3xl sm:text-5xl font-black tracking-tight mb-4">
                    Ready to shorten
                    <span class="grad-text block">and scale?</span>
                </h2>
                <p class="relative text-slate-400 max-w-lg mx-auto mb-8 text-base">Join thousands of marketers, creators, and teams already using {{ config('app.name') }} to drive smarter traffic.</p>

                <div class="relative flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="{{ route('register') }}" class="btn-primary w-full sm:w-auto inline-flex items-center justify-center gap-2 text-white font-semibold px-8 py-4 rounded-xl text-sm">
                        <span class="material-symbols-outlined !text-base">rocket_launch</span>
                        Start free — it only takes a minute
                    </a>
                    <a href="{{ route('login') }}" class="btn-outline w-full sm:w-auto inline-flex items-center justify-center gap-2 text-slate-300 font-medium px-6 py-4 rounded-xl text-sm">
                        Already have an account? Sign in
                        <span class="material-symbols-outlined !text-base">arrow_forward</span>
                    </a>
                </div>
            </div>
        </div>
    </section>


    {{-- ════════════════════════════════════════════════════════════
         FOOTER
    ════════════════════════════════════════════════════════════ --}}
    <footer class="border-t border-border-dark bg-card-dark/40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-14">
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-8 mb-12">

                {{-- Brand --}}
                <div class="col-span-2 sm:col-span-4 lg:col-span-2">
                    <a href="/" class="flex items-center gap-2.5 mb-4">
                        <div class="size-8 bg-primary rounded-lg flex items-center justify-center text-white shadow-lg shadow-primary/20">
                            <span class="material-symbols-outlined !text-xl">link</span>
                        </div>
                        <span class="text-sm font-bold text-white">{{ config('app.name') }}</span>
                    </a>
                    <p class="text-sm text-slate-500 leading-relaxed max-w-xs mb-5">The most powerful URL shortening platform for teams and individuals. Track, brand, and grow.</p>
                    <div class="flex gap-3">
                        @foreach([['twitter','Twitter'],['github','GitHub'],['mail','Email']] as $social)
                        <a href="#" class="size-8 glass rounded-lg flex items-center justify-center text-slate-400 hover:text-white hover:border-primary/40 transition-colors border border-border-dark">
                            <span class="material-symbols-outlined !text-sm">{{ $social[0] }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- Links columns --}}
                @php
                    $footerLinks = [
                        'Product'  => [['Features','#features'],['Pricing','#pricing'],['Analytics','#analytics'],['QR Codes','#features'],['Custom Domains','#features']],
                        'Company'  => [['About','#'],['Blog','#'],['Careers','#'],['Contact','#']],
                        'Legal'    => [['Privacy Policy', route('privacy')],['Terms of Service', route('terms')]],
                    ];
                @endphp
                @foreach($footerLinks as $heading => $links)
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">{{ $heading }}</h4>
                    <ul class="space-y-2.5">
                        @foreach($links as $link)
                        <li><a href="{{ $link[1] }}" class="text-sm text-slate-500 hover:text-white transition-colors">{{ $link[0] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>

            {{-- Bottom bar --}}
            <div class="border-t border-border-dark pt-7 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-600">
                <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <p class="flex items-center gap-1.5">
                    Built with <span class="material-symbols-outlined !text-sm text-red-400">favorite</span> using Laravel
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
