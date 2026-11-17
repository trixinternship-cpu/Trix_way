<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Notifications | TRIX WAY</title>

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



        /* FIX PADDING SEARCH INPUT */

        #searchInput { padding-left: 2.5rem !important; }



        /* ============ NOTIFICATION ITEM (DISAMAKAN DENGAN TABEL TRIP) ============ */

        .trix-notif {

            transition: background .15s ease;

            border-bottom: 1px solid #EEF2F7;

        }

        .trix-notif:last-child {

            border-bottom: none;

        }

        .trix-notif:hover { background: #F8FAFC; }



        /* Penanda unread yang lebih tegas namun bersih */

        .trix-notif.unread {

            background: #F4F6FB;

            border-left: 3px solid #6366F1;

        }

        .trix-notif.unread:hover {

            background: #EEF2FB;

        }



        /* ============ FILTER BUTTON ============ */

        .trix-filter {

            transition: all .2s cubic-bezier(.4,0,.2,1);

        }

        .trix-filter:hover:not(.active) {

            background: #F1F5F9;

        }



        /* ============ MODAL ============ */

        .trix-modal {

            display: none; position: fixed; inset: 0; z-index: 100;

            background: rgba(15,23,42,.55); backdrop-filter: blur(6px);

            align-items: center; justify-content: center; padding: 20px;

        }

        .trix-modal.show { display: flex; }

        .trix-modal-content {

            width: 100%; max-width: 560px; max-height: 90vh; overflow-y: auto;

            animation: trixFadeUp .3s cubic-bezier(.4,0,.2,1);

        }



        /* ============ CONFIRM MODAL (DELETE) ============ */

        .trix-confirm {

            display: none; position: fixed; inset: 0; z-index: 150;

            background: rgba(15,23,42,.55); backdrop-filter: blur(6px);

            align-items: center; justify-content: center; padding: 20px;

        }

        .trix-confirm.show { display: flex; }

        .trix-confirm-content {

            width: 100%; max-width: 400px;

            animation: trixFadeUp .3s cubic-bezier(.4,0,.2,1);

        }



        /* ============ ACTION BTN (DISAMAKAN DENGAN TABEL) ============ */

        .trix-action { 

            transition: all .15s ease; 

            border-width: 1px;

            border-style: solid;

        }

        .trix-action:hover { 

            transform: translateY(-1px); 

            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); 

        }



        /* ============ TOAST ANIMATION ============ */

        @keyframes trixSlideInRight { from { opacity: 0; transform: translateX(100%); } to { opacity: 1; transform: translateX(0); } }

        @keyframes trixFadeOut { from { opacity: 1; } to { opacity: 0; } }

        .trix-toast { animation: trixSlideInRight .3s cubic-bezier(.4,0,.2,1) forwards; }

        .trix-toast.hiding { animation: trixFadeOut .3s ease forwards; }



        /* ============ REDUCED MOTION ============ */

        @media (prefers-reduced-motion: reduce) {

            .trix-anim, .trix-toast { animation: none !important; opacity: 1 !important; }

            .trix-card, .trix-card-hover:hover, .trix-action:hover, .trix-notif { transition: none; transform: none; }

        }

    </style>

</head>



<body class="min-h-screen bg-[#F4F7FB] text-[#1C2A40] antialiased selection:bg-indigo-500/20">



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

                <a href="{{ url('/hr/trip-history') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition-colors duration-200 hover:bg-white/10 hover:text-white">

                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>

                    Trip History

                </a>

            </nav>

            <p class="mb-4 mt-9 px-4 text-[10px] font-bold tracking-[2px] text-slate-400">PREFERENCES</p>

            <nav>

                <a href="{{ url('/hr/notifications') }}" class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-950/50 ring-1 ring-white/15">

                    <span class="relative">

                        <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>

                    </span>

                    Notifications

                    <span id="sidebarUnread" class="ml-auto inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-rose-500 px-1.5 text-[10px] font-bold text-white ring-2 ring-indigo-500">

                        0

                    </span>

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

        <header class="sticky top-0 z-20 flex h-[76px] items-center justify-between border-b border-[#D6DEEA] bg-white px-5 sm:px-8 lg:px-10">

            <div>

                <p class="text-[10px] font-bold tracking-[2px] bg-gradient-to-r from-indigo-600 to-cyan-600 bg-clip-text text-transparent">TRIX WAY / WORKSPACE</p>

                <h2 class="mt-1 text-sm font-bold text-[#1C2A40]">Notifications</h2>

            </div>

            <div class="flex items-center gap-5">

                <button class="relative flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-indigo-50 hover:text-indigo-600">

                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>

                    <span id="topbarUnread" class="absolute -right-1 -top-1 inline-flex h-4 min-w-[16px] items-center justify-center rounded-full bg-rose-500 px-1 text-[9px] font-bold text-white ring-2 ring-white">

                        0

                    </span>

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

                        Notifi<span class="trix-gradient-text">cations</span>

                    </h1>

                    <p class="mt-2 text-sm leading-6 text-slate-500">Monitor important updates and activities across your fleet.</p>

                </div>

                <div class="flex flex-wrap items-center gap-3 self-start md:self-auto">
                    <button type="button" onclick="markAllAsRead()" class="group inline-flex items-center gap-2.5 rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-bold text-white shadow-md shadow-indigo-500/20 transition-all hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 active:scale-95">
                        <svg class="h-4 w-4 transition-transform group-hover:rotate-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5M20 12l-9 9-5-5"/></svg>
                        Mark All as Read
                    </button>

                    <button type="button" onclick="openDeleteAllConfirm()" class="inline-flex items-center gap-2.5 rounded-xl bg-rose-600 px-5 py-3.5 text-sm font-bold text-white shadow-md shadow-rose-500/20 transition-all hover:bg-rose-700 hover:shadow-lg hover:shadow-rose-500/30 active:scale-95">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4h8v2m-9 0 1 14h8l1-14M10 10v6m4-6v6"/></svg>
                        Delete All
                    </button>
                </div>

            </section>



            <!-- STATISTICS -->

            <section class="grid grid-cols-1 gap-5 sm:grid-cols-3">

                <div class="trix-anim trix-d2 trix-card trix-card-hover rounded-2xl border border-[#E2E7EF] bg-white p-6">

                    <div class="flex items-start justify-between">

                        <div class="min-w-0">

                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total</p>

                            <h3 id="totalNotifications" class="trix-num mt-3 text-[36px] font-bold leading-none text-indigo-600">0</h3>

                        </div>

                        <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100">

                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>

                        </div>

                    </div>

                    <p class="mt-4 flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-indigo-400"></span>All notifications</p>

                </div>

                <div class="trix-anim trix-d3 trix-card trix-card-hover rounded-2xl border border-[#E2E7EF] bg-white p-6">

                    <div class="flex items-start justify-between">

                        <div class="min-w-0">

                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Unread</p>

                            <h3 id="unreadNotifications" class="trix-num mt-3 text-[36px] font-bold leading-none text-orange-600">0</h3>

                        </div>

                        <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-orange-50 text-orange-600 border border-orange-100">

                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>

                        </div>

                    </div>

                    <p class="mt-4 flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-orange-400"></span>Awaiting attention</p>

                </div>

                <div class="trix-anim trix-d4 trix-card trix-card-hover rounded-2xl border border-[#E2E7EF] bg-white p-6">

                    <div class="flex items-start justify-between">

                        <div class="min-w-0">

                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Read</p>

                            <h3 id="readNotifications" class="trix-num mt-3 text-[36px] font-bold leading-none text-emerald-600">0</h3>

                        </div>

                        <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100">

                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7l9 6 9-6"/><path d="M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7"/><path d="m9 12 2 2 4-4"/></svg>

                        </div>

                    </div>

                    <p class="mt-4 flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>Already reviewed</p>

                </div>

            </section>



            <!-- NOTIFICATION CENTER -->

            <section class="trix-anim trix-d5 trix-card overflow-hidden rounded-2xl border border-[#E2E7EF] bg-white">

                <div class="flex flex-col justify-between gap-4 border-b border-[#E2E7EF] px-6 py-5 md:flex-row md:items-center">

                    <div>

                        <h3 class="text-sm font-bold text-[#1C2A40]">Notification Center</h3>

                        <p class="mt-1 text-[11px] text-slate-500">All system alerts and trip activity updates</p>

                    </div>

                    <div class="flex flex-wrap gap-2">

                        <button onclick="setFilter('all')" id="filterAll" class="trix-filter active rounded-lg bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition">All</button>

                        <button onclick="setFilter('unread')" id="filterUnread" class="trix-filter rounded-lg bg-slate-100 px-4 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-200">Unread</button>

                        <button onclick="setFilter('read')" id="filterRead" class="trix-filter rounded-lg bg-slate-100 px-4 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-200">Read</button>

                    </div>

                </div>



                <!-- SEARCH & FILTER -->

                <div class="grid grid-cols-1 gap-3 border-b border-[#E2E7EF] bg-[#FAFBFC] px-6 py-4 md:grid-cols-2">

                    <div class="relative flex items-center">

                        <svg class="pointer-events-none absolute left-3.5 z-10 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>

                        <input id="searchInput" type="text" oninput="renderNotifications()" placeholder="Search trip code or destination..." class="trix-input bg-white w-full border-[#E2E7EF]">

                    </div>

                    <select id="categoryFilter" onchange="renderNotifications()" class="trix-input bg-white cursor-pointer border-[#E2E7EF]">

                        <option value="">All Categories</option>

                        <option value="trip_started">Trip Started</option>

                        <option value="destination">Destination Update</option>

                        <option value="trip_completed">Trip Completed</option>

                        <option value="alert">Alerts</option>

                        <option value="system">System</option>

                    </select>

                </div>



                <div id="notificationList" class="flex flex-col"></div>



                <div id="emptyState" class="hidden px-6 py-16 text-center">

                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 text-slate-400 ring-1 ring-slate-200">

                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/><path d="M4 4l16 16"/></svg>

                    </div>

                    <h4 class="text-sm font-bold text-slate-700">No Notifications Found</h4>

                    <p class="mt-1.5 text-xs text-slate-500">There are no notifications matching your current filter.</p>

                </div>

            </section>



            <footer class="flex flex-col justify-between gap-2 border-t border-[#D6DEEA] pt-5 text-[10px] font-medium tracking-wide text-slate-500 sm:flex-row">

                <p>© {{ date('Y') }} TRIX WAY · PT TRIX INDONESIA</p>

                <p>FLEET OPERATIONS MANAGEMENT SYSTEM</p>

            </footer>

        </div>

    </main>

</div>



<!-- ============ NOTIFICATION DETAIL MODAL ============ -->

<div id="detailModal" class="trix-modal">

    <div class="trix-modal-content rounded-2xl bg-white shadow-2xl">

        <div class="flex items-center justify-between border-b border-[#E2E7EF] px-6 py-5">

            <div class="flex items-center gap-3">

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100">

                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>

                </div>

                <div>

                    <h3 class="text-sm font-bold text-[#1C2A40]">Notification Details</h3>

                    <p class="mt-0.5 text-[11px] text-slate-500">Notification information</p>

                </div>

            </div>

            <button onclick="closeDetailModal()" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600">

                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 6l12 12M18 6L6 18"/></svg>

            </button>

        </div>

        <div id="detailContent" class="space-y-5 p-6"></div>

        <div class="flex justify-end border-t border-[#E2E7EF] bg-[#FAFBFC] px-6 py-4">

            <button onclick="closeDetailModal()" class="rounded-lg bg-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-300">Close</button>

        </div>

    </div>

</div>



<!-- ============ DELETE CONFIRM MODAL ============ -->

<div id="deleteConfirmModal" class="trix-confirm">

    <div class="trix-confirm-content rounded-2xl bg-white p-6 shadow-2xl border border-[#E2E7EF]">

        <div class="flex items-start gap-4">

            <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 border border-rose-100">

                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">

                    <path d="M3 6h18M8 6V4h8v2m-9 0 1 14h8l1-14M10 10v6m4-6v6"/>

                </svg>

            </div>

            <div class="min-w-0">

                <h3 class="text-sm font-bold text-[#1C2A40]">Delete Notification?</h3>

                <p class="mt-1.5 text-xs leading-relaxed text-slate-500">

                    This notification will be permanently deleted and cannot be recovered.

                </p>

            </div>

        </div>

        <div class="mt-6 flex justify-end gap-2">

            <button type="button" onclick="closeDeleteConfirm()"

                class="rounded-lg bg-white border border-[#E2E7EF] px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">

                Cancel

            </button>

            <button type="button" id="confirmDeleteBtn"

                class="rounded-lg bg-rose-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-rose-700">

                Delete

            </button>

        </div>

    </div>

</div>





<!-- ============ DELETE ALL CONFIRM MODAL ============ -->
<div id="deleteAllConfirmModal" class="trix-confirm">
    <div class="trix-confirm-content rounded-2xl bg-white p-6 shadow-2xl border border-[#E2E7EF]">
        <div class="flex items-start gap-4">
            <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 border border-rose-100">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M3 6h18M8 6V4h8v2m-9 0 1 14h8l1-14M10 10v6m4-6v6"/>
                </svg>
            </div>
            <div class="min-w-0">
                <h3 class="text-sm font-bold text-[#1C2A40]">Delete All Notifications?</h3>
                <p class="mt-1.5 text-xs leading-relaxed text-slate-500">
                    All your notifications will be permanently deleted and cannot be recovered. Are you sure you want to continue?
                </p>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-2">
            <button type="button" onclick="closeDeleteAllConfirm()"
                class="rounded-lg bg-white border border-[#E2E7EF] px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                Cancel
            </button>
            <button type="button" id="confirmDeleteAllBtn" onclick="confirmDeleteAll()"
                class="rounded-lg bg-rose-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-rose-700">
                Delete All
            </button>
        </div>
    </div>
</div>

<script>

    // ============ STATE ============

    let notifications = [];

    let activeFilter = 'all';

    let hasCompletedFirstFetch = false;

    let knownNotificationIds = new Set();

    let pendingDeleteId = null;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');



    // ============ TOAST ============

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



    // Popup khusus saat notifikasi baru diterima dari server.

    function showIncomingNotification(item) {

        const container = document.getElementById('toastContainer');

        const toast = document.createElement('div');

        toast.className = 'trix-toast flex w-[340px] max-w-[calc(100vw-2rem)] items-start gap-3 rounded-xl border border-indigo-200 bg-white p-4 shadow-2xl pointer-events-auto';



        const icon = document.createElement('div');

        icon.className = 'flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600';

        icon.innerHTML = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>';



        const content = document.createElement('div');

        content.className = 'min-w-0 flex-1';

        const heading = document.createElement('p');

        heading.className = 'text-sm font-bold text-[#1C2A40]';

        heading.textContent = item.title || 'Notifikasi Baru';

        const message = document.createElement('p');

        message.className = 'mt-1 text-xs leading-relaxed text-slate-600';

        message.textContent = item.message || 'Ada notifikasi baru untuk Anda.';

        content.append(heading, message);



        const close = document.createElement('button');

        close.type = 'button';

        close.className = 'text-slate-400 hover:text-slate-700';

        close.setAttribute('aria-label', 'Tutup notifikasi');

        close.textContent = '×';

        close.addEventListener('click', () => toast.remove());



        toast.append(icon, content, close);

        container.appendChild(toast);

        setTimeout(() => { toast.classList.add('hiding'); setTimeout(() => toast.remove(), 300); }, 6000);

    }



    // ============ HELPERS ============

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



    function getNotificationIcon(category) {

        // SVG inline

        const carSvg = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 13h18l-1.5-5h-15z"/><path d="M5 13v5h14v-5M7 18v2m10-2v2M7 8l1-3h8l1 3"/><circle cx="7" cy="15.5" r=".8" fill="currentColor"/><circle cx="17" cy="15.5" r=".8" fill="currentColor"/></svg>';

        const pinSvg = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s-7-5.686-7-11a7 7 0 0 1 14 0c0 5.314-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>';

        const checkSvg = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg>';

        const alertSvg = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4M12 17h.01"/></svg>';

        const gearSvg = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>';



        const catStr = String(category || '').toLowerCase();



        if (catStr.includes('alert') || catStr.includes('warning')) {

            return { svg: alertSvg, color: 'bg-rose-50 text-rose-600 border border-rose-200' };

        }

        if (catStr.includes('complete') || catStr.includes('finish') || catStr.includes('arrive') || catStr.includes('tiba')) {

            return { svg: checkSvg, color: 'bg-emerald-50 text-emerald-600 border border-emerald-200' };

        }

        if (catStr.includes('destination') || catStr.includes('tujuan')) {

            return { svg: pinSvg, color: 'bg-cyan-50 text-cyan-600 border border-cyan-200' };

        }

        if (catStr.includes('trip') || catStr.includes('start') || catStr.includes('jalan') || catStr.includes('driver')) {

            return { svg: carSvg, color: 'bg-indigo-50 text-indigo-600 border border-indigo-200' };

        }

        return { svg: gearSvg, color: 'bg-slate-100 text-slate-600 border border-slate-200' };

    }



    function getCategoryLabel(category) {

        const labels = {

            trip_started: 'Trip Started', destination: 'Destination Update',

            trip_completed: 'Trip Completed', alert: 'Alert', system: 'System'

        };

        return labels[category] || 'Notification';

    }



    // ============ FETCH API ============

    async function fetchNotifications() {

        try {

            const response = await fetch("{{ route('hr.notifications.data') }}", {

                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }

            });

            if (!response.ok) throw new Error('Failed to fetch notifications');



            const data = await response.json();

            const incoming = (data.notifications || data.data || []).map(item => ({

                ...item,

                is_read: item.is_read ?? (item.read_at !== null)

            }));



            if (hasCompletedFirstFetch) {

                incoming

                    .filter(item => !item.is_read && !knownNotificationIds.has(String(item.id)))

                    .reverse()

                    .forEach(item => showIncomingNotification(item));

            } else {

                const unreadOnOpen = incoming.filter(item => !item.is_read);

                if (unreadOnOpen.length > 0) {

                    showIncomingNotification({

                        title: 'Ada notifikasi belum dibaca',

                        message: `Anda memiliki ${unreadOnOpen.length} notifikasi yang belum dibaca.`

                    });

                }

                hasCompletedFirstFetch = true;

            }



            notifications = incoming;

            knownNotificationIds = new Set(incoming.map(item => String(item.id)));

            renderNotifications();

        } catch (error) {

            console.error(error);

        }

    }



    async function markAsRead(id) {

        try {

            const response = await fetch(`/hr/notifications/${id}/read`, {

                method: 'PATCH',

                headers: {

                    'Content-Type': 'application/json',

                    'Accept': 'application/json',

                    'X-CSRF-TOKEN': csrfToken,

                    'X-Requested-With': 'XMLHttpRequest'

                }

            });

            if (!response.ok) throw new Error('Failed to mark as read');



            const item = notifications.find(n => Number(n.id) === Number(id));

            if (item) item.is_read = true;



            renderNotifications();

        } catch (error) {

            showToast('Failed to mark notification as read.', 'error');

        }

    }



    // ============ DELETE (CONFIRM MODAL) ============

    function deleteNotification(id) {

    pendingDeleteId = id;

    document.getElementById('deleteConfirmModal').classList.add('show');



    const confirmBtn = document.getElementById('confirmDeleteBtn');



    confirmBtn.onclick = async () => {

        // Ambil ID terlebih dahulu sebelum modal ditutup

        const targetId = pendingDeleteId;



        if (!targetId) return;



        closeDeleteConfirm();



        try {

            const response = await fetch(`/hr/notifications/${targetId}`, {

                method: 'DELETE',

                headers: {

                    'Accept': 'application/json',

                    'X-CSRF-TOKEN': csrfToken,

                    'X-Requested-With': 'XMLHttpRequest'

                }

            });



            if (!response.ok) {

                const errorData = await response.json().catch(() => ({}));

                throw new Error(errorData.message || `HTTP ${response.status}`);

            }



            notifications = notifications.filter(

                item => Number(item.id) !== Number(targetId)

            );



            renderNotifications();

            showToast('Notification deleted.', 'success');



        } catch (error) {

            console.error('Delete notification error:', error);

            showToast(error.message || 'Failed to delete notification.', 'error');

        }

    };

}



    function closeDeleteConfirm() {

        document.getElementById('deleteConfirmModal').classList.remove('show');

        pendingDeleteId = null;

    }



    
    function openDeleteAllConfirm() {
        if (notifications.length === 0) {
            showToast('There are no notifications to delete.', 'error');
            return;
        }

        document.getElementById('deleteAllConfirmModal').classList.add('show');
    }

    function closeDeleteAllConfirm() {
        document.getElementById('deleteAllConfirmModal').classList.remove('show');
    }

    async function confirmDeleteAll() {
        const button = document.getElementById('confirmDeleteAllBtn');

        if (notifications.length === 0) {
            closeDeleteAllConfirm();
            return;
        }

        button.disabled = true;
        button.textContent = 'Deleting...';

        try {
            const response = await fetch("{{ route('hr.notifications.destroy-all') }}", {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(result.message || 'Failed to delete all notifications.');
            }

            const deletedCount = Number(result.deleted ?? notifications.length);

            notifications = [];
            knownNotificationIds.clear();

            renderNotifications();
            closeDeleteAllConfirm();

            showToast(`${deletedCount} notifications deleted successfully.`, 'success');
        } catch (error) {
            console.error('Delete all notifications error:', error);
            showToast(error.message || 'Failed to delete all notifications.', 'error');
        } finally {
            button.disabled = false;
            button.textContent = 'Delete All';
        }
    }

async function markAllAsRead() {

        try {

            const response = await fetch("{{ route('hr.notifications.read-all') }}", {

                method: 'PATCH',

                headers: {

                    'Content-Type': 'application/json',

                    'Accept': 'application/json',

                    'X-CSRF-TOKEN': csrfToken,

                    'X-Requested-With': 'XMLHttpRequest'

                }

            });

            if (!response.ok) throw new Error('Failed to mark all as read');



            notifications.forEach(n => n.is_read = true);

            renderNotifications();

            showToast('All notifications marked as read.', 'success');

        } catch (error) {

            showToast('Failed to mark all as read.', 'error');

        }

    }



    // ============ RENDER LOGIC ============

    function updateStatistics() {

        const unread = notifications.filter(item => !item.is_read).length;

        const read = notifications.length - unread;



        document.getElementById('totalNotifications').textContent = notifications.length;

        document.getElementById('unreadNotifications').textContent = unread;

        document.getElementById('readNotifications').textContent = read;



        const sidebarBadge = document.getElementById('sidebarUnread');

        const topbarBadge = document.getElementById('topbarUnread');



        sidebarBadge.textContent = unread;

        topbarBadge.textContent = unread;



        if (unread === 0) {

            sidebarBadge.classList.add('hidden');

            topbarBadge.classList.add('hidden');

        } else {

            sidebarBadge.classList.remove('hidden');

            topbarBadge.classList.remove('hidden');

        }

    }



    function setFilter(filter) {

        activeFilter = filter;



        document.querySelectorAll('.trix-filter').forEach(button => {

            button.classList.remove('active', 'bg-indigo-600', 'text-white', 'shadow-sm');

            button.classList.add('bg-slate-100', 'text-slate-600', 'hover:bg-slate-200');

        });



        const activeButton = { all: 'filterAll', unread: 'filterUnread', read: 'filterRead' };

        const button = document.getElementById(activeButton[filter]);



        button.classList.remove('bg-slate-100', 'text-slate-600', 'hover:bg-slate-200');

        button.classList.add('active', 'bg-indigo-600', 'text-white', 'shadow-sm');



        renderNotifications();

    }



    function renderNotifications() {

        const search = document.getElementById('searchInput').value.toLowerCase();

        const category = document.getElementById('categoryFilter').value;



        const filtered = notifications.filter(item => {

            const searchableText = `${item.title || ''} ${item.message || ''} ${item.driver_name || ''} ${item.trip_code || ''}`.toLowerCase();

            const matchesSearch = searchableText.includes(search);

            const matchesCategory = !category || item.category === category;



            let matchesReadStatus = true;

            if (activeFilter === 'unread') matchesReadStatus = !item.is_read;

            if (activeFilter === 'read') matchesReadStatus = item.is_read;



            return matchesSearch && matchesCategory && matchesReadStatus;

        });



        const list = document.getElementById('notificationList');

        const emptyState = document.getElementById('emptyState');



        if (filtered.length === 0) {

            list.innerHTML = '';

            emptyState.classList.remove('hidden');

        } else {

            emptyState.classList.add('hidden');



            list.innerHTML = filtered.map(item => {

                const icon = getNotificationIcon(item.category);



                return `

                    <div class="trix-notif ${!item.is_read ? 'unread' : ''} flex flex-col gap-4 p-5 sm:flex-row sm:items-center bg-white hover:bg-[#FAFBFC]">

                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ${icon.color}">

                            ${icon.svg}

                        </div>



                        <div class="min-w-0 flex-1">

                            <div class="flex flex-wrap items-center gap-2">

                                <h4 class="text-sm font-bold text-[#1C2A40]">

                                    ${escapeHTML(item.title || 'Notification')}

                                </h4>

                                ${!item.is_read ? `

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-0.5 text-[10px] font-bold text-indigo-600 border border-indigo-200 shadow-sm">

                                        <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>

                                        UNREAD

                                    </span>

                                ` : ''}

                            </div>



                            <p class="mt-1 text-sm leading-relaxed text-slate-600">

                                ${escapeHTML(item.message || '')}

                            </p>



                            <div class="mt-2.5 flex flex-wrap items-center gap-4 text-[11px] font-medium text-slate-500">

                                <span class="inline-flex items-center gap-1.5">

                                    <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>

                                    ${escapeHTML(formatDateTime(item.created_at))}

                                </span>

                                <span class="inline-flex items-center gap-1.5">

                                    <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41 11 3H4v7l9.59 9.59a2 2 0 0 0 2.82 0l4.18-4.18a2 2 0 0 0 0-2.82z"/><circle cx="7" cy="7" r="1" fill="currentColor"/></svg>

                                    ${escapeHTML(getCategoryLabel(item.category))}

                                </span>

                                ${item.trip_code ? `

                                    <span class="inline-flex items-center gap-1.5">

                                        <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h15l-3-3m4 13H5l3 3"/><path d="M19 7v4M5 17v-4"/></svg>

                                        ${escapeHTML(item.trip_code)}

                                    </span>

                                ` : ''}

                            </div>

                        </div>



                        <div class="flex shrink-0 items-center gap-2">

                            <button onclick="showNotificationDetail(${Number(item.id)})" title="View details" class="trix-action flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 border-indigo-200 hover:bg-indigo-100">

                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>

                            </button>



                            ${!item.is_read ? `

                                <button onclick="markAsRead(${Number(item.id)})" title="Mark as read" class="trix-action flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 border-emerald-200 hover:bg-emerald-100">

                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 4 4L19 6"/></svg>

                                </button>

                            ` : ''}

                            <button onclick="deleteNotification(${Number(item.id)})" title="Delete notification" aria-label="Delete notification" class="trix-action flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600 border-rose-200 hover:bg-rose-100">

                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4h8v2m-9 0 1 14h8l1-14M10 10v6m4-6v6"/></svg>

                            </button>

                        </div>

                    </div>

                `;

            }).join('');

        }



        updateStatistics();

    }



    function showNotificationDetail(id) {

        const item = notifications.find(n => Number(n.id) === Number(id));

        if (!item) return;



        if (!item.is_read) {

            item.is_read = true;

            fetch(`/hr/notifications/${id}/read`, {

                method: 'PATCH',

                headers: {

                    'X-CSRF-TOKEN': csrfToken,

                    'Accept': 'application/json',

                    'X-Requested-With': 'XMLHttpRequest'

                }

            }).then(() => {

                renderNotifications();

            }).catch(console.error);

        }



        const icon = getNotificationIcon(item.category);

        const detailIconSvg = icon.svg.replace('h-5 w-5', 'h-6 w-6');



        document.getElementById('detailContent').innerHTML = `

            <div class="flex items-center gap-4">

                <div class="flex h-14 w-14 items-center justify-center rounded-xl ${icon.color}">

                    ${detailIconSvg}

                </div>

                <div>

                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">

                        ${escapeHTML(getCategoryLabel(item.category))}

                    </p>

                    <h4 class="mt-1 font-bold text-lg text-[#1C2A40]">

                        ${escapeHTML(item.title || 'Notification')}

                    </h4>

                </div>

            </div>



            <div class="rounded-xl border border-[#E2E7EF] bg-[#FAFBFC] p-4">

                <p class="text-sm leading-relaxed text-slate-700">

                    ${escapeHTML(item.message || '-')}

                </p>

            </div>



            <div class="space-y-3">

                <div class="flex justify-between gap-4 border-b border-[#EEF2F7] pb-3 text-sm">

                    <span class="text-slate-500">Date & Time</span>

                    <span class="text-right font-semibold text-[#1C2A40]">

                        ${escapeHTML(formatDateTime(item.created_at))}

                    </span>

                </div>

                <div class="flex justify-between gap-4 border-b border-[#EEF2F7] pb-3 text-sm">

                    <span class="text-slate-500">Trip Reference</span>

                    <span class="font-semibold text-[#1C2A40]">

                        ${escapeHTML(item.trip_code || '-')}

                    </span>

                </div>

                <div class="flex justify-between gap-4 border-b border-[#EEF2F7] pb-3 text-sm">

                    <span class="text-slate-500">Driver</span>

                    <span class="font-semibold text-[#1C2A40]">

                        ${escapeHTML(item.driver_name || '-')}

                    </span>

                </div>

                <div class="flex justify-between gap-4 text-sm">

                    <span class="text-slate-500">Read Status</span>

                    <span class="inline-flex items-center gap-1.5 font-semibold text-emerald-600">

                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>

                        Read

                    </span>

                </div>

            </div>

        `;



        document.getElementById('detailModal').classList.add('show');

    }



    function closeDetailModal() {

        document.getElementById('detailModal').classList.remove('show');

    }



    document.addEventListener('DOMContentLoaded', function() {

        fetchNotifications();

        setInterval(fetchNotifications, 5000);

    });



    document.getElementById('detailModal').addEventListener('click', function(event) {

        if (event.target === this) {

            closeDetailModal();

        }

    });



    document.getElementById('deleteConfirmModal').addEventListener('click', function(event) {

        if (event.target === this) {

            closeDeleteConfirm();

        }

    });

    document.getElementById('deleteAllConfirmModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeDeleteAllConfirm();
        }
    });

</script>



</body>

</html>