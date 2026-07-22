<!DOCTYPE html>
<html class="dark scroll-smooth" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Terms of Service | {{ config('app.name') }}</title>
    <meta name="description" content="Terms of Service for {{ config('app.name') }} — please read carefully before using the platform." />

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
        .prose-dark ol  { color: #94a3b8; list-style: decimal; padding-left: 1.5rem; margin-bottom: 1rem; }
        .prose-dark li  { margin-bottom: 0.4rem; line-height: 1.7; font-size: 0.9375rem; }
        .prose-dark a   { color: #CC66DA; text-decoration: underline; }
        .prose-dark strong { color: #e2e8f0; }
        .toc-link { display: block; font-size: 0.8125rem; color: #64748b; padding: 0.3rem 0; transition: color 0.15s; }
        .toc-link:hover { color: #CC66DA; }
        .section-divider { border-color: #1f1f1f; margin: 2rem 0; }
        .btn-primary { background: linear-gradient(135deg, #9a28eb 0%, #CC66DA 100%); transition: opacity 0.2s; }
        .btn-primary:hover { opacity: 0.9; }
        .highlight-box { background: rgba(154,40,235,0.06); border: 1px solid rgba(154,40,235,0.2); border-radius: 0.75rem; padding: 1.25rem 1.5rem; margin: 1.25rem 0; }
        .warning-box { background: rgba(234,179,8,0.06); border: 1px solid rgba(234,179,8,0.2); border-radius: 0.75rem; padding: 1.25rem 1.5rem; margin: 1.25rem 0; }
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
                            <a href="#acceptance"          class="toc-link">1. Acceptance of Terms</a>
                            <a href="#description"         class="toc-link">2. Service Description</a>
                            <a href="#eligibility"         class="toc-link">3. Eligibility</a>
                            <a href="#account"             class="toc-link">4. Your Account</a>
                            <a href="#acceptable-use"      class="toc-link">5. Acceptable Use</a>
                            <a href="#prohibited"          class="toc-link">6. Prohibited Content</a>
                            <a href="#plans-billing"       class="toc-link">7. Plans &amp; Billing</a>
                            <a href="#intellectual"        class="toc-link">8. Intellectual Property</a>
                            <a href="#user-content"        class="toc-link">9. User Content</a>
                            <a href="#termination"         class="toc-link">10. Termination</a>
                            <a href="#disclaimers"         class="toc-link">11. Disclaimers</a>
                            <a href="#limitation"          class="toc-link">12. Limitation of Liability</a>
                            <a href="#indemnification"     class="toc-link">13. Indemnification</a>
                            <a href="#governing-law"       class="toc-link">14. Governing Law</a>
                            <a href="#changes"             class="toc-link">15. Changes to Terms</a>
                            <a href="#contact"             class="toc-link">16. Contact</a>
                        </nav>
                        <hr class="section-divider mt-5" />
                        <a href="{{ route('privacy') }}" class="text-xs text-slate-500 hover:text-accent-pink transition-colors flex items-center gap-1.5 mt-3">
                            <span class="material-symbols-outlined !text-sm">privacy_tip</span>
                            Privacy Policy →
                        </a>
                    </div>
                </aside>

                {{-- Main content --}}
                <main class="min-w-0">

                    {{-- Header --}}
                    <div class="mb-10">
                        <div class="inline-flex items-center gap-2 text-xs font-medium px-3 py-1.5 rounded-full glass border border-primary/20 text-primary mb-4">
                            <span class="material-symbols-outlined !text-sm">gavel</span>
                            Legal
                        </div>
                        <h1 class="text-3xl sm:text-4xl font-black tracking-tight mb-3">
                            Terms of <span class="grad-text">Service</span>
                        </h1>
                        <div class="flex flex-wrap gap-4 text-sm text-slate-500">
                            <span class="flex items-center gap-1.5"><span class="material-symbols-outlined !text-sm">calendar_today</span> Last updated: March 01, 2026</span>
                            <span class="flex items-center gap-1.5"><span class="material-symbols-outlined !text-sm">schedule</span> ~10 min read</span>
                        </div>
                    </div>

                    {{-- Notice banner --}}
                    <div class="warning-box flex gap-3 mb-8">
                        <span class="material-symbols-outlined !text-lg text-yellow-400 shrink-0">warning</span>
                        <p class="text-sm text-slate-300 leading-relaxed"><strong class="text-yellow-300">Please read these Terms carefully.</strong> By accessing or using {{ config('app.name') }}, you agree to be legally bound by these Terms of Service. If you do not agree, you must not use the Service.</p>
                    </div>

                    <div class="prose-dark">

                        {{-- 1. Acceptance --}}
                        <section id="acceptance">
                            <h2>1. Acceptance of Terms</h2>
                            <p>These Terms of Service ("Terms") constitute a legally binding agreement between you ("User," "you," or "your") and {{ config('app.name') }} ("Company," "we," "us," or "our") governing your access to and use of the {{ config('app.name') }} URL shortening platform, including all related websites, APIs, mobile applications, and services (collectively, the "Service").</p>
                            <p>By creating an account, accessing, or using the Service in any manner, you acknowledge that you have read, understood, and agree to be bound by these Terms and our <a href="{{ route('privacy') }}">Privacy Policy</a>, which is incorporated herein by reference.</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 2. Service Description --}}
                        <section id="description">
                            <h2>2. Service Description</h2>
                            <p>{{ config('app.name') }} provides a URL shortening and link management platform that enables users to:</p>
                            <ul>
                                <li>Create shortened URLs ("Short Links") that redirect to a destination URL of your choosing.</li>
                                <li>Track click analytics including geographic data, device type, browser, and referrer information.</li>
                                <li>Generate QR codes associated with Short Links.</li>
                                <li>Use custom domain names for Short Links.</li>
                                <li>Collaborate with team members in shared workspaces.</li>
                                <li>Manage subscriptions and billing information.</li>
                            </ul>
                            <p>We reserve the right to modify, suspend, or discontinue any aspect of the Service at any time, with or without notice, without liability to you.</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 3. Eligibility --}}
                        <section id="eligibility">
                            <h2>3. Eligibility</h2>
                            <p>To use the Service, you must:</p>
                            <ul>
                                <li>Be at least 13 years of age (or 16 in certain jurisdictions, e.g. the European Union).</li>
                                <li>Have the legal capacity to enter into a binding contract in your jurisdiction.</li>
                                <li>Not be prohibited from accessing the Service under applicable law.</li>
                                <li>Not have a previously terminated {{ config('app.name') }} account.</li>
                            </ul>
                            <p>If you are using the Service on behalf of a business or organisation, you represent that you have the authority to bind that entity to these Terms.</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 4. Account --}}
                        <section id="account">
                            <h2>4. Your Account</h2>

                            <h3>4.1 Registration</h3>
                            <p>You must provide accurate, complete, and current information during registration. You are responsible for keeping your account information up to date.</p>

                            <h3>4.2 Account Security</h3>
                            <p>You are solely responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account. You must:</p>
                            <ul>
                                <li>Use a strong, unique password.</li>
                                <li>Enable two-factor authentication where available.</li>
                                <li>Immediately notify us of any unauthorised access or security breach at <a href="mailto:security@{{ strtolower(config('app.name')) }}.io">security@{{ strtolower(config('app.name')) }}.io</a>.</li>
                            </ul>
                            <p>We are not liable for any loss or damage arising from your failure to secure your account credentials.</p>

                            <h3>4.3 One Account Per User</h3>
                            <p>Each person may maintain only one free-tier account. Creating multiple accounts to circumvent plan limits is a violation of these Terms and will result in immediate termination.</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 5. Acceptable Use --}}
                        <section id="acceptable-use">
                            <h2>5. Acceptable Use</h2>
                            <p>You agree to use the Service only for lawful purposes and in accordance with these Terms. You agree that you will:</p>
                            <ul>
                                <li>Comply with all applicable local, national, and international laws and regulations.</li>
                                <li>Use the Service only for the purpose for which it is intended — link management and analytics.</li>
                                <li>Respect the intellectual property rights of others.</li>
                                <li>Not attempt to gain unauthorised access to any part of the Service or its infrastructure.</li>
                                <li>Not interfere with or disrupt the integrity or performance of the Service.</li>
                                <li>Not engage in data scraping, spidering, or bulk extraction without prior written consent.</li>
                                <li>Not use automated bots or scripts to create links at volumes exceeding your plan's limits.</li>
                            </ul>
                        </section>

                        <hr class="section-divider" />

                        {{-- 6. Prohibited Content --}}
                        <section id="prohibited">
                            <h2>6. Prohibited Content</h2>
                            <p>You must not use the Service to create Short Links that redirect to, promote, or facilitate:</p>
                            <div class="highlight-box not-prose">
                                <ul class="text-sm text-slate-400 space-y-1.5 list-disc list-inside">
                                    <li>Child sexual abuse material (CSAM) or any exploitation of minors.</li>
                                    <li>Phishing, fraud, identity theft, or social engineering attacks.</li>
                                    <li>Malware, ransomware, spyware, or any form of malicious code.</li>
                                    <li>Spam, unsolicited bulk messaging, or email harvesting.</li>
                                    <li>Hate speech, incitement to violence, or content targeting individuals on the basis of race, ethnicity, religion, gender, sexual orientation, or disability.</li>
                                    <li>Illegal gambling, narcotics trafficking, or weapons sales.</li>
                                    <li>Copyright-infringing content or trademark violations.</li>
                                    <li>Circumvention of geographic restrictions or access controls.</li>
                                    <li>Any other content that is illegal, harmful, or offensive under applicable law.</li>
                                </ul>
                            </div>
                            <p>We employ automated spam detection and reserve the right to immediately deactivate any link, suspend any account, and report activities to appropriate authorities without prior notice.</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 7. Plans & Billing --}}
                        <section id="plans-billing">
                            <h2>7. Plans &amp; Billing</h2>

                            <h3>7.1 Subscription Plans</h3>
                            <p>We offer a free tier and paid subscription plans. Plan features, limits, and pricing are described on our <a href="{{ url('/') }}#pricing">Pricing page</a> and may be updated from time to time. Current subscribers will be given 30 days' notice before any price increase takes effect.</p>

                            <h3>7.2 Billing Cycles</h3>
                            <p>Paid plans are billed monthly or annually in advance. Fees are non-refundable except as expressly stated herein. By subscribing, you authorise us to charge your payment method on a recurring basis.</p>

                            <h3>7.3 Free Trial</h3>
                            <p>Paid plans may include a 14-day free trial. No charges will be made during the trial period. Unless you cancel before the trial ends, your payment method will be charged at the start of the first billing cycle.</p>

                            <h3>7.4 Cancellation &amp; Refunds</h3>
                            <p>You may cancel your subscription at any time from the Billing page. Upon cancellation, your subscription will remain active until the end of the current billing period. No partial refunds are issued for unused time within a billing cycle, except where required by applicable consumer protection law.</p>

                            <h3>7.5 Downgrades &amp; Overages</h3>
                            <p>If you downgrade to a lower plan, features exceeding the new plan's limits will be restricted (not deleted) until you remove excess items. We do not charge overage fees — if you exceed plan limits, creation of new items will be blocked until the start of the next cycle or until you upgrade.</p>

                            <h3>7.6 Taxes</h3>
                            <p>All prices shown are exclusive of applicable taxes. You are responsible for any GST, VAT, or other taxes imposed by your jurisdiction. We will include applicable taxes in your invoices where required by law.</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 8. Intellectual Property --}}
                        <section id="intellectual">
                            <h2>8. Intellectual Property</h2>

                            <h3>8.1 Our IP</h3>
                            <p>The Service, including its design, code, branding, trademarks, and all content we create, is owned by {{ config('app.name') }} or its licensors and is protected by intellectual property laws. You are granted a limited, non-exclusive, non-transferable licence to use the Service in accordance with these Terms. No rights are transferred to you beyond this usage licence.</p>

                            <h3>8.2 Feedback</h3>
                            <p>If you submit feedback, suggestions, or ideas about the Service, you grant us a perpetual, irrevocable, worldwide, royalty-free licence to use and incorporate that feedback without any obligation to you.</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 9. User Content --}}
                        <section id="user-content">
                            <h2>9. User Content</h2>
                            <p>You retain ownership of any destination URLs, titles, descriptions, and other content you submit to the Service ("User Content"). By submitting User Content, you grant us a limited licence to store, process, and display that content solely to provide the Service.</p>
                            <p>You represent and warrant that:</p>
                            <ul>
                                <li>You own or have the necessary rights to the User Content.</li>
                                <li>The User Content does not violate any third-party rights or applicable law.</li>
                                <li>The User Content does not contain prohibited content as described in Section 6.</li>
                            </ul>
                        </section>

                        <hr class="section-divider" />

                        {{-- 10. Termination --}}
                        <section id="termination">
                            <h2>10. Termination</h2>

                            <h3>10.1 By You</h3>
                            <p>You may delete your account at any time from the Profile settings page. Upon deletion, your data will be soft-deleted and permanently purged within 30 days, except for billing records retained for statutory compliance.</p>

                            <h3>10.2 By Us</h3>
                            <p>We may suspend or terminate your account immediately, without prior notice or liability, if you:</p>
                            <ul>
                                <li>Breach any provision of these Terms.</li>
                                <li>Use the Service to distribute prohibited content.</li>
                                <li>Attempt to circumvent security measures or abuse platform resources.</li>
                                <li>Fail to pay subscription fees when due.</li>
                            </ul>

                            <h3>10.3 Effect of Termination</h3>
                            <p>Upon termination, all your Short Links will be deactivated (return HTTP 404) and your access to analytics data will be revoked. Sections 8, 11, 12, 13, and 14 of these Terms survive termination.</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 11. Disclaimers --}}
                        <section id="disclaimers">
                            <h2>11. Disclaimers</h2>
                            <p>THE SERVICE IS PROVIDED ON AN "AS IS" AND "AS AVAILABLE" BASIS, WITHOUT WARRANTIES OF ANY KIND, EITHER EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, NON-INFRINGEMENT, AND ANY WARRANTIES ARISING OUT OF COURSE OF DEALING OR USAGE OF TRADE.</p>
                            <p>We do not warrant that the Service will be uninterrupted, error-free, or secure. We do not warrant the accuracy, completeness, or timeliness of analytics data. You use the Service at your own risk.</p>
                            <p>We are not responsible for the content of websites that your Short Links point to. We do not endorse any destination URLs.</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 12. Limitation of Liability --}}
                        <section id="limitation">
                            <h2>12. Limitation of Liability</h2>
                            <p>TO THE FULLEST EXTENT PERMITTED BY APPLICABLE LAW, IN NO EVENT SHALL {{ strtoupper(config('app.name')) }}, ITS OFFICERS, DIRECTORS, EMPLOYEES, OR AGENTS BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, PUNITIVE, OR EXEMPLARY DAMAGES, INCLUDING LOSS OF PROFITS, DATA, GOODWILL, OR BUSINESS INTERRUPTION, ARISING OUT OF OR IN CONNECTION WITH YOUR USE OF THE SERVICE.</p>
                            <p>OUR TOTAL AGGREGATE LIABILITY TO YOU FOR ANY CLAIM ARISING FROM OR RELATED TO THE SERVICE SHALL NOT EXCEED THE GREATER OF (A) THE AMOUNT YOU PAID US IN THE 12 MONTHS PRECEDING THE CLAIM, OR (B) ₹1,000 (ONE THOUSAND INDIAN RUPEES).</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 13. Indemnification --}}
                        <section id="indemnification">
                            <h2>13. Indemnification</h2>
                            <p>You agree to indemnify, defend, and hold harmless {{ config('app.name') }} and its affiliates, officers, directors, employees, and agents from and against any claims, liabilities, damages, losses, and expenses (including reasonable legal fees) arising out of or in any way related to:</p>
                            <ul>
                                <li>Your use of or access to the Service.</li>
                                <li>Your violation of any provision of these Terms.</li>
                                <li>Your violation of any third-party right, including intellectual property or privacy rights.</li>
                                <li>Any claim that your User Content caused damage to a third party.</li>
                            </ul>
                        </section>

                        <hr class="section-divider" />

                        {{-- 14. Governing Law --}}
                        <section id="governing-law">
                            <h2>14. Governing Law &amp; Dispute Resolution</h2>
                            <p>These Terms shall be governed by and construed in accordance with the laws of India, without regard to conflict of law principles. Any disputes arising under these Terms shall be subject to the exclusive jurisdiction of the courts located in India.</p>
                            <p>Before initiating legal proceedings, you agree to attempt to resolve any dispute informally by contacting us at <a href="mailto:legal@{{ strtolower(config('app.name')) }}.io">legal@{{ strtolower(config('app.name')) }}.io</a>. We will make good-faith efforts to resolve the dispute within 30 days.</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 15. Changes --}}
                        <section id="changes">
                            <h2>15. Changes to Terms</h2>
                            <p>We reserve the right to modify these Terms at any time. For material changes, we will provide at least 14 days' notice by:</p>
                            <ul>
                                <li>Sending an email notification to your registered email address.</li>
                                <li>Displaying a notice within the dashboard.</li>
                                <li>Updating the "Last updated" date at the top of this page.</li>
                            </ul>
                            <p>Your continued use of the Service after the effective date of the updated Terms constitutes your acceptance. If you do not agree, you must cease using the Service and may cancel your account.</p>
                        </section>

                        <hr class="section-divider" />

                        {{-- 16. Contact --}}
                        <section id="contact">
                            <h2>16. Contact</h2>
                            <p>For any questions regarding these Terms of Service, please contact us:</p>
                            <div class="glass rounded-xl p-5 mt-4 not-prose">
                                <div class="space-y-3 text-sm">
                                    <div class="flex items-center gap-3 text-slate-300">
                                        <span class="material-symbols-outlined !text-base text-primary">mail</span>
                                        <a href="mailto:legal@{{ strtolower(config('app.name')) }}.io" class="hover:text-accent-pink transition-colors">legal@{{ strtolower(config('app.name')) }}.io</a>
                                    </div>
                                    <div class="flex items-center gap-3 text-slate-300">
                                        <span class="material-symbols-outlined !text-base text-primary">support_agent</span>
                                        <a href="mailto:support@{{ strtolower(config('app.name')) }}.io" class="hover:text-accent-pink transition-colors">support@{{ strtolower(config('app.name')) }}.io</a>
                                        <span class="text-xs text-slate-600">(general support)</span>
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
                            <a href="{{ route('privacy') }}" class="text-slate-400 hover:text-accent-pink transition-colors flex items-center gap-1.5">
                                <span class="material-symbols-outlined !text-sm">privacy_tip</span>Privacy Policy
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
