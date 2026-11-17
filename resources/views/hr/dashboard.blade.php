<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRIX WAY | HR Operations</title>
    <link rel="icon" type="image/png" href="{{ asset('images/trix-logo.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gradient-to-br from-[#EEF2FB] via-[#F1EEFB] to-[#EAF4F8] text-[#1C2A40] antialiased selection:bg-indigo-500/20">
<div id="hrNotificationToastContainer" class="fixed right-5 top-5 z-[300] flex w-[min(360px,calc(100vw-2.5rem))] flex-col gap-3"></div>

@php
    $fleetTotal = $summary['vehicles']['total'] ?? 0;
    $available = $summary['vehicles']['available'] ?? 0;
    $inUse = $summary['vehicles']['in_use'] ?? 0;
    $maintenance = $summary['vehicles']['maintenance'] ?? 0;

    $availablePercent = $fleetTotal > 0 ? ($available / $fleetTotal) * 100 : 0;
    $inUsePercent = $fleetTotal > 0 ? ($inUse / $fleetTotal) * 100 : 0;
    $maintenancePercent = $fleetTotal > 0 ? ($maintenance / $fleetTotal) * 100 : 0;

    // Palet multi-aksen
    $cIndigo = '#6366F1';
    $cCyan   = '#0891B2';
    $cAmber  = '#EA580C';
    $cGreen  = '#059669';
    $cRose   = '#E11D48';
    $cInk    = '#1C2A40';
@endphp

<style>
    /* ============ SCROLLBAR ============ */
    .trix-scroll::-webkit-scrollbar { width: 6px; }
    .trix-scroll::-webkit-scrollbar-track { background: transparent; }
    .trix-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 999px; }
    .trix-scroll::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.24); }

    /* ============ CARD SHADOW ============ */
    .trix-card {
        box-shadow:
            0 1px 2px rgba(20,35,60,0.06),
            0 6px 16px rgba(20,35,60,0.05),
            0 16px 40px -16px rgba(20,35,60,0.10);
        transition: box-shadow .3s cubic-bezier(.4,0,.2,1),
                    transform .3s cubic-bezier(.4,0,.2,1),
                    border-color .3s ease;
    }
    .trix-card-hover:hover {
        box-shadow:
            0 2px 4px rgba(20,35,60,0.08),
            0 12px 28px rgba(20,35,60,0.08),
            0 28px 56px -20px rgba(20,35,60,0.16);
        transform: translateY(-3px);
        border-color: #C4CFE4;
    }

    /* ============ ANGKA TABULAR ============ */
    .trix-num { font-variant-numeric: tabular-nums; letter-spacing: -0.025em; }

    /* ============ ANIMASI MASUK ============ */
    @keyframes trixFadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .trix-anim { opacity: 0; animation: trixFadeUp .55s cubic-bezier(.4,0,.2,1) forwards; }
    .trix-d1 { animation-delay: .05s; }
    .trix-d2 { animation-delay: .12s; }
    .trix-d3 { animation-delay: .19s; }
    .trix-d4 { animation-delay: .26s; }
    .trix-d5 { animation-delay: .33s; }
    .trix-d6 { animation-delay: .40s; }

    /* ============ PROGRESS BAR ============ */
    @keyframes trixFillBar { from { width: 0; } }
    .trix-bar-fill { animation: trixFillBar 1.1s cubic-bezier(.4,0,.2,1) forwards; }

    /* ============ DONUT RING ============ */
    @keyframes trixRingIn {
        from { stroke-dashoffset: 260; opacity: 0; }
        to   { opacity: 1; }
    }
    .trix-ring-seg { animation: trixRingIn 1.2s cubic-bezier(.4,0,.2,1) forwards; }

    /* ============ PULSE MARKER ============ */
    @keyframes trixPulse {
        0%   { transform: scale(0.85); opacity: .55; }
        70%  { transform: scale(2.2);  opacity: 0; }
        100% { transform: scale(2.2);  opacity: 0; }
    }
    .trix-pulse::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 9999px;
        background: currentColor;
        animation: trixPulse 2.2s ease-out infinite;
    }

    /* ============ LIVE DOT ============ */
    @keyframes trixLive {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%      { opacity: .4; transform: scale(.9); }
    }
    .trix-live-dot { animation: trixLive 1.6s ease-in-out infinite; }

    /* ============ GRADIENT TEXT ============ */
    .trix-gradient-text {
        background: linear-gradient(135deg, #6366F1 0%, #E11D48 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* ============ REDUCED MOTION ============ */
    @media (prefers-reduced-motion: reduce) {
        .trix-anim, .trix-bar-fill, .trix-ring-seg, .trix-pulse::after, .trix-live-dot {
            animation: none !important;
            opacity: 1 !important;
        }
    }
</style>

<div class="min-h-screen">

    <!-- SIDEBAR -->
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-[258px] flex-col bg-gradient-to-b from-[#0F1A2E] via-[#131F38] to-[#0B1424] text-white lg:flex">

        <!-- BRAND: LOGO TRIX ASLI DI PANEL PUTIH -->
        <div class="flex h-[104px] items-center justify-center border-b border-white/10 px-5">
            <div class="flex h-[68px] w-full items-center justify-center rounded-xl bg-white px-4 shadow-md shadow-black/20 ring-1 ring-white/10">
                <img
                    src="{{ asset('images/trix-logo.jpg') }}"
                    alt="Logo TRIX"
                    class="block max-h-[48px] w-full object-contain"
                >
            </div>
        </div>

        <!-- NAVIGATION -->
        <div class="trix-scroll flex-1 overflow-y-auto px-4 py-8">

            <p class="mb-4 px-4 text-[10px] font-bold tracking-[2px] text-slate-400">
                WORKSPACE
            </p>

            <nav class="space-y-1.5">

                <a href="{{ route('hr.dashboard') }}"
                   class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-950/50 ring-1 ring-white/15">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('hr.live-monitoring') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition-colors duration-200 hover:bg-white/10 hover:text-white">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M3 6.5 12 3l9 3.5v11L12 21l-9-3.5z"/>
                        <path d="m3 6.5 9 4 9-4M12 10.5V21"/>
                    </svg>
                    Live Monitoring
                </a>

                <a href="{{ route('hr.drivers.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition-colors duration-200 hover:bg-white/10 hover:text-white">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="9" cy="8" r="3"/>
                        <path d="M3 20v-1a6 6 0 0 1 12 0v1M16 5.5a3 3 0 0 1 0 5.8M18 14a5 5 0 0 1 3 4.5V20"/>
                    </svg>
                    Driver Management
                </a>

               <a href="{{ route('hr.vehicles.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition-colors duration-200 hover:bg-white/10 hover:text-white">

                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M3 13h18l-1.5-5h-15z"/>
                        <path d="M5 13v5h14v-5M7 18v2m10-2v2M7 8l1-3h8l1 3"/>
                        <circle cx="7" cy="15.5" r=".8" fill="currentColor"/>
                        <circle cx="17" cy="15.5" r=".8" fill="currentColor"/>
                    </svg>
                    Vehicle Management
                </a>

                <a href="{{ route('hr.trips.index') }}"class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition-colors duration-200 hover:bg-white/10 hover:text-white">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 7h15l-3-3m4 13H5l3 3"/>
                        <path d="M19 7v4M5 17v-4"/>
                    </svg>
                    Trip Management
                </a>

                <a href="{{ route('hr.trip-history') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition-colors duration-200 hover:bg-white/10 hover:text-white">

                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 7v5l3 2"/>
                    </svg>
                    Trip History
                </a>

            </nav>

            <p class="mb-4 mt-9 px-4 text-[10px] font-bold tracking-[2px] text-slate-400">
                PREFERENCES
            </p>

            <nav>
                <a href="{{ route('hr.notifications') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition-colors duration-200 hover:bg-white/10 hover:text-white">
                    <span class="relative">
                        <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                            <path d="M10 21h4"/>
                        </svg>
                        <span id="dashboardNotificationBadge" class="absolute -right-3 -top-2 hidden min-w-[18px] rounded-full bg-rose-500 px-1 text-center text-[10px] font-bold leading-[18px] text-white ring-2 ring-[#0F1A2E]">0</span>
                    </span>
                    Notifications
                </a>
            </nav>
        </div>

        <!-- SIDEBAR FOOTER -->
        <div class="border-t border-white/10 p-4">
            <div class="mb-4 flex items-center gap-3 rounded-xl bg-white/[0.07] p-3 ring-1 ring-white/10">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-sm font-bold text-white ring-1 ring-white/20">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="truncate text-xs font-semibold text-white">{{ auth()->user()->name }}</p>
                    <p class="mt-1 text-[10px] text-slate-400">HR Administrator</p>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-400 transition-colors duration-200 hover:bg-rose-500/15 hover:text-rose-300">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M10 17l5-5-5-5m5 5H3"/>
                        <path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7"/>
                    </svg>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>


    <!-- MOBILE NAVIGATION DRAWER -->
    <div id="hrMobileBackdrop" class="fixed inset-0 z-40 hidden bg-slate-950/55 opacity-0 transition-opacity duration-200 lg:hidden" aria-hidden="true"></div>

    <aside
        id="hrMobileDrawer"
        class="fixed inset-y-0 left-0 z-50 flex w-[min(84vw,300px)] -translate-x-full flex-col bg-gradient-to-b from-[#0F1A2E] via-[#131F38] to-[#0B1424] text-white shadow-2xl transition-transform duration-300 ease-out lg:hidden"
        aria-label="Navigasi HR"
        aria-hidden="true"
    >
        <div class="flex h-[88px] items-center justify-between border-b border-white/10 px-5">
            <div class="flex h-[58px] flex-1 items-center justify-center rounded-xl bg-white px-4">
                <img src="{{ asset('images/trix-logo.jpg') }}" alt="Logo TRIX" class="block max-h-[44px] w-full object-contain">
            </div>
            <button type="button" id="hrMobileCloseButton" aria-label="Tutup menu" class="ml-3 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-300 hover:bg-white/10 hover:text-white">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg>
            </button>
        </div>

        <div class="trix-scroll flex-1 overflow-y-auto px-4 py-6">
            <p class="mb-4 px-4 text-[10px] font-bold tracking-[2px] text-slate-400">WORKSPACE</p>
            <nav class="space-y-1.5">
                <a href="{{ route('hr.dashboard') }}" class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-3 text-sm font-semibold text-white shadow-lg">
                    <span>▦</span> Dashboard
                </a>
                <a href="{{ route('hr.live-monitoring') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white"><span>⌖</span> Live Monitoring</a>
                <a href="{{ route('hr.drivers.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white"><span>♙</span> Driver Management</a>
                <a href="{{ route('hr.vehicles.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white"><span>▰</span> Vehicle Management</a>
                <a href="{{ route('hr.trips.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white"><span>⇄</span> Trip Management</a>
                <a href="{{ route('hr.trip-history') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white"><span>◷</span> Trip History</a>
            </nav>

            <p class="mb-4 mt-8 px-4 text-[10px] font-bold tracking-[2px] text-slate-400">PREFERENCES</p>
            <a href="{{ route('hr.notifications') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white"><span>♧</span> Notifications</a>
        </div>

        <div class="border-t border-white/10 p-4">
            <div class="mb-3 flex items-center gap-3 rounded-xl bg-white/[0.07] p-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-sm font-bold text-white">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="truncate text-xs font-semibold text-white">{{ auth()->user()->name }}</p>
                    <p class="mt-1 text-[10px] text-slate-400">HR Administrator</p>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 hover:bg-rose-500/15 hover:text-rose-300">⇥ Sign Out</button>
            </form>
        </div>
    </aside>

    <!-- MAIN AREA -->
    <main class="min-h-screen lg:ml-[258px]">

        <!-- TOPBAR -->
        <header class="sticky top-0 z-20 flex h-[68px] items-center justify-between border-b border-[#D6DEEA] bg-white/95 px-3 backdrop-blur-md sm:h-[76px] sm:px-8 lg:px-10">
            <div class="flex min-w-0 items-center gap-3">
                <button
                    type="button"
                    id="hrMobileMenuButton"
                    aria-label="Buka menu navigasi"
                    aria-controls="hrMobileDrawer"
                    aria-expanded="false"
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-indigo-100 bg-indigo-50 text-indigo-700 transition hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-400 lg:hidden"
                >
                    <svg id="hrMenuIcon" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg id="hrCloseIcon" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m6 6 12 12M18 6 6 18"/>
                    </svg>
                </button>
                <div class="min-w-0">
                <p class="truncate text-[9px] font-bold tracking-[1.4px] bg-gradient-to-r from-indigo-600 to-cyan-600 bg-clip-text text-transparent sm:text-[10px] sm:tracking-[2px]">
                    TRIX WAY / WORKSPACE
                </p>
                <h2 class="mt-1 truncate text-xs font-bold text-[#1C2A40] sm:text-sm">
                    Operations Overview
                </h2>
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-2 sm:gap-5">
                <div class="hidden text-right sm:block">
                    <p class="text-xs font-bold text-[#1C2A40]">{{ auth()->user()->name }}</p>
                    <p class="mt-1 text-[10px] font-medium text-slate-500">Human Resources</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-sm font-bold text-white ring-2 ring-white shadow-md shadow-indigo-500/30">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            </div>
        </header>


        <!-- PAGE CONTENT -->
        <div class="mx-auto max-w-[1600px] space-y-5 p-3 sm:space-y-7 sm:p-8 lg:p-10">

            <!-- PAGE INTRO -->
            <section class="trix-anim trix-d1 flex flex-col justify-between gap-5 md:flex-row md:items-end">
                <div>
                    <p class="text-xs font-bold tracking-wide text-indigo-600">
                        {{ strtoupper(now()->format('l, d F Y')) }}
                    </p>

                    <h1 class="mt-2 text-2xl font-bold tracking-tight text-[#1C2A40] sm:mt-3 sm:text-[34px]">
                        Fleet <span class="trix-gradient-text">Overview</span>
                    </h1>

                    <p class="mt-2 max-w-xl text-xs leading-5 text-slate-600 sm:text-sm sm:leading-6">
                        Monitor fleet performance, driver activity, and ongoing transportation operations.
                    </p>
                </div>

                <div class="flex items-center gap-2 self-start rounded-full border border-indigo-200 bg-white px-4 py-2.5 shadow-sm md:self-auto">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-indigo-500 opacity-60"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-indigo-500"></span>
                    </span>
                    <span class="text-[11px] font-bold tracking-wide text-indigo-600">
                        OPERATIONS CENTER
                    </span>
                </div>
            </section>


            <!-- KPI CARDS — 4 WARNA BERBEDA -->
            <section class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">

                <!-- DRIVERS — INDIGO -->
                <div class="trix-anim trix-d2 trix-card trix-card-hover rounded-2xl border border-indigo-100 bg-white p-3 sm:p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-600">Total Drivers</p>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-600 ring-1 ring-indigo-200">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="9" cy="8" r="3"/>
                                <path d="M3 20v-1a6 6 0 0 1 12 0v1m2-14a3 3 0 0 1 0 6m1 3a5 5 0 0 1 3 5"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-5 flex items-end justify-between">
                        <h3 class="trix-num text-[26px] font-bold text-indigo-600 sm:text-[32px]">{{ $summary['drivers']['total'] ?? 0 }}</h3>
                        <span class="max-w-[90px] pb-1 text-right text-[9px] font-medium leading-3 text-slate-500 sm:max-w-none sm:text-[10px]">Registered personnel</span>
                    </div>
                </div>

                <!-- VEHICLES — CYAN -->
                <div class="trix-anim trix-d3 trix-card trix-card-hover rounded-2xl border border-cyan-100 bg-white p-3 sm:p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-600">Total Vehicles</p>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-50 to-cyan-100 text-cyan-600 ring-1 ring-cyan-200">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M3 13h18l-1.5-5h-15z"/>
                                <path d="M5 13v5h14v-5M7 18v2m10-2v2M7 8l1-3h8l1 3"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-5 flex items-end justify-between">
                        <h3 class="trix-num text-[26px] font-bold text-cyan-600 sm:text-[32px]">{{ $summary['vehicles']['total'] ?? 0 }}</h3>
                        <span class="max-w-[90px] pb-1 text-right text-[9px] font-medium leading-3 text-slate-500 sm:max-w-none sm:text-[10px]">Registered fleet</span>
                    </div>
                </div>

                <!-- ACTIVE TRIPS — AMBER/ORANGE -->
                <div class="trix-anim trix-d4 trix-card trix-card-hover rounded-2xl border border-orange-100 bg-white p-3 sm:p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-600">Active Trips</p>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-orange-50 to-amber-100 text-orange-600 ring-1 ring-orange-200">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M4 7h15l-3-3m4 13H5l3 3"/>
                                <path d="M19 7v4M5 17v-4"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-5 flex items-end justify-between">
                        <h3 class="trix-num text-[26px] font-bold text-orange-600 sm:text-[32px]">{{ $summary['trips']['active'] ?? 0 }}</h3>
                        <span class="max-w-[90px] pb-1 text-right text-[9px] font-medium leading-3 text-slate-500 sm:max-w-none sm:text-[10px]">Currently on the road</span>
                    </div>
                </div>

                <!-- AVAILABLE — EMERALD -->
                <div class="trix-anim trix-d5 trix-card trix-card-hover rounded-2xl border border-emerald-100 bg-white p-3 sm:p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-600">Available Vehicles</p>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-600 ring-1 ring-emerald-200">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="m5 12 4 4L19 6"/>
                                <circle cx="12" cy="12" r="9"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-5 flex items-end justify-between">
                        <h3 class="trix-num text-[26px] font-bold text-emerald-600 sm:text-[32px]">{{ $summary['vehicles']['available'] ?? 0 }}</h3>
                        <span class="max-w-[90px] pb-1 text-right text-[9px] font-medium leading-3 text-slate-500 sm:max-w-none sm:text-[10px]">Ready for assignment</span>
                    </div>
                </div>

            </section>


            <!-- MONITORING + ACTIVITY -->
            <section class="grid grid-cols-1 gap-5 xl:grid-cols-12">

                <!-- MAP PANEL -->
                <div class="trix-anim trix-d6 trix-card overflow-hidden rounded-2xl border border-[#D6DEEA] bg-white xl:col-span-8">

                    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[#E2E7EF] px-4 py-4 sm:px-6 sm:py-5">
                        <div>
                            <h3 class="text-sm font-bold text-[#1C2A40]">
                                Live Fleet Monitoring
                            </h3>
                            <p class="mt-1 text-[11px] text-slate-500">
                                Real-time vehicle location and fleet movement
                            </p>
                        </div>

                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-[10px] font-bold tracking-wide text-rose-600">
                            <span class="trix-live-dot relative flex h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                            LIVE VIEW
                        </span>
                    </div>

                    <!-- MAP PLACEHOLDER — gradient lebih hidup -->
                    <div class="relative flex h-[230px] items-center justify-center overflow-hidden bg-gradient-to-br sm:h-[300px] xl:h-[340px] from-[#E0E9F5] via-[#E8E5F4] to-[#DFEEF2]">

                        <svg class="absolute inset-0 h-full w-full" viewBox="0 0 900 400" preserveAspectRatio="xMidYMid slice">
                            <defs>
                                <pattern id="mapgrid" width="46" height="46" patternUnits="userSpaceOnUse">
                                    <path d="M 46 0 L 0 0 0 46" fill="none" stroke="#C9D4E3" stroke-width="1"/>
                                </pattern>
                                <radialGradient id="mapglow" cx="50%" cy="50%" r="55%">
                                    <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.6"/>
                                    <stop offset="100%" stop-color="#DFEEF2" stop-opacity="0"/>
                                </radialGradient>
                            </defs>

                            <rect width="900" height="400" fill="url(#mapgrid)"/>
                            <rect width="900" height="400" fill="url(#mapglow)"/>

                            <path d="M-20 280 C130 210 180 300 300 210 S480 140 580 210 S760 280 940 130"
                                  fill="none" stroke="#FFFFFF" stroke-width="30"/>
                            <path d="M-20 280 C130 210 180 300 300 210 S480 140 580 210 S760 280 940 130"
                                  fill="none" stroke="#C4CFE0" stroke-width="2"/>

                            <path d="M160 -20 C220 100 190 160 300 240 S430 320 450 430"
                                  fill="none" stroke="#FFFFFF" stroke-width="24"/>
                            <path d="M160 -20 C220 100 190 160 300 240 S430 320 450 430"
                                  fill="none" stroke="#C4CFE0" stroke-width="2"/>

                            <path d="M580 -20 C530 80 620 120 590 220 S680 310 720 430"
                                  fill="none" stroke="#FFFFFF" stroke-width="24"/>
                            <path d="M580 -20 C530 80 620 120 590 220 S680 310 720 430"
                                  fill="none" stroke="#C4CFE0" stroke-width="2"/>
                        </svg>

                        <!-- Pulse markers — 4 warna -->
                        <div class="absolute left-[28%] top-[38%] text-cyan-600">
                            <span class="trix-pulse relative inline-flex h-3 w-3 rounded-full bg-cyan-500 ring-4 ring-white/80"></span>
                        </div>
                        <div class="absolute left-[64%] top-[58%] text-emerald-600">
                            <span class="trix-pulse relative inline-flex h-3 w-3 rounded-full bg-emerald-500 ring-4 ring-white/80" style="animation-delay:.5s"></span>
                        </div>
                        <div class="absolute left-[48%] top-[70%] text-orange-500">
                            <span class="trix-pulse relative inline-flex h-3 w-3 rounded-full bg-orange-500 ring-4 ring-white/80" style="animation-delay:1s"></span>
                        </div>
                        <div class="absolute left-[75%] top-[30%] text-indigo-500">
                            <span class="trix-pulse relative inline-flex h-3 w-3 rounded-full bg-indigo-500 ring-4 ring-white/80" style="animation-delay:1.5s"></span>
                        </div>

                        <div class="relative z-10 max-w-sm px-6 text-center">
                            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl border border-indigo-200 bg-white text-indigo-500 shadow-md shadow-indigo-500/10">
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/>
                                    <circle cx="12" cy="10" r="2.5"/>
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-[#1C2A40]">Fleet Location Map</p>
                            <p class="mt-2 text-xs leading-5 text-slate-600">
                                Vehicle positions will appear here when tracking data is available.
                            </p>
                        </div>

                        <div class="absolute bottom-4 left-5 rounded-lg border border-white bg-white/95 px-3 py-2 text-[10px] font-semibold tracking-wide text-slate-600 shadow-sm backdrop-blur">
                            MAP DATA · PENDING INTEGRATION
                        </div>
                    </div>

                    <!-- Legend — 4 warna -->
                    <div class="flex flex-wrap items-center gap-6 border-t border-[#E2E7EF] px-4 py-3 sm:px-6 sm:py-4">
                        <div class="flex items-center gap-2 text-[10px] font-medium text-slate-600">
                            <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                            On Trip
                        </div>
                        <div class="flex items-center gap-2 text-[10px] font-medium text-slate-600">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Available
                        </div>
                        <div class="flex items-center gap-2 text-[10px] font-medium text-slate-600">
                            <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                            Maintenance
                        </div>
                        <div class="flex items-center gap-2 text-[10px] font-medium text-slate-600">
                            <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                            Idle
                        </div>
                    </div>
                </div>


                <!-- OPERATION STATUS -->
                <div class="trix-anim trix-d6 trix-card rounded-2xl border border-[#D6DEEA] bg-white p-6 xl:col-span-4">

                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-[#1C2A40]">Fleet Status</h3>
                            <p class="mt-1 text-[11px] text-slate-500">Vehicle availability overview</p>
                        </div>
                        <span class="rounded-md bg-gradient-to-r from-indigo-50 to-cyan-50 px-2 py-1 text-[10px] font-bold tracking-wide text-indigo-600">TODAY</span>
                    </div>

                    <!-- Donut ring -->
                    <div class="mt-8 flex items-center justify-center">
                        @php
                            $ring = 40;
                            $circ = 2 * M_PI * $ring;
                            $segAvailable = ($availablePercent / 100) * $circ;
                            $segInUse = ($inUsePercent / 100) * $circ;
                            $segMaint = ($maintenancePercent / 100) * $circ;
                        @endphp
                        <div class="relative flex h-40 w-40 items-center justify-center">
                            <svg class="absolute inset-0 h-full w-full -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="{{ $ring }}" fill="none" stroke="#E5EAF3" stroke-width="13"/>
                                @if($fleetTotal > 0)
                                    <circle cx="50" cy="50" r="{{ $ring }}" fill="none" stroke="#059669" stroke-width="13"
                                            stroke-dasharray="{{ $segAvailable }} {{ $circ }}"
                                            class="trix-ring-seg"/>
                                    <circle cx="50" cy="50" r="{{ $ring }}" fill="none" stroke="#0891B2" stroke-width="13"
                                            stroke-dasharray="{{ $segInUse }} {{ $circ }}"
                                            stroke-dashoffset="{{ -$segAvailable }}"
                                            class="trix-ring-seg" style="animation-delay:.15s"/>
                                    <circle cx="50" cy="50" r="{{ $ring }}" fill="none" stroke="#EA580C" stroke-width="13"
                                            stroke-dasharray="{{ $segMaint }} {{ $circ }}"
                                            stroke-dashoffset="{{ -($segAvailable + $segInUse) }}"
                                            class="trix-ring-seg" style="animation-delay:.3s"/>
                                @endif
                            </svg>
                            <div class="relative text-center">
                                <p class="trix-num text-3xl font-bold text-[#1C2A40]">{{ $fleetTotal }}</p>
                                <p class="mt-1 text-[10px] font-medium text-slate-500">Total Fleet</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 space-y-5">

                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <span class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Available
                                </span>
                                <span class="trix-num text-xs font-bold text-emerald-600">{{ $available }} ({{ number_format($availablePercent, 0) }}%)</span>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-[#E5EAF3]">
                                <div class="trix-bar-fill h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-600" style="width: {{ $availablePercent }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <span class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                    <span class="h-1.5 w-1.5 rounded-full bg-cyan-500"></span>
                                    On Trip
                                </span>
                                <span class="trix-num text-xs font-bold text-cyan-600">{{ $inUse }} ({{ number_format($inUsePercent, 0) }}%)</span>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-[#E5EAF3]">
                                <div class="trix-bar-fill h-full rounded-full bg-gradient-to-r from-cyan-400 to-cyan-600" style="width: {{ $inUsePercent }}%; animation-delay:.15s"></div>
                            </div>
                        </div>

                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <span class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                    <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>
                                    Maintenance
                                </span>
                                <span class="trix-num text-xs font-bold text-orange-600">{{ $maintenance }} ({{ number_format($maintenancePercent, 0) }}%)</span>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-[#E5EAF3]">
                                <div class="trix-bar-fill h-full rounded-full bg-gradient-to-r from-orange-400 to-orange-600" style="width: {{ $maintenancePercent }}%; animation-delay:.3s"></div>
                            </div>
                        </div>

                    </div>
                </div>

            </section>


            <!-- RECENT TRIPS -->
            <section class="trix-anim trix-d6 trix-card overflow-hidden rounded-2xl border border-[#D6DEEA] bg-white">

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#E2E7EF] px-6 py-5">
                    <div>
                        <h3 class="text-sm font-bold text-[#1C2A40]">Recent Trips</h3>
                        <p class="mt-1 text-[11px] text-slate-500">
                            Latest fleet movement records
                        </p>
                    </div>

                    <a href="#" class="text-xs font-bold text-indigo-600 transition hover:text-indigo-800 hover:underline underline-offset-4">
                        View all trips →
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[650px] text-left">
                        <thead class="bg-gradient-to-r from-indigo-50/60 via-white to-cyan-50/60">
                            <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                <th class="px-6 py-4">Driver</th>
                                <th class="px-6 py-4">Vehicle</th>
                                <th class="px-6 py-4">Destination</th>
                                <th class="px-6 py-4">Departure</th>
                                <th class="px-6 py-4">Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td colspan="5" class="px-6 py-14 text-center">
                                    <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-gradient-to-br from-indigo-50 to-cyan-50 text-indigo-500 ring-1 ring-indigo-100">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                            <path d="M4 7h16v13H4zM8 7V4h8v3"/>
                                            <path d="M8 12h8m-8 4h5"/>
                                        </svg>
                                    </div>
                                    <p class="mt-3 text-xs font-semibold text-slate-600">
                                        No trip records available
                                    </p>
                                    <p class="mt-1 text-[10px] font-medium text-slate-500">
                                        Trip activity will appear here once recorded.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </section>


            <!-- FOOTER -->
            <footer class="flex flex-col justify-between gap-2 border-t border-[#D6DEEA] pt-5 text-[10px] font-medium tracking-wide text-slate-500 sm:flex-row">
                <p>© {{ date('Y') }} TRIX WAY · PT TRIX INDONESIA</p>
                <p>FLEET OPERATIONS MANAGEMENT SYSTEM</p>
            </footer>

        </div>
    </main>
</div>


<script>
(function () {
    let firstNotificationCheck = true;
    let knownNotificationIds = new Set();
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function showHrNotificationPopup(item) {
        const container = document.getElementById('hrNotificationToastContainer');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = 'flex items-start gap-3 rounded-xl border border-indigo-200 bg-white p-4 shadow-2xl';
        const icon = document.createElement('div');
        icon.className = 'flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600';
        icon.innerHTML = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>';
        const content = document.createElement('div');
        content.className = 'min-w-0 flex-1';
        const title = document.createElement('p');
        title.className = 'text-sm font-bold text-slate-800';
        title.textContent = item.title || 'Notifikasi Baru';
        const message = document.createElement('p');
        message.className = 'mt-1 text-xs leading-relaxed text-slate-600';
        message.textContent = item.message || 'Ada notifikasi baru untuk Anda.';
        content.append(title, message);
        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'text-lg leading-none text-slate-400 hover:text-slate-700';
        close.textContent = '×';
        close.addEventListener('click', () => toast.remove());
        toast.append(icon, content, close);
        container.appendChild(toast);
        window.setTimeout(() => toast.remove(), 6500);
    }

    async function checkHrNotifications() {
        try {
            const response = await fetch("{{ route('hr.notifications.data') }}", {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) return;
            const data = await response.json();
            const items = (data.notifications || data.data || []).map(item => ({
                ...item,
                is_read: item.is_read ?? (item.read_at !== null)
            }));
            const unread = items.filter(item => !item.is_read).length;
            const badge = document.getElementById('dashboardNotificationBadge');
            if (badge) {
                badge.textContent = unread > 99 ? '99+' : String(unread);
                badge.classList.toggle('hidden', unread === 0);
            }

            if (firstNotificationCheck) {
                if (unread > 0) showHrNotificationPopup({
                    title: 'Ada notifikasi belum dibaca',
                    message: `Anda memiliki ${unread} notifikasi yang belum dibaca.`
                });
                firstNotificationCheck = false;
            } else {
                items.filter(item => !item.is_read && !knownNotificationIds.has(String(item.id)))
                    .reverse().forEach(showHrNotificationPopup);
            }
            knownNotificationIds = new Set(items.map(item => String(item.id)));
        } catch (error) {
            console.error('Gagal memeriksa notifikasi HR:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        checkHrNotifications();
        window.setInterval(checkHrNotifications, 5000);
    });
})();

document.addEventListener('DOMContentLoaded', function () {
    const drawer = document.getElementById('hrMobileDrawer');
    const backdrop = document.getElementById('hrMobileBackdrop');
    const openButton = document.getElementById('hrMobileMenuButton');
    const closeButton = document.getElementById('hrMobileCloseButton');
    const menuIcon = document.getElementById('hrMenuIcon');
    const closeIcon = document.getElementById('hrCloseIcon');

    if (!drawer || !backdrop || !openButton || !closeButton) return;

    function openDrawer() {
        drawer.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
        requestAnimationFrame(() => backdrop.classList.remove('opacity-0'));
        drawer.setAttribute('aria-hidden', 'false');
        backdrop.setAttribute('aria-hidden', 'false');
        openButton.setAttribute('aria-expanded', 'true');
        menuIcon?.classList.add('hidden');
        closeIcon?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeDrawer() {
        drawer.classList.add('-translate-x-full');
        backdrop.classList.add('opacity-0');
        drawer.setAttribute('aria-hidden', 'true');
        backdrop.setAttribute('aria-hidden', 'true');
        openButton.setAttribute('aria-expanded', 'false');
        menuIcon?.classList.remove('hidden');
        closeIcon?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        window.setTimeout(() => backdrop.classList.add('hidden'), 200);
    }

    openButton.addEventListener('click', openDrawer);
    closeButton.addEventListener('click', closeDrawer);
    backdrop.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !drawer.classList.contains('-translate-x-full')) {
            closeDrawer();
        }
    });
    drawer.querySelectorAll('a').forEach(link => link.addEventListener('click', closeDrawer));
});
</script>
\n</body>
</html>