<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Trip Management | TRIX WAY</title>

    <link rel="icon" type="image/png" href="{{ asset('images/trix-logo.jpg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>

        /* ============ SCROLLBAR ============ */

        .trix-scroll::-webkit-scrollbar { width: 6px; }

        .trix-scroll::-webkit-scrollbar-track { background: transparent; }

        .trix-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 999px; }

        .trix-scroll::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.24); }

        /* ============ CARD ============ */

        .trix-card {

            box-shadow: 0 1px 2px rgba(20,35,60,0.06), 0 6px 16px rgba(20,35,60,0.05), 0 16px 40px -16px rgba(20,35,60,0.10);

            transition: box-shadow .3s cubic-bezier(.4,0,.2,1), transform .3s cubic-bezier(.4,0,.2,1), border-color .3s ease;

        }

        .trix-card-hover:hover {

            box-shadow: 0 2px 4px rgba(20,35,60,0.08), 0 12px 28px rgba(20,35,60,0.08), 0 28px 56px -20px rgba(20,35,60,0.16);

            transform: translateY(-3px);

            border-color: #C4CFE4;

        }

        /* ============ ANGKA TABULAR ============ */

        .trix-num { font-variant-numeric: tabular-nums; letter-spacing: -0.03em; }

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

        /* ============ LIVE DOT ============ */

        @keyframes trixLive {

            0%, 100% { opacity: 1; transform: scale(1); }

            50%      { opacity: .4; transform: scale(.9); }

        }

        .trix-live-dot { animation: trixLive 1.6s ease-in-out infinite; }

        /* ============ GRADIENT TEXT ============ */

        .trix-gradient-text {

            background: linear-gradient(135deg, #6366F1 0%, #0891B2 100%);

            -webkit-background-clip: text;

            background-clip: text;

            -webkit-text-fill-color: transparent;

        }

        /* ============ FORM INPUT ============ */

        .trix-input {

            width: 100%; height: 44px; border: 1.5px solid #D6DEEA; border-radius: 11px;

            padding: 0 12px; font-size: 13px; background: #F8FAFC; color: #1C2A40;

            outline: none; transition: all .2s cubic-bezier(.4,0,.2,1);

        }

        .trix-input:hover { border-color: #B9C8DE; background: #FFFFFF; }

        .trix-input:focus { border-color: #6366F1; background: #FFFFFF; box-shadow: 0 0 0 4px rgba(99,102,241,0.10); }

        textarea.trix-input { height: auto; padding: 10px 12px; }

        /* FIX SEARCH BAR (Padding untuk ikon) */

        #searchTrip { padding-left: 2.5rem !important; }

        /* ============ MODAL ============ */

        .trix-modal {

            display: none; position: fixed; inset: 0; z-index: 100;

            background: rgba(15,23,42,.55); backdrop-filter: blur(6px);

            align-items: center; justify-content: center; padding: 20px;

        }

        .trix-modal.show { display: flex; }

        .trix-modal-content {

            width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto;

            animation: trixFadeUp .3s cubic-bezier(.4,0,.2,1);

        }

        /* ============ TABLE ROW ============ */

        .trix-row { transition: background .2s ease; }

        .trix-row:hover { background: #FAFBFD; }

        /* ============ ACTION BTN ============ */

        .trix-action { transition: all .2s cubic-bezier(.4,0,.2,1); }

        .trix-action:hover { transform: translateY(-1px) scale(1.05); box-shadow: 0 4px 10px rgba(20,35,60,0.10); }

        /* ============ TOAST ANIMATION ============ */

        @keyframes trixSlideInRight { from { opacity: 0; transform: translateX(100%); } to { opacity: 1; transform: translateX(0); } }

        @keyframes trixFadeOut { from { opacity: 1; } to { opacity: 0; } }

        .trix-toast { animation: trixSlideInRight .3s cubic-bezier(.4,0,.2,1) forwards; }

        .trix-toast.hiding { animation: trixFadeOut .3s ease forwards; }

        /* ============ REDUCED MOTION ============ */

        @media (prefers-reduced-motion: reduce) {

            .trix-anim, .trix-live-dot, .trix-toast { animation: none !important; opacity: 1 !important; }

            .trix-card, .trix-card-hover:hover, .trix-action:hover { transition: none; transform: none; }

        }

    </style>

</head>

<body class="min-h-screen bg-gradient-to-br from-[#EEF2FB] via-[#F1EEFB] to-[#EAF4F8] text-[#1C2A40] antialiased selection:bg-indigo-500/20">

<!-- TOAST CONTAINER -->

<div id="toastContainer" class="fixed top-6 right-6 z-[200] flex flex-col gap-3 pointer-events-none"></div>

<div class="min-h-screen">

    <!-- ============ SIDEBAR ============ -->

    <aside class="fixed inset-y-0 left-0 z-30 hidden w-[258px] flex-col bg-gradient-to-b from-[#0F1A2E] via-[#131F38] to-[#0B1424] text-white lg:flex">

        <div class="flex h-[104px] items-center justify-center border-b border-white/10 px-5">

            <div class="flex h-[68px] w-full items-center justify-center rounded-xl bg-white px-4 shadow-md shadow-black/20 ring-1 ring-white/10">

                <img src="{{ asset('images/trix-logo.jpg') }}" alt="Logo TRIX" class="block max-h-[48px] w-full object-contain">

            </div>

        </div>

        <div class="trix-scroll flex-1 overflow-y-auto px-4 py-8">

            <p class="mb-4 px-4 text-[10px] font-bold tracking-[2px] text-slate-400">WORKSPACE</p>

            <nav class="space-y-1.5">

                <a href="{{ route('hr.dashboard') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition-colors duration-200 hover:bg-white/10 hover:text-white">

                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>

                    Dashboard

                </a>

                <a href="{{ route('hr.live-monitoring') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition-colors duration-200 hover:bg-white/10 hover:text-white">

                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 6.5 12 3l9 3.5v11L12 21l-9-3.5z"/><path d="m3 6.5 9 4 9-4M12 10.5V21"/></svg>

                    Live Monitoring

                </a>

                <a href="{{ route('hr.drivers.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition-colors duration-200 hover:bg-white/10 hover:text-white">

                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><path d="M3 20v-1a6 6 0 0 1 12 0v1M16 5.5a3 3 0 0 1 0 5.8M18 14a5 5 0 0 1 3 4.5V20"/></svg>

                    Driver Management

                </a>

                <a href="{{ route('hr.vehicles.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition-colors duration-200 hover:bg-white/10 hover:text-white">

                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 13h18l-1.5-5h-15z"/><path d="M5 13v5h14v-5M7 18v2m10-2v2M7 8l1-3h8l1 3"/><circle cx="7" cy="15.5" r=".8" fill="currentColor"/><circle cx="17" cy="15.5" r=".8" fill="currentColor"/></svg>

                    Vehicle Management

                </a>

                <a href="{{ route('hr.trips.index') }}" class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-950/50 ring-1 ring-white/15">

                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h15l-3-3m4 13H5l3 3"/><path d="M19 7v4M5 17v-4"/></svg>

                    Trip Management

                </a>

                <a href="{{ route('hr.trip-history') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition-colors duration-200 hover:bg-white/10 hover:text-white">

                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>

                    Trip History

                </a>

            </nav>

            <p class="mb-4 mt-9 px-4 text-[10px] font-bold tracking-[2px] text-slate-400">PREFERENCES</p>

            <nav>

                <a href="{{ route('hr.notifications') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition-colors duration-200 hover:bg-white/10 hover:text-white">

                    <span class="relative">

                        <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>

                        <span class="absolute -right-0.5 -top-0.5 h-2 w-2 rounded-full bg-rose-500 ring-2 ring-[#0F1A2E]"></span>

                    </span>

                    Notifications

                </a>

            </nav>

        </div>

        <div class="border-t border-white/10 p-4">

            <div class="mb-4 flex items-center gap-3 rounded-xl bg-white/[0.07] p-3 ring-1 ring-white/10">

                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-sm font-bold text-white ring-1 ring-white/20">

                    {{ strtoupper(substr(auth()->user()->name ?? 'HR', 0, 1)) }}

                </div>

                <div class="min-w-0">

                    <p class="truncate text-xs font-semibold text-white">{{ auth()->user()->name ?? 'HR Admin' }}</p>

                    <p class="mt-1 text-[10px] text-slate-400">HR Administrator</p>

                </div>

            </div>

            <form action="{{ route('logout') }}" method="POST">@csrf

                <button class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-400 transition-colors duration-200 hover:bg-rose-500/15 hover:text-rose-300">

                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10 17l5-5-5-5m5 5H3"/><path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7"/></svg>

                    Sign Out

                </button>

            </form>

        </div>

    </aside>

    <!-- ============ MAIN ============ -->

    <main class="min-h-screen lg:ml-[258px]">

        <header class="sticky top-0 z-20 flex h-[76px] items-center justify-between border-b border-[#D6DEEA] bg-white/90 px-5 backdrop-blur-md sm:px-8 lg:px-10">

            <div>

                <p class="text-[10px] font-bold tracking-[2px] bg-gradient-to-r from-indigo-600 to-cyan-600 bg-clip-text text-transparent">TRIX WAY / WORKSPACE</p>

                <h2 class="mt-1 text-sm font-bold text-[#1C2A40]">Trip Management</h2>

            </div>

            <div class="flex items-center gap-5">

                <button class="relative flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-indigo-50 hover:text-indigo-600">

                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>

                    <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white"></span>

                </button>

                <div class="hidden text-right sm:block">

                    <p class="text-xs font-bold text-[#1C2A40]">{{ auth()->user()->name ?? 'HR Admin' }}</p>

                    <p class="mt-1 text-[10px] font-medium text-slate-500">Human Resources</p>

                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-sm font-bold text-white ring-2 ring-white shadow-md shadow-indigo-500/30">

                    {{ strtoupper(substr(auth()->user()->name ?? 'HR', 0, 1)) }}

                </div>

            </div>

        </header>

        <!-- CONTENT -->

        <div class="mx-auto max-w-[1700px] space-y-7 p-5 sm:p-8 lg:p-9">

            <!-- PAGE INTRO -->

            <section class="trix-anim trix-d1 flex flex-col justify-between gap-4 md:flex-row md:items-end">

                <div>

                    <p class="text-xs font-bold tracking-wide text-indigo-600">{{ strtoupper(now()->format('l, d F Y')) }}</p>

                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-[#1C2A40] sm:text-[34px]">

                        Trip <span class="trix-gradient-text">Management</span>

                    </h1>

                    <p class="mt-2 text-sm leading-6 text-slate-600">Monitor and manage all company trips in one place.</p>

                </div>

                <button onclick="openAddModal()" class="group inline-flex items-center gap-2.5 rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-500/30 ring-1 ring-white/15 transition-all hover:shadow-xl hover:shadow-indigo-500/40 hover:-translate-y-0.5 active:translate-y-0">

                    <svg class="h-4 w-4 transition-transform group-hover:rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>

                    Add New Trip

                </button>

            </section>

            <!-- STATISTICS -->

            <section class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

                <div class="trix-anim trix-d2 trix-card trix-card-hover rounded-2xl border border-indigo-100 bg-white p-6">

                    <div class="flex items-start justify-between">

                        <div class="min-w-0">

                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Trips</p>

                            <h3 id="totalTrips" class="trix-num mt-3 text-[36px] font-bold leading-none text-indigo-600">{{ $summary['total'] }}</h3>

                        </div>

                        <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-600 ring-1 ring-indigo-200">

                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h15l-3-3m4 13H5l3 3"/><path d="M19 7v4M5 17v-4"/></svg>

                        </div>

                    </div>

                    <p class="mt-4 flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-indigo-400"></span>All registered trips</p>

                </div>

                <div class="trix-anim trix-d3 trix-card trix-card-hover rounded-2xl border border-cyan-100 bg-white p-6">

                    <div class="flex items-start justify-between">

                        <div class="min-w-0">

                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Active Trips</p>

                            <h3 id="activeTrips" class="trix-num mt-3 text-[36px] font-bold leading-none text-cyan-600">{{ $summary['active'] }}</h3>

                        </div>

                        <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-50 to-cyan-100 text-cyan-600 ring-1 ring-cyan-200">

                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/><path d="M12 3v2M12 19v2M3 12h2M19 12h2"/></svg>

                        </div>

                    </div>

                    <p class="mt-4 flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-cyan-400"></span>Currently in progress</p>

                </div>

                <div class="trix-anim trix-d4 trix-card trix-card-hover rounded-2xl border border-orange-100 bg-white p-6">

                    <div class="flex items-start justify-between">

                        <div class="min-w-0">

                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Pending Trips</p>

                            <h3 id="pendingTrips" class="trix-num mt-3 text-[36px] font-bold leading-none text-orange-600">{{ $summary['pending'] }}</h3>

                        </div>

                        <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-orange-50 to-amber-100 text-orange-600 ring-1 ring-orange-200">

                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>

                        </div>

                    </div>

                    <p class="mt-4 flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-orange-400"></span>Waiting to start</p>

                </div>

                <div class="trix-anim trix-d5 trix-card trix-card-hover rounded-2xl border border-emerald-100 bg-white p-6">

                    <div class="flex items-start justify-between">

                        <div class="min-w-0">

                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Completed Trips</p>

                            <h3 id="completedTrips" class="trix-num mt-3 text-[36px] font-bold leading-none text-emerald-600">{{ $summary['completed'] }}</h3>

                        </div>

                        <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-600 ring-1 ring-emerald-200">

                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m5 12 4 4L19 6"/><circle cx="12" cy="12" r="9"/></svg>

                        </div>

                    </div>

                    <p class="mt-4 flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>Successfully completed</p>

                </div>

            </section>

            <!-- TRIP DIRECTORY -->

            <section class="trix-anim trix-d6 trix-card overflow-hidden rounded-2xl border border-[#D6DEEA] bg-white">

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#E2E7EF] px-6 py-5">

                    <div>

                        <h3 class="text-sm font-bold text-[#1C2A40]">Trip Directory</h3>

                        <p class="mt-1 text-[11px] text-slate-500">View trip schedules and current progress.</p>

                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 w-full md:w-auto">

                        <!-- SEARCH FIELD -->

                        <div class="relative flex items-center w-full sm:w-80">

                            <svg class="pointer-events-none absolute left-3.5 z-10 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">

                                <circle cx="11" cy="11" r="7"/>

                                <path d="m21 21-4.35-4.35"/>

                            </svg>

                            <input id="searchTrip" name="search" value="{{ request('search') }}" type="text" placeholder="Search trip code or destination..." class="trix-input w-full" autocomplete="off">

                        </div>

                        <div class="w-full sm:w-44 shrink-0">

                            <select id="statusFilter" name="status" class="trix-input cursor-pointer" onchange="applyTripFilters()">

                                <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Status</option>

                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>

                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>

                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>

                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>

                            </select>

                        </div>

                    </div>

                </div>

                <div class="overflow-x-auto">

                    <table class="w-full min-w-[1050px] text-left">

                        <thead class="bg-[#F2F5F9]">

                            <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-600">

                                <th class="px-6 py-4">Trip ID</th>

                                <th class="px-6 py-4">Driver</th>

                                <th class="px-6 py-4">Vehicle</th>

                                <th class="px-6 py-4">Destination</th>

                                <th class="px-6 py-4">Start Time</th>

                                <th class="px-6 py-4">End Time</th>

                                <th class="px-6 py-4">Status</th>

                                <th class="px-6 py-4 text-center">Actions</th>

                            </tr>

                        </thead>

                        <tbody id="tripTableBody" class="divide-y divide-[#EEF2F7] text-sm">

                            @forelse($trips as $trip)

                                @php

                                    $statusColors = [

                                        'pending' => 'bg-orange-50 text-orange-700 ring-orange-200',

                                        'active' => 'bg-cyan-50 text-cyan-700 ring-cyan-200',

                                        'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',

                                        'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200'

                                    ];

                                    $statusDots = [

                                        'pending' => 'bg-orange-500',

                                        'active' => 'bg-cyan-500',

                                        'completed' => 'bg-emerald-500',

                                        'cancelled' => 'bg-rose-500'

                                    ];

                                    // --- HELPER WAKTU WIB ---

                                    $formatWib = function ($value) {

                                        if (empty($value)) return '-';

                                        try {

                                            return \Carbon\Carbon::parse($value)

                                                ->timezone(config('app.timezone', 'Asia/Jakarta'))

                                                ->format('d M Y • H:i') . ' WIB';

                                        } catch (\Throwable $e) {

                                            return '-';

                                        }

                                    };

                                    // --- HELPER FORMAT UNTUK INPUT datetime-local ---

                                    $formatInput = function ($value) {

                                        if (empty($value)) return '';

                                        try {

                                            return \Carbon\Carbon::parse($value)

                                                ->timezone(config('app.timezone', 'Asia/Jakarta'))

                                                ->format('Y-m-d\TH:i');

                                        } catch (\Throwable $e) {

                                            return '';

                                        }

                                    };

                                    $displayStartDate = $formatWib($trip->actual_start_time ?? null);

                                    $displayEndDate   = $formatWib($trip->actual_end_time ?? null);

                                    // --- INJECT FIELD TERFORMAT KE OBJEK TRIP ---

                                    $tripPayload = $trip->toArray();

                                    $tripPayload['formatted_start_time'] = $formatInput($trip->actual_start_time ?? null);

                                    $tripPayload['formatted_end_time']   = $formatInput($trip->actual_end_time ?? null);

                                    $tripPayload['display_start_time']   = $displayStartDate;

                                    $tripPayload['display_end_time']     = $displayEndDate;

                                @endphp

                                <tr class="trix-row">

                                    <td class="px-6 py-4 font-semibold text-[#1C2A40]">{{ $trip->trip_code }}</td>

                                    <td class="px-6 py-4 text-slate-600">{{ $trip->driver->name ?? '-' }}</td>

                                    <td class="px-6 py-4 text-slate-600">{{ $trip->vehicle->plate_number ?? '-' }}</td>

                                    <td class="px-6 py-4 text-slate-600">{{ $trip->stops->first()->destination ?? '-' }}</td>

                                    <td class="px-6 py-4 text-slate-500">{{ $displayStartDate }}</td>

                                    <td class="px-6 py-4 text-slate-500">{{ $displayEndDate }}</td>

                                    <td class="px-6 py-4">

                                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[11px] font-bold ring-1 {{ $statusColors[$trip->status] }}">

                                            <span class="h-1.5 w-1.5 rounded-full {{ $statusDots[$trip->status] }}"></span>

                                            {{ ucfirst($trip->status) }}

                                        </span>

                                    </td>

                                    <td class="px-6 py-4 text-center">

                                        <div class="flex justify-center gap-1.5">

                                            <!-- Tombol View Detail -->

                                            <button type="button" onclick='viewTrip(@json($tripPayload))' class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100" title="View Details">

                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>

                                            </button>

                                            <!-- Tombol Simulasi Driver Start (Testing) -->

                                            @if($trip->status === 'pending')

                                                <form action="{{ route('hr.trips.simulate', [$trip->id, 'start']) }}" method="POST" class="inline">

                                                    @csrf @method('PATCH')

                                                    <button type="submit" class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 hover:bg-indigo-200" title="Simulate: Driver Start">

                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>

                                                    </button>

                                                </form>

                                            @endif

                                            <!-- Tombol Edit (HR) -->

                                            <button type="button" onclick='openEditModal(@json($tripPayload))' class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100" title="Edit Trip">

                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>

                                            </button>

                                            <!-- Tombol Cancel (HR) -->

                                            @if(in_array($trip->status, ['pending', 'active']))

                                                <button type="button" onclick="openCancelModal({{ $trip->id }}, '{{ $trip->trip_code }}')" class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100" title="Cancel Trip">

                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>

                                                </button>

                                            @endif

                                            <!-- Tombol Delete (HR) -->

                                            <form action="{{ route('hr.trips.destroy', $trip->id) }}" method="POST" class="inline delete-form" data-name="{{ $trip->trip_code }}">

                                                @csrf

                                                @method('DELETE')

                                                <button type="button" onclick="openDeleteModal(this)" class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-100" title="Delete Trip">

                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>

                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="8" class="py-16 text-center">

                                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-indigo-50 via-white to-cyan-50 text-indigo-500 ring-1 ring-indigo-100 shadow-sm">

                                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 7h15l-3-3m4 13H5l3 3"/><path d="M19 7v4M5 17v-4"/></svg>

                                        </div>

                                        <p class="mt-4 text-sm font-bold text-slate-700">No trips found</p>

                                        <p class="mt-1 text-xs text-slate-500">

                                            @if(request('search'))

                                                No results for "<span class="font-semibold">{{ request('search') }}</span>". Try a different code, purpose, driver, or vehicle.

                                            @else

                                                Trip records will appear here when available.

                                            @endif

                                        </p>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="flex items-center justify-between border-t border-[#E2E7EF] bg-[#FAFBFD] px-6 py-4">

                    <p class="text-[11px] font-medium text-slate-500">Showing {{ $trips->firstItem() ?? 0 }} to {{ $trips->lastItem() ?? 0 }} of {{ $trips->total() }} trips</p>

                    <div>{{ $trips->links() }}</div>

                </div>

            </section>

            <footer class="flex flex-col justify-between gap-2 border-t border-[#D6DEEA] pt-5 text-[10px] font-medium tracking-wide text-slate-500 sm:flex-row">

                <p>© {{ date('Y') }} TRIX WAY · PT TRIX INDONESIA</p>

                <p>FLEET OPERATIONS MANAGEMENT SYSTEM</p>

            </footer>

        </div>

    </main>

</div>

<!-- ============ ADD / EDIT TRIP MODAL ============ -->

<div id="addTripModal" class="trix-modal">

    <div class="trix-modal-content rounded-2xl bg-white shadow-2xl">

        <div class="flex items-center justify-between border-b border-[#E2E7EF] px-6 py-5">

            <div class="flex items-center gap-3">

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-500 text-white shadow-lg shadow-indigo-500/30">

                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>

                </div>

                <div>

                    <h3 id="modalTitle" class="text-sm font-bold text-[#1C2A40]">Add New Trip</h3>

                    <p class="mt-0.5 text-[11px] text-slate-500">Assign a driver and vehicle for a new journey.</p>

                </div>

            </div>

            <button type="button" onclick="closeModal('addTripModal')" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-500">

                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 6l12 12M18 6L6 18"/></svg>

            </button>

        </div>

        <form id="tripForm" method="POST" class="space-y-4 p-6">

            @csrf

            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div>

                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Driver *</label>

                    <select name="driver_id" class="trix-input cursor-pointer" required>

                        <option value="">Select Driver</option>

                        @forelse($drivers as $driver)

                            <option value="{{ $driver->id }}">{{ $driver->name }}</option>

                        @empty

                            <option value="" disabled>No available drivers</option>

                        @endforelse

                    </select>

                    @if($drivers->isEmpty())

                        <p class="mt-1 text-[10px] text-rose-500">
                            All drivers are currently assigned to active trips.
                        </p>

                    @endif

                </div>

                <div>

                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Vehicle *</label>

                    <select name="vehicle_id" class="trix-input cursor-pointer" required>

                        <option value="">Select Vehicle</option>

                        @foreach($vehicles as $vehicle)

                            <option value="{{ $vehicle->id }}">{{ $vehicle->plate_number }} ({{ $vehicle->model }})</option>

                        @endforeach

                    </select>

                </div>

            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div>

                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Planned Start Date *</label>

                    <input type="date" name="planned_start_date" class="trix-input" required>

                </div>

                <div>

                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Planned Start Time *</label>

                    <input type="time" name="planned_start_time" class="trix-input" required>

                </div>

            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div>

                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Purpose *</label>

                    <input type="text" name="purpose" class="trix-input" placeholder="e.g. Delivery" required>

                </div>

                <div>

                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Origin *</label>

                    <input type="text" name="origin" class="trix-input" placeholder="e.g. Warehouse A" required>

                </div>

            </div>

            <div>

                <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Notes</label>

                <textarea name="notes" class="trix-input resize-none" rows="2" placeholder="Optional notes..."></textarea>

            </div>

            <!-- Destinations Dynamic -->

            <div class="mt-4 border-t border-[#E2E7EF] pt-4">

                <div class="flex items-center justify-between mb-3">

                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Destinations *</label>

                    <button type="button" onclick="addDestinationRow()" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800">+ Add Destination</button>

                </div>

                <div id="destinationsContainer" class="space-y-3">

                    <div class="destination-row flex gap-2 items-start">

                        <div class="flex-1 space-y-2">

                            <input type="text" name="destinations[0][name]" class="trix-input" placeholder="Destination Name" required>

                            <input type="text" name="destinations[0][address]" class="trix-input" placeholder="Address (optional)">

                        </div>

                    </div>

                </div>

            </div>

            <div class="flex justify-end gap-3 border-t border-[#E2E7EF] pt-5">

                <button type="button" onclick="closeModal('addTripModal')" class="rounded-lg border border-[#D6DEEA] bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">

                    Cancel

                </button>

                <button type="submit" class="rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-500/30 transition hover:shadow-xl hover:shadow-indigo-500/40 hover:-translate-y-0.5 active:translate-y-0">

                    Save Trip

                </button>

            </div>

        </form>

    </div>

</div>

<!-- ============ TRIP DETAIL MODAL ============ -->

<div id="detailModal" class="trix-modal">

    <div class="trix-modal-content rounded-2xl bg-white shadow-2xl">

        <div class="flex items-center justify-between border-b border-[#E2E7EF] px-6 py-5">

            <div class="flex items-center gap-3">

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-500 text-white shadow-lg shadow-indigo-500/30">

                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h15l-3-3m4 13H5l3 3"/><path d="M19 7v4M5 17v-4"/></svg>

                </div>

                <div>

                    <h3 class="text-sm font-bold text-[#1C2A40]">Trip Details</h3>

                    <p class="mt-0.5 text-[11px] text-slate-500">Trip and destination information</p>

                </div>

            </div>

            <button onclick="closeModal('detailModal')" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-500">

                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 6l12 12M18 6L6 18"/></svg>

            </button>

        </div>

        <div id="detailContent" class="p-6"></div>

        <div class="border-t border-[#E2E7EF] px-6 py-4 text-right">

            <button onclick="closeModal('detailModal')" class="rounded-lg bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-200">

                Close

            </button>

        </div>

    </div>

</div>

<!-- ============ DELETE CONFIRMATION MODAL ============ -->

<div id="deleteModal" class="trix-modal">

    <div class="trix-modal-content max-w-sm rounded-2xl bg-white shadow-2xl">

        <div class="p-6 text-center">

            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-rose-50 text-rose-500 ring-4 ring-rose-100 mb-4">

                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>

            </div>

            <h3 class="text-lg font-bold text-[#1C2A40]">Delete Trip?</h3>

            <p class="mt-2 text-sm text-slate-500 leading-relaxed">

                Are you sure you want to delete trip <strong id="deleteTripName" class="text-[#1C2A40]"></strong>? This action cannot be undone.

            </p>

        </div>

        <div class="flex gap-3 border-t border-[#E2E7EF] px-6 py-4">

            <button type="button" onclick="closeModal('deleteModal')" class="flex-1 rounded-lg border border-[#D6DEEA] bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">

                Cancel

            </button>

            <button type="button" id="confirmDeleteBtn" class="flex-1 rounded-lg bg-rose-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-rose-500/30 transition hover:bg-rose-600 hover:-translate-y-0.5 active:translate-y-0">

                Yes, Delete

            </button>

        </div>

    </div>

</div>

<!-- ============ CANCEL CONFIRMATION MODAL ============ -->

<div id="cancelModal" class="trix-modal">

    <div class="trix-modal-content max-w-sm rounded-2xl bg-white shadow-2xl">

        <form id="cancelForm" method="POST">

            @csrf

            @method('PATCH')

            <div class="p-6 text-center">

                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-orange-50 text-orange-500 ring-4 ring-orange-100 mb-4">

                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>

                </div>

                <h3 class="text-lg font-bold text-[#1C2A40]">Cancel Trip?</h3>

                <p class="mt-2 text-sm text-slate-500 leading-relaxed">

                    You are about to cancel trip <strong id="cancelTripName" class="text-[#1C2A40]"></strong>.

                </p>

                <div class="mt-4 text-left">

                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Reason *</label>

                    <input type="text" name="cancel_reason" class="trix-input" placeholder="e.g. Driver sakit, kendaraan rusak" required>

                </div>

            </div>

            <div class="flex gap-3 border-t border-[#E2E7EF] px-6 py-4">

                <button type="button" onclick="closeModal('cancelModal')" class="flex-1 rounded-lg border border-[#D6DEEA] bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">

                    Back

                </button>

                <button type="submit" class="flex-1 rounded-lg bg-orange-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-orange-500/30 transition hover:bg-orange-600 hover:-translate-y-0.5 active:translate-y-0">

                    Yes, Cancel

                </button>

            </div>

        </form>

    </div>

</div>

<!-- ============ SAVE CONFIRMATION MODAL ============ -->

<div id="saveModal" class="trix-modal">

    <div class="trix-modal-content max-w-sm rounded-2xl bg-white shadow-2xl">

        <div class="p-6 text-center">

            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-indigo-50 text-indigo-500 ring-4 ring-indigo-100 mb-4">

                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>

            </div>

            <h3 class="text-lg font-bold text-[#1C2A40]">Save Data?</h3>

            <p class="mt-2 text-sm text-slate-500 leading-relaxed">

                Are you sure the data you entered is correct and you want to save it?

            </p>

        </div>

        <div class="flex gap-3 border-t border-[#E2E7EF] px-6 py-4">

            <button type="button" onclick="closeModal('saveModal')" class="flex-1 rounded-lg border border-[#D6DEEA] bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">

                Cancel

            </button>

            <button type="button" id="confirmSaveBtn" class="flex-1 rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-500/30 transition hover:shadow-xl hover:shadow-indigo-500/40 hover:-translate-y-0.5 active:translate-y-0">

                Yes, Save

            </button>

        </div>

    </div>

</div>

<script>

    // ============ CUSTOM TOAST FUNCTION ============

    function showToast(message, type = 'success') {

        const container = document.getElementById('toastContainer');

        const toast = document.createElement('div');

        const isSuccess = type === 'success';

        const bgClass = isSuccess ? 'bg-white border-emerald-200' : 'bg-white border-rose-200';

        const iconBg = isSuccess ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600';

        const iconSvg = isSuccess 

            ? `<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 4 4L19 6"/></svg>`

            : `<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 8v4M12 16h.01"/><circle cx="12" cy="12" r="9"/></svg>`;

        const title = isSuccess ? 'Success!' : 'Failed!';

        toast.className = `trix-toast flex w-[320px] items-start gap-3 rounded-xl border p-4 shadow-xl pointer-events-auto ${bgClass}`;

        toast.innerHTML = `

            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full ${iconBg}">${iconSvg}</div>

            <div class="flex-1 pt-0.5">

                <p class="text-sm font-bold text-[#1C2A40]">${title}</p>

                <p class="mt-1 text-[11px] font-medium leading-relaxed text-slate-500">${message}</p>

            </div>

            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">

                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>

            </button>

        `;

        container.appendChild(toast);

        setTimeout(() => { toast.classList.add('hiding'); setTimeout(() => toast.remove(), 300); }, 3500);

    }

    @if(session('success'))

        showToast("{{ session('success') }}", 'success');

    @endif

    @if(session('error'))

        showToast("{{ session('error') }}", 'error');

    @endif

    @if($errors->any())

        showToast("{{ $errors->first() }}", 'error');

    @endif

    let destinationIndex = 1;

    // ============ HELPER FORMAT WIB (JS) ============

    const MONTHS_ID = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

    function formatWib(iso) {

        if (!iso) return '-';

        try {

            const d = new Date(iso);

            if (isNaN(d.getTime())) return '-';

            const parts = new Intl.DateTimeFormat('en-GB', {

                timeZone: 'Asia/Jakarta',

                year: 'numeric', month: '2-digit', day: '2-digit',

                hour: '2-digit', minute: '2-digit', hour12: false,

            }).formatToParts(d);

            const get = (t) => parts.find(p => p.type === t)?.value;

            const day   = get('day');

            const month = MONTHS_ID[parseInt(get('month'), 10) - 1];

            const year  = get('year');

            const hour  = get('hour') === '24' ? '00' : get('hour');

            const min   = get('minute');

            return `${parseInt(day, 10)} ${month} ${year} • ${hour}:${min} WIB`;

        } catch (e) {

            return '-';

        }

    }

    // ============ OPEN ADD MODAL ============

    function openAddModal() {

        const form = document.getElementById('tripForm');

        form.reset();

        form.action = "{{ route('hr.trips.store') }}";

        document.getElementById('formMethod').value = 'POST';

        document.getElementById('modalTitle').textContent = 'Add New Trip';

        document.getElementById('destinationsContainer').innerHTML = `

            <div class="destination-row flex gap-2 items-start">

                <div class="flex-1 space-y-2">

                    <input type="text" name="destinations[0][name]" class="trix-input" placeholder="Destination Name" required>

                    <input type="text" name="destinations[0][address]" class="trix-input" placeholder="Address (optional)">

                </div>

            </div>

        `;

        destinationIndex = 1;

        document.getElementById('addTripModal').classList.add('show');

    }

    // ============ OPEN EDIT MODAL ============

    function openEditModal(trip) {

        const form = document.getElementById('tripForm');

        form.reset();

        form.action = `/hr/trips/${trip.id}`; 

        document.getElementById('formMethod').value = 'PUT';

        document.getElementById('modalTitle').textContent = 'Edit Trip';

        form.querySelector('[name="driver_id"]').value = trip.driver_id;

        form.querySelector('[name="vehicle_id"]').value = trip.vehicle_id;

        form.querySelector('[name="purpose"]').value = trip.purpose;

        form.querySelector('[name="origin"]').value = trip.origin;

        form.querySelector('[name="planned_start_date"]').value = trip.planned_start_date;

        form.querySelector('[name="planned_start_time"]').value = trip.planned_start_time;

        form.querySelector('[name="notes"]').value = trip.notes || '';

        const container = document.getElementById('destinationsContainer');

        container.innerHTML = '';

        if(trip.stops && trip.stops.length > 0) {

            trip.stops.forEach((stop, i) => {

                const row = document.createElement('div');

                row.className = 'destination-row flex gap-2 items-start';

                row.innerHTML = `

                    <div class="flex-1 space-y-2">

                        <input type="text" name="destinations[${i}][name]" value="${stop.destination}" class="trix-input" placeholder="Destination Name" required>

                        <input type="text" name="destinations[${i}][address]" value="${stop.address || ''}" class="trix-input" placeholder="Address (optional)">

                    </div>

                    ${i > 0 ? `<button type="button" onclick="this.closest('.destination-row').remove()" class="mt-2 text-rose-500 hover:text-rose-700"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg></button>` : ''}

                `;

                container.appendChild(row);

            });

            destinationIndex = trip.stops.length;

        }

        document.getElementById('addTripModal').classList.add('show');

    }

    function addDestinationRow() {

        const container = document.getElementById('destinationsContainer');

        const row = document.createElement('div');

        row.className = 'destination-row flex gap-2 items-start';

        row.innerHTML = `

            <div class="flex-1 space-y-2">

                <input type="text" name="destinations[${destinationIndex}][name]" class="trix-input" placeholder="Destination Name" required>

                <input type="text" name="destinations[${destinationIndex}][address]" class="trix-input" placeholder="Address (optional)">

            </div>

            <button type="button" onclick="this.closest('.destination-row').remove()" class="mt-2 text-rose-500 hover:text-rose-700">

                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>

            </button>

        `;

        container.appendChild(row);

        destinationIndex++;

    }

    // ============ VIEW DETAIL & EDIT TIMES ============

    function viewTrip(trip) {

        const statusColors = {

            pending: 'bg-orange-50 text-orange-700 ring-orange-200',

            active: 'bg-cyan-50 text-cyan-700 ring-cyan-200',

            completed: 'bg-emerald-50 text-emerald-700 ring-emerald-200',

            cancelled: 'bg-rose-50 text-rose-700 ring-rose-200'

        };

        const statusDots = {

            pending: 'bg-orange-500',

            active: 'bg-cyan-500',

            completed: 'bg-emerald-500',

            cancelled: 'bg-rose-500'

        };

        const startVal = trip.formatted_start_time || '';

        const endVal   = trip.formatted_end_time   || '';

        const displayStart = trip.display_start_time

            || (trip.actual_start_time ? formatWib(trip.actual_start_time) : '-');

        const displayEnd = trip.display_end_time

            || (trip.actual_end_time ? formatWib(trip.actual_end_time) : '-');

        let destHtml = '';

        if(trip.stops && trip.stops.length > 0) {

            destHtml = trip.stops.map((stop, i) => {

                const isArrived = stop.status === 'arrived';

                const arrivalTime = stop.arrival_time ? formatWib(stop.arrival_time) : '-';

                const departureTime = stop.departure_time ? formatWib(stop.departure_time) : '-';

                return `

                <div class="flex items-start gap-3">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full ${isArrived ? 'bg-emerald-500' : 'bg-slate-300'} text-[11px] font-bold text-white ring-2 ring-white">

                        ${i + 1}

                    </div>

                    <div class="flex-1 border-b border-[#EEF2F7] pb-2">

                        <p class="text-sm font-semibold text-[#1C2A40]">${stop.destination}</p>

                        <div class="mt-1 flex flex-col gap-1 text-[10px] text-slate-500">

                            <div class="flex items-center gap-2">

                                <span class="font-bold ${isArrived ? 'text-emerald-600' : 'text-slate-400'}">${isArrived ? 'ARRIVED' : 'PENDING'}</span>

                                <span>•</span>

                                <span>Tiba: ${arrivalTime}</span>

                            </div>

                            <div class="flex items-center gap-2">

                                <span>Berangkat: ${departureTime}</span>

                            </div>

                        </div>

                    </div>

                </div>

                `;

            }).join('');

        }

        document.getElementById('detailContent').innerHTML = `

            <div class="mb-6 flex items-start justify-between">

                <div>

                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Trip Reference</p>

                    <h4 class="mt-1 text-xl font-bold text-[#1C2A40]">${trip.trip_code}</h4>

                    <span class="mt-2 inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[11px] font-bold ring-1 ${statusColors[trip.status]}">

                        <span class="h-1.5 w-1.5 rounded-full ${statusDots[trip.status]}"></span>

                        ${trip.status.toUpperCase()}

                    </span>

                </div>

            </div>

            <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2">

                <div class="rounded-xl border border-[#E2E7EF] bg-[#FAFBFD] p-4">

                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Driver</p>

                    <p class="mt-1 text-sm font-semibold text-[#1C2A40]">${trip.driver ? trip.driver.name : '-'}</p>

                </div>

                <div class="rounded-xl border border-[#E2E7EF] bg-[#FAFBFD] p-4">

                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Vehicle</p>

                    <p class="mt-1 text-sm font-semibold text-[#1C2A40]">${trip.vehicle ? trip.vehicle.plate_number : '-'}</p>

                </div>

            </div>

            <!-- FORM EDIT WAKTU -->

            <div class="mb-6 rounded-xl border border-indigo-100 bg-[#FAFBFD] p-5">

                <h5 class="mb-4 text-xs font-bold text-indigo-700 flex items-center gap-2">

                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>

                    Correct Actual Time

                </h5>

                <form action="/hr/trips/${trip.id}/update-times" method="POST" class="space-y-4">

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <input type="hidden" name="_method" value="PATCH">

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div>

                            <label class="mb-1.5 block text-[11px] font-semibold text-slate-600">Actual Start</label>

                            <input type="datetime-local" name="actual_start_time" value="${startVal}" class="w-full rounded-lg border border-[#D6DEEA] px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white">

                            <p class="mt-1.5 text-[10px] text-slate-400">Current: ${displayStart}</p>

                        </div>

                        <div>

                            <label class="mb-1.5 block text-[11px] font-semibold text-slate-600">Actual End</label>

                            <input type="datetime-local" name="actual_end_time" value="${endVal}" class="w-full rounded-lg border border-[#D6DEEA] px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white">

                            <p class="mt-1.5 text-[10px] text-slate-400">Current: ${displayEnd}</p>

                        </div>

                    </div>

                    <div class="flex justify-end pt-2">

                        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-700 transition-colors">

                            Save Time

                        </button>

                    </div>

                </form>

            </div>

            <h5 class="mb-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">Destination Timeline</h5>

            <div class="space-y-3">${destHtml}</div>

        `;

        document.getElementById('detailModal').classList.add('show');

    }

    // ============ SAVE CONFIRMATION LOGIC ============

    let formToSave = null;

    document.getElementById('tripForm').addEventListener('submit', function(e) {

        e.preventDefault(); 

        formToSave = this; 

        document.getElementById('saveModal').classList.add('show'); 

    });

    document.getElementById('confirmSaveBtn').addEventListener('click', function() {

        if (formToSave) {

            HTMLFormElement.prototype.submit.call(formToSave); 

        }

    });

    // ============ DELETE LOGIC ============

    let formToDelete = null;

    function openDeleteModal(button) {

        formToDelete = button.closest('.delete-form');

        const name = formToDelete.getAttribute('data-name');

        document.getElementById('deleteTripName').textContent = name;

        document.getElementById('deleteModal').classList.add('show');

    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {

        if (formToDelete) {

            formToDelete.submit();

        }

    });

    // ============ CANCEL LOGIC ============

    function openCancelModal(id, name) {

        const form = document.getElementById('cancelForm');

        form.action = `/hr/trips/${id}/cancel`; 

        document.getElementById('cancelTripName').textContent = name;

        document.getElementById('cancelModal').classList.add('show');

    }

    // ============ MODAL UTILS ============

    function closeModal(id) {

        document.getElementById(id).classList.remove('show');

    }

    document.querySelectorAll('.trix-modal').forEach(modal => {

        modal.addEventListener('click', event => {

            if (event.target === modal) modal.classList.remove('show');

        });

    });

    // ============ LIVE SEARCH + STATUS FILTER ============

    const searchTripInput = document.getElementById('searchTrip');

    const statusFilterTrip = document.getElementById('statusFilter');

    const tripTableBody = document.getElementById('tripTableBody');

    let debounceTimerTrip = null;

    let currentRequestTrip = null;

    function applyTripFilters() {

        const params = new URLSearchParams();

        const q = searchTripInput.value.trim();

        const status = statusFilterTrip.value;

        if (q) params.set('search', q);

        if (status && status !== 'all') params.set('status', status);

        const url = `{{ route('hr.trips.index') }}?${params.toString()}`;

        searchTripInput.classList.add('opacity-50');

        if (currentRequestTrip) currentRequestTrip.abort();

        currentRequestTrip = new AbortController();

        fetch(url, {

            headers: {

                'X-Requested-With': 'XMLHttpRequest',

                'Accept': 'application/json',

            },

            signal: currentRequestTrip.signal,

        })

        .then(res => {

            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            return res.json();

        })

        .then(data => {

            if (data.html !== undefined) {

                tripTableBody.innerHTML = data.html;

            }

            window.history.replaceState({}, '', url);

        })

        .catch(err => {

            if (err.name !== 'AbortError') console.error('Filter error:', err);

        })

        .finally(() => {

            searchTripInput.classList.remove('opacity-50');

        });

    }

    searchTripInput.addEventListener('input', function () {

        clearTimeout(debounceTimerTrip);

        debounceTimerTrip = setTimeout(applyTripFilters, 400);

    });

    searchTripInput.addEventListener('keydown', function (e) {

        if (e.key === 'Enter') {

            e.preventDefault();

            clearTimeout(debounceTimerTrip);

            applyTripFilters();

        }

    });

</script>

</body>

</html>