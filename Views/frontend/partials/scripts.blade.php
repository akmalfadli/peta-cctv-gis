<!-- Leaflet JS & MarkerCluster JS & Hls.js -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.js"></script>
<script src="https://cdn.jsdelivr.net/npm/hls.js@1.4.12/dist/hls.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var camerasData = [];
        var pembangunansData = [];
        var hlsPlayer = null;

        var activeCctvVisible = true;
        var activePembangunanVisible = true;
        var selectedPolyline = null;
        var selectedProjectObj = null;

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

        // Weather API configuration
        @if($weather_enabled)
            var weatherOverlay = document.getElementById('weather_overlay');
            var btnToggleWeather = document.getElementById('btn_toggle_weather');
            var btnHideWeather = document.getElementById('btn_hide_weather');
            var weatherFetched = false;

            // Fetch and render weather data
            function fetchWeather() {
                if (weatherFetched) return;
                var url = `{{ ci_route('petagis.weather') }}`;

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('HTTP error ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        document.getElementById('weather_loading').classList.add('hidden');
                        document.getElementById('weather_data').classList.remove('hidden');

                        // Current conditions (from the first forecast index)
                        var current = data.list[0];
                        var temp = Math.round(current.main.temp);
                        var description = current.weather[0].description;
                        var icon = current.weather[0].icon;
                        var humidity = current.main.humidity;
                        var windSpeed = current.wind.speed;

                        document.getElementById('weather_temp').innerText = temp + '°C';
                        document.getElementById('weather_desc').innerText = description;
                        document.getElementById('weather_icon').src = `https://openweathermap.org/img/wn/${icon}@2x.png`;
                        document.getElementById('weather_humidity').innerText = humidity + '%';
                        document.getElementById('weather_wind').innerText = Math.round(windSpeed * 3.6) + ' km/h';

                        // Render 3 consecutive 3-hour forecasts (index 1, 2, 3)
                        var forecastEl = document.getElementById('weather_forecast');
                        forecastEl.innerHTML = ''; // Clear fallback placeholders

                        for (var i = 1; i <= 3; i++) {
                            var fc = data.list[i];
                            if (!fc) break;

                            var fcTemp = Math.round(fc.main.temp);
                            var fcIcon = fc.weather[0].icon;

                            // Parse time from timestamp (HH:MM)
                            var date = new Date(fc.dt * 1000);
                            var hours = String(date.getHours()).padStart(2, '0');
                            var mins = String(date.getMinutes()).padStart(2, '0');
                            var timeStr = `${hours}:${mins}`;

                            var fcItem = document.createElement('div');
                            fcItem.className = 'flex items-center justify-between bg-slate-50/50 border border-slate-100 rounded py-0.5 px-2.5';
                            fcItem.innerHTML = `
                                                                                        <span class="text-[9px] text-slate-500 font-extrabold">${timeStr}</span>
                                                                                        <div class="flex items-center space-x-1.5">
                                                                                            <img src="https://openweathermap.org/img/wn/${fcIcon}.png" alt="Cuaca" class="w-8 h-8 my-0.5 select-none pointer-events-none shrink-0">
                                                                                            <span class="text-xs text-slate-800 font-extrabold leading-none">${fcTemp}°</span>
                                                                                        </div>
                                                                                    `;
                            forecastEl.appendChild(fcItem);
                        }

                        weatherFetched = true;
                    })
                    .catch(err => {
                        console.error('Weather widget load failure:', err);
                        document.getElementById('weather_loading').classList.add('hidden');
                        document.getElementById('weather_error').classList.remove('hidden');
                    });
            }

            // Restore visibility state from localStorage on load
            var weatherVisible = localStorage.getItem('weather_visible');
            if (weatherVisible === 'false') {
                if (weatherOverlay) weatherOverlay.classList.add('hidden');
                if (btnToggleWeather) {
                    btnToggleWeather.classList.remove('bg-white', 'text-slate-600');
                    btnToggleWeather.classList.add('bg-slate-100', 'text-slate-400');
                }
            } else {
                fetchWeather();
            }

            // Restore minimized state from localStorage on load (Default is collapsed)
            var weatherMinimized = localStorage.getItem('weather_minimized_v2');
            if (weatherMinimized === 'false') {
                // Expand the widget
                var weatherContent = document.getElementById('weather_content');
                var weatherHeader = document.getElementById('weather_header');
                var minimizeIcon = document.getElementById('weather_minimize_icon');
                if (weatherContent) weatherContent.classList.remove('hidden');
                if (weatherHeader) {
                    weatherHeader.classList.add('border-b', 'border-ops-border', 'pb-1.5', 'mb-2');
                }
                if (btnHideWeather) btnHideWeather.setAttribute('title', 'Minimalkan');
                if (minimizeIcon) {
                    minimizeIcon.className = 'fa-solid fa-chevron-up text-xs';
                }
            } else {
                // Keep collapsed by default
                var weatherContent = document.getElementById('weather_content');
                var weatherHeader = document.getElementById('weather_header');
                var minimizeIcon = document.getElementById('weather_minimize_icon');
                if (weatherContent) weatherContent.classList.add('hidden');
                if (weatherHeader) {
                    weatherHeader.classList.remove('border-b', 'border-ops-border', 'pb-1.5', 'mb-2');
                }
                if (btnHideWeather) btnHideWeather.setAttribute('title', 'Maksimalkan');
                if (minimizeIcon) {
                    minimizeIcon.className = 'fa-solid fa-chevron-down text-xs';
                }
            }

            // Toggle logic from Toolbar button
            if (btnToggleWeather) {
                btnToggleWeather.addEventListener('click', function () {
                    if (weatherOverlay.classList.contains('hidden')) {
                        weatherOverlay.classList.remove('hidden');
                        localStorage.setItem('weather_visible', 'true');
                        fetchWeather();
                        btnToggleWeather.classList.remove('bg-slate-100', 'text-slate-400');
                        btnToggleWeather.classList.add('bg-white', 'text-slate-600');
                    } else {
                        weatherOverlay.classList.add('hidden');
                        localStorage.setItem('weather_visible', 'false');
                        btnToggleWeather.classList.remove('bg-white', 'text-slate-600');
                        btnToggleWeather.classList.add('bg-slate-100', 'text-slate-400');
                    }
                });
            }

            // Minimize/Collapse logic from inside Card button
            if (btnHideWeather) {
                btnHideWeather.addEventListener('click', function () {
                    var weatherContent = document.getElementById('weather_content');
                    var weatherHeader = document.getElementById('weather_header');
                    var minimizeIcon = document.getElementById('weather_minimize_icon');
                    if (weatherContent) {
                        if (weatherContent.classList.contains('hidden')) {
                            weatherContent.classList.remove('hidden');
                            if (weatherHeader) {
                                weatherHeader.classList.add('border-b', 'border-ops-border', 'pb-1.5', 'mb-2');
                            }
                            btnHideWeather.setAttribute('title', 'Minimalkan');
                            if (minimizeIcon) {
                                minimizeIcon.className = 'fa-solid fa-chevron-up text-xs';
                            }
                            localStorage.setItem('weather_minimized_v2', 'false');
                        } else {
                            weatherContent.classList.add('hidden');
                            if (weatherHeader) {
                                weatherHeader.classList.remove('border-b', 'border-ops-border', 'pb-1.5', 'mb-2');
                            }
                            btnHideWeather.setAttribute('title', 'Maksimalkan');
                            if (minimizeIcon) {
                                minimizeIcon.className = 'fa-solid fa-chevron-down text-xs';
                            }
                            localStorage.setItem('weather_minimized_v2', 'true');
                        }
                    }
                });
            }
        @endif

        // Initialize Leaflet Viewport (standard Leaflet controls)
        var map = L.map('map', {
            zoomControl: false,
            attributionControl: false,
            maxZoom: 21
        }).setView([defaultLat, defaultLng], 15);

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

        // CCTV Cluster Layer
        var markerClusterGroup = L.markerClusterGroup({
            showCoverageOnHover: false,
            maxClusterRadius: 40
        });
        map.addLayer(markerClusterGroup);

        // Pembangunan Layer Group
        var pembangunanLayerGroup = L.featureGroup();
        map.addLayer(pembangunanLayerGroup);

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

        // Helper to sync toolbar filters depending on active layers
        function syncFilterVisibility() {
            var cctvCat = document.getElementById('category_select');
            var cctvStatus = document.getElementById('status_select');
            var pemCat = document.getElementById('pembangunan_kategori_select');

            if (activeCctvVisible) {
                if (cctvCat) cctvCat.classList.remove('hidden');
                if (cctvStatus) cctvStatus.classList.remove('hidden');
            } else {
                if (cctvCat) cctvCat.classList.add('hidden');
                if (cctvStatus) cctvStatus.classList.add('hidden');
            }

            if (activePembangunanVisible) {
                if (pemCat) pemCat.classList.remove('hidden');
            } else {
                if (pemCat) pemCat.classList.add('hidden');
            }
        }

        // Helper to map Pembangunan Desa categories to beautiful colors/badges
        function getPembangunanStyle(kategori) {
            var cat = (kategori || '').toLowerCase();
            switch (cat) {
                case 'pendidikan':
                    return {
                        color: '#6366f1',
                        icon: 'fa-graduation-cap',
                        bg: '#6366f1',
                        name: 'Bidang Pendidikan',
                        badgeClass: 'px-2 py-0.5 rounded bg-indigo-50 text-indigo-600 border border-indigo-150 font-bold'
                    };
                case 'kesehatan':
                    return {
                        color: '#ef4444',
                        icon: 'fa-heartbeat',
                        bg: '#ef4444',
                        name: 'Bidang Kesehatan',
                        badgeClass: 'px-2 py-0.5 rounded bg-red-50 text-red-600 border border-red-150 font-bold'
                    };
                case 'ekonomi':
                    return {
                        color: '#10b981',
                        icon: 'fa-wallet',
                        bg: '#10b981',
                        name: 'Ekonomi Desa',
                        badgeClass: 'px-2 py-0.5 rounded bg-emerald-50 text-emerald-600 border border-emerald-150 font-bold'
                    };
                case 'lingkungan':
                    return {
                        color: '#f59e0b',
                        icon: 'fa-leaf',
                        bg: '#f59e0b',
                        name: 'Lingkungan & Bencana',
                        badgeClass: 'px-2 py-0.5 rounded bg-amber-50 text-amber-600 border border-amber-150 font-bold'
                    };
                case 'infrastruktur':
                default:
                    return {
                        color: '#3b82f6',
                        icon: 'fa-road',
                        bg: '#3b82f6',
                        name: 'Infrastruktur Desa',
                        badgeClass: 'px-2 py-0.5 rounded bg-blue-50 text-blue-600 border border-blue-150 font-bold'
                    };
            }
        }

        // Initial Load Data
        syncFilterVisibility();
        fetchData();

        function fetchData() {
            fetchCameras();
            fetchPembangunans();
        }

        // Fetch CCTV cameras
        function fetchCameras() {
            var categoryId = document.getElementById('category_select').value;
            var status = document.getElementById('status_select').value;
            var search = document.getElementById('search_input').value;

            var url = "{{ ci_route('petagis.api_cameras') }}?search=" + encodeURIComponent(search) +
                "&category_id=" + categoryId +
                "&status=" + status;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    camerasData = data;
                    renderCameras();
                    updateStats();
                })
                .catch(err => {
                    console.error('Error fetching cameras:', err);
                });
        }

        // Fetch Pembangunans
        function fetchPembangunans() {
            var search = document.getElementById('search_input').value;
            var kategori = document.getElementById('pembangunan_kategori_select').value;
            var url = "{{ ci_route('petagis.api_pembangunans') }}?search=" + encodeURIComponent(search) +
                "&kategori=" + encodeURIComponent(kategori);

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    pembangunansData = data;
                    renderPembangunans();
                    updateStats();
                })
                .catch(err => {
                    console.error('Error fetching pembangunans:', err);
                });
        }

        // Sync stats
        function updateStats() {
            var total = camerasData.length;
            var online = camerasData.filter(c => c.status === 'online').length;
            var offline = total - online;
            var pembangunansCount = pembangunansData.length;

            document.getElementById('stats_total').innerText = total;
            document.getElementById('stats_online').innerText = online;
            document.getElementById('stats_pembangunans').innerText = pembangunansCount;
        }

        // Dynamic category icon and color mapper for cameras
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

        // Render camera markers
        function renderCameras() {
            markerClusterGroup.clearLayers();
            if (!activeCctvVisible) return;

            camerasData.forEach((cam) => {
                var isOnline = cam.status === 'online';
                var categoryName = cam.category || '';
                var iconInfo = getCategoryIconAndColor(categoryName, isOnline);
                var hasStream = !!cam.stream_url;

                // DivIcon
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
            });
        }

        // Render Pembangunans (markers & polylines)
        function renderPembangunans() {
            pembangunanLayerGroup.clearLayers();
            if (!activePembangunanVisible) return;

            pembangunansData.forEach((project) => {
                var styleInfo = getPembangunanStyle(project.kategori);

                // If it is a road (Jalan/Polyline)
                if (project.type === 'road' && project.coordinates) {
                    try {
                        var latlngs = typeof project.coordinates === 'string' ? JSON.parse(project.coordinates) : project.coordinates;
                        if (Array.isArray(latlngs) && latlngs.length > 0) {
                            var poly = L.polyline(latlngs, {
                                color: styleInfo.color,
                                weight: 4.5,
                                opacity: 0.8,
                                lineJoin: 'round'
                            });

                            // Add to map layer
                            poly.addTo(pembangunanLayerGroup);

                            // Store reference
                            poly.projectData = project;

                            // Setup mouse hovers
                            poly.on('mouseover', function () {
                                if (selectedPolyline !== poly) {
                                    poly.setStyle({
                                        color: '#f59e0b', // Gold Highlight
                                        weight: 7,
                                        opacity: 1
                                    });
                                    poly.bringToFront();
                                }
                            });

                            poly.on('mouseout', function () {
                                if (selectedPolyline !== poly) {
                                    var origStyle = getPembangunanStyle(project.kategori);
                                    poly.setStyle({
                                        color: origStyle.color,
                                        weight: 4.5,
                                        opacity: 0.8
                                    });
                                }
                            });

                            // Setup Popup HTML for this project
                            var formattedBudget = 'Rp 0';
                            if (project.anggaran) {
                                formattedBudget = 'Rp ' + parseInt(project.anggaran).toLocaleString('id-ID');
                            }

                            var typeBadge = 'JALAN / JEMBATAN';
                            var typeClass = 'bg-sky-100 text-sky-800 border border-sky-200';
                            var cleanBadgeClass = (styleInfo.badgeClass || '').replace('col-span-2 text-right', '');

                            var popupHtml = `
                                <div class="p-3 text-xs text-slate-800 w-64 max-w-[280px]">
                                    <div class="flex flex-col border-b border-slate-200 pb-2 mb-2 font-sans">
                                        <span class="font-extrabold text-xs text-slate-900 uppercase leading-snug">${project.jenis_kegiatan}</span>
                                        <div class="flex items-center gap-1.5 mt-1.5">
                                            <span class="inline-block px-1.5 py-0.5 rounded-[3px] text-[8px] font-bold uppercase ${typeClass}">${typeBadge}</span>
                                            <span class="inline-block px-1.5 py-0.5 rounded-[3px] text-[8px] font-extrabold uppercase ${cleanBadgeClass}">${styleInfo.name}</span>
                                        </div>
                                    </div>
                                    
                                    ${project.photo ? `
                                        <div class="w-full aspect-[4/3] bg-cover bg-center border border-slate-200 rounded mb-2.5 shadow-sm" style="background-image: url('${project.photo}')"></div>
                                    ` : `
                                        <div class="w-full aspect-[4/3] bg-slate-100 border border-slate-200 rounded flex flex-col items-center justify-center text-slate-400 mb-2.5">
                                            <i class="fa-regular fa-image text-xl"></i>
                                            <span class="text-[9px] mt-0.5 font-bold tracking-wide">TIDAK ADA FOTO</span>
                                        </div>
                                    `}

                                    <div class="space-y-1.5 text-[10px] bg-slate-50 border border-slate-150 rounded p-2 font-sans">
                                        <div class="flex justify-between items-center border-b border-slate-100 pb-1">
                                            <span class="text-slate-400 font-medium font-bold text-slate-500">ANGGARAN</span>
                                            <strong class="text-emerald-700 text-right">${formattedBudget}</strong>
                                        </div>
                                        <div class="flex justify-between items-center border-b border-slate-100 pb-1">
                                            <span class="text-slate-400 font-medium font-bold text-slate-500">TAHUN ANGGARAN</span>
                                            <span class="text-slate-800 font-bold text-right">${project.tahun_anggaran || '-'}</span>
                                        </div>
                                        <div class="flex justify-between items-center border-b border-slate-100 pb-1">
                                            <span class="text-slate-400 font-medium font-bold text-slate-500">SUMBER DANA</span>
                                            <span class="text-slate-800 font-bold text-right">${project.sumber_dana || '-'}</span>
                                        </div>
                                        <div class="flex justify-between items-center border-b border-slate-100 pb-1">
                                            <span class="text-slate-400 font-medium font-bold text-slate-500">LOKASI</span>
                                            <span class="text-slate-700 text-right truncate max-w-[130px]" title="${project.lokasi || '-'}">${project.lokasi || '-'}</span>
                                        </div>
                                        <div class="flex justify-between items-center border-b border-slate-100 pb-1">
                                            <span class="text-slate-400 font-medium font-bold text-slate-500">VOLUME</span>
                                            <span class="text-slate-700 text-right truncate max-w-[130px]">${project.volume || '-'}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-slate-400 font-medium font-bold text-slate-500">PELAKSANA</span>
                                            <span class="text-slate-700 text-right truncate max-w-[130px]">${project.pelaksana || '-'}</span>
                                        </div>
                                    </div>
                                </div>
                            `;

                            // Bind popup directly to polyline
                            poly.bindPopup(popupHtml, { closeButton: false });

                            poly.on('popupopen', function () {
                                selectedPolyline = poly;
                                poly.setStyle({
                                    color: '#ea580c', // Persistent Highlighting Orange-600
                                    weight: 7,
                                    opacity: 1
                                });
                                poly.bringToFront();
                            });

                            poly.on('popupclose', function () {
                                selectedPolyline = null;
                                var origStyle = getPembangunanStyle(project.kategori);
                                poly.setStyle({
                                    color: origStyle.color,
                                    weight: 4.5,
                                    opacity: 0.8
                                });
                            });

                            // Draw a beautiful marker at the start of the polyline
                            var startPoint = latlngs[0];
                            if (startPoint && startPoint.length === 2) {
                                var roadHtml = `
                                    <div class="w-7 h-7 border-2 border-white text-white flex items-center justify-center rounded-lg shadow-md hover:scale-110 transition-all duration-200" style="background-color: ${styleInfo.bg}">
                                        <i class="fa-solid ${styleInfo.icon} text-[10px]"></i>
                                    </div>
                                `;
                                var customRoadIcon = L.divIcon({
                                    html: roadHtml,
                                    className: 'gis-road-marker-icon',
                                    iconSize: [28, 28],
                                    iconAnchor: [14, 14]
                                });

                                var marker = L.marker([startPoint[0], startPoint[1]], {
                                    icon: customRoadIcon
                                });

                                marker.projectData = project;
                                marker.polyRef = poly; // Keep reference to the polyline

                                marker.addTo(pembangunanLayerGroup);

                                marker.on('mouseover', function () {
                                    if (selectedPolyline !== poly) {
                                        poly.setStyle({
                                            color: '#f59e0b', // Gold Highlight
                                            weight: 7,
                                            opacity: 1
                                        });
                                        poly.bringToFront();
                                    }
                                });

                                marker.on('mouseout', function () {
                                    if (selectedPolyline !== poly) {
                                        var origStyle = getPembangunanStyle(project.kategori);
                                        poly.setStyle({
                                            color: origStyle.color,
                                            weight: 4.5,
                                            opacity: 0.8
                                        });
                                    }
                                });

                                // Bind popup to start marker too
                                marker.bindPopup(popupHtml, { closeButton: false });

                                marker.on('popupopen', function () {
                                    selectedPolyline = poly;
                                    poly.setStyle({
                                        color: '#ea580c', // Persistent Highlighting Orange-600
                                        weight: 7,
                                        opacity: 1
                                    });
                                    poly.bringToFront();
                                });

                                marker.on('popupclose', function () {
                                    selectedPolyline = null;
                                    var origStyle = getPembangunanStyle(project.kategori);
                                    poly.setStyle({
                                        color: origStyle.color,
                                        weight: 4.5,
                                        opacity: 0.8
                                    });
                                });
                            }
                        }
                    } catch (e) {
                        console.error('Failed to parse polyline coordinates:', e, project);
                    }
                }

                // If it is a building (Gedung/Marker)
                else if (project.type === 'building' && project.latitude && project.longitude) {
                    // Marker building look
                    var buildingHtml = `
                        <div class="w-7 h-7 border-2 border-white text-white flex items-center justify-center rounded-lg shadow-md hover:scale-110 transition-all duration-200" style="background-color: ${styleInfo.bg}">
                            <i class="fa-solid ${styleInfo.icon} text-[10px]"></i>
                        </div>
                    `;
                    var customBuildingIcon = L.divIcon({
                        html: buildingHtml,
                        className: 'gis-building-icon',
                        iconSize: [28, 28],
                        iconAnchor: [14, 14]
                    });

                    var marker = L.marker([project.latitude, project.longitude], {
                        icon: customBuildingIcon
                    });

                    marker.projectData = project;
                    marker.addTo(pembangunanLayerGroup);

                    // Setup Popup HTML for this building
                    var formattedBudget = 'Rp 0';
                    if (project.anggaran) {
                        formattedBudget = 'Rp ' + parseInt(project.anggaran).toLocaleString('id-ID');
                    }

                    var typeBadge = 'GEDUNG / BANGUNAN';
                    var typeClass = 'bg-amber-100 text-amber-800 border border-amber-200';
                    var cleanBadgeClass = (styleInfo.badgeClass || '').replace('col-span-2 text-right', '');

                    var popupHtml = `
                         <div class="p-3 text-xs text-slate-800 w-64 max-w-[280px]">
                             <div class="flex flex-col border-b border-slate-200 pb-2 mb-2 font-sans">
                                 <span class="font-extrabold text-xs text-slate-900 uppercase leading-snug">${project.jenis_kegiatan}</span>
                                 <div class="flex items-center gap-1.5 mt-1.5">
                                     <span class="inline-block px-1.5 py-0.5 rounded-[3px] text-[8px] font-bold uppercase ${typeClass}">${typeBadge}</span>
                                     <span class="inline-block px-1.5 py-0.5 rounded-[3px] text-[8px] font-extrabold uppercase ${cleanBadgeClass}">${styleInfo.name}</span>
                                 </div>
                             </div>
                             
                             ${project.photo ? `
                                 <div class="w-full aspect-[4/3] bg-cover bg-center border border-slate-200 rounded mb-2.5 shadow-sm" style="background-image: url('${project.photo}')"></div>
                             ` : `
                                 <div class="w-full aspect-[4/3] bg-slate-100 border border-slate-200 rounded flex flex-col items-center justify-center text-slate-400 mb-2.5">
                                     <i class="fa-regular fa-image text-xl"></i>
                                     <span class="text-[9px] mt-0.5 font-bold tracking-wide">TIDAK ADA FOTO</span>
                                 </div>
                             `}

                             <div class="space-y-1.5 text-[10px] bg-slate-50 border border-slate-150 rounded p-2 font-sans">
                                 <div class="flex justify-between items-center border-b border-slate-100 pb-1">
                                     <span class="text-slate-400 font-medium font-bold text-slate-500">ANGGARAN</span>
                                     <strong class="text-emerald-700 text-right">${formattedBudget}</strong>
                                 </div>
                                 <div class="flex justify-between items-center border-b border-slate-100 pb-1">
                                     <span class="text-slate-400 font-medium font-bold text-slate-500">TAHUN ANGGARAN</span>
                                     <span class="text-slate-800 font-bold text-right">${project.tahun_anggaran || '-'}</span>
                                 </div>
                                 <div class="flex justify-between items-center border-b border-slate-100 pb-1">
                                     <span class="text-slate-400 font-medium font-bold text-slate-500">SUMBER DANA</span>
                                     <span class="text-slate-800 font-bold text-right">${project.sumber_dana || '-'}</span>
                                 </div>
                                 <div class="flex justify-between items-center border-b border-slate-100 pb-1">
                                     <span class="text-slate-400 font-medium font-bold text-slate-500">LOKASI</span>
                                     <span class="text-slate-700 text-right truncate max-w-[130px]" title="${project.lokasi || '-'}">${project.lokasi || '-'}</span>
                                 </div>
                                 <div class="flex justify-between items-center border-b border-slate-100 pb-1">
                                     <span class="text-slate-400 font-medium font-bold text-slate-500">VOLUME</span>
                                     <span class="text-slate-700 text-right truncate max-w-[130px]">${project.volume || '-'}</span>
                                 </div>
                                 <div class="flex justify-between items-center">
                                     <span class="text-slate-400 font-medium font-bold text-slate-500">PELAKSANA</span>
                                     <span class="text-slate-700 text-right truncate max-w-[130px]">${project.pelaksana || '-'}</span>
                                 </div>
                             </div>
                         </div>
                     `;

                    marker.bindPopup(popupHtml, { closeButton: false });
                }
            });
        }

        // Highlight persistent road polyline
        function highlightProject(leafletPoly) {
            // Reset previous selected style
            if (selectedPolyline && selectedPolyline !== leafletPoly) {
                var prevStyle = getPembangunanStyle(selectedPolyline.projectData.kategori);
                selectedPolyline.setStyle({
                    color: prevStyle.color,
                    weight: 4.5,
                    opacity: 0.8
                });
            }

            selectedPolyline = leafletPoly;
            leafletPoly.setStyle({
                color: '#ea580c', // Persistent Highlighting Orange-600
                weight: 7,
                opacity: 1
            });
            leafletPoly.bringToFront();

            showProjectDetails(leafletPoly.projectData);
        }

        // Display Slide-over panel
        function showProjectDetails(project) {
            selectedProjectObj = project;

            // Formatter rupiah
            var formattedBudget = 'Rp 0';
            if (project.anggaran) {
                formattedBudget = 'Rp ' + parseInt(project.anggaran).toLocaleString('id-ID');
            }

            var styleInfo = getPembangunanStyle(project.kategori);

            // Fill text
            document.getElementById('panel_title').innerText = project.jenis_kegiatan;
            document.getElementById('panel_budget').innerText = formattedBudget;
            document.getElementById('panel_source').innerText = project.sumber_dana || '-';
            document.getElementById('panel_location').innerText = project.lokasi || '-';
            document.getElementById('panel_volume').innerText = project.volume || '-';
            document.getElementById('panel_executor').innerText = project.pelaksana || '-';

            // Set kategori badge
            var kategoriBadge = document.getElementById('panel_kategori_badge');
            if (kategoriBadge) {
                kategoriBadge.innerText = styleInfo.name.toUpperCase();
                kategoriBadge.className = 'col-span-2 text-right font-extrabold text-[10px] uppercase ' + styleInfo.badgeClass;
            }

            // Set type badge
            var typeBadge = document.getElementById('panel_type_badge');
            if (project.type === 'road') {
                typeBadge.innerText = 'JALAN / JEMBATAN';
                typeBadge.className = 'inline-block px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-sky-100 text-sky-800 mt-1';
            } else {
                typeBadge.innerText = 'GEDUNG / BANGUNAN';
                typeBadge.className = 'inline-block px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-amber-100 text-amber-800 mt-1';
            }

            // Photo management
            var photoEl = document.getElementById('panel_photo');
            var placeholderEl = document.getElementById('panel_photo_placeholder');

            if (project.photo) {
                photoEl.src = project.photo;
                photoEl.classList.remove('hidden');
                placeholderEl.classList.add('hidden');
            } else {
                photoEl.classList.add('hidden');
                placeholderEl.classList.remove('hidden');
            }

            // Slide in
            var panel = document.getElementById('project_details_panel');
            panel.classList.remove('translate-x-[120%]');
            panel.classList.add('translate-x-0');
        }

        // Close Slide-over panel
        function closeDetailsPanel() {
            var panel = document.getElementById('project_details_panel');
            panel.classList.remove('translate-x-0');
            panel.classList.add('translate-x-[120%]');

            // Reset active polyline highlight
            if (selectedPolyline) {
                var prevStyle = getPembangunanStyle(selectedPolyline.projectData.kategori);
                selectedPolyline.setStyle({
                    color: prevStyle.color,
                    weight: 4.5,
                    opacity: 0.8
                });
                selectedPolyline = null;
            }
            selectedProjectObj = null;
        }

        document.getElementById('close_details_panel').addEventListener('click', closeDetailsPanel);

        // Center map zoom on the active project
        document.getElementById('btn_zoom_project').addEventListener('click', function () {
            if (!selectedProjectObj) return;

            if (selectedProjectObj.type === 'road' && selectedPolyline) {
                map.fitBounds(selectedPolyline.getBounds(), { padding: [40, 40] });
            } else if (selectedProjectObj.latitude && selectedProjectObj.longitude) {
                map.setView([selectedProjectObj.latitude, selectedProjectObj.longitude], 17);
            }
        });

        // Map general click resets panel
        map.on('click', function () {
            closeDetailsPanel();
        });

        // Layer toggling logic
        var toggleCctvBtn = document.getElementById('toggle_layer_cctv');
        var togglePembangunanBtn = document.getElementById('toggle_layer_pembangunan');

        // Helper: Set button active/inactive visual state
        function setToggleBtnActive(btn) {
            btn.className = 'h-7 px-2.5 text-[10px] font-bold uppercase rounded transition flex items-center space-x-1 bg-ops-primary text-white';
        }
        function setToggleBtnInactive(btn) {
            btn.className = 'h-7 px-2.5 text-[10px] font-bold uppercase rounded transition flex items-center space-x-1 text-slate-600 hover:bg-slate-100';
        }

        // Initialize button states to match initial visibility flags
        if (activeCctvVisible) {
            setToggleBtnActive(toggleCctvBtn);
        } else {
            setToggleBtnInactive(toggleCctvBtn);
        }
        if (activePembangunanVisible) {
            setToggleBtnActive(togglePembangunanBtn);
        } else {
            setToggleBtnInactive(togglePembangunanBtn);
        }

        toggleCctvBtn.addEventListener('click', function () {
            activeCctvVisible = !activeCctvVisible;
            if (activeCctvVisible) {
                map.addLayer(markerClusterGroup);
                setToggleBtnActive(toggleCctvBtn);
            } else {
                map.removeLayer(markerClusterGroup);
                setToggleBtnInactive(toggleCctvBtn);
            }
            syncFilterVisibility();
            renderCameras();
        });

        togglePembangunanBtn.addEventListener('click', function () {
            activePembangunanVisible = !activePembangunanVisible;
            if (activePembangunanVisible) {
                map.addLayer(pembangunanLayerGroup);
                setToggleBtnActive(togglePembangunanBtn);
            } else {
                map.removeLayer(pembangunanLayerGroup);
                setToggleBtnInactive(togglePembangunanBtn);
            }
            syncFilterVisibility();
            renderPembangunans();
        });

        // --- surveillance theater mode modal playback ---
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

        // Backdrop click closing for mobile responsiveness
        document.getElementById('stream_modal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeStreamModal();
            }
        });

        // Escape key closing
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeStreamModal();
            }
        });

        // Filter Event handlers
        document.getElementById('category_select').addEventListener('change', fetchData);
        document.getElementById('status_select').addEventListener('change', fetchData);
        document.getElementById('pembangunan_kategori_select').addEventListener('change', fetchPembangunans);

        // Reset filters
        document.getElementById('btn_reset_filter').addEventListener('click', function () {
            document.getElementById('search_input').value = '';
            document.getElementById('category_select').value = '';
            document.getElementById('status_select').value = '';
            document.getElementById('pembangunan_kategori_select').value = '';
            fetchData();
            closeDetailsPanel();
        });

        // Debounced Search key-ups (400ms buffer)
        var searchTimeout = null;
        document.getElementById('search_input').addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(fetchData, 400);
        });

        // Mobile legend Leaflet popup toggle
        var btnLegendToggle = document.getElementById('btn_legend_toggle');
        if (btnLegendToggle) {
            btnLegendToggle.addEventListener('click', function (e) {
                e.stopPropagation();

                var legendPopupContent = `
                    <div class="p-3 text-xs text-slate-800 font-sans" style="min-width: 200px;">
                        <div class="border-b border-slate-200 pb-1.5 mb-2.5 font-extrabold uppercase text-[11px] text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-circle-info text-ops-primary text-[10px]"></i>
                            <span>Keterangan Peta</span>
                        </div>

                        <!-- CCTV Status -->
                        <div class="mb-3">
                            <span class="text-[9px] font-extrabold text-slate-400 uppercase tracking-wider block mb-1.5">KAMERA CCTV</span>
                            <div class="flex gap-4 font-bold text-[10px]">
                                <span class="flex items-center gap-2 text-emerald-700">
                                    <span class="w-1.5 h-1.5 bg-green-600 rounded-full shrink-0"></span>ONLINE
                                </span>
                                <span class="flex items-center gap-1.5 text-rose-600">
                                    <span class="w-1.5 h-1.5 bg-rose-600 rounded-full shrink-0"></span>OFFLINE
                                </span>
                            </div>
                        </div>

                        <!-- Bidang Pembangunan -->
                        <div class="mb-3">
                            <span class="text-[9px] font-extrabold text-slate-400 uppercase tracking-wider block mb-1.5">BIDANG PEMBANGUNAN</span>
                            <div class="space-y-1.5 font-bold text-[10px]">
                                <span class="flex items-center gap-2 text-blue-600">
                                    <span class="w-2.5 h-[3px] bg-blue-500 rounded-sm shrink-0"></span>Infrastruktur Desa
                                </span>
                                <span class="flex items-center gap-2 text-indigo-600">
                                    <span class="w-2.5 h-[3px] bg-indigo-500 rounded-sm shrink-0"></span>Bidang Pendidikan
                                </span>
                                <span class="flex items-center gap-2 text-red-600">
                                    <span class="w-2.5 h-[3px] bg-red-500 rounded-sm shrink-0"></span>Bidang Kesehatan
                                </span>
                                <span class="flex items-center gap-2 text-emerald-600">
                                    <span class="w-2.5 h-[3px] bg-emerald-500 rounded-sm shrink-0"></span>Ekonomi Desa
                                </span>
                                <span class="flex items-center gap-2 text-amber-600">
                                    <span class="w-2.5 h-[3px] bg-amber-400 rounded-sm shrink-0"></span>Lingkungan & Bencana
                                </span>
                            </div>
                        </div>

                        <!-- Tipe Pembangunan -->
                        <div>
                            <span class="text-[9px] font-extrabold text-slate-400 uppercase tracking-wider block mb-1.5">TIPE PEMBANGUNAN</span>
                            <div class="flex gap-4 font-bold text-[10px]">
                                <span class="flex items-center gap-1.5 text-slate-600">
                                    <span class="w-3.5 h-[3px] bg-slate-400 rounded-sm shrink-0"></span>Jalan
                                </span>
                                <span class="flex items-center gap-2 text-slate-600">
                                    <i class="fa-solid fa-building text-[9px] text-slate-400 shrink-0"></i>Gedung
                                </span>
                            </div>
                        </div>
                    </div>
                `;

                // Get the bounding box of the button and the map container to compute pixel-precise location
                var buttonRect = btnLegendToggle.getBoundingClientRect();
                var mapRect = document.getElementById('map').getBoundingClientRect();

                // Horizontal center of the button, and top of the button minus 15px margin
                var x = buttonRect.left - mapRect.left + (buttonRect.width / 2) + 20;
                var y = buttonRect.top - mapRect.top - 45;

                // Convert container relative pixel coordinates to Leaflet map LatLng
                var targetLatLng = map.containerPointToLatLng([x, y]);

                L.popup({
                    closeButton: true,
                    className: 'gis-legend-popup',
                    maxWidth: 260
                })
                    .setLatLng(targetLatLng)
                    .setContent(legendPopupContent)
                    .openOn(map);
            });
        }
    });
</script>