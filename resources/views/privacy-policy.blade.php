<!DOCTYPE html>
<html class="dark scroll-smooth" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Privacy Policy | {{ config('app.name') }}</title>
    <meta name="description" content="Privacy Policy for {{ config('app.name') }} — learn how we collect, use, and protect your data." />

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet" />

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary":         "#9a28eb",
                        "accent-pink":     "#CC66DA",
                        "background-dark": "#000000",
                        "card-dark":       "#0a0a0a",
                        "border-dark":     "#1f1f1f",
                    },
                    fontFamily: { sans: ["Inter", "sans-serif"] },
                },
            },
        };
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; background: #000; }
        .grad-text {
            background: linear-gradient(135deg, #9a28eb 0%, #CC66DA 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .glass { background: rgba(10,10,10,0.7); backdrop-filter: blur(16px); border: 1px solid #1f1f1f; }
        .nav-blur { background: rgba(0,0,0,0.8); backdrop-filter: blur(20px); border-bottom: 1px solid #111; }
        .hero-glow { background: radial-gradient(ellipse 80% 40% at 50% 0%, rgba(154,40,235,0.12) 0%, transparent 70%); }
        .grid-bg {
            background-image: linear-gradient(rgba(154,40,235,0.03) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(154,40,235,0.03) 1px, transparent 1px);
            background-size: 48px 48px;
        }
        .prose-dark h2  { color: #f1f5f9; font-size: 1.25rem; font-weight: 700; margin-top: 2.5rem; margin-bottom: 0.75rem; }
        .prose-dark h3  { color: #e2e8f0; font-size: 1rem; font-weight: 600; margin-top: 1.75rem; margin-bottom: 0.5rem; }
        .prose-dark p   { color: #94a3b8; line-height: 1.8; margin-bottom: 1rem; font-size: 0.9375rem; }
        .prose-dark ul  { color: #94a3b8; list-style: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
        .prose-dark li  { margin-bottom: 0.4rem; line-height: 1.7; font-size: 0.9375rem; }
        .prose-dark a   { color: #CC66DA; text-decoration: underline; }
        .prose-dark strong { color: #e2e8f0; }
        .toc-link { display: block; font-size: 0.8125rem; color: #64748b; padding: 0.3rem 0; transition: color 0.15s; }
        .toc-link:hover { color: #CC66DA; }
        .section-divider { border-color: #1f1f1f; margin: 2rem 0; }
        .btn-primary { background: linear-gradient(135deg, #9a28eb 0%, #CC66DA 100%); transition: opacity 0.2s; }
        .btn-primary:hover { opacity: 0.9; }
    </style>
</head>
<body class="text-slate-100 min-h-screen">

    {{-- Ambient glow --}}
    <div class="hero-glow fixed inset-0 pointer-events-none"></div>
    <div class="grid-bg fixed inset-0 pointer-events-none opacity-60"></div>

    {{-- ── Navbar ── --}}
    <nav class="fixed top-0 inset-x-0 z-50 nav-blur">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                <div class="size-8 bg-primary rounded-lg flex items-center justify-center text-white shadow-lg shadow-primary/30">
                    <span class="material-symbols-outlined !text-xl">link</span>
                </div>
                <span class="text-sm font-bold text-white">{{ config('app.name') }}</span>
            </a>
            <div class="flex items-center gap-3">
                <a href="{{ url('/') }}" class="text-sm text-slate-400 hover:text-white transition-colors flex items-center gap-1.5">
                    <span class="material-symbols-outlined !text-base">arrow_back</span>
                    Back to Home
                </a>
                <a href="{{ route('register') }}" class="btn-primary text-white text-sm font-medium px-4 py-2 rounded-lg">Get Started</a>
            </div>
        </div>
    </nav>

    {{-- ── Page layout ── --}}
    <div class="relative pt-24 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="grid lg:grid-cols-[260px_1fr] gap-10">

                {{-- Table of contents (sticky sidebar) --}}
                <aside class="hidden lg:block">
                    <div class="sticky top-24 glass rounded-2xl p-5">
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4">On this page</p>
                        <nav class="space-y-0.5">
                            <a href="#introduction"          class="toc-link">1. Introduction</a>
                            <a href="#information-we-collect" class="toc-link">2. Information We Collect</a>
                            <a href="#how-we-use"            class="toc-link">3. How We Use Your Information</a>
                            <a href="#sharing"               class="toc-link">4. Sharing Your Information</a>
                            <a href="#data-retention"        class="toc-link">5. Data Retention</a>
                            <a href="#security"              class="toc-link">6. Security</a>
                            <a href="#your-rights"           class="toc-link">7. Your Rights</a>
                            <a href="#cookies"               class="toc-link">8. Cookies</a>
                            <a href="#third-party"           class="toc-link">9. Third-Party Services</a>
                            <a href="#children"              class="toc-link">10. Children's Privacy</a>
                            <a href="#changes"               class="toc-link">11. Changes to This Policy</a>
                            <a href="#contact"               class="toc-link">12. Contact Us</a>
                        </nav>
                        <hr class="section-divider mt-5" />
                        <a href="{{ route('terms') }}" class="text-xs text-slate-500 hover:text-accent-pink transition-colors flex items-center gap-1.5 mt-3">
                            <span class="material-symbols-outlined !text-sm">description</span>
                            Terms of Service →
                        </a>
                    </div>
                </aside>

                {{-- Main content --}}
                <main class="min-w-0">

                    {{-- Header --}}
                    <div class="mb-10">
                        <div class="inline-flex items-center gap-2 text-xs font-medium px-3 py-1.5 rounded-full glass border border-primary/20 text-primary mb-4">
                            <span class="material-symbols-outlined !text-sm">privacy_tip</span>
                            Legal
                        </div>
                        <h1 class="text-3xl sm:text-4xl font-black tracking-tight mb-3">
                            Privacy <span class="grad-text">Policy</span>
                        </h1>
                        <div class="flex flex-wrap gap-4 text-sm text-slate-500">
                            <span class="flex items-center gap-1.5"><span class="material-symbols-outlined !text-sm">calendar_today</span> Last updated: March 01, 2026</span>
                            <span class="flex items-center gap-1.5"><span class="material-symbols-outlined !text-sm">schedule</span> ~8 min read</span>
                        </div>
                    </div>

                    {{-- Notice banner --}}
                    <div class="glass rounded-xl px-5 py-4 mb-8 border-l-2 border-primary flex gap-3">
                        <span class="material-symbols-outlined !text-lg text-primary shrink-0">info</span>
                        <p class="text-sm text-slate-400 leading-relaxed">This Privacy Policy describes how <strong class="text-slate-200">{{ config('app.name') }}</strong> ("we", "us", or "our") collects, uses, and shares your personal information when you use our URL shortening platform and related services.</p>
                    </div>

                    <div class="prose-dark">

                        {{-- 1. Introduction --}}
                        <section id="introduction">
                            <h2>1. Introduction</h2>
                            <p>Welcome to {{ config('app.name') }}. We are committed to protecting your personal information and your right to privacy. This Privacy Policy applies to all information collected through our website, platform, and any related services, sales, marketing, or events.</p>
                            <p>By accessing or using our Service, you agree to the terms of this Privacy Policy. If you do not agree, please discontinue use of the Service immediately.</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 2. Information We Collect --}}
                        <section id="information-we-collect">
                            <h2>2. Information We Collect</h2>
                            <p>We collect information you provide directly to us, information collected automatically when you use our Service, and information from third-party sources.</p>

                            <h3>2.1 Information You Provide</h3>
                            <ul>
                                <li><strong>Account information:</strong> name, email address, password, avatar, and timezone when you register.</li>
                                <li><strong>Billing information:</strong> payment method details processed securely by Razorpay. We do not store raw card numbers.</li>
                                <li><strong>Team data:</strong> team names, member email addresses you invite, and role assignments.</li>
                                <li><strong>Links and content:</strong> URLs you shorten, titles, descriptions, and associated metadata you enter.</li>
                                <li><strong>Communications:</strong> messages you send us through support channels or feedback forms.</li>
                            </ul>

                            <h3>2.2 Information Collected Automatically</h3>
                            <ul>
                                <li><strong>Click analytics:</strong> IP address, approximate geolocation (country, region, city), device type, operating system, browser, and referrer URL — captured when someone clicks one of your short links.</li>
                                <li><strong>Usage data:</strong> pages you visit on our platform, features you use, and time spent.</li>
                                <li><strong>Log data:</strong> server logs including request timestamps, error logs, and performance metrics.</li>
                                <li><strong>Cookies and tracking:</strong> session cookies for authentication and preference cookies. See Section 8 for details.</li>
                            </ul>

                            <h3>2.3 Information from Third Parties</h3>
                            <ul>
                                <li>Payment transaction records from Razorpay (transaction IDs, status, and amounts).</li>
                                <li>MaxMind GeoIP data used to resolve IP addresses to approximate locations.</li>
                            </ul>
                        </section>

                        <hr class="section-divider" />

                        {{-- 3. How We Use --}}
                        <section id="how-we-use">
                            <h2>3. How We Use Your Information</h2>
                            <p>We use the information we collect to:</p>
                            <ul>
                                <li>Provide, operate, and maintain the {{ config('app.name') }} platform and its features.</li>
                                <li>Process transactions and send related information such as invoices and receipts.</li>
                                <li>Send transactional emails (account verification, password reset, subscription confirmations).</li>
                                <li>Send product updates, security notices, and support responses.</li>
                                <li>Generate aggregated, anonymised analytics you can view in your dashboard.</li>
                                <li>Detect, investigate, and prevent fraudulent or abusive activity and spam links.</li>
                                <li>Comply with legal obligations.</li>
                                <li>Improve and develop new features based on usage patterns (using aggregated data only).</li>
                            </ul>
                            <p>We do <strong>not</strong> sell your personal data to third parties. We do not use your data to serve third-party advertising.</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 4. Sharing --}}
                        <section id="sharing">
                            <h2>4. Sharing Your Information</h2>
                            <p>We may share your information in the following limited circumstances:</p>
                            <ul>
                                <li><strong>Service providers:</strong> Trusted vendors who assist us in operating the platform (e.g., hosting, email delivery, payment processing) under strict data processing agreements.</li>
                                <li><strong>Team members:</strong> If you belong to a team workspace, team owners and admins may see the links you create within that workspace.</li>
                                <li><strong>Legal requirements:</strong> When required by law, court order, or governmental authority.</li>
                                <li><strong>Business transfers:</strong> In connection with a merger, acquisition, or sale of assets, under confidentiality obligations.</li>
                                <li><strong>With your consent:</strong> In any other circumstances where you have explicitly authorised the disclosure.</li>
                            </ul>
                            <p>Aggregated, anonymised analytics data (e.g., "links created this month globally") may be shared publicly or with partners — this data cannot identify you.</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 5. Data Retention --}}
                        <section id="data-retention">
                            <h2>5. Data Retention</h2>
                            <p>We retain your personal information for as long as your account is active or as needed to provide the Service. Specifically:</p>
                            <ul>
                                <li><strong>Account data:</strong> Retained until you delete your account. Soft-deleted and purged after 30 days.</li>
                                <li><strong>Click analytics:</strong> Retained for 24 months from the date of the click. Older data is aggregated and anonymised.</li>
                                <li><strong>Billing records:</strong> Retained for 7 years to comply with accounting and tax regulations.</li>
                                <li><strong>Support communications:</strong> Retained for 2 years after ticket closure.</li>
                            </ul>
                            <p>You may request deletion of your data at any time by contacting us (see Section 12). Note that certain data may need to be retained for legal compliance.</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 6. Security --}}
                        <section id="security">
                            <h2>6. Security</h2>
                            <p>We implement industry-standard technical and organisational measures to protect your data, including:</p>
                            <ul>
                                <li>TLS/SSL encryption for all data in transit.</li>
                                <li>AES-256 encryption for sensitive data at rest.</li>
                                <li>Hashed passwords using bcrypt with a cost factor of 12.</li>
                                <li>Regular automated security scans and dependency audits.</li>
                                <li>Role-based access control restricting internal access to personal data.</li>
                                <li>Two-factor authentication support for all accounts.</li>
                            </ul>
                            <p>Despite our efforts, no method of transmission over the internet is 100% secure. If you suspect a security incident, please contact us immediately at <a href="mailto:security@{{ strtolower(config('app.name')) }}.io">security@{{ strtolower(config('app.name')) }}.io</a>.</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 7. Your Rights --}}
                        <section id="your-rights">
                            <h2>7. Your Rights</h2>
                            <p>Depending on your jurisdiction, you may have the following rights regarding your personal data:</p>
                            <ul>
                                <li><strong>Access:</strong> Request a copy of the personal data we hold about you.</li>
                                <li><strong>Rectification:</strong> Correct inaccurate or incomplete data.</li>
                                <li><strong>Erasure:</strong> Request deletion of your personal data ("right to be forgotten").</li>
                                <li><strong>Portability:</strong> Receive your data in a machine-readable format.</li>
                                <li><strong>Objection:</strong> Object to processing of your data for certain purposes.</li>
                                <li><strong>Restriction:</strong> Request restricted processing in certain circumstances.</li>
                                <li><strong>Withdraw consent:</strong> Where processing is based on consent, withdraw it at any time.</li>
                            </ul>
                            <p>To exercise any of these rights, contact us at <a href="mailto:privacy@{{ strtolower(config('app.name')) }}.io">privacy@{{ strtolower(config('app.name')) }}.io</a>. We will respond within 30 days.</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 8. Cookies --}}
                        <section id="cookies">
                            <h2>8. Cookies</h2>
                            <p>We use cookies and similar tracking technologies to operate the Service. The types of cookies we use:</p>
                            <ul>
                                <li><strong>Essential cookies:</strong> Required for authentication sessions, CSRF protection, and core platform functionality. Cannot be disabled.</li>
                                <li><strong>Preference cookies:</strong> Store your UI preferences such as timezone and display settings.</li>
                                <li><strong>Analytics cookies:</strong> First-party analytics to understand how users interact with the platform (no third-party analytics trackers).</li>
                            </ul>
                            <p>We do <strong>not</strong> use advertising or tracking cookies from third parties. You can control non-essential cookies through your browser settings or account preferences.</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 9. Third-Party Services --}}
                        <section id="third-party">
                            <h2>9. Third-Party Services</h2>
                            <p>Our Service integrates with the following third-party providers. Their privacy practices apply when you interact with them:</p>
                            <ul>
                                <li><strong>Razorpay</strong> — payment processing. Governed by the <a href="https://razorpay.com/privacy/" target="_blank" rel="noopener noreferrer">Razorpay Privacy Policy</a>.</li>
                                <li><strong>MaxMind GeoIP</strong> — IP geolocation (server-side only; your IP is not shared with MaxMind in real-time).</li>
                                <li><strong>Google Fonts</strong> — font delivery. May set performance cookies per Google's policies.</li>
                            </ul>
                            <p>We are not responsible for the privacy practices of third-party services. We encourage you to review their privacy policies.</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 10. Children --}}
                        <section id="children">
                            <h2>10. Children's Privacy</h2>
                            <p>Our Service is not directed to individuals under the age of 13. We do not knowingly collect personal information from children under 13. If you become aware that a child has provided us with personal data, please contact us and we will take steps to delete such information.</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 11. Changes --}}
                        <section id="changes">
                            <h2>11. Changes to This Policy</h2>
                            <p>We may update this Privacy Policy from time to time. We will notify you of material changes by:</p>
                            <ul>
                                <li>Sending an email to your registered address at least 14 days before the change takes effect.</li>
                                <li>Displaying a prominent notice in the dashboard.</li>
                                <li>Updating the "Last updated" date at the top of this page.</li>
                            </ul>
                            <p>Continued use of the Service after the effective date constitutes acceptance of the updated policy.</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 12. Contact --}}
                        <section id="contact">
                            <h2>12. Contact Us</h2>
                            <p>If you have any questions, concerns, or requests regarding this Privacy Policy, please contact us:</p>
                            <div class="glass rounded-xl p-5 mt-4 not-prose">
                                <div class="space-y-3 text-sm">
                                    <div class="flex items-center gap-3 text-slate-300">
                                        <span class="material-symbols-outlined !text-base text-primary">mail</span>
                                        <a href="mailto:privacy@{{ strtolower(config('app.name')) }}.io" class="hover:text-accent-pink transition-colors">privacy@{{ strtolower(config('app.name')) }}.io</a>
                                    </div>
                                    <div class="flex items-center gap-3 text-slate-300">
                                        <span class="material-symbols-outlined !text-base text-primary">mail</span>
                                        <a href="mailto:security@{{ strtolower(config('app.name')) }}.io" class="hover:text-accent-pink transition-colors">security@{{ strtolower(config('app.name')) }}.io</a>
                                        <span class="text-xs text-slate-600">(security issues)</span>
                                    </div>
                                    <div class="flex items-start gap-3 text-slate-400">
                                        <span class="material-symbols-outlined !text-base text-primary shrink-0 mt-0.5">location_on</span>
                                        <span>{{ config('app.name') }}, India</span>
                                    </div>
                                </div>
                            </div>
                        </section>

                    </div>{{-- /prose-dark --}}

                    {{-- Footer nav --}}
                    <div class="mt-14 pt-8 border-t border-border-dark flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex gap-4 text-sm">
                            <a href="{{ route('terms') }}"  class="text-slate-400 hover:text-accent-pink transition-colors flex items-center gap-1.5">
                                <span class="material-symbols-outlined !text-sm">description</span>Terms of Service
                            </a>
                        </div>
                        <a href="{{ url('/') }}" class="text-sm text-slate-500 hover:text-white transition-colors flex items-center gap-1.5">
                            <span class="material-symbols-outlined !text-sm">arrow_back</span>
                            Back to Home
                        </a>
                    </div>

                </main>
            </div>
        </div>
    </div>

</body>
</html>
