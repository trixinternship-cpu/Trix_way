<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vehicle Management | TRIX WAY</title>
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
        
        /* PERBAIKAN SEARCH BAR */
        #vehicleSearch { padding-left: 2.5rem !important; }
        #vehicleSearch.is-loading { background-image: linear-gradient(90deg,#F8FAFC 25%,#EEF2F7 50%,#F8FAFC 75%); background-size: 200% 100%; animation: trixShimmer 1.2s infinite; }
        #vehicleSearch:not(:placeholder-shown) ~ .search-hint { opacity: 0; }
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
        @media (prefers-reduced-motion: reduce) { .trix-anim,.trix-live-dot,.trix-toast,#vehicleSearch.is-loading { animation: none !important; opacity: 1 !important; } .trix-card,.trix-card-hover:hover,.trix-action:hover { transition: none; transform: none; } }
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
                <a href="{{ route('hr.dashboard') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('hr.live-monitoring') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 6.5 12 3l9 3.5v11L12 21l-9-3.5z"/><path d="m3 6.5 9 4 9-4M12 10.5V21"/></svg>
                    Live Monitoring
                </a>
                <a href="{{ route('hr.drivers.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><path d="M3 20v-1a6 6 0 0 1 12 0v1M16 5.5a3 3 0 0 1 0 5.8M18 14a5 5 0 0 1 3 4.5V20"/></svg>
                    Driver Management
                </a>
                <a href="{{ route('hr.vehicles.index') }}" class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-950/50 ring-1 ring-white/15">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 13h18l-1.5-5h-15z"/><path d="M5 13v5h14v-5M7 18v2m10-2v2M7 8l1-3h8l1 3"/></svg>
                    Vehicle Management
                </a>
                <a href="{{ route('hr.trips.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h15l-3-3m4 13H5l3 3"/><path d="M19 7v4M5 17v-4"/></svg>
                    Trip Management
                </a>
                <a href="{{ route('hr.trip-history') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    Trip History
                </a>
            </nav>
            <p class="mb-4 mt-9 px-4 text-[10px] font-bold tracking-[2px] text-slate-400">PREFERENCES</p>
            <nav>
                <a href="{{ route('hr.notifications') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                    Notifications
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
                <h2 class="mt-1 text-sm font-bold text-[#1C2A40]">Vehicle Management</h2>
            </div>
            <div class="flex items-center gap-5">
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
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-[#1C2A40] sm:text-[34px]">Vehicle <span class="trix-gradient-text">Management</span></h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Manage company vehicles, availability, and maintenance status.</p>
                </div>
                <button type="button" onclick="openAddModal()" class="group inline-flex items-center gap-2.5 rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-500/30 ring-1 ring-white/15 transition-all hover:shadow-xl hover:-translate-y-0.5">
                    <svg class="h-4 w-4 transition-transform group-hover:rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                    Add New Vehicle
                </button>
            </section>

            {{-- STATISTICS --}}
            <section class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <div class="trix-anim trix-d2 trix-card trix-card-hover rounded-2xl border border-indigo-100 bg-white p-6">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Vehicles</p>
                    <h3 class="trix-num mt-3 text-[36px] font-bold leading-none text-indigo-600">{{ $summary['total'] ?? 0 }}</h3>
                    <p class="mt-4 flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-indigo-400"></span>All registered vehicles</p>
                </div>
                <div class="trix-anim trix-d3 trix-card trix-card-hover rounded-2xl border border-emerald-100 bg-white p-6">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Available</p>
                    <h3 class="trix-num mt-3 text-[36px] font-bold leading-none text-emerald-600">{{ $summary['available'] ?? 0 }}</h3>
                    <p class="mt-4 flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>Ready for assignment</p>
                </div>
                <div class="trix-anim trix-d4 trix-card trix-card-hover rounded-2xl border border-cyan-100 bg-white p-6">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">In Use</p>
                    <h3 class="trix-num mt-3 text-[36px] font-bold leading-none text-cyan-600">{{ $summary['in_use'] ?? 0 }}</h3>
                    <p class="mt-4 flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-cyan-400"></span>Assigned to active trips</p>
                </div>
                <div class="trix-anim trix-d5 trix-card trix-card-hover rounded-2xl border border-orange-100 bg-white p-6">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Maintenance</p>
                    <h3 class="trix-num mt-3 text-[36px] font-bold leading-none text-orange-600">{{ $summary['maintenance'] ?? 0 }}</h3>
                    <p class="mt-4 flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-orange-400"></span>Under maintenance</p>
                </div>
            </section>

            {{-- VEHICLE DIRECTORY --}}
            <section class="trix-anim trix-d6 trix-card overflow-hidden rounded-2xl border border-[#D6DEEA] bg-white">
                <div class="flex flex-col justify-between gap-4 border-b border-[#E2E7EF] px-6 py-5 md:flex-row md:items-center">
                    <div class="shrink-0">
                        <h3 class="text-sm font-bold text-[#1C2A40]">Vehicle Directory</h3>
                        <p class="mt-1 text-[11px] text-slate-500">View and manage all registered company vehicles.</p>
                    </div>
                    <div class="flex w-full flex-col sm:flex-row sm:items-center gap-2.5 md:w-auto">
                        {{-- PERBAIKAN: Hanya 1 Teks Abu-Abu (Placeholder) --}}
                        <div class="w-full sm:w-80">
                            <div class="relative flex items-center">
                                <svg class="pointer-events-none absolute left-3.5 z-10 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
                                <input
                                    id="vehicleSearch"
                                    name="search"
                                    value="{{ request('search') }}"
                                    type="text"
                                    placeholder=" "
                                    class="trix-input w-full"
                                    autocomplete="off"
                                >
                                <span class="search-hint pointer-events-none absolute left-10 right-3 truncate text-[13px] text-slate-400 transition-opacity">
                                    Search by plate number, brand, or model...
                                </span>
                            </div>
                        </div>
                        <div class="w-full sm:w-44 shrink-0">
                            <select name="status" id="statusFilter" class="trix-input cursor-pointer" onchange="applyFilters()">
                                <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                                <option value="in_use" {{ request('status') === 'in_use' ? 'selected' : '' }}>In Use</option>
                                <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
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
                                <th class="px-6 py-4">Vehicle</th>
                                <th class="px-6 py-4">Plate Number</th>
                                <th class="px-6 py-4">Brand / Model</th>
                                <th class="px-6 py-4">Year</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="vehicleTableBody" class="divide-y divide-[#EEF2F7] text-sm">
                            @forelse($vehicles as $vehicle)
                                @php
                                    $isInUse = (bool) ($vehicle->has_active_trip ?? false);
                                    $status = $isInUse ? 'in_use' : $vehicle->status;
                                    $statusStyles = [
                                        'available' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                        'in_use' => 'bg-cyan-50 text-cyan-700 ring-cyan-200',
                                        'maintenance' => 'bg-orange-50 text-orange-700 ring-orange-200',
                                        'inactive' => 'bg-slate-100 text-slate-600 ring-slate-200',
                                    ];
                                    $statusLabels = [
                                        'available' => 'Available',
                                        'in_use' => 'In Use',
                                        'maintenance' => 'Maintenance',
                                        'inactive' => 'Inactive',
                                    ];
                                @endphp
                                <tr class="trix-row">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-50 to-cyan-50 text-indigo-600 ring-1 ring-indigo-100">
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 13h18l-1.5-5h-15z"/><path d="M5 13v5h14v-5M7 18v2m10-2v2M7 8l1-3h8l1 3"/></svg>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-[#1C2A40]">{{ $vehicle->brand ?: '-' }} {{ $vehicle->model ?: '' }}</p>
                                                <p class="mt-0.5 text-[10px] text-slate-500">Vehicle ID #{{ $vehicle->id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-[#1C2A40]">{{ $vehicle->plate_number }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $vehicle->brand ?: '-' }} / {{ $vehicle->model ?: '-' }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $vehicle->year ?: '-' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[11px] font-bold ring-1 {{ $statusStyles[$status] ?? $statusStyles['inactive'] }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $status === 'available' ? 'bg-emerald-500' : ($status === 'in_use' ? 'bg-cyan-500' : ($status === 'maintenance' ? 'bg-orange-500' : 'bg-slate-500')) }}"></span>
                                            {{ $statusLabels[$status] ?? ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-center gap-1.5">
                                            <button type="button" onclick="viewVehicle({{ $vehicle->id }})" class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100" title="View Details">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </button>
                                            <button type="button" onclick="openEditModal({{ $vehicle->id }})" class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100" title="Edit Vehicle">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                            </button>
                                            <button type="button" onclick="openStatusModal({{ $vehicle->id }}, @js($status))" class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200" title="Change Status" {{ $isInUse ? 'disabled' : '' }}>
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                                            </button>
                                            <button type="button" onclick="openDeleteModal('{{ route('hr.vehicles.destroy', $vehicle) }}', @js($vehicle->plate_number))" class="trix-action flex h-9 w-9 items-center justify-center rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-100" title="Delete Vehicle">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-16 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-indigo-50 via-white to-cyan-50 text-indigo-500 ring-1 ring-indigo-100 shadow-sm">
                                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 13h18l-1.5-5h-15z"/><path d="M5 13v5h14v-5M7 18v2m10-2v2M7 8l1-3h8l1 3"/></svg>
                                    </div>
                                    <p class="mt-4 text-sm font-bold text-slate-700">No vehicles found</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        @if(request('search'))
                                            No results for "<span class="font-semibold">{{ request('search') }}</span>". Try a different plate number, brand, or model.
                                        @else
                                            Vehicle records will appear here when available.
                                        @endif
                                    </p>
                                </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex items-center justify-between border-t border-[#E2E7EF] bg-[#FAFBFD] px-6 py-4">
                    <p id="vehicleCount" class="text-[11px] font-medium text-slate-500">Showing {{ $vehicles->count() }} vehicles</p>
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

{{-- ============ ADD / EDIT VEHICLE MODAL ============ --}}
<div id="vehicleModal" class="trix-modal">
    <div class="trix-modal-content rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-[#E2E7EF] px-6 py-5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-500 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 13h18l-1.5-5h-15z"/><path d="M5 13v5h14v-5M7 18v2m10-2v2M7 8l1-3h8l1 3"/></svg>
                </div>
                <div><h3 id="modalTitle" class="text-sm font-bold text-[#1C2A40]">Add New Vehicle</h3><p class="mt-0.5 text-[11px] text-slate-500">Enter vehicle information below.</p></div>
            </div>
            <button type="button" onclick="closeModal('vehicleModal')" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-500"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
        </div>
        <form id="vehicleForm" method="POST" action="{{ route('hr.vehicles.store') }}" class="space-y-4 p-6">
            @csrf
            <input type="hidden" name="_method" id="vehicleMethod" value="POST">
            <div>
                <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Plate Number *</label>
                <input id="plateNumber" name="plate_number" class="trix-input" placeholder="Example: B 1234 XYZ" value="{{ old('plate_number') }}" required>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div><label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Brand *</label><input id="brand" name="brand" class="trix-input" placeholder="Example: Toyota" value="{{ old('brand') }}" required></div>
                <div><label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Vehicle Model / Type *</label><input id="vehicleModel" name="model" class="trix-input" placeholder="Example: Avanza" value="{{ old('model') }}" required></div>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div><label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Production Year</label><input id="vehicleYear" name="year" type="number" min="1900" max="2099" class="trix-input" placeholder="Example: 2024" value="{{ old('year') }}"></div>
                <div><label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Status</label>
                    <select id="vehicleStatus" name="status" class="trix-input cursor-pointer" required>
                        <option value="available">Available</option><option value="maintenance">Maintenance</option><option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <p class="rounded-lg bg-amber-50 px-3 py-2 text-[11px] leading-5 text-amber-700 ring-1 ring-amber-200">Vehicles currently assigned to an active trip cannot be edited or have their status changed.</p>
            <div class="flex justify-end gap-3 border-t border-[#E2E7EF] pt-5">
                <button type="button" onclick="closeModal('vehicleModal')" class="rounded-lg border border-[#D6DEEA] bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Cancel</button>
                <button type="submit" class="rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-500/30 transition hover:-translate-y-0.5">Save Vehicle</button>
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
            <h3 id="saveConfirmTitle" class="text-lg font-bold text-[#1C2A40]">Save Vehicle Data?</h3>
            <p id="saveConfirmMessage" class="mt-2 text-sm leading-relaxed text-slate-500">Make sure the vehicle information is correct before saving.</p>
        </div>
        <div class="flex gap-3 border-t border-[#E2E7EF] px-6 py-4">
            <button type="button" onclick="cancelSaveConfirmation()" class="flex-1 rounded-lg border border-[#D6DEEA] bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Review Again</button>
            <button type="button" id="confirmSaveButton" onclick="confirmSaveVehicle()" class="flex-1 rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-500/30 hover:shadow-xl">Yes, Save</button>
        </div>
    </div>
</div>

{{-- ============ VEHICLE DETAIL MODAL ============ --}}
<div id="detailModal" class="trix-modal">
    <div class="trix-modal-content rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-[#E2E7EF] px-6 py-5">
            <div><h3 class="text-sm font-bold text-[#1C2A40]">Vehicle Details</h3><p class="mt-0.5 text-[11px] text-slate-500">Vehicle information and current status.</p></div>
            <button type="button" onclick="closeModal('detailModal')" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-500"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
        </div>
        <div id="detailContent" class="p-6"></div>
        <div class="border-t border-[#E2E7EF] px-6 py-4 text-right"><button type="button" onclick="closeModal('detailModal')" class="rounded-lg bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-200">Close</button></div>
    </div>
</div>

{{-- ============ CHANGE STATUS MODAL ============ --}}
<div id="statusModal" class="trix-modal">
    <div class="trix-modal-content max-w-md rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-[#E2E7EF] px-6 py-5">
            <div><h3 class="text-sm font-bold text-[#1C2A40]">Change Vehicle Status</h3><p id="statusVehiclePlate" class="mt-1 text-[11px] text-slate-500"></p></div>
            <button type="button" onclick="closeModal('statusModal')" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-500">✕</button>
        </div>
        <form id="statusForm" method="POST" class="space-y-4 p-6">
            @csrf
            @method('PATCH')
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600">New Status</label>
            <select name="status" id="newVehicleStatus" class="trix-input" required><option value="available">Available</option><option value="maintenance">Maintenance</option><option value="inactive">Inactive</option></select>
            <div><label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">Reason (Optional)</label><textarea name="reason" class="trix-input resize-none" rows="2" placeholder="Reason for status change..."></textarea></div>
            <div class="flex justify-end gap-3 border-t border-[#E2E7EF] pt-5">
                <button type="button" onclick="closeModal('statusModal')" class="rounded-lg border border-[#D6DEEA] px-5 py-2.5 text-sm font-semibold text-slate-600">Cancel</button>
                <button type="submit" class="rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-5 py-2.5 text-sm font-bold text-white">Update Status</button>
            </div>
        </form>
    </div>
</div>

{{-- ============ DELETE CONFIRMATION MODAL ============ --}}
<div id="deleteModal" class="trix-modal">
    <div class="trix-modal-content max-w-sm rounded-2xl bg-white shadow-2xl">
        <div class="p-6 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-rose-50 text-rose-500 ring-4 ring-rose-100"><svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/></svg></div>
            <h3 class="text-lg font-bold text-[#1C2A40]">Delete Vehicle?</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">Are you sure you want to delete vehicle <strong id="deleteVehicleName" class="text-[#1C2A40]"></strong>?</p>
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
    const vehicleData = @json($vehicles->keyBy('id'));

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
    const searchInput  = document.getElementById('vehicleSearch');
    const statusFilter = document.getElementById('statusFilter');
    const tableBody    = document.getElementById('vehicleTableBody');
    const countLabel   = document.getElementById('vehicleCount');
    let debounceTimer  = null;
    let currentRequest = null;

    function applyFilters() {
        const params = new URLSearchParams();
        const q      = searchInput.value.trim();
        const status = statusFilter.value;

        if (q)                          params.set('search', q);
        if (status && status !== 'all') params.set('status', status);

        const url = `{{ route('hr.vehicles.index') }}?${params.toString()}`;

        searchInput.classList.add('is-loading');
        if (currentRequest) currentRequest.abort();
        currentRequest = new AbortController();

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
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
                countLabel.textContent = 'Showing ' + data.count + ' vehicles';
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
        const form = document.getElementById('vehicleForm');
        form.reset();
        form.action = @js(route('hr.vehicles.store'));
        document.getElementById('vehicleMethod').value = 'POST';
        document.getElementById('modalTitle').textContent = 'Add New Vehicle';
        document.getElementById('vehicleStatus').value = 'available';
        document.getElementById('vehicleModal').classList.add('show');
    }

    function openEditModal(vehicleId) {
        const vehicle = vehicleData[vehicleId];
        if (!vehicle) return;
        const form = document.getElementById('vehicleForm');
        form.action = `/hr/vehicles/${vehicle.id}`;
        document.getElementById('vehicleMethod').value = 'PUT';
        document.getElementById('modalTitle').textContent = 'Edit Vehicle';
        document.getElementById('plateNumber').value = vehicle.plate_number ?? '';
        document.getElementById('brand').value = vehicle.brand ?? '';
        document.getElementById('vehicleModel').value = vehicle.model ?? '';
        document.getElementById('vehicleYear').value = vehicle.year ?? '';
        document.getElementById('vehicleStatus').value = vehicle.status ?? 'available';
        document.getElementById('vehicleModal').classList.add('show');
    }

    // ============ SAVE CONFIRMATION ============
    let pendingVehicleForm = null;

    document.getElementById('vehicleForm').addEventListener('submit', function (event) {
        event.preventDefault();
        if (!this.reportValidity()) return;

        pendingVehicleForm = this;
        const isEdit = document.getElementById('vehicleMethod').value === 'PUT';

        document.getElementById('saveConfirmTitle').textContent = isEdit ? 'Update Vehicle Data?' : 'Save New Vehicle?';
        document.getElementById('saveConfirmMessage').textContent = isEdit
            ? 'Are you sure you want to save the changes to this vehicle?'
            : 'Are you sure you want to add this vehicle to the vehicle directory?';
        document.getElementById('confirmSaveButton').textContent = isEdit ? 'Yes, Update' : 'Yes, Save';

        closeModal('vehicleModal');
        document.getElementById('saveConfirmModal').classList.add('show');
    });

    function cancelSaveConfirmation() {
        document.getElementById('saveConfirmModal').classList.remove('show');
        pendingVehicleForm = null;
        document.getElementById('vehicleModal').classList.add('show');
    }

    function confirmSaveVehicle() {
        if (!pendingVehicleForm) return;
        const form = pendingVehicleForm;
        pendingVehicleForm = null;
        document.getElementById('saveConfirmModal').classList.remove('show');
        HTMLFormElement.prototype.submit.call(form);
    }

    // ============ VIEW DETAIL ============
    function escapeHTML(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
    }

    function viewVehicle(vehicleId) {
        const vehicle = vehicleData[vehicleId];
        if (!vehicle) return;
        const inUse = Boolean(vehicle.has_active_trip);
        const status = inUse ? 'In Use' : ({available:'Available',maintenance:'Maintenance',inactive:'Inactive'}[vehicle.status] ?? vehicle.status);
        document.getElementById('detailContent').innerHTML = `
            <div class="mb-6 text-center">
                <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-500 text-white ring-4 ring-indigo-100"><svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 13h18l-1.5-5h-15z"/><path d="M5 13v5h14v-5M7 18v2m10-2v2M7 8l1-3h8l1 3"/></svg></div>
                <h4 class="text-lg font-bold text-[#1C2A40]">${escapeHTML(vehicle.plate_number)}</h4>
                <p class="mt-1 text-xs text-slate-500">${escapeHTML(vehicle.brand)} ${escapeHTML(vehicle.model)}</p>
            </div>
            <div class="space-y-4 text-sm">
                <div class="flex justify-between gap-4 border-b border-[#EEF2F7] pb-3"><span class="text-slate-500">Plate Number</span><span class="font-semibold text-[#1C2A40]">${escapeHTML(vehicle.plate_number)}</span></div>
                <div class="flex justify-between gap-4 border-b border-[#EEF2F7] pb-3"><span class="text-slate-500">Brand</span><span class="font-semibold text-[#1C2A40]">${escapeHTML(vehicle.brand || '-')}</span></div>
                <div class="flex justify-between gap-4 border-b border-[#EEF2F7] pb-3"><span class="text-slate-500">Vehicle Model</span><span class="font-semibold text-[#1C2A40]">${escapeHTML(vehicle.model || '-')}</span></div>
                <div class="flex justify-between gap-4 border-b border-[#EEF2F7] pb-3"><span class="text-slate-500">Production Year</span><span class="font-semibold text-[#1C2A40]">${escapeHTML(vehicle.year || '-')}</span></div>
                <div class="flex justify-between gap-4"><span class="text-slate-500">Status</span><span class="font-semibold text-[#1C2A40]">${escapeHTML(status)}</span></div>
            </div>`;
        document.getElementById('detailModal').classList.add('show');
    }

    // ============ CHANGE STATUS ============
    function openStatusModal(vehicleId, currentStatus) {
        const vehicle = vehicleData[vehicleId];
        if (!vehicle || currentStatus === 'in_use') return;
        document.getElementById('statusForm').action = `/hr/vehicles/${vehicle.id}/status`;
        document.getElementById('statusVehiclePlate').textContent = vehicle.plate_number;
        document.getElementById('newVehicleStatus').value = vehicle.status;
        document.getElementById('statusModal').classList.add('show');
    }

    // ============ DELETE ============
    function openDeleteModal(action, plate) {
        document.getElementById('deleteForm').action = action;
        document.getElementById('deleteVehicleName').textContent = plate;
        document.getElementById('deleteModal').classList.add('show');
    }
</script>
</body>
</html>