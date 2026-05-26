<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <!-- Tailwind CSS for functional utility layout -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ops: {
                            bg: '#f8fafc',          // Muted light gray
                            panel: '#ffffff',       // Pure white panels
                            border: '#e2e8f0',      // Clean slate-200 borders
                            textMain: '#1e293b',    // Slate-800 text
                            textMuted: '#64748b',   // Slate-500 muted labels
                            primary: '#1e3a8a',     // Muted Navy Blue (government operational)
                            primaryHover: '#172554',
                            success: '#2e7d32',     // Muted Green (Online)
                            danger: '#c62828',      // Muted Red (Offline)
                            inactive: '#757575',    // Muted Gray
                            hoverbg: '#f1f5f9'
                        }
                    }
                }
            }
        }
    </script>
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

        /* Custom operational scrollbar */
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

    <!-- 1. Compact Top Navigation Header (Height: 52px) -->
    <header
        class="w-full h-[52px] bg-ops-panel border-b border-ops-border px-3 sm:px-4 flex items-center justify-between z-[1002] shrink-0">
        <div class="flex items-center space-x-2 sm:space-x-3 overflow-hidden mr-2">
            @if(!empty($desa->logo))
                <img src="{{ gambar_desa($desa->logo) }}" alt="Logo" class="h-6 sm:h-7 w-auto shrink-0">
            @else
                <div
                    class="w-6 h-6 sm:w-7 sm:h-7 bg-ops-primary flex items-center justify-center text-white font-bold text-[10px] sm:text-xs shadow-sm rounded-md shrink-0">
                    CCTV
                </div>
            @endif
            <div class="overflow-hidden">
                <h1
                    class="text-slate-900 font-extrabold text-[10px] sm:text-xs md:text-sm tracking-wider uppercase leading-none truncate max-w-[150px] xs:max-w-[220px] sm:max-w-[340px] md:max-w-none">
                    PETA PEMANTAUAN CCTV &mdash; {{ strtoupper(setting('sebutan_desa') ?: 'DESA') }}
                    {{ strtoupper($desa->nama_desa ?: 'WIRADESA') }}
                </h1>
                <p
                    class="text-ops-textMuted text-[8px] sm:text-[10px] tracking-wider uppercase mt-0.5 font-semibold truncate">
                    PEMERINTAH KABUPATEN PURBALINGGA &bull; PORTAL GIS
                </p>
            </div>
        </div>

        <!-- Minimal Navigation -->
        <div class="flex items-center space-x-2 sm:space-x-4 shrink-0">
            <div
                class="hidden md:flex items-center space-x-1.5 bg-ops-bg border border-ops-border px-2.5 py-1 text-xs text-slate-700 font-bold rounded-md">
                <i class="fa-regular fa-clock text-slate-400"></i>
                <span id="live_clock">0000-00-00 00:00:00 WIB</span>
            </div>
            @if(can('l'))
                <a href="{{ site_url('cctv_admin') }}"
                    class="px-2 py-1 sm:px-2.5 sm:py-1 border border-ops-border bg-ops-bg hover:bg-slate-200 text-slate-700 hover:text-slate-900 text-[10px] sm:text-xs font-bold uppercase rounded-md transition">
                    <i class="fa-solid fa-gear sm:mr-1"></i><span class="hidden sm:inline">ADMINISTRASI</span>
                </a>
            @endif
        </div>
    </header>

    <!-- 2. Compact Horizontal Search & Filter Bar (Toolbar Structure) -->
    <section
        class="w-full h-12 bg-ops-bg border-b border-ops-border px-3 sm:px-4 flex flex-row items-center justify-between gap-4 z-[1002] shrink-0 overflow-x-auto scrollbar-thin">

        <!-- Filters (Left side) -->
        <div class="flex items-center space-x-2 shrink-0 py-1">
            <!-- Search field -->
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" id="search_input"
                    class="w-36 sm:w-44 md:w-52 bg-white border border-ops-border rounded-md py-1 pl-7 pr-2 h-8 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-slate-400 uppercase"
                    placeholder="CARI KAMERA...">
            </div>

            <!-- Category select -->
            <select id="category_select"
                class="bg-white border border-ops-border rounded-md px-2 h-8 text-xs text-slate-900 focus:outline-none">
                <option value="">KATEGORI</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ strtoupper($cat->name) }}</option>
                @endforeach
            </select>

            <!-- Status select -->
            <select id="status_select"
                class="bg-white border border-ops-border rounded-md px-2 h-8 text-xs text-slate-900 focus:outline-none">
                <option value="">STATUS</option>
                <option value="online">● ONLINE</option>
                <option value="offline">● OFFLINE</option>
            </select>

            <!-- Reset filter button -->
            <button id="btn_reset_filter"
                class="h-8 border border-ops-border bg-white hover:bg-slate-100 text-slate-600 hover:text-slate-900 px-2.5 text-xs font-bold uppercase rounded-md transition flex items-center space-x-1">
                <i class="fa-solid fa-undo"></i>
                <span class="hidden xs:inline">RESET</span>
            </button>
        </div>

    </section>

    <!-- 3. Large Map Area - Dominates 85%+ visible space -->
    <main id="map" class="flex-1 w-full z-[100] relative bg-slate-100"></main>

    <!-- Muted Base Map switcher & GIS Status Legend (Bottom-Left Map Overlays) -->
    <div
        class="absolute bottom-4 left-3 sm:bottom-6 sm:left-4 z-[1000] flex flex-col md:flex-row items-start md:items-center gap-2 select-none">

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

    <!-- 4. Playback Surveillance theater mode modal (YouTube/CCTV look) -->
    <div id="stream_modal"
        class="fixed inset-0 z-[2000] hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm p-2 md:p-6 transition-all duration-150">

        <!-- Clean operational surveillance viewport container -->
        <div
            class="relative w-full max-w-4xl bg-white border border-ops-border shadow-2xl flex flex-col rounded-md overflow-hidden">

            <!-- Video Frame (surveillance HUD) -->
            <div class="bg-black aspect-video flex items-center justify-center relative w-full overflow-hidden"
                id="player_viewport_wrapper">
                <div id="player_viewport" class="w-full h-full flex items-center justify-center"></div>

                <!-- REAL SURVEILLANCE OVERLAYS (Light Theme) -->
                <!-- Bottom-Left overlay: Camera Name & Location with Record Dot -->
                <div
                    class="absolute bottom-3 left-3 sm:bottom-4 sm:left-4 z-20 bg-white/95 border border-slate-200 text-slate-800 font-mono-tech text-[8px] sm:text-[10px] tracking-wider px-2 py-0.5 sm:px-2.5 sm:py-1 pointer-events-none select-none shadow-sm rounded-md flex items-center space-x-1.5 sm:space-x-2">
                    <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-rose-600 live-pulse shrink-0"></span>
                    <span>
                        <span id="cctv_overlay_name" class="font-bold text-slate-900">CAMERA</span> &bull; <span
                            id="cctv_overlay_desc" class="text-slate-600">LOCATION</span>
                    </span>
                </div>

                <!-- Bottom-Right overlay: GPS coords (Hidden on mobile to prevent overlay collisions) -->
                <div
                    class="hidden md:block absolute bottom-4 right-4 z-20 bg-white/95 border border-slate-200 text-slate-800 font-mono-tech text-[10px] tracking-wider px-2.5 py-1 pointer-events-none select-none shadow-sm rounded-md">
                    GPS: <span id="cctv_overlay_coords" class="text-slate-600 font-bold">0.0000, 0.0000</span>
                </div>
            </div>
        </div>

        <!-- Theater mode absolute close button -->
        <button id="close_stream_modal"
            class="absolute top-4 right-4 z-[2010] text-slate-700 hover:text-slate-900 border border-slate-300 bg-white hover:bg-slate-100 px-3 py-1.5 text-[9px] sm:text-[10px] font-mono-tech font-bold uppercase cursor-pointer rounded-md shadow-sm transition">
            CLOSE
        </button>
    </div>

    <!-- Leaflet JS & MarkerCluster JS & Hls.js -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@1.4.12/dist/hls.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var camerasData = [];
            var hlsPlayer = null;

            // Header live ticking clock
            function updateClock() {
                var now = new Date();
                var yyyy = now.getFullYear();
                var mm = String(now.getMonth() + 1).padStart(2, '0');
                var dd = String(now.getDate()).padStart(2, '0');
                var hh = String(now.getHours()).padStart(2, '0');
                var min = String(now.getMinutes()).padStart(2, '0');
                var ss = String(now.getSeconds()).padStart(2, '0');
                var clockText = `${yyyy}-${mm}-${dd} ${hh}:${min}:${ss} WIB`;
                var clockEl = document.getElementById('live_clock');
                if (clockEl) clockEl.innerText = clockText;
            }
            setInterval(updateClock, 1000);
            updateClock();

            // Default Map coordinate setup (Wiradesa Center)
            var defaultLat = {{ !empty($desa->lat) ? (float) $desa->lat : -7.382046 }};
            var defaultLng = {{ !empty($desa->lng) ? (float) $desa->lng : 109.364406 }};

            // Initialize Leaflet Viewport (standard Leaflet controls)
            var map = L.map('map', {
                zoomControl: false,
                attributionControl: false,
                maxZoom: 21
            }).setView([defaultLat, defaultLng], 16);

            L.control.zoom({
                position: 'bottomright'
            }).addTo(map);

            L.control.scale({
                imperial: false,
                position: 'bottomright'
            }).addTo(map);

            // Establish base maps
            var layerStreet = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 21,
                maxNativeZoom: 19
            });
            var layerLight = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                maxZoom: 21,
                maxNativeZoom: 20
            });
            var layerSatellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 21,
                maxNativeZoom: 19
            });

            // Default base map: standard OpenStreetMap
            layerStreet.addTo(map);

            // Base map togglers logic
            function updateBaseMapLayer(activeLayer, activeBtnId) {
                map.removeLayer(layerStreet);
                map.removeLayer(layerLight);
                map.removeLayer(layerSatellite);

                activeLayer.addTo(map);

                // Update active button styling
                ['btn_layer_street', 'btn_layer_light', 'btn_layer_satellite'].forEach(id => {
                    var el = document.getElementById(id);
                    if (id === activeBtnId) {
                        el.className = "px-2 py-0.5 sm:px-2.5 sm:py-1 text-[9px] sm:text-xs font-bold uppercase transition bg-ops-primary text-white border border-ops-primary rounded";
                    } else {
                        el.className = "px-2 py-0.5 sm:px-2.5 sm:py-1 text-[9px] sm:text-xs font-bold uppercase transition hover:bg-slate-100 text-slate-600 rounded";
                    }
                });
            }

            document.getElementById('btn_layer_street').addEventListener('click', () => updateBaseMapLayer(layerStreet, 'btn_layer_street'));
            document.getElementById('btn_layer_light').addEventListener('click', () => updateBaseMapLayer(layerLight, 'btn_layer_light'));
            document.getElementById('btn_layer_satellite').addEventListener('click', () => updateBaseMapLayer(layerSatellite, 'btn_layer_satellite'));

            // Layer marker cluster
            var markerClusterGroup = L.markerClusterGroup({
                showCoverageOnHover: false,
                maxClusterRadius: 40
            });
            map.addLayer(markerClusterGroup);

            // Add Village Office Marker to Map (Muted square pin)
            var officeHtml = `
                <div class="w-6 h-6 bg-ops-primary border border-white text-white flex items-center justify-center shadow-sm font-bold text-xs rounded-md">
                    <i class="fa-solid fa-building-flag text-[9px]"></i>
                </div>
            `;
            var officeIcon = L.divIcon({
                html: officeHtml,
                className: 'gis-office-icon',
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });
            L.marker([defaultLat, defaultLng], { icon: officeIcon })
                .bindPopup('<div class="p-2 font-mono-tech text-[10px]"><h4 class="text-slate-900 font-extrabold text-[11px] uppercase border-b border-slate-200 pb-1 mb-1">{{ setting("sebutan_desa") ?: "Kantor Desa" }} {{ $desa->nama_desa ?: "Wiradesa" }}</h4><p class="text-slate-500 text-[9px] mt-1">Pusat Administrasi & Integrasi GIS</p></div>')
                .addTo(map);

            // Fetch CCTV data
            fetchCameras();

            function fetchCameras() {
                var categoryId = document.getElementById('category_select').value;
                var status = document.getElementById('status_select').value;
                var search = document.getElementById('search_input').value;

                var url = "{{ ci_route('cctv.api_cameras') }}?search=" + encodeURIComponent(search) +
                    "&category_id=" + categoryId +
                    "&status=" + status;

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        camerasData = data;
                        renderUI();
                        updateToolbarStats();
                    })
                    .catch(err => {
                        console.error('Error fetching cameras:', err);
                    });
            }

            // Sync stats
            function updateToolbarStats() {
                var total = camerasData.length;
                var online = camerasData.filter(c => c.status === 'online').length;
                var offline = total - online;

                document.getElementById('stats_total').innerText = total;
                document.getElementById('stats_online').innerText = online;
                document.getElementById('stats_offline').innerText = offline;
            }

            // Dynamic category icon and color mapper
            function getCategoryIconAndColor(categoryName, isOnline) {
                var name = (categoryName || '').toLowerCase();
                var statusDot = isOnline ? 'bg-emerald-500' : 'bg-rose-500';

                // Defaults (Standard CCTV)
                var iconClass = 'fa-solid fa-video';
                var bgColor = isOnline ? '#10b981' : '#ef4444'; // Emerald-500 / Rose-500

                if (name.includes('sekolah') || name.includes('school') || name.includes('sd') || name.includes('smp') || name.includes('sma') || name.includes('madrasah') || name.includes('tk') || name.includes('paud')) {
                    iconClass = 'fa-solid fa-graduation-cap';
                    bgColor = '#3b82f6'; // Blue-500
                } else if (name.includes('masjid') || name.includes('mosque') || name.includes('mushola') || name.includes('ibadah')) {
                    iconClass = 'fa-solid fa-mosque';
                    bgColor = '#0d9488'; // Teal-600
                } else if (name.includes('kantor') || name.includes('office') || name.includes('dinas') || name.includes('balai') || name.includes('pemerintah')) {
                    iconClass = 'fa-solid fa-building';
                    bgColor = '#475569'; // Slate-600
                } else if (name.includes('wisata') || name.includes('tourist') || name.includes('pantai') || name.includes('taman') || name.includes('rekreasi') || name.includes('curug') || name.includes('atraksi')) {
                    iconClass = 'fa-solid fa-mountain-sun';
                    bgColor = '#db2777'; // Pink-600
                } else if (name.includes('pasar') || name.includes('market') || name.includes('toko') || name.includes('warung') || name.includes('mart')) {
                    iconClass = 'fa-solid fa-store';
                    bgColor = '#ea580c'; // Orange-600
                } else if (name.includes('puskesmas') || name.includes('kesehatan') || name.includes('klinik') || name.includes('posyandu') || name.includes('dokter') || name.includes('medis')) {
                    iconClass = 'fa-solid fa-house-chimney-medical';
                    bgColor = '#e11d48'; // Rose-600
                }

                return { iconClass: iconClass, bgColor: bgColor, statusDot: statusDot };
            }

            // Render camera coordinate indicators
            function renderUI() {
                markerClusterGroup.clearLayers();
                var latLngs = [];

                camerasData.forEach((cam, index) => {
                    var isOnline = cam.status === 'online';
                    var categoryName = cam.category || '';
                    var iconInfo = getCategoryIconAndColor(categoryName, isOnline);
                    var hasStream = !!cam.stream_url;

                    // GIS DivIcon matching municipal square marker look
                    var iconHtml = `
                        <div class="relative w-7 h-7 flex items-center justify-center rounded-lg text-white shadow-md border-2 border-white transition-all duration-200" style="background-color: ${iconInfo.bgColor}">
                            <i class="${iconInfo.iconClass} text-[10px]"></i>
                            ${hasStream ? `
                                <span class="absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full border border-white ${iconInfo.statusDot} ${isOnline ? 'live-pulse' : ''}"></span>
                            ` : ''}
                        </div>
                    `;

                    var customIcon = L.divIcon({
                        html: iconHtml,
                        className: 'gis-leaflet-icon',
                        iconSize: [28, 28],
                        iconAnchor: [14, 14]
                    });

                    var marker = L.marker([cam.latitude, cam.longitude], {
                        icon: customIcon
                    });

                    var statusText = hasStream ? (isOnline ? '● ONLINE' : '● OFFLINE') : '● TEMPAT';
                    var statusClass = hasStream ? (isOnline ? 'text-emerald-700' : 'text-rose-600') : 'text-slate-500';

                    // Utilitarian Popups matching exact PRD: Name, Status, Thumbnail, Location, Action
                    var popupHtml = `
                        <div class="p-3 text-xs text-slate-800">
                            <div class="flex justify-between items-center border-b border-slate-200 pb-1.5 mb-2 font-extrabold uppercase text-xs text-slate-900">
                                <span class="truncate pr-1">${cam.name}</span>
                                <span class="${statusClass} shrink-0">${statusText}</span>
                            </div>
                            ${cam.thumbnail ?
                            `<div class="aspect-video w-full bg-cover bg-center border border-slate-200 mb-2" style="background-image: url('${cam.thumbnail}')"></div>` :
                            `<div class="aspect-video w-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 mb-2 font-semibold text-[10px]">NO PHOTO CACHE</div>`
                        }
                            <div class="text-[11px] text-slate-500 mb-2.5 uppercase font-medium">
                                Lokasi: ${cam.description || 'Marker Lokasi Desa'}
                            </div>
                            ${hasStream ? `
                                <button onclick="openStreamModal('${cam.id}')" class="w-full bg-ops-primary hover:bg-ops-primaryHover text-white rounded-md py-1.5 text-xs font-bold tracking-wider border border-ops-primary transition uppercase">
                                    Buka Live Stream
                                </button>
                            ` : `
                                <div class="w-full bg-slate-100 text-slate-500 rounded-md py-1.5 text-center text-[10px] font-bold tracking-wider border border-slate-200 uppercase">
                                    Hanya Marker Lokasi
                                </div>
                            `}
                        </div>
                    `;

                    marker.bindPopup(popupHtml, { closeButton: false });
                    markerClusterGroup.addLayer(marker);
                    latLngs.push([cam.latitude, cam.longitude]);
                });
            }

            // --- survaillance theater mode modal playback ---
            window.openStreamModal = function (id) {
                console.log("openStreamModal triggered for ID:", id, "Type:", typeof id);
                var cam = camerasData.find(c => String(c.id) === String(id));
                if (!cam) {
                    console.error("Camera not found in camerasData for ID:", id, "Available:", camerasData);
                    return;
                }

                // Load modal overlays
                document.getElementById('cctv_overlay_name').innerText = cam.name;
                document.getElementById('cctv_overlay_desc').innerText = cam.description || 'BALAI DESA';

                var lat = typeof cam.latitude === 'number' ? cam.latitude : parseFloat(cam.latitude) || 0;
                var lng = typeof cam.longitude === 'number' ? cam.longitude : parseFloat(cam.longitude) || 0;
                document.getElementById('cctv_overlay_coords').innerText = lat.toFixed(4) + ', ' + lng.toFixed(4);

                var viewport = document.getElementById('player_viewport');
                viewport.innerHTML = ''; // reset

                // Build HLS/Iframe/YouTube elements only when opening
                if (cam.stream_type === 'hls') {
                    var video = document.createElement('video');
                    video.id = 'video_player';
                    video.className = 'w-full h-full object-contain z-10 relative';
                    video.controls = true;
                    video.autoplay = false;
                    video.playsInline = true;
                    viewport.appendChild(video);

                    if (Hls.isSupported()) {
                        hlsPlayer = new Hls();
                        hlsPlayer.loadSource(cam.stream_url);
                        hlsPlayer.attachMedia(video);
                        createCctvConnectOverlay(video, () => video.play(), cam.status);
                    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                        video.src = cam.stream_url;
                        createCctvConnectOverlay(video, () => video.play(), cam.status);
                    } else {
                        viewport.innerHTML = `
                            <div class="text-center py-10 text-rose-500 font-mono-tech text-xs z-10">
                                [!] HLS PROTOCOL UNSUPPORTED BY THIS BROWSER
                            </div>
                        `;
                    }
                } else if (cam.stream_type === 'youtube') {
                    var iframe = document.createElement('iframe');
                    iframe.className = 'w-full h-full border-none z-10 relative';
                    iframe.src = cam.stream_url;
                    iframe.allow = "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture";
                    iframe.allowFullscreen = true;
                    viewport.appendChild(iframe);
                } else if (cam.stream_type === 'iframe') {
                    var srcUrl = cam.stream_url;
                    if (cam.stream_url.includes('<iframe')) {
                        var match = cam.stream_url.match(/src="([^"]+)"/);
                        if (match) {
                            srcUrl = match[1];
                        }
                    }

                    var iframe = document.createElement('iframe');
                    iframe.className = 'w-full h-full border-none z-10 relative';
                    iframe.src = srcUrl;
                    iframe.sandbox = "allow-scripts allow-same-origin allow-popups";
                    iframe.allowFullscreen = true;
                    viewport.appendChild(iframe);
                }

                var modal = document.getElementById('stream_modal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            // Create flat Connect HUD overlay
            function createCctvConnectOverlay(videoEl, playCallback, status) {
                var overlay = document.createElement('div');
                overlay.className = 'absolute inset-0 bg-white/95 flex flex-col items-center justify-center cursor-pointer z-10 border border-slate-200';

                if (status === 'offline') {
                    overlay.innerHTML = `
                        <span class="text-rose-700 text-[10px] font-bold font-mono-tech tracking-wider uppercase border border-rose-300 bg-rose-50 px-2.5 py-1 mb-1 shadow-sm">[!] FEED OFFLINE</span>
                        <span class="text-slate-500 text-[8px] font-mono-tech">LOST INGEST SYNC</span>
                    `;
                    videoEl.parentNode.appendChild(overlay);
                    return;
                }

                overlay.innerHTML = `
                    <div class="w-8 h-8 border border-slate-300 bg-slate-50 flex items-center justify-center text-slate-700 mb-2 font-mono-tech text-xs hover:bg-slate-100 transition shadow-sm">
                        ▶
                    </div>
                    <span class="text-slate-600 text-[9px] font-bold font-mono-tech tracking-wider uppercase">CONNECT LIVE STREAM</span>
                `;

                videoEl.parentNode.appendChild(overlay);

                overlay.addEventListener('click', () => {
                    overlay.remove();
                    playCallback();
                });
            }

            // Close stream modal and destroy instances completely to preserve client bandwidth
            function closeStreamModal() {
                var modal = document.getElementById('stream_modal');
                modal.classList.remove('flex');
                modal.classList.add('hidden');

                var viewport = document.getElementById('player_viewport');
                var video = document.getElementById('video_player');

                if (hlsPlayer) {
                    hlsPlayer.destroy();
                    hlsPlayer = null;
                }

                if (video) {
                    video.pause();
                    video.removeAttribute('src');
                    video.load();
                }

                viewport.innerHTML = ''; // flush
            }

            document.getElementById('close_stream_modal').addEventListener('click', closeStreamModal);

            // Filter Event handlers
            document.getElementById('category_select').addEventListener('change', fetchCameras);
            document.getElementById('status_select').addEventListener('change', fetchCameras);

            // Reset filters
            document.getElementById('btn_reset_filter').addEventListener('click', function () {
                document.getElementById('search_input').value = '';
                document.getElementById('category_select').value = '';
                document.getElementById('status_select').value = '';
                fetchCameras();
            });

            // Debounced Search key-ups (400ms buffer)
            var searchTimeout = null;
            document.getElementById('search_input').addEventListener('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(fetchCameras, 400);
            });
        });
    </script>
</body>

</html>