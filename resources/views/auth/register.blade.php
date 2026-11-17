<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create HR Account | TRIX WAY</title>

    <link rel="icon" type="image/png" href="{{ asset('images/trix-logo.jpg') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ============ FLOATING PARTICLES ============ */
        @keyframes trixFloat {
            0%, 100% { transform: translateY(0) translateX(0); opacity: 0.4; }
            50%      { transform: translateY(-30px) translateX(15px); opacity: 0.8; }
        }
        .trix-particle {
            position: absolute;
            border-radius: 9999px;
            animation: trixFloat 8s ease-in-out infinite;
        }

        /* ============ BLOB ============ */
        @keyframes trixBlob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33%      { transform: translate(30px, -40px) scale(1.08); }
            66%      { transform: translate(-25px, 25px) scale(0.95); }
        }
        .trix-blob   { animation: trixBlob 18s ease-in-out infinite; }
        .trix-blob-2 { animation: trixBlob 22s ease-in-out infinite reverse; }

        /* ============ ENTRANCE ANIMATIONS ============ */
        @keyframes trixSlideInLeft {
            from { opacity: 0; transform: translateX(-30px) rotate(-1deg); }
            to   { opacity: 1; transform: translateX(0) rotate(0); }
        }
        @keyframes trixSlideInRight {
            from { opacity: 0; transform: translateX(30px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes trixFadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes trixScaleIn {
            from { opacity: 0; transform: scale(0.9); }
            to   { opacity: 1; transform: scale(1); }
        }
        .trix-slide-l { opacity: 0; animation: trixSlideInLeft .7s cubic-bezier(.4,0,.2,1) forwards; }
        .trix-slide-r { opacity: 0; animation: trixSlideInRight .7s cubic-bezier(.4,0,.2,1) forwards; }
        .trix-fade-up { opacity: 0; animation: trixFadeUp .6s cubic-bezier(.4,0,.2,1) forwards; }
        .trix-scale   { opacity: 0; animation: trixScaleIn .5s cubic-bezier(.4,0,.2,1) forwards; }
        .trix-d1 { animation-delay: .05s; }
        .trix-d2 { animation-delay: .15s; }
        .trix-d3 { animation-delay: .25s; }
        .trix-d4 { animation-delay: .35s; }
        .trix-d5 { animation-delay: .45s; }
        .trix-d6 { animation-delay: .55s; }
        .trix-d7 { animation-delay: .65s; }
        .trix-d8 { animation-delay: .75s; }
        .trix-d9 { animation-delay: .85s; }

        /* ============ SHAKE ============ */
        @keyframes trixShake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }
        .trix-shake { animation: trixShake .4s ease-in-out; }

        /* ============ SHIMMER (logo panel) ============ */
        @keyframes trixShimmer {
            0%   { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .trix-shimmer {
            background-image: linear-gradient(
                110deg,
                transparent 25%,
                rgba(255,255,255,0.6) 50%,
                transparent 75%
            );
            background-size: 200% 100%;
            animation: trixShimmer 3s ease-in-out infinite;
        }

        /* ============ TYPING CURSOR ============ */
        @keyframes trixBlink {
            0%, 50%   { opacity: 1; }
            51%, 100% { opacity: 0; }
        }
        .trix-cursor {
            display: inline-block;
            width: 3px;
            height: 1em;
            background: #059669;
            margin-left: 4px;
            vertical-align: text-bottom;
            animation: trixBlink 1s step-end infinite;
        }

        /* ============ FLOATING LABEL ============ */
        .trix-field { position: relative; }
        .trix-field input {
            width: 100%;
            padding: 22px 16px 10px 16px;
            border-radius: 14px;
            border: 1.5px solid #D6DEEA;
            background: #F8FAFC;
            font-size: 14px;
            color: #1C2A40;
            transition: all .25s cubic-bezier(.4,0,.2,1);
            outline: none;
        }
        .trix-field input.has-icon {
            padding-right: 48px;
        }
        .trix-field input:hover {
            border-color: #B9C8DE;
            background: #FFFFFF;
        }
        .trix-field input:focus {
            border-color: #6366F1;
            background: #FFFFFF;
            box-shadow: 0 0 0 4px rgba(99,102,241,0.12), 0 0 0 1px rgba(99,102,241,0.4);
            animation: trixInputPulse 2s ease-in-out infinite;
        }
        @keyframes trixInputPulse {
            0%, 100% { box-shadow: 0 0 0 4px rgba(99,102,241,0.12), 0 0 0 1px rgba(99,102,241,0.4); }
            50%      { box-shadow: 0 0 0 6px rgba(99,102,241,0.08), 0 0 0 1px rgba(99,102,241,0.5); }
        }
        .trix-field label {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            color: #64748B;
            pointer-events: none;
            transition: all .2s cubic-bezier(.4,0,.2,1);
            padding: 0 4px;
        }
        .trix-field input:focus + label,
        .trix-field input:not(:placeholder-shown) + label {
            top: 10px;
            transform: translateY(0);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #6366F1;
            background: #FFFFFF;
        }
        .trix-field input:not(:focus):not(:placeholder-shown) + label {
            color: #64748B;
        }

        /* ============ BUTTON ============ */
        .trix-btn {
            position: relative;
            overflow: hidden;
            transition: all .3s cubic-bezier(.4,0,.2,1);
        }
        .trix-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
            opacity: 0;
            transition: opacity .3s ease;
            z-index: 0;
        }
        .trix-btn:hover::before { opacity: 1; }
        .trix-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px -8px rgba(99,102,241,0.45);
        }
        .trix-btn:active { transform: translateY(0) scale(0.98); }
        .trix-btn > * { position: relative; z-index: 1; }

        /* Ripple */
        .trix-ripple {
            position: absolute;
            border-radius: 9999px;
            background: rgba(255,255,255,0.5);
            transform: scale(0);
            animation: trixRipple .6s linear;
            pointer-events: none;
        }
        @keyframes trixRipple {
            to { transform: scale(4); opacity: 0; }
        }

        /* ============ FEATURE CARD ============ */
        .trix-feature {
            transition: all .3s cubic-bezier(.4,0,.2,1);
        }
        .trix-feature:hover {
            transform: translateY(-4px) scale(1.02);
            background: rgba(255,255,255,0.14);
            border-color: rgba(255,255,255,0.22);
            box-shadow: 0 12px 30px -8px rgba(0,0,0,0.3);
        }
        .trix-feature:hover .trix-feature-icon {
            animation: trixWiggle .5s ease-in-out;
        }
        @keyframes trixWiggle {
            0%, 100% { transform: rotate(0); }
            25%      { transform: rotate(-8deg); }
            75%      { transform: rotate(8deg); }
        }

        /* ============ PROGRESS BAR (loading top) ============ */
        @keyframes trixProgress {
            0%   { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        .trix-progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            overflow: hidden;
            z-index: 9999;
            opacity: 0;
            transition: opacity .2s;
        }
        .trix-progress-bar.active { opacity: 1; }
        .trix-progress-bar::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, #6366F1, #0891B2, #6366F1);
            background-size: 200% 100%;
            animation: trixProgress 1.2s ease-in-out infinite;
        }

        /* ============ PASSWORD STRENGTH ============ */
        .trix-strength-bar {
            height: 3px;
            border-radius: 9999px;
            background: #E2E8F0;
            overflow: hidden;
            margin-top: 8px;
        }
        .trix-strength-fill {
            height: 100%;
            width: 0;
            border-radius: 9999px;
            transition: all .3s cubic-bezier(.4,0,.2,1);
        }

        /* ============ REDUCED MOTION ============ */
        @media (prefers-reduced-motion: reduce) {
            .trix-blob, .trix-blob-2, .trix-particle, .trix-slide-l, .trix-slide-r,
            .trix-fade-up, .trix-scale, .trix-shake, .trix-shimmer, .trix-cursor,
            .trix-progress-bar::after {
                animation: none !important;
                opacity: 1 !important;
            }
            .trix-btn:hover, .trix-feature:hover { transform: none; }
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-[#EEF2FB] via-[#F1EEFB] to-[#EAF4F8] text-[#1C2A40] antialiased selection:bg-indigo-500/20">

    <!-- Top progress bar -->
    <div id="topProgress" class="trix-progress-bar"></div>

    <main class="min-h-screen flex items-center justify-center p-5">

        <div class="trix-scale w-full max-w-6xl bg-white rounded-3xl shadow-2xl shadow-indigo-900/10 overflow-hidden grid lg:grid-cols-2 ring-1 ring-slate-900/5">

            <!-- LEFT PANEL -->
            <section class="hidden lg:flex flex-col justify-between bg-gradient-to-br from-[#0F1A2E] via-[#131F38] to-[#0B1424] text-white p-12 relative overflow-hidden">

                <!-- Blobs -->
                <div class="trix-blob absolute -right-24 -top-24 w-96 h-96 rounded-full bg-gradient-to-br from-indigo-500/30 to-violet-500/10 blur-3xl"></div>
                <div class="trix-blob-2 absolute -left-24 -bottom-24 w-96 h-96 rounded-full bg-gradient-to-tr from-cyan-500/20 to-emerald-500/10 blur-3xl"></div>

                <!-- Rings -->
                <div class="absolute -right-24 -top-24 w-80 h-80 rounded-full border-[50px] border-white/[0.03]"></div>
                <div class="absolute -left-20 -bottom-24 w-80 h-80 rounded-full border-[50px] border-white/[0.03]"></div>

                <!-- Floating particles -->
                <div class="trix-particle w-2 h-2 bg-indigo-400/60" style="top: 15%; left: 20%; animation-delay: 0s;"></div>
                <div class="trix-particle w-1.5 h-1.5 bg-cyan-400/60" style="top: 35%; left: 75%; animation-delay: 1.5s;"></div>
                <div class="trix-particle w-2.5 h-2.5 bg-emerald-400/50" style="top: 65%; left: 30%; animation-delay: 3s;"></div>
                <div class="trix-particle w-1 h-1 bg-violet-400/70" style="top: 80%; left: 60%; animation-delay: 4.5s;"></div>
                <div class="trix-particle w-1.5 h-1.5 bg-rose-400/50" style="top: 50%; left: 85%; animation-delay: 2s;"></div>

                <!-- BRAND -->
                <div class="trix-slide-l trix-d1 relative z-10">
                    <div class="relative flex h-[72px] w-[180px] items-center justify-center rounded-xl bg-white px-4 shadow-lg shadow-black/20 ring-1 ring-white/10 overflow-hidden">
                        <img src="{{ asset('images/trix-logo.jpg') }}" alt="Logo TRIX" class="relative z-10 block max-h-[52px] w-full object-contain">
                        <div class="trix-shimmer absolute inset-0 pointer-events-none"></div>
                    </div>
                </div>

                <!-- HERO -->
                <div class="relative z-10 max-w-md">
                    <span class="trix-slide-l trix-d2 inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur text-emerald-300 text-[11px] font-bold tracking-wider mb-6 ring-1 ring-white/10">
                        <span class="relative flex h-1.5 w-1.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-70"></span>
                            <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        </span>
                        HR MANAGEMENT PORTAL
                    </span>

                    <h2 class="trix-slide-l trix-d3 text-4xl font-bold leading-tight mb-5">
                        Join the team.
                        <br>
                        <span class="bg-gradient-to-r from-emerald-300 via-cyan-300 to-indigo-300 bg-clip-text text-transparent">
                            Drive the future<span class="trix-cursor" style="background: #34D399;"></span>
                        </span>
                    </h2>

                    <p class="trix-slide-l trix-d4 text-slate-300 leading-relaxed">
                        Register your HR account and start managing your fleet, drivers,
                        and company operations in one platform.
                    </p>

                    <div class="trix-slide-l trix-d5 mt-12 grid grid-cols-2 gap-4">
                        <div class="trix-feature bg-white/[0.07] border border-white/10 rounded-2xl p-5 backdrop-blur-sm">
                            <div class="trix-feature-icon flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-500 text-white mb-3 shadow-lg shadow-indigo-500/30">
                                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 6.5 12 3l9 3.5v11L12 21l-9-3.5z"/>
                                    <path d="m3 6.5 9 4 9-4M12 10.5V21"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-white">Live Fleet Monitoring</p>
                            <p class="text-[11px] text-slate-400 mt-1">Real-time tracking</p>
                        </div>

                        <div class="trix-feature bg-white/[0.07] border border-white/10 rounded-2xl p-5 backdrop-blur-sm">
                            <div class="trix-feature-icon flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500 to-emerald-500 text-white mb-3 shadow-lg shadow-cyan-500/30">
                                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="9" cy="8" r="3"/>
                                    <path d="M3 20v-1a6 6 0 0 1 12 0v1M16 5.5a3 3 0 0 1 0 5.8M18 14a5 5 0 0 1 3 4.5V20"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-white">Driver Management</p>
                            <p class="text-[11px] text-slate-400 mt-1">Team operations</p>
                        </div>
                    </div>
                </div>

                <p class="trix-slide-l trix-d6 relative z-10 text-xs text-slate-400">
                    © {{ date('Y') }} TRIX WAY · PT TRIX INDONESIA
                </p>
            </section>

            <!-- RIGHT PANEL -->
            <section class="flex items-center justify-center p-7 sm:p-12">

                <div class="w-full max-w-md">

                    <!-- MOBILE BRAND -->
                    <div class="trix-slide-r trix-d1 lg:hidden flex items-center gap-3 mb-10">
                        <div class="flex h-[56px] w-[140px] items-center justify-center rounded-xl bg-white px-3 shadow-sm ring-1 ring-slate-200">
                            <img src="{{ asset('images/trix-logo.jpg') }}" alt="Logo TRIX" class="block max-h-[40px] w-full object-contain">
                        </div>
                    </div>

                    <!-- HEADING -->
                    <div class="trix-slide-r trix-d2 mb-8">
                        <p class="inline-flex items-center gap-1.5 text-xs font-bold tracking-wider text-indigo-600 mb-3">
                            <span class="h-1 w-6 rounded-full bg-gradient-to-r from-indigo-500 to-violet-500"></span>
                            GET STARTED
                        </p>
                        <h2 class="text-3xl font-bold text-[#1C2A40]">Create HR Account</h2>
                        <p class="text-slate-600 mt-3 text-sm leading-6">
                            Register an HR account using the company's official registration code.
                        </p>
                    </div>

                    <!-- ERROR -->
                    @if ($errors->any())
                        <div class="trix-shake mb-5 flex items-start gap-3 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 8v4M12 16h.01"/>
                            </svg>
                            <ul class="list-disc pl-1 space-y-1">
                                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- FORM -->
                    <form method="POST" action="{{ route('register.process') }}" class="space-y-4" id="registerForm">
                        @csrf

                        <!-- FULL NAME -->
                        <div class="trix-slide-r trix-d3 trix-field">
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder=" "
                                required
                                maxlength="255"
                                autocomplete="name"
                                autofocus
                            >
                            <label for="name">Full Name</label>
                        </div>

                        <!-- EMPLOYEE ID + PHONE -->
                        <div class="trix-slide-r trix-d4 grid gap-4 sm:grid-cols-2">
                            <div class="trix-field">
                                <input
                                    type="text"
                                    id="employee_id"
                                    name="employee_id"
                                    value="{{ old('employee_id') }}"
                                    placeholder=" "
                                    required
                                    maxlength="100"
                                    autocomplete="username"
                                >
                                <label for="employee_id">Employee ID</label>
                            </div>
                            <div class="trix-field">
                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    value="{{ old('phone') }}"
                                    placeholder=" "
                                    maxlength="20"
                                    autocomplete="tel"
                                >
                                <label for="phone">Phone Number</label>
                            </div>
                        </div>

                        <!-- EMAIL -->
                        <div class="trix-slide-r trix-d5 trix-field">
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder=" "
                                required
                                maxlength="255"
                                autocomplete="email"
                            >
                            <label for="email">Email</label>
                        </div>

                        <!-- PASSWORD -->
                        <div class="trix-slide-r trix-d6 trix-field">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder=" "
                                required
                                minlength="8"
                                autocomplete="new-password"
                                class="has-icon"
                            >
                            <label for="password">Password</label>
                            <button type="button" onclick="togglePassword('password', 'eyeIcon1')" tabindex="-1"
                                class="absolute right-3 top-1/2 -translate-y-1/2 flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                                aria-label="Toggle password visibility">
                                <svg id="eyeIcon1" class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                            <div class="trix-strength-bar">
                                <div id="strengthFill" class="trix-strength-fill"></div>
                            </div>
                        </div>

                        <!-- CONFIRM PASSWORD -->
                        <div class="trix-slide-r trix-d7 trix-field">
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder=" "
                                required
                                minlength="8"
                                autocomplete="new-password"
                                class="has-icon"
                            >
                            <label for="password_confirmation">Confirm Password</label>
                            <button type="button" onclick="togglePassword('password_confirmation', 'eyeIcon2')" tabindex="-1"
                                class="absolute right-3 top-1/2 -translate-y-1/2 flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                                aria-label="Toggle password visibility">
                                <svg id="eyeIcon2" class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>

                        <!-- REGISTRATION CODE -->
                        <div class="trix-slide-r trix-d8 trix-field">
                            <input
                                type="password"
                                id="registration_code"
                                name="registration_code"
                                placeholder=" "
                                required
                                autocomplete="off"
                            >
                            <label for="registration_code">Company Registration Code</label>
                            <p class="mt-1.5 text-xs text-slate-500">Kode ini hanya diberikan kepada personel HR yang berwenang.</p>
                        </div>

                        <!-- SUBMIT -->
                        <div class="trix-slide-r trix-d9 pt-2">
                            <button
                                type="submit"
                                id="submitBtn"
                                class="trix-btn w-full bg-[#1C2A40] text-white font-bold py-4 rounded-xl shadow-lg shadow-slate-900/20"
                            >
                                <span class="flex items-center justify-center gap-2">
                                    <span id="submitText">Create HR Account</span>
                                    <svg id="submitArrow" class="h-4 w-4 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M5 12h14M13 6l6 6-6 6"/>
                                    </svg>
                                    <svg id="submitSpinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
                                        <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </form>

                    <p class="trix-slide-r trix-d9 text-center text-sm text-slate-600 mt-7">
                        Already have an account?
                        <a href="{{ route('login') }}" class="font-bold text-indigo-600 hover:text-indigo-800 hover:underline underline-offset-4">Back to Login</a>
                    </p>

                    <p class="trix-slide-r trix-d9 text-center text-xs text-slate-500 mt-5 leading-5">
                        Authorized personnel only.
                        <br>
                        <span class="text-slate-400">TRIX WAY · PT TRIX INDONESIA</span>
                    </p>

                </div>
            </section>

        </div>
    </main>

    <script>
        // Toggle password
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = `
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                    <path d="M2 2l20 20"/>
                `;
            } else {
                input.type = 'password';
                icon.innerHTML = `
                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
                    <circle cx="12" cy="12" r="3"/>
                `;
            }
        }

        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthFill = document.getElementById('strengthFill');
        const strengthBar = strengthFill.parentElement;

        passwordInput.addEventListener('input', function() {
            const val = this.value;
            let score = 0;
            if (val.length >= 8) score++;
            if (val.length >= 12) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            const percent = Math.min((score / 5) * 100, 100);
            const colors = ['#EF4444', '#F59E0B', '#EAB308', '#22C55E', '#059669'];
            const color = val.length === 0 ? '#E2E8F0' : colors[Math.max(0, score - 1)];

            strengthFill.style.width = percent + '%';
            strengthFill.style.background = color;

            if (val.length === 0) {
                strengthBar.style.background = '#E2E8F0';
                strengthFill.style.width = '0%';
            }
        });

        // Ripple effect on submit button
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const ripple = document.createElement('span');
            const size = Math.max(rect.width, rect.height);
            ripple.className = 'trix-ripple';
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
            ripple.style.top  = (e.clientY - rect.top  - size / 2) + 'px';
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });

        // Arrow slide hover
        submitBtn.addEventListener('mouseenter', () => {
            const arrow = document.getElementById('submitArrow');
            if (arrow) arrow.style.transform = 'translateX(4px)';
        });
        submitBtn.addEventListener('mouseleave', () => {
            const arrow = document.getElementById('submitArrow');
            if (arrow) arrow.style.transform = 'translateX(0)';
        });

        // Loading state + top progress
        document.getElementById('registerForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            const text = document.getElementById('submitText');
            const arrow = document.getElementById('submitArrow');
            const spinner = document.getElementById('submitSpinner');
            const progress = document.getElementById('topProgress');

            text.textContent = 'Creating account...';
            arrow.classList.add('hidden');
            spinner.classList.remove('hidden');
            btn.disabled = true;
            btn.style.cursor = 'wait';

            progress.classList.add('active');
        });
    </script>

</body>
</html>