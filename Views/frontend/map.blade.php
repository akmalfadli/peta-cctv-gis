<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <!-- Local Tailwind CSS -->
    <link rel="stylesheet" href="{{ base_url('assets/modules/gis/css/tailwind.min.css') }}">

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

        /* Custom Premium CSS Tooltip Popup (Leaflet Popup-style) */
        .gis-tooltip-container {
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .gis-tooltip-popup {
            display: none;
            position: absolute;
            bottom: 140%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #ffffff;
            color: #1e293b;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 5px 8px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
            font-size: 9px;
            font-weight: 700;
            white-space: nowrap;
            z-index: 1005;
            pointer-events: none;
        }

        .gis-tooltip-popup::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #ffffff transparent transparent transparent;
        }

        .gis-tooltip-popup::before {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -6px;
            border-width: 6px;
            border-style: solid;
            border-color: #cbd5e1 transparent transparent transparent;
            z-index: -1;
        }

        .gis-tooltip-container:hover .gis-tooltip-popup {
            display: block;
        }
    </style>
</head>

<body class="w-screen h-screen flex flex-col relative text-slate-800">

    <!-- 1. Navigation Header -->
    @include('gis::frontend.partials.header')

    <!-- 2. Horizontal Search & Filter Bar (Toolbar) -->
    @include('gis::frontend.partials.toolbar')

    <!-- 3. Large Map Area -->
    <main id="map" class="flex-1 w-full z-[100] relative bg-slate-100">
        <!-- Floating Weather Overlay Container -->
        @include('gis::frontend.partials.weather')

        <!-- Floating Project Details Slide-over Panel (Disabled/Hidden in favor of Leaflet Popup) -->
        <div id="project_details_panel" style="display: none;"
            class="absolute top-4 right-4 z-[1001] w-80 max-w-[calc(100vw-32px)] bg-white border border-ops-border shadow-2xl rounded-md transition-all transform translate-x-[120%] duration-300 overflow-hidden flex flex-col max-h-[calc(100vh-140px)]">
            <!-- Header -->
            <div class="px-4 py-3 bg-slate-900 text-white flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider"><i
                        class="fa-solid fa-road mr-1.5 text-sky-400"></i>DETAIL PROYEK</h3>
                <button id="close_details_panel" class="text-slate-400 hover:text-white transition text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <!-- Content Scroll -->
            <div class="p-4 overflow-y-auto space-y-4 text-xs">
                <!-- Photo -->
                <div id="panel_photo_container"
                    class="w-full aspect-[4/3] bg-slate-150 rounded-md overflow-hidden relative border border-slate-200">
                    <img id="panel_photo" src="" alt="Dokumentasi Proyek" class="w-full h-full object-cover hidden">
                    <div id="panel_photo_placeholder"
                        class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
                        <i class="fa-regular fa-image text-3xl"></i>
                        <span class="text-[10px] mt-1">Belum ada foto</span>
                    </div>
                </div>
                <!-- Title -->
                <div>
                    <h4 id="panel_title" class="text-sm font-extrabold text-slate-900 leading-tight uppercase">NAMA
                        PROYEK</h4>
                    <span id="panel_type_badge"
                        class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold uppercase mt-1">TIPE</span>
                </div>
                <!-- Specs Table -->
                <div class="border border-slate-150 rounded-md overflow-hidden bg-slate-50">
                    <div class="grid grid-cols-3 border-b border-slate-150 py-2 px-3">
                        <span class="text-slate-400 font-medium">KATEGORI</span>
                        <span id="panel_kategori_badge"
                            class="col-span-2 text-right font-bold text-[10px] uppercase">-</span>
                    </div>
                    <div class="grid grid-cols-3 border-b border-slate-150 py-2 px-3">
                        <span class="text-slate-400 font-medium">ANGGARAN</span>
                        <span id="panel_budget"
                            class="col-span-2 text-slate-900 font-bold text-right text-emerald-700">Rp 0</span>
                    </div>
                    <div class="grid grid-cols-3 border-b border-slate-150 py-2 px-3">
                        <span class="text-slate-400 font-medium">SUMBER DANA</span>
                        <span id="panel_source" class="col-span-2 text-slate-800 text-right">-</span>
                    </div>
                    <div class="grid grid-cols-3 border-b border-slate-150 py-2 px-3">
                        <span class="text-slate-400 font-medium">LOKASI</span>
                        <span id="panel_location" class="col-span-2 text-slate-800 text-right">-</span>
                    </div>
                    <div class="grid grid-cols-3 border-b border-slate-150 py-2 px-3">
                        <span class="text-slate-400 font-medium">VOLUME</span>
                        <span id="panel_volume" class="col-span-2 text-slate-800 text-right">-</span>
                    </div>
                    <div class="grid grid-cols-3 py-2 px-3">
                        <span class="text-slate-400 font-medium">PELAKSANA</span>
                        <span id="panel_executor" class="col-span-2 text-slate-800 text-right">-</span>
                    </div>
                </div>
                <!-- Center Map Focus -->
                <button id="btn_zoom_project"
                    class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold uppercase rounded transition text-[10px] flex items-center justify-center space-x-1 border border-slate-250">
                    <i class="fa-solid fa-crosshairs"></i>
                    <span>FOKUS PETA</span>
                </button>
            </div>
        </div>
    </main>

    <!-- Muted Base Map switcher & GIS Status Legend (Bottom-Left Map Overlays) -->
    <div class="absolute left-3 sm:left-4 z-[1000] flex flex-col md:flex-row items-start md:items-center gap-2 select-none"
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
            class="bg-ops-panel border border-ops-border shadow-sm rounded-md text-[10px] p-1.5 font-semibold select-none min-w-[300px] sm:min-w-0">

             <!-- Always-visible stats row + legend toggle on mobile -->
            <div class="flex items-center gap-2 px-2 p-1 sm:px-3 sm:py-2">
                <!-- Stats: always visible -->
                <div class="flex items-center gap-2 text-slate-500">
                    <span class="flex items-center gap-2 gis-tooltip-container">
                        <i class="fa-solid fa-video text-slate-400 text-[9px]"></i>
                        <strong class="text-slate-900" id="stats_total">0</strong>
                        <span class="gis-tooltip-popup">Total Kamera CCTV Terpasang</span>
                    </span>
                    <span class="h-2.5 w-[1px] bg-ops-border"></span>
                    <span class="flex items-center gap-2 gis-tooltip-container">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full shrink-0"></span>
                        <strong class="text-emerald-700" id="stats_online">0</strong>
                        <span class="gis-tooltip-popup">Kamera CCTV yang Aktif / Online</span>
                    </span>
                    <span class="h-2.5 w-[1px] bg-ops-border"></span>
                    <span class="flex items-center gap-2 gis-tooltip-container">
                        <i class="fa-solid fa-road text-slate-400 text-[9px]"></i>
                        <strong class="text-sky-700" id="stats_pembangunans">0</strong>
                        <span class="gis-tooltip-popup">Total Proyek Pembangunan Desa</span>
                    </span>
                </div>

                <!-- Legend toggle button (mobile) / divider (desktop) -->
                <button id="btn_legend_toggle"
                    class="sm:hidden h-5 w-5 flex items-center justify-center rounded bg-slate-100 hover:bg-slate-200 text-slate-500 transition shrink-0"
                    title="Tampilkan Keterangan Detail Peta">
                    <i class="fa-solid fa-circle-info text-[9px]"></i>
                </button>

                <!-- Inline legend: hidden on mobile, shown on sm+ -->
                <div class="hidden sm:flex items-center gap-2">
                    <span class="h-2.5 w-[1px] bg-ops-border shrink-0"></span>

                    <!-- CCTV status -->
                    <span class="flex items-center gap-2 text-emerald-700 gis-tooltip-container">
                        <span class="w-1.5 h-1.5 bg-green-600 rounded-full shrink-0"></span>ONLINE
                        <span class="gis-tooltip-popup">Kamera CCTV Aktif / Berfungsi</span>
                    </span>
                    <span class="flex items-center gap-2 text-rose-600 gis-tooltip-container">
                        <span class="w-1.5 h-1.5 bg-rose-600 rounded-full shrink-0"></span>OFFLINE
                        <span class="gis-tooltip-popup">Kamera CCTV Tidak Aktif / Offline</span>
                    </span>

                    <span class="h-2.5 w-[1px] bg-ops-border shrink-0"></span>

                    <!-- Pembangunan categories -->
                    <span class="flex items-center gap-2 text-blue-600 gis-tooltip-container">
                        <span class="w-2.5 h-[3px] bg-blue-500 rounded-sm shrink-0"></span>INFRA
                        <span class="gis-tooltip-popup">Bidang Pembangunan: Infrastruktur Desa</span>
                    </span>
                    <span class="flex items-center gap-2 text-indigo-600 gis-tooltip-container">
                        <span class="w-2.5 h-[3px] bg-indigo-500 rounded-sm shrink-0"></span>PEND
                        <span class="gis-tooltip-popup">Bidang Pembangunan: Pendidikan</span>
                    </span>
                    <span class="flex items-center gap-2 text-red-600 gis-tooltip-container">
                        <span class="w-2.5 h-[3px] bg-red-500 rounded-sm shrink-0"></span>KES
                        <span class="gis-tooltip-popup">Bidang Pembangunan: Kesehatan</span>
                    </span>
                    <span class="flex items-center gap-2 text-emerald-600 gis-tooltip-container">
                        <span class="w-2.5 h-[3px] bg-emerald-500 rounded-sm shrink-0"></span>EKO
                        <span class="gis-tooltip-popup">Bidang Pembangunan: Ekonomi Desa</span>
                    </span>
                    <span class="flex items-center gap-2 text-amber-600 gis-tooltip-container">
                        <span class="w-2.5 h-[3px] bg-amber-400 rounded-sm shrink-0"></span>LING
                        <span class="gis-tooltip-popup">Bidang Pembangunan: Lingkungan & Bencana</span>
                    </span>

                    <span class="h-2.5 w-[1px] bg-ops-border shrink-0"></span>

                    <!-- Type indicators -->
                    <span class="flex items-center gap-2 text-slate-500 gis-tooltip-container">
                        <span class="w-3 h-[3px] bg-slate-400 rounded-sm shrink-0"></span>JALAN
                        <span class="gis-tooltip-popup">Tipe Proyek: Pembangunan Jalan / Jembatan (Garis Lintasan)</span>
                    </span>
                    <span class="flex items-center gap-2 text-slate-500 gis-tooltip-container">
                        <i class="fa-solid fa-location-dot text-[9px] shrink-0"></i>GEDUNG
                        <span class="gis-tooltip-popup">Tipe Proyek: Gedung / Bangunan Fisik (Titik Lokasi)</span>
                    </span>
                </div>
            </div>

        </div>
    </div>

    <!-- 4. Playback theater modal -->
    @include('gis::frontend.partials.modal')

    <!-- 5. Scripts and GIS Logic -->
    @include('gis::frontend.partials.scripts')

</body>

</html>