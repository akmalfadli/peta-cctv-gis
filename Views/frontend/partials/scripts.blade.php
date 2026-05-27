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

        // Weather API configuration
        @if($weather_enabled && !empty($weather_api_key))
        var weatherApiKey = "{{ $weather_api_key }}";
        var weatherOverlay = document.getElementById('weather_overlay');
        var btnToggleWeather = document.getElementById('btn_toggle_weather');
        var btnHideWeather = document.getElementById('btn_hide_weather');
        var weatherFetched = false;

        // Fetch and render weather data
        function fetchWeather() {
            if (weatherFetched) return;
            var url = `https://api.openweathermap.org/data/2.5/forecast?lat=${defaultLat}&lon=${defaultLng}&appid=${weatherApiKey}&units=metric&lang=id`;
            
            fetch(url)
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
                        fcItem.className = 'flex flex-col items-center bg-slate-50/50 border border-slate-100 rounded py-1 px-1';
                        fcItem.innerHTML = `
                            <div class="flex items-center justify-center space-x-0.5">
                                <img src="https://openweathermap.org/img/wn/${fcIcon}.png" alt="Cuaca" class="w-9 h-9 my-0.5 select-none pointer-events-none shrink-0">
                                <span class="text-xs text-slate-800 font-extrabold leading-none">${fcTemp}°</span>
                            </div>
                            <span class="text-[7.5px] text-slate-400 font-bold leading-none mt-0.5">${timeStr}</span>
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

        // Restore minimized state from localStorage on load
        var weatherMinimized = localStorage.getItem('weather_minimized');
        if (weatherMinimized === 'true') {
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
                        localStorage.setItem('weather_minimized', 'false');
                    } else {
                        weatherContent.classList.add('hidden');
                        if (weatherHeader) {
                            weatherHeader.classList.remove('border-b', 'border-ops-border', 'pb-1.5', 'mb-2');
                        }
                        btnHideWeather.setAttribute('title', 'Maksimalkan');
                        if (minimizeIcon) {
                            minimizeIcon.className = 'fa-solid fa-chevron-down text-xs';
                        }
                        localStorage.setItem('weather_minimized', 'true');
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
