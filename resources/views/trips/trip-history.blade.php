<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip History | TRIX WAY</title>
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

        /* FIX PADDING SEARCH */
        #searchInput { padding-left: 2.5rem !important; }

        /* ============ STATUS BADGE ============ */
        .trix-status-completed {
            display: inline-flex; align-items: center; gap: 6px; border-radius: 9999px;
            background: #ECFDF5; color: #047857; padding: 6px 12px; font-size: 11px;
            font-weight: 700; box-shadow: 0 0 0 1px #A7F3D0;
        }
        .trix-status-cancelled {
            display: inline-flex; align-items: center; gap: 6px; border-radius: 9999px;
            background: #FFF1F2; color: #BE123C; padding: 6px 12px; font-size: 11px;
            font-weight: 700; box-shadow: 0 0 0 1px #FECDD3;
        }
        .trix-status-completed::before, .trix-status-cancelled::before {
            content: ''; width: 6px; height: 6px; border-radius: 9999px;
        }
        .trix-status-completed::before { background: #10B981; }
        .trix-status-cancelled::before { background: #F43F5E; }

        /* ============ RECYCLE BIN ============ */
        .trix-deleted-row { transition: background .2s ease; }
        .trix-deleted-row:hover { background: #FAFBFD; }
        .trix-btn-restore { transition: all .2s; }
        .trix-btn-restore:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(16,185,129,0.15); }
        .trix-btn-delete { transition: all .2s; }
        .trix-btn-delete:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(244,63,94,0.15); }

        /* ============ MODAL ============ */
        .trix-modal {
            display: none; position: fixed; inset: 0; z-index: 100;
            background: rgba(15,23,42,.55); backdrop-filter: blur(6px);
            align-items: center; justify-content: center; padding: 20px;
        }
        .trix-modal.show { display: flex; }
        .trix-modal-content {
            width: 100%; max-width: 640px; max-height: 90vh; overflow-y: auto;
            animation: trixFadeUp .3s cubic-bezier(.4,0,.2,1);
        }

        /* ============ TABLE ROW ============ */
        .trix-row { transition: background .2s ease; }
        .trix-row:hover { background: #FAFBFD; }

        /* ============ ACTION BTN ============ */
        .trix-action { transition: all .2s cubic-bezier(.4,0,.2,1); }
        .trix-action:hover { transform: translateY(-1px) scale(1.05); box-shadow: 0 4px 10px rgba(20,35,60,0.10); }

        /* ============ REDUCED MOTION ============ */
        @media (prefers-reduced-motion: reduce) {
            .trix-anim, .trix-live-dot { animation: none !important; opacity: 1 !important; }
            .trix-card, .trix-card-hover:hover, .trix-action:hover { transition: none; transform: none; }
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-[#EEF2FB] via-[#F1EEFB] to-[#EAF4F8] text-[#1C2A40] antialiased selection:bg-indigo-500/20">

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
                <a href="{{ url('/hr/dashboard') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition-colors duration-200 hover:bg-white/10 hover:text-white">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                    Dashboard
                </a>
                <a href="{{ url('/hr/live-monitoring') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition-colors duration-200 hover:bg-white/10 hover:text-white">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 6.5 12 3l9 3.5v11L12 21l-9-3.5z"/><path d="m3 6.5 9 4 9-4M12 10.5V21"/></svg>
                    Live Monitoring
                </a>
                <a href="{{ url('/hr/drivers') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition-colors duration-200 hover:bg-white/10 hover:text-white">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><path d="M3 20v-1a6 6 0 0 1 12 0v1M16 5.5a3 3 0 0 1 0 5.8M18 14a5 5 0 0 1 3 4.5V20"/></svg>
                    Driver Management
                </a>
                <a href="{{ url('/hr/vehicles') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition-colors duration-200 hover:bg-white/10 hover:text-white">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 13h18l-1.5-5h-15z"/><path d="M5 13v5h14v-5M7 18v2m10-2v2M7 8l1-3h8l1 3"/><circle cx="7" cy="15.5" r=".8" fill="currentColor"/><circle cx="17" cy="15.5" r=".8" fill="currentColor"/></svg>
                    Vehicle Management
                </a>
                <a href="{{ url('/hr/trips') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition-colors duration-200 hover:bg-white/10 hover:text-white">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h15l-3-3m4 13H5l3 3"/><path d="M19 7v4M5 17v-4"/></svg>
                    Trip Management
                </a>
                <a href="{{ url('/hr/trip-history') }}" class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-950/50 ring-1 ring-white/15">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    Trip History
                </a>
            </nav>
            <p class="mb-4 mt-9 px-4 text-[10px] font-bold tracking-[2px] text-slate-400">PREFERENCES</p>
            <nav>
                <a href="{{ url('/hr/notifications') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition-colors duration-200 hover:bg-white/10 hover:text-white">
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
                    <p class="truncate text-xs font-semibold text-white">{{ auth()->user()->name ?? 'HR Administrator' }}</p>
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
                <h2 class="mt-1 text-sm font-bold text-[#1C2A40]">Trip History</h2>
            </div>
            <div class="flex items-center gap-5">
                <button class="relative flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-indigo-50 hover:text-indigo-600">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                    <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
                </button>
                <div class="hidden text-right sm:block">
                    <p class="text-xs font-bold text-[#1C2A40]">{{ auth()->user()->name ?? 'HR Administrator' }}</p>
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
                        Trip <span class="trix-gradient-text">History</span>
                    </h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600">View historical trip records, journey details, and driver activity.</p>
                </div>
                <button onclick="resetFilters()" class="inline-flex items-center gap-2 self-start rounded-xl border border-[#D6DEEA] bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 md:self-auto">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
                    Reset Filters
                </button>
            </section>

            <!-- STATISTICS -->
            <section class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <div class="trix-anim trix-d2 trix-card trix-card-hover rounded-2xl border border-emerald-100 bg-white p-6">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Completed</p>
                            <h3 id="totalCompleted" class="trix-num mt-3 text-[36px] font-bold leading-none text-emerald-600">0</h3>
                        </div>
                        <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-600 ring-1 ring-emerald-200">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m5 12 4 4L19 6"/><circle cx="12" cy="12" r="9"/></svg>
                        </div>
                    </div>
                    <p class="mt-4 flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>Successfully completed</p>
                </div>
                <div class="trix-anim trix-d3 trix-card trix-card-hover rounded-2xl border border-indigo-100 bg-white p-6">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">This Month</p>
                            <h3 id="thisMonth" class="trix-num mt-3 text-[36px] font-bold leading-none text-indigo-600">0</h3>
                        </div>
                        <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-600 ring-1 ring-indigo-200">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><path d="m9 16 2 2 4-4"/></svg>
                        </div>
                    </div>
                    <p class="mt-4 flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-indigo-400"></span>Trips completed this month</p>
                </div>
                <div class="trix-anim trix-d4 trix-card trix-card-hover rounded-2xl border border-cyan-100 bg-white p-6">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Distance</p>
                            <h3 id="totalDistance" class="trix-num mt-3 text-[36px] font-bold leading-none text-cyan-600">—</h3>
                        </div>
                        <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-50 to-cyan-100 text-cyan-600 ring-1 ring-cyan-200">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h15l-3-3m4 13H5l3 3"/><path d="M19 7v4M5 17v-4"/></svg>
                        </div>
                    </div>
                    <p class="mt-4 flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-cyan-400"></span>Accumulated trip distance</p>
                </div>
                <div class="trix-anim trix-d5 trix-card trix-card-hover rounded-2xl border border-orange-100 bg-white p-6">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Avg. Duration</p>
                            <h3 id="averageDuration" class="trix-num mt-3 text-[36px] font-bold leading-none text-orange-600">—</h3>
                        </div>
                        <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-orange-50 to-amber-100 text-orange-600 ring-1 ring-orange-200">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2.5 2.5M9 2h6"/></svg>
                        </div>
                    </div>
                    <p class="mt-4 flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-orange-400"></span>Average journey duration</p>
                </div>
            </section>

            <!-- FILTERS -->
            <section class="trix-anim trix-d6 trix-card rounded-2xl border border-[#D6DEEA] bg-white p-6">
                <div class="mb-5 flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-500 text-white shadow-sm">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 4h18l-7 9v6l-4 2v-8L3 4z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-[#1C2A40]">Filter Trip Records</h3>
                        <p class="mt-0.5 text-[11px] text-slate-500">Narrow down the historical records</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Search</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 z-10 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
                            <input id="searchInput" type="text" placeholder="Trip ID, driver, vehicle..." oninput="renderTrips()" class="trix-input">
                        </div>
                    </div>
                    <div>
                        <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Start Date</label>
                        <input id="startDate" type="date" onchange="renderTrips()" class="trix-input">
                    </div>
                    <div>
                        <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">End Date</label>
                        <input id="endDate" type="date" onchange="renderTrips()" class="trix-input">
                    </div>
                    <div>
                        <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Trip Status</label>
                        <select id="statusFilter" onchange="renderTrips()" class="trix-input cursor-pointer">
                            <option value="">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- TRIP HISTORY TABLE -->
            <section class="trix-anim trix-d6 trix-card overflow-hidden rounded-2xl border border-[#D6DEEA] bg-white">
                <div class="flex flex-col justify-between gap-3 border-b border-[#E2E7EF] px-6 py-5 sm:flex-row sm:items-center">
                    <div>
                        <h3 class="text-sm font-bold text-[#1C2A40]">Historical Trip Records</h3>
                        <p class="mt-1 text-[11px] text-slate-500">List of previously recorded driver journeys</p>
                    </div>
                    <span id="recordCount" class="inline-flex items-center gap-1.5 self-start rounded-full bg-indigo-50 px-3 py-1.5 text-[11px] font-bold text-indigo-700 ring-1 ring-indigo-200 sm:self-auto">
                        <span class="trix-live-dot h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                        0 Records
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1100px] text-left">
                        <thead class="bg-[#F2F5F9]">
                            <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                <th class="px-6 py-4">Trip ID</th>
                                <th class="px-6 py-4">Driver</th>
                                <th class="px-6 py-4">Vehicle</th>
                                <th class="px-6 py-4">Destination</th>
                                <th class="px-6 py-4">Start Time</th>
                                <th class="px-6 py-4">End Time</th>
                                <th class="px-6 py-4">Duration</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tripTableBody" class="divide-y divide-[#EEF2F7] text-sm">
                        </tbody>
                    </table>

                    <div id="emptyState" class="hidden px-6 py-16 text-center">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-indigo-50 via-white to-cyan-50 text-indigo-500 ring-1 ring-indigo-100 shadow-sm">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-slate-700">No Trip History Found</h4>
                        <p class="mt-1.5 text-xs text-slate-500">There are no trip records matching your filters.</p>
                    </div>
                </div>
            </section>

            <!-- RECENTLY DELETED (Warna disamakan) -->
            <section class="trix-anim trix-d6 trix-card overflow-hidden rounded-2xl border border-[#D6DEEA] bg-white">
                <div class="flex flex-col justify-between gap-4 border-b border-[#E2E7EF] px-6 py-5 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 ring-1 ring-slate-200">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-[#1C2A40]">Recently Deleted</h3>
                            <p class="mt-1 text-[11px] text-slate-500">Deleted trips are saved for 7 days and can only be restored from this page.</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 self-start rounded-full bg-rose-50 px-3 py-1.5 text-[11px] font-bold text-rose-700 ring-1 ring-rose-200 sm:self-auto">
                        <span class="trix-live-dot h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                        {{ $deletedTrips->count() }} Deleted
                    </span>
                </div>

                @if(session('success'))
                    <div class="mx-6 mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="mx-6 mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ session('error') }}</div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-[#F2F5F9]">
                            <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                <th class="px-6 py-4">Trip ID</th>
                                <th class="px-6 py-4">Driver</th>
                                <th class="px-6 py-4">Deleted At</th>
                                <th class="px-6 py-4">Expires At</th>
                                <th class="px-6 py-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EEF2F7]">
                        @forelse($deletedTrips as $deletedTrip)
                            <tr class="trix-deleted-row">
                                <td class="px-6 py-4 font-semibold text-[#1C2A40]">{{ $deletedTrip->trip_code }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $deletedTrip->driver->name ?? 'Driver archived' }}</td>
                                <td class="px-6 py-4 text-slate-500">{{ $deletedTrip->deleted_at?->format('d M Y H:i') ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-50 px-2.5 py-1 text-[10px] font-bold text-orange-700 ring-1 ring-orange-200">
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                                        {{ $deletedTrip->deleted_at?->copy()->addDays(7)->format('d M Y H:i') ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center gap-2">
                                        <!-- Gunakan tombol type="button" agar tidak submit langsung -->
                                        <button type="button" onclick="confirmRestore('{{ route('hr.trip-history.restore', $deletedTrip->id) }}')" class="trix-btn-restore inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-2 text-[11px] font-bold text-emerald-700 ring-1 ring-emerald-200 hover:bg-emerald-100">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
                                            Restore
                                        </button>
                                        <button type="button" onclick="confirmForceDelete('{{ route('hr.trip-history.force-delete', $deletedTrip->id) }}')" class="trix-btn-delete inline-flex items-center gap-1.5 rounded-lg bg-rose-50 px-3 py-2 text-[11px] font-bold text-rose-700 ring-1 ring-rose-200 hover:bg-rose-100">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-16 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-slate-50 via-white to-slate-50 text-slate-400 ring-1 ring-slate-100 shadow-sm">
                                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                    </div>
                                    <p class="mt-4 text-sm font-bold text-slate-700">Recycle Bin is Empty</p>
                                    <p class="mt-1 text-xs text-slate-500">There are no recently deleted trips.</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <footer class="flex flex-col justify-between gap-2 border-t border-[#D6DEEA] pt-5 text-[10px] font-medium tracking-wide text-slate-500 sm:flex-row">
                <p>© {{ date('Y') }} TRIX WAY · PT TRIX INDONESIA</p>
                <p>FLEET OPERATIONS MANAGEMENT SYSTEM</p>
            </footer>
        </div>
    </main>
</div>

<!-- ============ TRIP DETAIL MODAL ============ -->
<div id="detailModal" class="trix-modal">
    <div class="trix-modal-content rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-[#E2E7EF] px-6 py-5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-500 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-[#1C2A40]">Trip Details</h3>
                    <p class="mt-0.5 text-[11px] text-slate-500">Historical journey information</p>
                </div>
            </div>
            <button onclick="closeDetailModal()" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-500">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <div id="detailContent" class="space-y-6 p-6"></div>
        <div class="flex justify-end border-t border-[#E2E7EF] px-6 py-4">
            <button onclick="closeDetailModal()" class="rounded-lg bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-200">Close</button>
        </div>
    </div>
</div>

<!-- ============ DELETE ACTION MODAL (PENGGANTI ALERT) ============ -->
<div id="actionModal" class="trix-modal">
    <div class="trix-modal-content max-w-sm rounded-2xl bg-white shadow-2xl">
        <div class="p-6 text-center">
            <div id="actionModalIcon" class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full ring-4">
                <!-- Icon diisi via JS -->
            </div>
            <h3 id="actionModalTitle" class="text-lg font-bold text-[#1C2A40]">Title</h3>
            <p id="actionModalMessage" class="mt-2 text-sm leading-relaxed text-slate-500">Message</p>
        </div>
        <div class="flex gap-3 border-t border-[#E2E7EF] px-6 py-4">
            <button type="button" onclick="closeActionModal()" class="flex-1 rounded-lg border border-[#D6DEEA] bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                Cancel
            </button>
            <button type="button" id="actionModalConfirmBtn" class="flex-1 rounded-lg px-4 py-2.5 text-sm font-bold text-white shadow-lg transition hover:-translate-y-0.5">
                Confirm
            </button>
        </div>
    </div>
</div>

<script>
    let trips = @json($trips);
    let actionForm = null;

    function escapeHTML(value) {
        return String(value ?? '').replace(/[&<>"']/g, function(character) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[character];
        });
    }

    function formatDateTime(value) {
        if (!value) return '-';
        const date = new Date(value);
        if (isNaN(date.getTime())) return '-';
        return date.toLocaleString('id-ID', {
            day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
        });
    }

    function getDuration(start, end) {
        if (!start || !end) return '-';
        const startTime = new Date(start);
        const endTime = new Date(end);
        if (isNaN(startTime.getTime()) || isNaN(endTime.getTime())) return '-';
        const totalMinutes = Math.max(0, Math.floor((endTime - startTime) / 60000));
        const hours = Math.floor(totalMinutes / 60);
        const minutes = totalMinutes % 60;
        if (hours > 0) return `${hours}h ${minutes}m`;
        return `${minutes}m`;
    }

    function getStatusBadge(status) {
        if (status === 'completed') {
            return `<span class="trix-status-completed"><i class="fa-solid fa-circle-check"></i> Completed</span>`;
        }
        return `<span class="trix-status-cancelled"><i class="fa-solid fa-circle-xmark"></i> Cancelled</span>`;
    }

    function getDestinations(stops) {
        if (!Array.isArray(stops) || stops.length === 0) return '-';
        return stops.map(stop => stop.destination || stop.address || stop.name || '').filter(Boolean).join(' → ');
    }

    function renderTrips() {
        const search = document.getElementById('searchInput').value.toLowerCase();
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        const status = document.getElementById('statusFilter').value;

        const filteredTrips = trips.filter(trip => {
            const driverName = trip.driver?.name || '';
            const vehicleName = trip.vehicle?.plate_number || '';
            const tripId = trip.trip_code || trip.id || '';
            const destinations = getDestinations(trip.stops);

            const searchableText = `${driverName} ${vehicleName} ${tripId} ${destinations}`.toLowerCase();
            const matchesSearch = searchableText.includes(search);

            const tripDate = trip.start_time ? new Date(trip.start_time).toISOString().slice(0, 10) : '';
            const matchesStart = !startDate || (tripDate && tripDate >= startDate);
            const matchesEnd = !endDate || (tripDate && tripDate <= endDate);
            const matchesStatus = !status || trip.status === status;

            return matchesSearch && matchesStart && matchesEnd && matchesStatus;
        });

        const tbody = document.getElementById('tripTableBody');
        const emptyState = document.getElementById('emptyState');

        document.getElementById('recordCount').textContent = `${filteredTrips.length} Records`;

        if (filteredTrips.length === 0) {
            tbody.innerHTML = '';
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');

            tbody.innerHTML = filteredTrips.map(trip => {
                const destinations = getDestinations(trip.stops);
                const driverName = trip.driver?.name || '-';
                const plateNumber = trip.vehicle?.plate_number || '';

                return `
                    <tr class="trix-row">
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700 ring-1 ring-indigo-100">
                                #${escapeHTML(trip.trip_code || trip.id || '-')}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-white text-xs font-bold ring-2 ring-indigo-100">
                                    ${escapeHTML((driverName[0] || 'D').toUpperCase())}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-[#1C2A40]">${escapeHTML(driverName)}</p>
                                    <p class="truncate text-[11px] text-slate-500">${escapeHTML(trip.driver?.employee_id || '-')}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4"><span class="font-semibold text-[#1C2A40]">${escapeHTML(plateNumber || '-')}</span></td>
                        <td class="max-w-[220px] px-6 py-4"><p class="truncate text-sm text-slate-600" title="${escapeHTML(destinations)}">${escapeHTML(destinations)}</p></td>
                        <td class="px-6 py-4 text-slate-600">${escapeHTML(formatDateTime(trip.start_time))}</td>
                        <td class="px-6 py-4 text-slate-600">${escapeHTML(formatDateTime(trip.end_time))}</td>
                        <td class="px-6 py-4"><span class="font-semibold text-[#1C2A40]">${escapeHTML(getDuration(trip.start_time, trip.end_time))}</span></td>
                        <td class="px-6 py-4">${getStatusBadge(trip.status)}</td>
                        <td class="px-6 py-4 text-center">
                            <button onclick="showTripDetail(${Number(trip.id)})" class="trix-action inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-600 hover:bg-indigo-100">
                                <i class="fa-solid fa-eye"></i> Details
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }
        updateStatistics();
    }

    function updateStatistics() {
        const completedTrips = trips.filter(trip => trip.status === 'completed');
        const currentMonth = new Date().getMonth();
        const currentYear = new Date().getFullYear();

        const monthlyTrips = completedTrips.filter(trip => {
            if (!trip.end_time) return false;
            const date = new Date(trip.end_time);
            return date.getMonth() === currentMonth && date.getFullYear() === currentYear;
        });

        document.getElementById('totalCompleted').textContent = completedTrips.length;
        document.getElementById('thisMonth').textContent = monthlyTrips.length;

        const totalDistance = trips.reduce((total, trip) => total + (Number(trip.distance_km) || 0), 0);
        document.getElementById('totalDistance').textContent = totalDistance > 0 ? `${totalDistance.toFixed(1)} km` : '—';

        const durations = completedTrips.filter(trip => trip.start_time && trip.end_time).map(trip => {
            const start = new Date(trip.start_time).getTime();
            const end = new Date(trip.end_time).getTime();
            return Number.isFinite(start) && Number.isFinite(end) && end >= start ? (end - start) / 60000 : null;
        }).filter(value => value !== null);

        if (durations.length) {
            const averageMinutes = Math.round(durations.reduce((total, value) => total + value, 0) / durations.length);
            const hours = Math.floor(averageMinutes / 60);
            const minutes = averageMinutes % 60;
            document.getElementById('averageDuration').textContent = hours > 0 ? `${hours}h ${minutes}m` : `${minutes}m`;
        } else {
            document.getElementById('averageDuration').textContent = '—';
        }
    }

    function showTripDetail(id) {
        const trip = trips.find(item => Number(item.id) === Number(id));
        if (!trip) return;

        const destinations = Array.isArray(trip.stops) ? trip.stops : [];
        const destinationHTML = destinations.length ? destinations.map((stop, index) => `
            <div class="flex gap-3">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-[11px] font-bold text-white ring-2 ring-indigo-100">${index + 1}</div>
                <div>
                    <p class="text-sm font-semibold text-[#1C2A40]">${escapeHTML(stop.destination || stop.address || stop.name || 'Destination')}</p>
                    <p class="text-[11px] text-slate-500">${escapeHTML(stop.status || 'Recorded stop')}</p>
                </div>
            </div>
        `).join('') : `<p class="text-sm text-slate-500">No destination records available.</p>`;

        document.getElementById('detailContent').innerHTML = `
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Trip Reference</p>
                    <h4 class="mt-1 text-xl font-bold text-[#1C2A40]">#${escapeHTML(trip.trip_code || trip.id)}</h4>
                </div>
                ${getStatusBadge(trip.status)}
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-[#E2E7EF] bg-[#FAFBFD] p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Driver</p>
                    <p class="mt-1 font-semibold text-[#1C2A40]">${escapeHTML(trip.driver?.name || '-')}</p>
                    <p class="mt-1 text-[11px] text-slate-500">${escapeHTML(trip.driver?.employee_id || '-')}</p>
                </div>
                <div class="rounded-xl border border-[#E2E7EF] bg-[#FAFBFD] p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Vehicle</p>
                    <p class="mt-1 font-semibold text-[#1C2A40]">${escapeHTML(trip.vehicle?.plate_number || '-')}</p>
                </div>
                <div class="rounded-xl border border-[#E2E7EF] bg-[#FAFBFD] p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Start Time</p>
                    <p class="mt-1 font-semibold text-[#1C2A40]">${escapeHTML(formatDateTime(trip.start_time))}</p>
                </div>
                <div class="rounded-xl border border-[#E2E7EF] bg-[#FAFBFD] p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">End Time</p>
                    <p class="mt-1 font-semibold text-[#1C2A40]">${escapeHTML(formatDateTime(trip.end_time))}</p>
                </div>
                <div class="rounded-xl border border-[#E2E7EF] bg-[#FAFBFD] p-4 sm:col-span-2">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Trip Duration</p>
                    <p class="mt-1 font-semibold text-[#1C2A40]">${escapeHTML(getDuration(trip.start_time, trip.end_time))}</p>
                </div>
            </div>

            <div>
                <h4 class="mb-4 flex items-center gap-2 text-sm font-bold text-[#1C2A40]">
                    <svg class="h-4 w-4 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                    Destination Timeline
                </h4>
                <div class="space-y-4">${destinationHTML}</div>
            </div>

            ${trip.notes ? `<div class="rounded-xl border border-[#E2E7EF] bg-[#FAFBFD] p-4"><p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Trip Notes</p><p class="mt-2 text-sm text-slate-600">${escapeHTML(trip.notes)}</p></div>` : ''}
        `;

        document.getElementById('detailModal').classList.add('show');
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.remove('show');
    }

    function resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('startDate').value = '';
        document.getElementById('endDate').value = '';
        document.getElementById('statusFilter').value = '';
        renderTrips();
    }

    // ============ CUSTOM MODAL LOGIC (PENGGANTI ALERT) ============
    
    // Buat form dinamis secara tersembunyi untuk menampung request POST
    function createHiddenForm(action, method) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action;
        form.style.display = 'none';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = method;

        form.appendChild(csrfToken);
        form.appendChild(methodInput);
        document.body.appendChild(form);
        return form;
    }

    function confirmRestore(action) {
        actionForm = createHiddenForm(action, 'PATCH');
        
        // Set Tampilan Modal
        const iconBox = document.getElementById('actionModalIcon');
        iconBox.className = 'mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 text-emerald-500 ring-4 ring-emerald-100';
        iconBox.innerHTML = `<svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>`;
        
        document.getElementById('actionModalTitle').textContent = 'Restore Trip?';
        document.getElementById('actionModalMessage').textContent = 'This travel data will be restored and returned to the main history list.';
        
        const btn = document.getElementById('actionModalConfirmBtn');
        btn.className = 'flex-1 rounded-lg bg-emerald-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/30 transition hover:bg-emerald-600 hover:-translate-y-0.5';
        btn.textContent = 'Yes, Restore';

        document.getElementById('actionModal').classList.add('show');
    }

    function confirmForceDelete(action) {
        actionForm = createHiddenForm(action, 'DELETE');
        
        // Set Tampilan Modal
        const iconBox = document.getElementById('actionModalIcon');
        iconBox.className = 'mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-rose-50 text-rose-500 ring-4 ring-rose-100';
        iconBox.innerHTML = `<svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>`;
        
        document.getElementById('actionModalTitle').textContent = 'Delete Permanently?';
        document.getElementById('actionModalMessage').textContent = 'This travel data along with its GPS history and details will be permanently deleted. This action cannot be undone.';
        
        const btn = document.getElementById('actionModalConfirmBtn');
        btn.className = 'flex-1 rounded-lg bg-rose-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-rose-500/30 transition hover:bg-rose-600 hover:-translate-y-0.5';
        btn.textContent = 'Yes, Delete';

        document.getElementById('actionModal').classList.add('show');
    }

    function closeActionModal() {
        document.getElementById('actionModal').classList.remove('show');
        if (actionForm) {
            actionForm.remove(); // Hapus form dari DOM jika dibatalkan
            actionForm = null;
        }
    }

    document.getElementById('actionModalConfirmBtn').addEventListener('click', function() {
        if (actionForm) {
            actionForm.submit();
        }
    });

    // ============ EVENT LISTENERS ============

    document.addEventListener('DOMContentLoaded', function() {
        renderTrips();
    });

    document.getElementById('detailModal').addEventListener('click', function(event) {
        if (event.target === this) closeDetailModal();
    });
    
    document.getElementById('actionModal').addEventListener('click', function(event) {
        if (event.target === this) closeActionModal();
    });
</script>

</body>
</html>