<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <!-- Local Tailwind CSS -->
    <link rel="stylesheet" href="{{ base_url('assets/css/tailwind.min.css') }}">

    <!-- Leaflet GIS CSS & MarkerCluster CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.css" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.Default.css" />
    <!-- Fonts: Inter for UI typography, Space Mono for coordinates/telemetry -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            overflow: hidden;
            color: #1e293b;
        }

        .font-mono-tech {
            font-family: 'Space Mono', 'Courier New', monospace;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        ::-webkit-scrollbar-track {
            background: #f8fafc;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Leaflet Popups matching Utilitarian GIS theme */
        .leaflet-popup-content-wrapper {
            background: #ffffff !important;
            color: #1e293b !important;
            border-radius: 6px !important;
            border: 1px solid #cbd5e1 !important;
            padding: 0 !important;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08) !important;
            overflow: hidden !important;
        }

        .leaflet-popup-content {
            margin: 0 !important;
            width: 260px !important;
        }

        .leaflet-popup-tip {
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
        }

        .leaflet-popup-close-button {
            color: #64748b !important;
            padding: 4px 6px !important;
            font-size: 13px !important;
        }

        .leaflet-popup-close-button:hover {
            color: #0f172a !important;
        }

        /* Compact Leaflet Marker style */
        .gis-marker-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            transition: transform 0.1s ease;
        }

        .gis-marker-icon:hover {
            transform: scale(1.1);
        }

        /* Flashing recording alert */
        @keyframes pulse-live {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }
        }

        .live-pulse {
            animation: pulse-live 1.2s infinite;
        }
    </style>
</head>

<body class="w-screen h-screen flex flex-col relative text-slate-800">

    <!-- 1. Navigation Header -->
    @include('cctv::frontend.partials.header')

    <!-- 2. Horizontal Search & Filter Bar (Toolbar) -->
    @include('cctv::frontend.partials.toolbar')

    <!-- 3. Large Map Area -->
    <main id="map" class="flex-1 w-full z-[100] relative bg-slate-100">
        <!-- Floating Weather Overlay Container -->
        @include('cctv::frontend.partials.weather')
    </main>

    <!-- Muted Base Map switcher & GIS Status Legend (Bottom-Left Map Overlays) -->
    <div
        class="absolute left-3 sm:left-4 z-[1000] flex flex-col md:flex-row items-start md:items-center gap-2 select-none"
        style="bottom: 3.5rem;">

        <!-- Tileset switcher -->
        <div
            class="flex items-center space-x-1 sm:space-x-1.5 bg-ops-panel border border-ops-border p-1 shadow-sm rounded-md">
            <span
                class="hidden sm:inline text-[10px] font-bold text-slate-400 tracking-wider px-1.5 uppercase">TILESET:</span>
            <button id="btn_layer_street"
                class="px-2 py-0.5 sm:px-2.5 sm:py-1 text-[9px] sm:text-xs font-bold uppercase transition bg-ops-primary text-white border border-ops-primary rounded">
                OSM
            </button>
            <button id="btn_layer_light"
                class="px-2 py-0.5 sm:px-2.5 sm:py-1 text-[9px] sm:text-xs font-bold uppercase transition hover:bg-slate-100 text-slate-600 rounded">
                GRAY
            </button>
            <button id="btn_layer_satellite"
                class="px-2 py-0.5 sm:px-2.5 sm:py-1 text-[9px] sm:text-xs font-bold uppercase transition hover:bg-slate-100 text-slate-600 rounded">
                SAT
            </button>
        </div>

        <!-- GIS Status Legend & Summary Overlay -->
        <div
            class="flex flex-row items-center gap-2 sm:gap-3 bg-ops-panel border border-ops-border p-1.5 shadow-sm rounded-md text-[10px] sm:text-xs font-semibold">
            <!-- Camera Ingested stats -->
            <div class="flex items-center space-x-2 text-slate-500">
                <span>TOT: <strong class="text-slate-900" id="stats_total">0</strong></span>
                <span class="h-2.5 w-[1px] bg-ops-border"></span>
                <span>ON: <strong class="text-emerald-700" id="stats_online">0</strong></span>
                <span class="h-2.5 w-[1px] bg-ops-border"></span>
                <span>OFF: <strong class="text-rose-600" id="stats_offline">0</strong></span>
            </div>

            <div class="hidden sm:block h-3 w-[1px] bg-ops-border"></div>

            <!-- Muted legend tags -->
            <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1">
                <span class="flex items-center text-emerald-700">
                    <span class="w-1.5 h-1.5 bg-emerald-700 rounded-full inline-block mr-1"></span>ONLINE
                </span>
                <span class="flex items-center text-rose-600">
                    <span class="w-1.5 h-1.5 bg-rose-600 rounded-full inline-block mr-1"></span>OFFLINE
                </span>
                <span class="flex items-center text-ops-primary">
                    <span class="w-1.5 h-1.5 bg-ops-primary rounded-full inline-block mr-1"></span>BALAI DESA
                </span>
                <span class="flex items-center text-blue-500">
                    <i class="fa-solid fa-graduation-cap text-[10px] mr-1"></i>SEKOLAH
                </span>
                <span class="flex items-center text-teal-600">
                    <i class="fa-solid fa-mosque text-[10px] mr-1"></i>MASJID
                </span>
                <span class="flex items-center text-slate-600">
                    <i class="fa-solid fa-building text-[10px] mr-1"></i>KANTOR
                </span>
                <span class="flex items-center text-pink-600">
                    <i class="fa-solid fa-mountain-sun text-[10px] mr-1"></i>WISATA
                </span>
            </div>
        </div>
    </div>

    <!-- 4. Playback theater modal -->
    @include('cctv::frontend.partials.modal')

    <!-- 5. Scripts and GIS Logic -->
    @include('cctv::frontend.partials.scripts')

</body>

</html>