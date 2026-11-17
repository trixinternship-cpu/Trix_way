<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Driver Management | TRIX WAY</title>
    <link rel="icon" type="image/png" href="{{ asset('images/trix-logo.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .trix-scroll::-webkit-scrollbar { width: 6px; }
        .trix-scroll::-webkit-scrollbar-track { background: transparent; }
        .trix-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 999px; }
        .trix-card { box-shadow: 0 1px 2px rgba(20,35,60,.06), 0 6px 16px rgba(20,35,60,.05), 0 16px 40px -16px rgba(20,35,60,.10); transition: box-shadow .3s, transform .3s, border-color .3s; }
        .trix-card-hover:hover { box-shadow: 0 2px 4px rgba(20,35,60,.08), 0 12px 28px rgba(20,35,60,.08), 0 28px 56px -20px rgba(20,35,60,.16); transform: translateY(-3px); border-color: #C4CFE4; }
        .trix-num { font-variant-numeric: tabular-nums; letter-spacing: -.03em; }
        @keyframes trixFadeUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
        .trix-anim { opacity: 0; animation: trixFadeUp .55s cubic-bezier(.4,0,.2,1) forwards; }
        .trix-d1 { animation-delay: .05s; } .trix-d2 { animation-delay: .12s; } .trix-d3 { animation-delay: .19s; }
        .trix-d4 { animation-delay: .26s; } .trix-d5 { animation-delay: .33s; } .trix-d6 { animation-delay: .40s; }
        @keyframes trixLive { 0%,100% { opacity: 1; transform: scale(1); } 50% { opacity: .4; transform: scale(.9); } }
        .trix-live-dot { animation: trixLive 1.6s ease-in-out infinite; }
        .trix-gradient-text { background: linear-gradient(135deg,#6366F1 0%,#0891B2 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
        .trix-input { width: 100%; height: 44px; border: 1.5px solid #D6DEEA; border-radius: 11px; padding: 0 12px; font-size: 13px; background: #F8FAFC; color: #1C2A40; outline: none; transition: all .2s; }
        .trix-input:hover { border-color: #B9C8DE; background: #fff; }
        .trix-input:focus { border-color: #6366F1; background: #fff; box-shadow: 0 0 0 4px rgba(99,102,241,.10); }
        .trix-input:disabled { background: #EEF2F7; color: #94A3B8; cursor: not-allowed; }
        textarea.trix-input { height: auto; padding: 10px 12px; }

        /* SEARCH BAR */
        #driverSearch { padding-left: 2.5rem !important; }
        #driverSearch.is-loading { background-image: linear-gradient(90deg,#F8FAFC 25%,#EEF2F7 50%,#F8FAFC 75%); background-size: 200% 100%; animation: trixShimmer 1.2s infinite; }
        #driverSearch:not(:placeholder-shown) ~ .search-hint { opacity: 0; }
        @keyframes trixShimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

        .trix-modal { display: none; position: fixed; inset: 0; z-index: 100; background: rgba(15,23,42,.55); backdrop-filter: blur(6px); align-items: center; justify-content: center; padding: 20px; }
        .trix-modal.show { display: flex; }
        .trix-modal-content { width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; animation: trixFadeUp .3s cubic-bezier(.4,0,.2,1); }
        .trix-row { transition: background .2s ease; } .trix-row:hover { background: #FAFBFD; }
        .trix-action { transition: all .2s; } .trix-action:hover { transform: translateY(-1px) scale(1.05); box-shadow: 0 4px 10px rgba(20,35,60,.10); }
        @keyframes trixSlideInRight { from { opacity: 0; transform: translateX(100%); } to { opacity: 1; transform: translateX(0); } }
        @keyframes trixFadeOut { from { opacity: 1; } to { opacity: 0; } }
        .trix-toast { animation: trixSlideInRight .3s cubic-bezier(.4,0,.2,1) forwards; }
        .trix-toast.hiding { animation: trixFadeOut .3s ease forwards; }
        @media (prefers-reduced-motion: reduce) { .trix-anim,.trix-live-dot,.trix-toast,#driverSearch.is-loading { animation: none !important; opacity: 1 !important; } .trix-card,.trix-card-hover:hover,.trix-action:hover { transition: none; transform: none; } }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-[#EEF2FB] via-[#F1EEFB] to-[#EAF4F8] text-[#1C2A40] antialiased selection:bg-indigo-500/20">
<div id="toastContainer" class="fixed top-6 right-6 z-[200] flex flex-col gap-3 pointer-events-none"></div>

<div class="min-h-screen">
    {{-- ============ SIDEBAR ============ --}}
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-[258px] flex-col bg-gradient-to-b from-[#0F1A2E] via-[#131F38] to-[#0B1424] text-white lg:flex">
        <div class="flex h-[104px] items-center justify-center border-b border-white/10 px-5">
            <div class="flex h-[68px] w-full items-center justify-center rounded-xl bg-white px-4 shadow-md shadow-black/20 ring-1 ring-white/10">
                <img src="{{ asset('images/trix-logo.jpg') }}" alt="Logo TRIX" class="block max-h-[48px] w-full object-contain">
            </div>
        </div>
        <div class="trix-scroll flex-1 overflow-y-auto px-4 py-8">
            <p class="mb-4 px-4 text-[10px] font-bold tracking-[2px] text-slate-400">WORKSPACE</p>
            <nav class="space-y-1.5">
                <a href="{{ url('/hr/dashboard') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                    Dashboard
                </a>
                <a href="{{ url('/hr/live-monitoring') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 6.5 12 3l9 3.5v11L12 21l-9-3.5z"/><path d="m3 6.5 9 4 9-4M12 10.5V21"/></svg>
                    Live Monitoring
                </a>
                <a href="{{ route('hr.drivers.index') }}" class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-950/50 ring-1 ring-white/15">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><path d="M3 20v-1a6 6 0 0 1 12 0v1M16 5.5a3 3 0 0 1 0 5.8M18 14a5 5 0 0 1 3 4.5V20"/></svg>
                    Driver Management
                </a>
                <a href="{{ url('/hr/vehicles') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 13h18l-1.5-5h-15z"/><path d="M5 13v5h14v-5M7 18v2m10-2v2M7 8l1-3h8l1 3"/></svg>
                    Vehicle Management
                </a>
                <a href="{{ url('/hr/trips') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h15l-3-3m4 13H5l3 3"/><path d="M19 7v4M5 17v-4"/></svg>
                    Trip Management
                </a>
                <a href="{{ url('/hr/trip-history') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    Trip History
                </a>
            </nav>
            <p class="mb-4 mt-9 px-4 text-[10px] font-bold tracking-[2px] text-slate-400">PREFERENCES</p>
            <nav>
                <a href="{{ url('/hr/notifications') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                    Notifications
                    <span id="sidebarUnread" class="ml-auto hidden inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-rose-500 px-1.5 text-[10px] font-bold text-white">0</span>
                </a>
            </nav>
        </div>
        <div class="border-t border-white/10 p-4">
            <div class="mb-4 flex items-center gap-3 rounded-xl bg-white/[0.07] p-3 ring-1 ring-white/10">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-sm font-bold text-white ring-1 ring-white/20">{{ strtoupper(substr(auth()->user()->name ?? 'HR', 0, 1)) }}</div>
                <div class="min-w-0">
                    <p class="truncate text-xs font-semibold text-white">{{ auth()->user()->name ?? 'HR Admin' }}</p>
                    <p class="mt-1 text-[10px] text-slate-400">HR Administrator</p>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">@csrf
                <button class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-400 transition hover:bg-rose-500/15 hover:text-rose-300">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10 17l5-5-5-5m5 5H3"/><path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7"/></svg>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>

    {{-- ============ MAIN ============ --}}
    <main class="min-h-screen lg:ml-[258px]">
        <header class="sticky top-0 z-20 flex h-[76px] items-center justify-between border-b border-[#D6DEEA] bg-white/90 px-5 backdrop-blur-md sm:px-8 lg:px-10">
            <div>
                <p class="text-[10px] font-bold tracking-[2px] bg-gradient-to-r from-indigo-600 to-cyan-600 bg-clip-text text-transparent">TRIX WAY / WORKSPACE</p>
                <h2 class="mt-1 text-sm font-bold text-[#1C2A40]">Driver Management</h2>
            </div>
            <div class="flex items-center gap-5">
                <a href="{{ url('/hr/notifications') }}" class="relative flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-indigo-50 hover:text-indigo-600">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                    <span id="topbarUnread" class="absolute -right-1 -top-1 hidden inline-flex h-4 min-w-[16px] items-center justify-center rounded-full bg-rose-500 px-1 text-[9px] font-bold text-white">0</span>
                </a>
                <div class="hidden text-right sm:block">
                    <p class="text-xs font-bold text-[#1C2A40]">{{ auth()->user()->name ?? 'HR Admin' }}</p>
                    <p class="mt-1 text-[10px] font-medium text-slate-500">Human Resources</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-sm font-bold text-white ring-2 ring-white shadow-md shadow-indigo-500/30">{{ strtoupper(substr(auth()->user()->name ?? 'HR', 0, 1)) }}</div>
            </div>
        </header>

        <div class="mx-auto max-w-[1700px] space-y-7 p-5 sm:p-8 lg:p-9">
            {{-- PAGE INTRO --}}
            <section class="trix-anim trix-d1 flex flex-col justify-between gap-4 md:flex-row md:items-end">
                <div>
                    <p class="text-xs font-bold tracking-wide text-indigo-600">{{ strtoupper(now()->format('l, d F Y')) }}</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-[#1C2A40] sm:text-[34px]">Driver <span class="trix-gradient-text">Management</span></h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Manage driver accounts, availability, and access to TRIX WAY.</p>
                </div>
                <button type="button" onclick="openAddModal()" class="group inline-flex items-center gap-2.5 rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-500/30 ring-1 ring-white/15 transition-all hover:shadow-xl hover:-translate-y-0.5">
                    <svg class="h-4 w-4 transition-transform group-hover:rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                    Add New Driver
                </button>
            </section>

            {{-- STATISTICS --}}
            <section class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div class="trix-anim trix-d2 trix-card trix-card-hover rounded-2xl border border-indigo-100 bg-white p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Drivers</p>
                            <h3 class="trix-num mt-3 text-[36px] font-bold leading-none text-indigo-600">{{ $summary['total'] ?? $drivers->count() }}</h3>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-600 ring-1 ring-indigo-200">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><path d="M3 20v-1a6 6 0 0 1 12 0v1M16 5.5a3 3 0 0 1 0 5.8M18 14a5 5 0 0 1 3 4.5V20"/></svg>
                        </div>
                    </div>
                    <p class="mt-4 flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-indigo-400"></span>Registered driver accounts</p>
                </div>
                <div class="trix-anim trix-d3 trix-card trix-card-hover rounded-2xl border border-emerald-100 bg-white p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Active Drivers</p>
                            <h3 class="trix-num mt-3 text-[36px] font-bold leading-none text-emerald-600">{{ $summary['active'] ?? 0 }}</h3>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-600 ring-1 ring-emerald-200">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m5 12 4 4L19 6"/><circle cx="12" cy="12" r="9"/></svg>
                        </div>
                    </div>
                    <p class="mt-4 flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>Can access the driver app</p>
                </div>
                <div class="trix-anim trix-d4 trix-card trix-card-hover rounded-2xl border border-orange-100 bg-white p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Inactive Drivers</p>
                            <h3 class="trix-num mt-3 text-[36px] font-bold leading-none text-orange-600">{{ $summary['inactive'] ?? 0 }}</h3>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-orange-50 to-amber-100 text-orange-600 ring-1 ring-orange-200">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8 12h8"/></svg>
                        </div>
                    </div>
                    <p class="mt-4 flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-orange-400"></span>Access currently disabled</p>
                </div>
            </section>

            {{-- DRIVER DIRECTORY --}}
            <section class="trix-anim trix-d5 trix-card overflow-hidden rounded-2xl border border-[#D6DEEA] bg-white">
                <div class="flex flex-col justify-between gap-4 border-b border-[#E2E7EF] px-6 py-5 md:flex-row md:items-center">
                    <div class="shrink-0">
                        <h3 class="text-sm font-bold text-[#1C2A40]">Driver Directory</h3>
                        <p class="mt-1 text-[11px] text-slate-500">View and manage all registered drivers.</p>
                    </div>
                    <div class="flex w-full flex-col sm:flex-row sm:items-center gap-2.5 md:w-auto">
                        <div class="w-full sm:w-80">
                            <div class="relative flex items-center">
                                <svg class="pointer-events-none absolute left-3.5 z-10 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
                                <input id="driverSearch" name="search" value="{{ request('search') }}" type="text" placeholder=" " class="trix-input w-full" autocomplete="off">
                                <span class="search-hint pointer-events-none absolute left-10 right-3 truncate text-[13px] text-slate-400 transition-opacity">
                                    Search by name, employee ID, or email...
                                </span>
                            </div>
                        </div>
                        <div class="w-full sm:w-44 shrink-0">
                            <select name="status" id="statusFilter" class="trix-input cursor-pointer" onchange="applyFilters()">
                                <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                @if($errors->any())
                    <div class="mx-6 mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left">
                        <thead class="bg-[#F2F5F9]">
                            <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                <th class="px-6 py-4">Driver</th>
                                <th class="px-6 py-4">Employee ID</th>
                                <th class="px-6 py-4">Contact</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Registered</th>
                                <th class="px-6 py-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="driverTableBody" class="divide-y divide-[#EEF2F7] text-sm">
                            @forelse($drivers as $driver)
                                <tr class="trix-row">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-100 to-violet-100 text-sm font-bold text-indigo-700 ring-1 ring-indigo-100">{{ strtoupper(substr($driver->name ?? 'D', 0, 1)) }}</div>
                                            <div class="min-w-0">
                                                <p class="max-w-[220px] truncate font-semibold text-[#1C2A40]">{{ $driver->name }}</p>
                                                <p class="mt-0.5 max-w-[220px] truncate text-[10px] text-slate-500">{{ $driver->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4"><span class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-700">{{ $driver->employee_id }}</span></td>
                                    <td class="px-6 py-4 text-slate-600">{{ $driver->phone ?: '—' }}</td>
                                    <td class="px-6 py-4">
                                        @if($driver->is_active)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-[11px] font-bold text-emerald-700 ring-1 ring-emerald-200"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Active</span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-50 px-3 py-1.5 text-[11px] font-bold text-orange-700 ring-1 ring-orange-200"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">{{ optional($driver->created_at)->format('d M Y') ?? '—' }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-center gap-1.5">
                                            <button type="button" title="Edit driver" data-id="{{ $driver->id }}" data-name="{{ $driver->name }}" data-employee-id="{{ $driver->employee_id }}" data-phone="{{ $driver->phone }}" data-email="{{ $driver->email }}" data-status="{{ $driver->is_active ? 'active' : 'inactive' }}" onclick="openEditModal(this)" class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                            </button>
                                            <button type="button" title="Reset password" onclick="openPasswordModal({{ $driver->id }}, @js($driver->name))" class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg>
                                            </button>
                                            <button type="button" title="Delete driver" onclick="openDeleteModal('{{ route('hr.drivers.destroy', $driver) }}', @js($driver->name))" class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-100">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-16 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-indigo-50 via-white to-cyan-50 text-indigo-500 ring-1 ring-indigo-100 shadow-sm">
                                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="9" cy="8" r="3"/><path d="M3 20v-1a6 6 0 0 1 12 0v1M17 8h4m-2-2v4"/></svg>
                                    </div>
                                    <p class="mt-4 text-sm font-bold text-slate-700">No drivers found</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        @if(request('search'))
                                            No results for "<span class="font-semibold">{{ request('search') }}</span>". Try a different name, employee ID, or email.
                                        @else
                                            Driver records will appear here when available.
                                        @endif
                                    </p>
                                </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex items-center justify-between border-t border-[#E2E7EF] bg-[#FAFBFD] px-6 py-4">
                    <p id="driverCount" class="text-[11px] font-medium text-slate-500">Showing {{ $drivers->count() }} drivers</p>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700 ring-1 ring-emerald-200"><span class="trix-live-dot h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Live data</span>
                </div>
            </section>

            <footer class="flex flex-col justify-between gap-2 border-t border-[#D6DEEA] pt-5 text-[10px] font-medium tracking-wide text-slate-500 sm:flex-row">
                <p>© {{ date('Y') }} TRIX WAY · PT TRIX INDONESIA</p>
                <p>FLEET OPERATIONS MANAGEMENT SYSTEM</p>
            </footer>
        </div>
    </main>
</div>

{{-- ============ ADD / EDIT DRIVER MODAL ============ --}}
<div id="driverModal" class="trix-modal">
    <div class="trix-modal-content rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-[#E2E7EF] px-6 py-5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-500 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="8" r="3"/><path d="M3 20v-1a6 6 0 0 1 12 0v1M16 5.5a3 3 0 0 1 0 5.8M18 14a5 5 0 0 1 3 4.5V20"/></svg>
                </div>
                <div>
                    <h3 id="modalTitle" class="text-sm font-bold text-[#1C2A40]">Add New Driver</h3>
                    <p class="mt-0.5 text-[11px] text-slate-500">Enter driver information below.</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('driverModal')" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-500">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <form id="driverForm" method="POST" action="{{ route('hr.drivers.store') }}" class="space-y-4 p-6">
            @csrf
            <input type="hidden" name="_method" id="driverMethod" value="POST">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Full Name *</label>
                    <input id="driverName" name="name" class="trix-input" placeholder="Example: Budi Santoso" value="{{ old('name') }}" required>
                </div>
                <div>
                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Employee ID *</label>
                    <input id="driverEmployeeId" name="employee_id" class="trix-input" placeholder="Example: DRV-001" value="{{ old('employee_id') }}" required>
                </div>
                <div>
                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Phone Number *</label>
                    <input id="driverPhone" name="phone" class="trix-input" placeholder="Example: 08123456789" value="{{ old('phone') }}" required>
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Email Address *</label>
                    <input id="driverEmail" type="email" name="email" class="trix-input" placeholder="driver@trix-indo.co.id" value="{{ old('email') }}" required>
                </div>
                <div id="passwordFields">
                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Initial Password *</label>
                    <input type="password" name="password" class="trix-input" placeholder="Minimum 8 characters" minlength="8">
                </div>
                <div id="passwordConfirmFields">
                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Confirm Password *</label>
                    <input type="password" name="password_confirmation" class="trix-input" placeholder="Repeat password" minlength="8">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Account Status</label>
                    <select id="driverStatus" name="status" class="trix-input cursor-pointer" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="flex items-start gap-2 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-[11px] leading-relaxed text-amber-700">
                <svg class="mt-0.5 h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
                Drivers set to inactive will have their existing app access tokens revoked.
            </div>
            <div class="flex justify-end gap-3 border-t border-[#E2E7EF] pt-5">
                <button type="button" onclick="closeModal('driverModal')" class="rounded-lg border border-[#D6DEEA] bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Cancel</button>
                <button type="submit" class="rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-500/30 transition hover:-translate-y-0.5">Save Driver</button>
            </div>
        </form>
    </div>
</div>

{{-- ============ SAVE CONFIRMATION MODAL ============ --}}
<div id="saveConfirmModal" class="trix-modal">
    <div class="trix-modal-content max-w-sm rounded-2xl bg-white shadow-2xl">
        <div class="p-6 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 ring-4 ring-indigo-100">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12.5 9.5 17 19 7.5"/><path d="M12 3a9 9 0 1 0 9 9"/></svg>
            </div>
            <h3 id="saveConfirmTitle" class="text-lg font-bold text-[#1C2A40]">Save Driver Data?</h3>
            <p id="saveConfirmMessage" class="mt-2 text-sm leading-relaxed text-slate-500">Make sure the driver information is correct before saving.</p>
        </div>
        <div class="flex gap-3 border-t border-[#E2E7EF] px-6 py-4">
            <button type="button" onclick="cancelSaveConfirmation()" class="flex-1 rounded-lg border border-[#D6DEEA] bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Review Again</button>
            <button type="button" id="confirmSaveButton" onclick="confirmSaveDriver()" class="flex-1 rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-500/30 hover:shadow-xl">Yes, Save</button>
        </div>
    </div>
</div>

{{-- ============ RESET PASSWORD MODAL ============ --}}
<div id="passwordModal" class="trix-modal">
    <div class="trix-modal-content max-w-md rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-[#E2E7EF] px-6 py-5">
            <div>
                <h3 class="text-sm font-bold text-[#1C2A40]">Reset Driver Password</h3>
                <p id="passwordDriverName" class="mt-1 text-[11px] text-slate-500"></p>
            </div>
            <button type="button" onclick="closeModal('passwordModal')" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-500">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <form id="passwordForm" method="POST" class="space-y-4 p-6">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">New Password *</label>
                <input type="password" name="password" class="trix-input" placeholder="Minimum 8 characters" minlength="8" required>
            </div>
            <div>
                <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Confirm New Password *</label>
                <input type="password" name="password_confirmation" class="trix-input" placeholder="Repeat new password" minlength="8" required>
            </div>
            <div class="flex items-start gap-2 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-[11px] leading-relaxed text-amber-700">
                <svg class="mt-0.5 h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
                The driver's existing app tokens will be revoked after the password is changed.
            </div>
            <div class="flex justify-end gap-3 border-t border-[#E2E7EF] pt-5">
                <button type="button" onclick="closeModal('passwordModal')" class="rounded-lg border border-[#D6DEEA] bg-white px-5 py-2.5 text-sm font-semibold text-slate-600">Cancel</button>
                <button type="submit" class="rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-amber-500/30">Update Password</button>
            </div>
        </form>
    </div>
</div>

{{-- ============ DELETE CONFIRMATION MODAL ============ --}}
<div id="deleteModal" class="trix-modal">
    <div class="trix-modal-content max-w-sm rounded-2xl bg-white shadow-2xl">
        <div class="p-6 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-rose-50 text-rose-500 ring-4 ring-rose-100">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/></svg>
            </div>
            <h3 class="text-lg font-bold text-[#1C2A40]">Delete Driver?</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">Are you sure you want to delete driver <strong id="deleteDriverName" class="text-[#1C2A40]"></strong>?</p>
        </div>
        <form id="deleteForm" method="POST" class="flex gap-3 border-t border-[#E2E7EF] px-6 py-4">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeModal('deleteModal')" class="flex-1 rounded-lg border border-[#D6DEEA] bg-white px-4 py-2.5 text-sm font-semibold text-slate-600">Cancel</button>
            <button type="submit" class="flex-1 rounded-lg bg-rose-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-rose-500/30 hover:bg-rose-600">Yes, Delete</button>
        </form>
    </div>
</div>

<script>
    // ============ TOAST ============
    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        const isSuccess = type === 'success';
        const iconSvg = isSuccess
            ? `<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 4 4L19 6"/></svg>`
            : `<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 8v4M12 16h.01"/><circle cx="12" cy="12" r="9"/></svg>`;
        toast.className = `trix-toast pointer-events-auto flex w-[320px] items-start gap-3 rounded-xl border p-4 shadow-xl ${isSuccess ? 'border-emerald-200 bg-white' : 'border-rose-200 bg-white'}`;
        toast.innerHTML = `
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full ${isSuccess ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'}">${iconSvg}</div>
            <div class="flex-1 pt-0.5">
                <p class="text-sm font-bold text-[#1C2A40]">${isSuccess ? 'Success!' : 'Failed!'}</p>
                <p class="mt-1 text-[11px] font-medium leading-relaxed text-slate-500"></p>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-600">✕</button>`;
        toast.querySelector('p.mt-1').textContent = message;
        toast.querySelector('button').addEventListener('click', () => toast.remove());
        container.appendChild(toast);
        setTimeout(() => { toast.classList.add('hiding'); setTimeout(() => toast.remove(), 300); }, 3500);
    }

    @if(session('success')) showToast(@js(session('success')), 'success'); @endif
    @if(session('error')) showToast(@js(session('error')), 'error'); @endif
    @if($errors->any()) showToast(@js($errors->first()), 'error'); @endif

    // ============ DATA ============
    const driverData = @json($drivers->keyBy('id'));

    // ============ MODAL UTILS ============
    function closeModal(id) { document.getElementById(id).classList.remove('show'); }

    document.querySelectorAll('.trix-modal').forEach(modal => {
        modal.addEventListener('click', event => {
            if (event.target === modal && modal.id !== 'saveConfirmModal') {
                modal.classList.remove('show');
            }
        });
    });

    // ============ LIVE SEARCH + STATUS FILTER ============
    const searchInput  = document.getElementById('driverSearch');
    const statusFilter = document.getElementById('statusFilter');
    const tableBody    = document.getElementById('driverTableBody');
    const countLabel   = document.getElementById('driverCount');
    let debounceTimer  = null;
    let currentRequest = null;

    function applyFilters() {
        const params = new URLSearchParams();
        const q      = searchInput.value.trim();
        const status = statusFilter.value;

        if (q)                          params.set('search', q);
        if (status && status !== 'all') params.set('status', status);

        const url = `{{ route('hr.drivers.index') }}?${params.toString()}`;

        searchInput.classList.add('is-loading');
        if (currentRequest) currentRequest.abort();
        currentRequest = new AbortController();

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            signal: currentRequest.signal,
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(data => {
            if (data.html !== undefined) {
                tableBody.innerHTML = data.html;
            }
            if (data.count !== undefined && countLabel) {
                countLabel.textContent = 'Showing ' + data.count + ' drivers';
            }
            window.history.replaceState({}, '', url);
        })
        .catch(err => {
            if (err.name !== 'AbortError') console.error('Filter error:', err);
        })
        .finally(() => {
            searchInput.classList.remove('is-loading');
        });
    }

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(applyFilters, 350);
    });

    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(debounceTimer);
            applyFilters();
        }
    });

    // ============ ADD / EDIT ============
    function openAddModal() {
        const form = document.getElementById('driverForm');
        form.reset();
        form.action = @js(route('hr.drivers.store'));
        document.getElementById('driverMethod').value = 'POST';
        document.getElementById('modalTitle').textContent = 'Add New Driver';
        document.getElementById('driverStatus').value = 'active';

        // Password required for new driver
        document.querySelector('#passwordFields input').required = true;
        document.querySelector('#passwordConfirmFields input').required = true;

        document.getElementById('driverModal').classList.add('show');
    }

    function openEditModal(button) {
        const driver = button.dataset;
        const form = document.getElementById('driverForm');
        form.action = `/hr/drivers/${driver.id}`;
        document.getElementById('driverMethod').value = 'PUT';
        document.getElementById('modalTitle').textContent = 'Edit Driver';
        document.getElementById('driverName').value = driver.name ?? '';
        document.getElementById('driverEmployeeId').value = driver.employeeId ?? '';
        document.getElementById('driverPhone').value = driver.phone ?? '';
        document.getElementById('driverEmail').value = driver.email ?? '';
        document.getElementById('driverStatus').value = driver.status ?? 'active';

        // Password optional for edit
        document.querySelector('#passwordFields input').required = false;
        document.querySelector('#passwordConfirmFields input').required = false;
        document.querySelector('#passwordFields input').value = '';
        document.querySelector('#passwordConfirmFields input').value = '';

        document.getElementById('driverModal').classList.add('show');
    }

    // ============ SAVE CONFIRMATION ============
    let pendingDriverForm = null;

    document.getElementById('driverForm').addEventListener('submit', function (event) {
        event.preventDefault();
        if (!this.reportValidity()) return;

        pendingDriverForm = this;
        const isEdit = document.getElementById('driverMethod').value === 'PUT';

        document.getElementById('saveConfirmTitle').textContent = isEdit ? 'Update Driver Data?' : 'Save New Driver?';
        document.getElementById('saveConfirmMessage').textContent = isEdit
            ? 'Are you sure you want to save the changes to this driver?'
            : 'Are you sure you want to add this driver to the driver directory?';
        document.getElementById('confirmSaveButton').textContent = isEdit ? 'Yes, Update' : 'Yes, Save';

        closeModal('driverModal');
        document.getElementById('saveConfirmModal').classList.add('show');
    });

    function cancelSaveConfirmation() {
        document.getElementById('saveConfirmModal').classList.remove('show');
        pendingDriverForm = null;
        document.getElementById('driverModal').classList.add('show');
    }

    function confirmSaveDriver() {
        if (!pendingDriverForm) return;
        const form = pendingDriverForm;
        pendingDriverForm = null;
        document.getElementById('saveConfirmModal').classList.remove('show');
        HTMLFormElement.prototype.submit.call(form);
    }

    // ============ RESET PASSWORD ============
    function openPasswordModal(id, name) {
        document.getElementById('passwordForm').action = `/hr/drivers/${id}/reset-password`;
        document.getElementById('passwordDriverName').textContent = `Set a new password for ${name}.`;
        document.getElementById('passwordForm').reset();
        document.getElementById('passwordModal').classList.add('show');
    }

    // ============ DELETE ============
    function openDeleteModal(action, name) {
        document.getElementById('deleteForm').action = action;
        document.getElementById('deleteDriverName').textContent = name;
        document.getElementById('deleteModal').classList.add('show');
    }
</script>
</body>
</html>