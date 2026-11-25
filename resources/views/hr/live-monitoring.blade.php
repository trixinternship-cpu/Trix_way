<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRIX WAY | Live Monitoring</title>
<link rel="icon" type="image/png" href="{{ asset('images/trix-logo.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <style>
        .trix-card {
            box-shadow: 0 1px 2px rgba(20,35,60,.06),
                        0 6px 16px rgba(20,35,60,.05),
                        0 16px 40px -16px rgba(20,35,60,.10);
            transition: box-shadow .3s, transform .3s, border-color .3s;
        }

        .trix-card-hover:hover {
            box-shadow: 0 12px 28px rgba(20,35,60,.08);
            transform: translateY(-3px);
        }

        .trix-num {
            font-variant-numeric: tabular-nums;
        }

        @keyframes trixFadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .trix-anim {
            opacity: 1;
            animation: trixFadeUp .55s ease forwards;
        }

        .trix-live-dot {
            animation: trixLive 1.6s ease-in-out infinite;
        }

        @keyframes trixLive {
            0%,100% { opacity: 1; }
            50% { opacity: .4; }
        }

        .trix-gradient-text {
            background: linear-gradient(135deg,#6366F1 0%,#0891B2 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        #fleetMap {
            height: calc(100vh - 330px);
            min-height: 430px;
            z-index: 1;
        }

        /* Fullscreen map panel */
        #fleetMapCard:fullscreen {
            width: 100vw;
            height: 100vh;
            max-width: none;
            border: 0;
            border-radius: 0;
            display: flex;
            flex-direction: column;
            background: #fff;
        }

        #fleetMapCard:fullscreen > .fleet-map-toolbar,
        #fleetMapCard:fullscreen > .fleet-map-legend {
            flex: 0 0 auto;
        }

        #fleetMapCard:fullscreen #fleetMap {
            flex: 1 1 auto;
            height: auto;
            min-height: 0;
            width: 100%;
        }

        #fleetMapCard:fullscreen .leaflet-container {
            height: 100%;
        }

        #fullscreenMapButton {
            transition: background .2s ease, border-color .2s ease;
        }

        #fullscreenMapButton:hover {
            background: #EEF2FF;
            border-color: #6366F1;
        }

        .leaflet-container {
            font-family: inherit;
        }

        .driver-select-card {
            width: 100%;
            text-align: left;
            cursor: pointer;
            transition: all .2s ease;
        }

        .driver-select-card:hover {
            border-color: #818cf8;
            background: #f8faff;
        }

        .driver-select-card.selected {
            border-color: #6366f1;
            background: #eef2ff;
            box-shadow: 0 0 0 2px rgba(99,102,241,.12);
        }

        /* ============ REFRESH MAP EFFECT ============ */
        @keyframes trixSpin {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }
        @keyframes trixMapFlash {
            0%   { box-shadow: inset 0 0 0 0 rgba(99,102,241,0); }
            30%  { box-shadow: inset 0 0 0 4px rgba(99,102,241,.35); }
            100% { box-shadow: inset 0 0 0 0 rgba(99,102,241,0); }
        }
        @keyframes trixScanLine {
            0%   { transform: translateY(-100%); opacity: 0; }
            20%  { opacity: 1; }
            80%  { opacity: 1; }
            100% { transform: translateY(100%); opacity: 0; }
        }
        @keyframes trixPulseRing {
            0%   { transform: scale(.9); opacity: .7; }
            100% { transform: scale(2.2); opacity: 0; }
        }

        /* Tombol refresh */
        #refreshMapButton {
            position: relative;
            overflow: hidden;
            transition: all .25s cubic-bezier(.4,0,.2,1);
        }
        #refreshMapButton:hover {
            background: #EEF2FF;
            border-color: #6366F1;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99,102,241,.18);
        }
        #refreshMapButton:active {
            transform: translateY(0) scale(.97);
        }
        #refreshMapButton .trix-refresh-icon {
            display: inline-block;
            transition: transform .3s ease;
        }
        #refreshMapButton.is-loading {
            pointer-events: none;
            color: #6366F1;
            border-color: #6366F1;
            background: #EEF2FF;
        }
        #refreshMapButton.is-loading .trix-refresh-icon {
            animation: trixSpin .8s linear infinite;
        }
        #refreshMapButton.is-success {
            background: #ECFDF5;
            border-color: #10B981;
            color: #059669;
        }
        #refreshMapButton.is-success .trix-refresh-icon {
            animation: none;
            transform: rotate(360deg);
        }

        /* Overlay di atas peta saat refresh */
        #fleetMap {
            position: relative;
        }

        .trix-map-overlay {
            position: absolute;
            inset: 0;
            z-index: 500;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity .3s ease;
            background: radial-gradient(circle at center, rgba(99,102,241,.06) 0%, transparent 70%);
        }
        .trix-map-overlay.active {
            opacity: 1;
        }

        /* Scan line yang bergerak dari atas ke bawah */
        .trix-map-overlay::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            height: 40%;
            background: linear-gradient(
                to bottom,
                transparent 0%,
                rgba(99,102,241,.10) 50%,
                transparent 100%
            );
            transform: translateY(-100%);
        }
        .trix-map-overlay.active::before {
            animation: trixScanLine 1.1s ease-in-out;
        }

        /* Pulse ring di tengah */
        .trix-map-overlay::after {
            content: '';
            position: absolute;
            width: 80px;
            height: 80px;
            border-radius: 9999px;
            border: 2px solid rgba(99,102,241,.5);
            top: 50%;
            left: 50%;
            margin-left: -40px;
            margin-top: -40px;
            transform: scale(.9);
            opacity: 0;
        }
        .trix-map-overlay.active::after {
            animation: trixPulseRing 1.1s ease-out;
        }

        /* Flash border pada container peta */
        #fleetMap.map-flash {
            animation: trixMapFlash 1.1s ease-out;
        }

        /* Badge "Refreshing..." di pojok kiri atas peta */
        .trix-refresh-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            z-index: 501;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 9999px;
            background: #1C2A40;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .05em;
            box-shadow: 0 4px 14px rgba(28,42,64,.3);
            opacity: 0;
            transform: translateY(-6px);
            transition: opacity .25s ease, transform .25s ease;
            pointer-events: none;
        }
        .trix-refresh-badge.show {
            opacity: 1;
            transform: translateY(0);
        }
        .trix-refresh-badge .dot {
            width: 6px;
            height: 6px;
            border-radius: 9999px;
            background: #34D399;
            animation: trixLive 1.2s ease-in-out infinite;
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-[#EEF2FB] via-[#F1EEFB] to-[#EAF4F8] text-[#1C2A40] antialiased">

<div class="min-h-screen">

    <!-- SIDEBAR -->
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-[258px] flex-col bg-gradient-to-b from-[#0F1A2E] via-[#131F38] to-[#0B1424] text-white lg:flex">

        <div class="flex h-[104px] items-center justify-center border-b border-white/10 px-5">
            <div class="flex h-[68px] w-full items-center justify-center rounded-xl bg-white px-4">
                <img src="{{ asset('images/trix-logo.jpg') }}"
                     alt="Logo TRIX"
                     class="block max-h-[48px] w-full object-contain">
            </div>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-8">

            <p class="mb-4 px-4 text-[10px] font-bold tracking-[2px] text-slate-400">
                WORKSPACE
            </p>

            <nav class="space-y-1.5">

                <a href="{{ route('hr.dashboard') }}"
                   class="block rounded-xl px-4 py-3 text-sm text-slate-300 hover:bg-white/10">
                    Dashboard
                </a>

                <a href="{{ route('hr.live-monitoring') }}"
                   class="block rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-3 text-sm font-semibold text-white">
                    Live Monitoring
                </a>

                <a href="{{ route('hr.drivers.index') }}"
                   class="block rounded-xl px-4 py-3 text-sm text-slate-300 hover:bg-white/10">
                    Driver Management
                </a>

                <a href="{{ route('hr.vehicles.index') }}"
                   class="block rounded-xl px-4 py-3 text-sm text-slate-300 hover:bg-white/10">
                    Vehicle Management
                </a>

                <a href="{{ route('hr.trips.index') }}"
                   class="block rounded-xl px-4 py-3 text-sm text-slate-300 hover:bg-white/10">
                    Trip Management
                </a>

                <a href="{{ route('hr.trip-history') }}"
                   class="block rounded-xl px-4 py-3 text-sm text-slate-300 hover:bg-white/10">
                    Trip History
                </a>

            </nav>

            <p class="mb-4 mt-9 px-4 text-[10px] font-bold tracking-[2px] text-slate-400">
                PREFERENCES
            </p>

            <a href="{{ route('hr.notifications') }}"
               class="block rounded-xl px-4 py-3 text-sm text-slate-300 hover:bg-white/10">
                Notifications
            </a>

        </div>

        <div class="border-t border-white/10 p-4">

            <div class="mb-4 flex items-center gap-3 rounded-xl bg-white/[0.07] p-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-500 font-bold">
                    {{ strtoupper(substr(auth()->user()->name,0,1)) }}
                </div>

                <div>
                    <p class="text-xs font-semibold">{{ auth()->user()->name }}</p>
                    <p class="mt-1 text-[10px] text-slate-400">HR Administrator</p>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf

                <button class="w-full rounded-xl px-4 py-3 text-left text-sm text-slate-400 hover:bg-rose-500/15 hover:text-rose-300">
                    Sign Out
                </button>
            </form>

        </div>
    </aside>


    <!-- MAIN -->
    <main class="min-h-screen lg:ml-[258px]">

        <!-- TOPBAR -->
        <header class="sticky top-0 z-20 flex h-[76px] items-center justify-between border-b border-[#D6DEEA] bg-white/90 px-5 backdrop-blur-md sm:px-8">

            <div>
                <p class="text-[10px] font-bold tracking-[2px] text-indigo-600">
                    TRIX WAY / WORKSPACE
                </p>

                <h2 class="mt-1 text-sm font-bold">Live Fleet Monitoring</h2>
            </div>

            <div class="text-right">
                <p class="text-xs font-bold">{{ auth()->user()->name }}</p>
                <p class="mt-1 text-[10px] text-slate-500">Human Resources</p>
            </div>

        </header>


        <div class="mx-auto max-w-[1700px] space-y-6 p-5 sm:p-8 lg:p-9">

            <!-- PAGE TITLE -->
            <section class="trix-anim flex flex-col justify-between gap-4 md:flex-row md:items-end">

                <div>
                    <p class="text-xs font-bold text-indigo-600">
                        {{ strtoupper(now()->format('l, d F Y')) }}
                    </p>

                    <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-[34px]">
                        Live <span class="trix-gradient-text">Monitoring</span>
                    </h1>

                    <p class="mt-2 text-sm text-slate-600">
                        Pantau posisi dan aktivitas kendaraan dalam satu tampilan operasional.
                    </p>
                </div>

                <div class="flex items-center gap-2 rounded-full border border-emerald-200 bg-white px-4 py-2.5">
                    <span class="trix-live-dot h-2 w-2 rounded-full bg-emerald-500"></span>

                    <span class="text-[10px] font-bold text-emerald-700">
                        MONITORING READY
                    </span>
                </div>

            </section>


            <!-- KPI -->
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">

                <div class="trix-anim trix-card trix-card-hover rounded-2xl border border-indigo-100 bg-white p-5">

                    <p class="text-xs font-semibold text-slate-600">
                        Active Trips
                    </p>

                    <div class="mt-5 flex items-end justify-between">
                        <h3 id="kpiActiveTrips" class="trix-num text-[32px] font-bold text-indigo-600">
                            —
                        </h3>

                        <span class="text-[10px] text-slate-500">
                            Perjalanan berjalan
                        </span>
                    </div>

                </div>


                <div class="trix-anim trix-card trix-card-hover rounded-2xl border border-cyan-100 bg-white p-5">

                    <p class="text-xs font-semibold text-slate-600">
                        Vehicles Reporting
                    </p>

                    <div class="mt-5 flex items-end justify-between">
                        <h3 id="kpiVehiclesReporting" class="trix-num text-[32px] font-bold text-cyan-600">
                            —
                        </h3>

                        <span class="text-[10px] text-slate-500">
                            Mengirim koordinat
                        </span>
                    </div>

                </div>


                <div class="trix-anim trix-card trix-card-hover rounded-2xl border border-orange-100 bg-white p-5">

                    <p class="text-xs font-semibold text-slate-600">
                        GPS Updates
                    </p>

                    <div class="mt-5 flex items-end justify-between">
                        <h3 id="kpiGpsUpdates" class="trix-num text-[32px] font-bold text-orange-600">
                            —
                        </h3>

                        <span class="text-[10px] text-slate-500">
                            Kendaraan dengan GPS
                        </span>
                    </div>

                </div>

            </section>


            <!-- MAP AND ACTIVITY -->
            <section class="grid grid-cols-1 gap-5 2xl:grid-cols-[minmax(0,1fr)_350px]">

                <!-- MAP -->
                <div id="fleetMapCard" class="trix-anim trix-card overflow-hidden rounded-2xl border border-[#D6DEEA] bg-white">

                    <div class="fleet-map-toolbar flex flex-wrap items-center justify-between gap-3 border-b border-[#E2E7EF] px-5 py-4">

                        <div>
                            <h3 class="text-sm font-bold">Fleet Location Map</h3>

                            <p class="mt-1 text-[11px] text-slate-500">
                                Lokasi kendaraan berdasarkan koordinat GPS driver
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" id="fullscreenMapButton"
                                    class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-[10px] font-bold text-slate-700"
                                    aria-label="Tampilkan peta fullscreen" aria-pressed="false">
                                <svg class="mr-1 inline h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M8 3H5a2 2 0 0 0-2 2v3M16 3h3a2 2 0 0 1 2 2v3M21 16v3a2 2 0 0 1-2 2h-3M3 16v3a2 2 0 0 0 2 2h3"/>
                                </svg>
                                <span id="fullscreenMapText">Fullscreen</span>
                            </button>

                            <button type="button" id="refreshMapButton"
                                    onclick="window.trixFleetRefresh && window.trixFleetRefresh()"
                                    class="rounded-lg border border-indigo-200 bg-white px-3 py-2 text-[10px] font-bold text-indigo-600 hover:bg-indigo-50">
                                <span class="trix-refresh-icon inline-block mr-1">
                                    <svg class="h-3 w-3 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                        <path d="M23 4v6h-6M1 20v-6h6"/>
                                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                                    </svg>
                                </span>
                                <span id="refreshMapText">Refresh Map</span>
                            </button>
                        </div>

                    </div>

                    <div id="fleetMap" style="position:relative;">
                        <!-- OVERLAY REFRESH -->
                        <div id="mapRefreshOverlay" class="trix-map-overlay">
                            <div class="trix-refresh-badge" id="mapRefreshBadge">
                                <span class="dot"></span>
                                REFRESHING MAP…
                            </div>
                        </div>
                    </div>

                    <div class="fleet-map-legend flex flex-wrap items-center gap-6 border-t border-[#E2E7EF] px-5 py-4 text-[10px] text-slate-600">
                        <span>● On Trip</span>
                        <span class="text-emerald-600">● GPS Active</span>
                        <span class="text-orange-500">● Waiting GPS</span>
                    </div>

                </div>


                <!-- ACTIVITY -->
                <aside class="trix-anim trix-card rounded-2xl border border-[#D6DEEA] bg-white p-5">

                    <div class="flex items-center justify-between">

                        <div>
                            <h3 class="text-sm font-bold">Vehicle Activity</h3>

                            <p class="mt-1 text-[11px] text-slate-500">
                                Pilih driver untuk melihat lokasinya
                            </p>
                        </div>

                        <span class="rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-[10px] font-bold text-rose-600">
                            LIVE
                        </span>

                    </div>


                    <!-- SELECTED DRIVER INFO -->
                    <div id="selectedDriverInfo"
                         class="mt-4 hidden rounded-xl border border-indigo-200 bg-indigo-50 p-3">

                        <p class="text-[9px] font-bold uppercase tracking-wider text-indigo-500">
                            Driver Terpilih
                        </p>

                        <p id="selectedDriverName"
                           class="mt-1 text-xs font-bold text-indigo-900">
                            —
                        </p>

                        <p id="selectedDriverLocation"
                           class="mt-1 text-[10px] text-indigo-700">
                            —
                        </p>

                    </div>


                    <!-- DRIVER LIST -->
                    <div id="vehicleActivity"
                         class="mt-5 rounded-xl border border-dashed border-[#C9D4E3] bg-indigo-50/30 px-4 py-10 text-center">

                        <p class="text-xs font-semibold text-slate-700">
                            Memuat data kendaraan...
                        </p>

                    </div>


                    <!-- CONNECTION INFO -->
                    <div class="mt-5 space-y-3 border-t border-[#E2E7EF] pt-4">

                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Last data received</span>

                            <span id="lastDataReceived"
                                  class="font-semibold text-slate-700">
                                —
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Tracking connection</span>

                            <span id="trackingConnection"
                                  class="font-semibold text-amber-600">
                                Menunggu data
                            </span>
                        </div>

                    </div>

                </aside>

            </section>


            <!-- FOOTER -->
            <footer class="flex flex-col justify-between gap-2 border-t border-[#D6DEEA] pt-5 text-[10px] text-slate-500 sm:flex-row">

                <p>© {{ date('Y') }} TRIX WAY · PT TRIX INDONESIA</p>

                <p>FLEET OPERATIONS MANAGEMENT SYSTEM</p>

            </footer>

        </div>

    </main>

</div>


<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const mapElement = document.getElementById('fleetMap');

    if (!mapElement || typeof L === 'undefined') {
        console.error('Leaflet atau elemen peta tidak ditemukan.');
        return;
    }


    // ==========================================
    // INITIALIZE MAP
    // ==========================================

    const fleetMap = L.map('fleetMap').setView([-6.9147, 107.6098], 12);

    window.trixFleetMap = fleetMap;

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(fleetMap);


    // ==========================================
    // CONFIGURATION
    // ==========================================

    const dataUrl = @json(route('hr.live-monitoring.data'));

    const markers = new Map();

    const activeTripsElement = document.getElementById('kpiActiveTrips');
    const reportingElement = document.getElementById('kpiVehiclesReporting');
    const updatesElement = document.getElementById('kpiGpsUpdates');

    const activityElement = document.getElementById('vehicleActivity');

    const lastReceivedElement = document.getElementById('lastDataReceived');
    const connectionElement = document.getElementById('trackingConnection');

    const selectedDriverInfo = document.getElementById('selectedDriverInfo');
    const selectedDriverName = document.getElementById('selectedDriverName');
    const selectedDriverLocation = document.getElementById('selectedDriverLocation');

    const refreshMapButton = document.getElementById('refreshMapButton');


    let hasFittedBounds = false;
    let requestInProgress = false;

    // Menyimpan ID trip yang dipilih HR.
    let selectedTripId = null;

    // Menyimpan data trip terbaru.
    let latestTrips = [];


    // ==========================================
    // HELPER FUNCTIONS
    // ==========================================

    function escapeHtml(value) {

        return String(value ?? '').replace(/[&<>"']/g, function (character) {

            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[character];

        });

    }


    function getSession(trip) {

        return trip.tracking_session || trip.trackingSession || null;

    }


    function getPosition(session) {

        if (!session) return null;

        const latitude = Number(session.latitude);
        const longitude = Number(session.longitude);

        if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
            return null;
        }

        if (latitude < -90 || latitude > 90) return null;
        if (longitude < -180 || longitude > 180) return null;

        if (latitude === 0 && longitude === 0) return null;

        return [latitude, longitude];

    }


    function getLastUpdate(session) {

        return session?.last_received_at
            || session?.last_location_at
            || session?.updated_at
            || null;

    }


    function formatDate(value) {

        if (!value) return 'Belum ada data GPS';

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return 'Waktu tidak diketahui';
        }

        return date.toLocaleString('id-ID');

    }


    function setConnection(message, state) {

        if (!connectionElement) return;

        connectionElement.textContent = message;

        connectionElement.className =
            'font-semibold ' +
            (
                state === 'ok'
                    ? 'text-emerald-600'
                    : state === 'error'
                        ? 'text-rose-600'
                        : 'text-amber-600'
            );

    }


    function getDriverName(trip) {

        return trip.driver?.name || 'Driver';

    }


    function getVehicleName(trip) {

        return trip.vehicle?.plate_number || 'Kendaraan';

    }


    // ==========================================
    // UPDATE SELECTED DRIVER INFORMATION
    // ==========================================

    function updateSelectedDriverInfo(trip) {

        if (!trip) {

            selectedDriverInfo.classList.add('hidden');

            return;
        }

        const session = getSession(trip);
        const position = getPosition(session);

        selectedDriverInfo.classList.remove('hidden');

        selectedDriverName.textContent =
            getDriverName(trip) + ' - ' + getVehicleName(trip);

        selectedDriverLocation.textContent = position
            ? position[0].toFixed(6) + ', ' + position[1].toFixed(6)
            : 'Menunggu koordinat GPS driver';

    }


    // ==========================================
    // FOCUS MAP TO SELECTED DRIVER
    // ==========================================

    function focusDriver(trip) {

        if (!trip) return;

        const markerId = String(trip.id);

        const session = getSession(trip);
        const position = getPosition(session);

        updateSelectedDriverInfo(trip);

        // Driver belum memiliki koordinat GPS.
        if (!position) {

            setConnection('Driver belum mengirim GPS', 'waiting');

            return;
        }

        // Pindahkan peta ke posisi driver.
        fleetMap.flyTo(position, 16, {
            animate: true,
            duration: 1.2
        });

        // Buka popup marker driver.
        const marker = markers.get(markerId);

        if (marker) {

            setTimeout(function () {
                marker.openPopup();
            }, 500);

        }

    }


    // ==========================================
    // RENDER DRIVER ACTIVITY
    // ==========================================

    function renderActivity(trips) {

        if (!activityElement) return;

        if (!trips.length) {

            activityElement.className =
                'mt-5 rounded-xl border border-dashed border-[#C9D4E3] bg-indigo-50/30 px-4 py-10 text-center';

            activityElement.innerHTML = `
                <p class="text-xs font-semibold text-slate-700">
                    Belum ada kendaraan aktif
                </p>

                <p class="mt-1 text-[11px] leading-5 text-slate-500">
                    Kendaraan akan tampil di sini saat driver memulai perjalanan.
                </p>
            `;

            return;
        }


        activityElement.className =
            'mt-5 max-h-[430px] space-y-3 overflow-y-auto';


        activityElement.innerHTML = trips.map(function (trip) {

            const session = getSession(trip);
            const position = getPosition(session);

            const driver = getDriverName(trip);
            const vehicle = getVehicleName(trip);

            const gpsLabel = position ? 'GPS AKTIF' : 'MENUNGGU GPS';

            const gpsClass = position
                ? 'text-emerald-600'
                : 'text-amber-600';

            const coordinates = position
                ? position[0].toFixed(6) + ', ' + position[1].toFixed(6)
                : 'Koordinat belum diterima';

            const isSelected =
                String(selectedTripId) === String(trip.id);

            const selectedClass = isSelected ? 'selected' : '';

            const selectedLabel = isSelected
                ? '<span class="text-[9px] font-bold text-indigo-600">DIPILIH</span>'
                : '';


            return `

                <button
                    type="button"
                    class="driver-select-card ${selectedClass} rounded-xl border border-[#E2E7EF] bg-white p-3"
                    data-trip-id="${escapeHtml(trip.id)}"
                    aria-pressed="${isSelected ? 'true' : 'false'}"
                >

                    <div class="flex items-start justify-between gap-2">

                        <div class="min-w-0">

                            <p class="truncate text-xs font-bold text-slate-800">
                                ${escapeHtml(driver)}
                            </p>

                            <p class="mt-1 text-[11px] text-slate-500">
                                ${escapeHtml(vehicle)}
                            </p>

                        </div>

                        <div class="shrink-0 text-right">

                            <span class="text-[9px] font-bold ${gpsClass}">
                                ${gpsLabel}
                            </span>

                            <div class="mt-1">
                                ${selectedLabel}
                            </div>

                        </div>

                    </div>


                    <div class="mt-3 border-t border-slate-100 pt-2">

                        <p class="text-left text-[10px] text-slate-500">
                            ${escapeHtml(coordinates)}
                        </p>

                        <p class="mt-1 text-left text-[10px] text-slate-400">
                            Update: ${escapeHtml(formatDate(getLastUpdate(session)))}
                        </p>

                        <p class="mt-2 text-left text-[10px] font-semibold text-indigo-600">
                            Klik untuk melihat lokasi di peta →
                        </p>

                    </div>

                </button>

            `;

        }).join('');

    }


    // ==========================================
    // CLICK DRIVER CARD
    // ==========================================

    activityElement.addEventListener('click', function (event) {

        const card = event.target.closest('[data-trip-id]');

        if (!card) return;

        const tripId = String(card.dataset.tripId);

        const trip = latestTrips.find(function (item) {
            return String(item.id) === tripId;
        });

        if (!trip) return;

        // Simpan driver yang sedang dipilih.
        selectedTripId = tripId;

        // Render ulang agar kartu terpilih tersorot.
        renderActivity(latestTrips);

        // Fokuskan peta ke driver.
        focusDriver(trip);

    });


    // ==========================================
    // LOAD LIVE TRACKING DATA
    // ==========================================

    async function loadTracking() {

        if (requestInProgress) return;

        requestInProgress = true;

        try {

            const response = await fetch(dataUrl, {

                method: 'GET',

                credentials: 'same-origin',

                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },

                cache: 'no-store'

            });


            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }


            const result = await response.json();

            const trips = Array.isArray(result.data)
                ? result.data
                : [];


            latestTrips = trips;


            const reportingTrips = [];

            const activeIds = new Set();


            // ==================================
            // UPDATE MARKERS
            // ==================================

            trips.forEach(function (trip) {

                const markerId = String(trip.id);

                activeIds.add(markerId);

                const session = getSession(trip);

                const position = getPosition(session);

                if (!position) return;


                reportingTrips.push({

                    trip: trip,

                    session: session,

                    position: position

                });


                const driver = getDriverName(trip);

                const vehicle = getVehicleName(trip);


                const popup = `

                    <div style="min-width:180px">

                        <strong>${escapeHtml(driver)}</strong>

                        <br>

                        ${escapeHtml(vehicle)}

                        <br><br>

                        Latitude: ${position[0].toFixed(6)}

                        <br>

                        Longitude: ${position[1].toFixed(6)}

                        <br><br>

                        <small>
                            Update:
                            ${escapeHtml(formatDate(getLastUpdate(session)))}
                        </small>

                    </div>

                `;


                if (markers.has(markerId)) {

                    markers.get(markerId)
                        .setLatLng(position)
                        .setPopupContent(popup);

                } else {

                    const marker = L.marker(position)
                        .addTo(fleetMap)
                        .bindPopup(popup);

                    markers.set(markerId, marker);

                }

            });


            // ==================================
            // REMOVE INACTIVE MARKERS
            // ==================================

            for (const [id, marker] of markers.entries()) {

                if (!activeIds.has(id)) {

                    fleetMap.removeLayer(marker);

                    markers.delete(id);

                }

            }


            // ==================================
            // CLEAR SELECTED DRIVER IF INACTIVE
            // ==================================

            if (
                selectedTripId !== null &&
                !activeIds.has(String(selectedTripId))
            ) {

                selectedTripId = null;

                selectedDriverInfo.classList.add('hidden');

            }


            // ==================================
            // UPDATE KPI
            // ==================================

            if (activeTripsElement) {
                activeTripsElement.textContent = trips.length;
            }

            if (reportingElement) {
                reportingElement.textContent = reportingTrips.length;
            }

            if (updatesElement) {
                updatesElement.textContent = reportingTrips.length;
            }


            // ==================================
            // UPDATE ACTIVITY LIST
            // ==================================

            renderActivity(trips);


            // ==================================
            // UPDATE LAST GPS RECEIVED
            // ==================================

            const newest = reportingTrips

                .map(item => getLastUpdate(item.session))

                .filter(Boolean)

                .map(value => new Date(value))

                .filter(date => !Number.isNaN(date.getTime()))

                .sort((a, b) => b.getTime() - a.getTime())[0];


            if (lastReceivedElement) {

                lastReceivedElement.textContent = newest
                    ? formatDate(newest)
                    : 'Belum ada data GPS';

            }


            // ==================================
            // UPDATE CONNECTION STATUS
            // ==================================

            setConnection(

                reportingTrips.length
                    ? 'GPS terhubung'
                    : 'Menunggu data GPS',

                reportingTrips.length ? 'ok' : 'waiting'

            );


            // ==================================
            // KEEP SELECTED DRIVER INFO UPDATED
            // ==================================

            if (selectedTripId !== null) {

                const selectedTrip = trips.find(function (trip) {

                    return String(trip.id) === String(selectedTripId);

                });

                if (selectedTrip) {

                    updateSelectedDriverInfo(selectedTrip);

                }

            }


            // ==================================
            // INITIAL MAP VIEW
            // ==================================

            // Hanya tampilkan seluruh marker saat pertama kali.
            // Setelah HR memilih driver, peta tidak akan dipaksa
            // kembali ke seluruh kendaraan setiap 5 detik.

            if (reportingTrips.length && !hasFittedBounds) {

                const bounds = L.latLngBounds(

                    reportingTrips.map(item => item.position)

                );

                fleetMap.fitBounds(bounds, {

                    padding: [40, 40],

                    maxZoom: 15

                });

                hasFittedBounds = true;

            }

        } catch (error) {

            console.error('TRIX WAY Live Monitoring:', error);

            setConnection('Koneksi bermasalah', 'error');

        } finally {

            requestInProgress = false;

        }

    }


    // ==========================================
    // REFRESH MAP BUTTON
    // ==========================================

    if (refreshMapButton) {

        refreshMapButton.addEventListener('click', function () {

            fleetMap.invalidateSize();

            if (selectedTripId !== null) {

                const selectedTrip = latestTrips.find(function (trip) {

                    return String(trip.id) === String(selectedTripId);

                });

                if (selectedTrip) {

                    focusDriver(selectedTrip);

                    return;

                }

            }

            if (markers.size > 0) {

                const bounds = L.latLngBounds(

                    Array.from(markers.values()).map(marker => marker.getLatLng())

                );

                fleetMap.fitBounds(bounds, {

                    padding: [40, 40],

                    maxZoom: 15

                });

            }

        });

    }


    // ==========================================
    // FULLSCREEN MAP
    // ==========================================

    const mapCard = document.getElementById('fleetMapCard');
    const fullscreenButton = document.getElementById('fullscreenMapButton');
    const fullscreenText = document.getElementById('fullscreenMapText');

    function updateFullscreenButton() {
        const isFullscreen = document.fullscreenElement === mapCard;

        if (fullscreenText) {
            fullscreenText.textContent = isFullscreen ? 'Exit Fullscreen' : 'Fullscreen';
        }

        if (fullscreenButton) {
            fullscreenButton.setAttribute('aria-pressed', isFullscreen ? 'true' : 'false');
            fullscreenButton.setAttribute(
                'aria-label',
                isFullscreen ? 'Keluar dari fullscreen' : 'Tampilkan peta fullscreen'
            );
        }

        // Leaflet perlu menghitung ulang ukuran setelah panel berubah.
        setTimeout(function () {
            fleetMap.invalidateSize({ pan: false });
        }, 150);
    }

    if (mapCard && fullscreenButton) {
        fullscreenButton.addEventListener('click', async function () {
            try {
                if (document.fullscreenElement === mapCard) {
                    await document.exitFullscreen();
                } else if (!document.fullscreenElement) {
                    await mapCard.requestFullscreen();
                }
            } catch (error) {
                console.error('Gagal mengubah mode fullscreen peta:', error);
                alert('Browser tidak dapat membuka peta fullscreen. Coba gunakan browser terbaru.');
            }
        });

        document.addEventListener('fullscreenchange', updateFullscreenButton);
    }

    // ==========================================
    // START MONITORING
    // ==========================================

    setTimeout(function () {

        fleetMap.invalidateSize();

    }, 300);

    // Fungsi refresh manual peta dan data GPS.
window.trixFleetRefresh = function () {
    fleetMap.invalidateSize();
    loadTracking();
};

    loadTracking();


    // Ambil data terbaru setiap 2 detik.
    // requestInProgress mencegah request bertumpuk.
    setInterval(loadTracking, 2000);


});


/* ============================================================
   REFRESH MAP EFFECT — VISUAL FEEDBACK
   (Ditambahkan tanpa mengubah kode di atas)
   ============================================================ */
(function () {
    const btn        = document.getElementById('refreshMapButton');
    const btnText    = document.getElementById('refreshMapText');
    const overlay    = document.getElementById('mapRefreshOverlay');
    const badge      = document.getElementById('mapRefreshBadge');
    const mapEl      = document.getElementById('fleetMap');

    if (!btn || !overlay || !mapEl) return;

    const originalText = btnText ? btnText.textContent : 'Refresh Map';

    let cooldown = false;

    function runRefreshAnimation() {
        if (cooldown) return;
        cooldown = true;

        // 1. State tombol -> loading
        btn.classList.add('is-loading');
        btn.classList.remove('is-success');
        if (btnText) btnText.textContent = 'Refreshing…';

        // 2. Tampilkan overlay peta + badge
        overlay.classList.add('active');
        if (badge) badge.classList.add('show');

        // 3. Flash border peta
        mapEl.classList.add('map-flash');

        // 4. Trigger fungsi refresh yang sudah ada
        if (typeof window.trixFleetRefresh === 'function') {
            window.trixFleetRefresh();
        } else {
            // fallback kalau fungsi belum terdefinisi
            const ev = new Event('click');
            btn.dispatchEvent(ev);
        }

        // 5. Selesai — beri tanda sukses
        setTimeout(function () {
            btn.classList.remove('is-loading');
            btn.classList.add('is-success');
            if (btnText) btnText.textContent = 'Refreshed ✓';

            overlay.classList.remove('active');
            if (badge) badge.classList.remove('show');
            mapEl.classList.remove('map-flash');

            // 6. Reset tombol ke state awal
            setTimeout(function () {
                btn.classList.remove('is-success');
                if (btnText) btnText.textContent = originalText;
                cooldown = false;
            }, 1400);
        }, 1100);
    }

    // Ganti handler default dengan animasi + panggil refresh asli
    btn.addEventListener('click', function (e) {
        // Cegah handler inline double-fire
        e.preventDefault();
        e.stopPropagation();
        runRefreshAnimation();
    }, true);

    // Tangkap pemanggilan programatik (misal dari console) tetap animasi
    const originalTrixRefresh = window.trixFleetRefresh;
    window.trixFleetRefresh = function () {
        if (!cooldown) {
            // kalau dipanggil dari luar tombol, tetap jalankan animasi
            runRefreshAnimation();
            return;
        }
        if (typeof originalTrixRefresh === 'function') {
            originalTrixRefresh();
        }
    };
})();
</script>

</body>
</html>