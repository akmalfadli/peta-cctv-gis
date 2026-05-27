@if($weather_enabled)
    <!-- Floating Weather Overlay (Top-Right) -->
    <div id="weather_overlay" style="z-index: 1000;"
        class="absolute top-4 right-4 z-[1000] bg-white/90 backdrop-blur-md border border-ops-border shadow-lg rounded-lg p-3 w-60 text-slate-800 transition-all duration-300 font-sans select-none">
        <!-- Header -->
        <div id="weather_header" class="flex items-center justify-between">
            <div class="flex items-center space-x-1.5 text-ops-primary">
                <i class="fa-solid fa-cloud-sun text-base text-ops-primary/80 animate-pulse"></i>
                <span class="text-[12px] font-extrabold tracking-wider">Cuaca Desa</span>
            </div>
            <button id="btn_hide_weather" class="text-slate-400 hover:text-slate-600 transition" title="Maksimalkan">
                <i id="weather_minimize_icon" class="fa-solid fa-chevron-down text-xs"></i>
            </button>
        </div>
        <!-- Body -->
        <div id="weather_content" class="flex flex-col gap-2 hidden">
            <div id="weather_loading" class="flex items-center justify-center py-4 text-xs text-slate-500 font-semibold">
                <i class="fa-solid fa-spinner animate-spin mr-2"></i> Memuat Data...
            </div>
            <div id="weather_data" class="hidden">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col">
                        <span id="weather_temp"
                            class="text-3xl font-bold tracking-tight text-slate-900 leading-none">--°C</span>
                        <span id="weather_desc"
                            class="text-[9px] font-bold text-slate-500 tracking-wider mt-1.5 truncate max-w-[120px]">Memuat...</span>
                    </div>
                    <img id="weather_icon" src="" alt="Cuaca" class="w-12 h-12 -my-2 select-none pointer-events-none">
                </div>
                <div
                    class="grid grid-cols-2 gap-2 border-t border-ops-border mt-2 pt-2 text-[9px] text-slate-600 font-semibold">
                    <div class="flex items-center space-x-1">
                        <i class="fa-solid fa-droplet text-blue-500 w-3"></i>
                        <span>Hum: <strong id="weather_humidity" class="text-slate-800">--%</strong></span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <i class="fa-solid fa-wind text-teal-500 w-3"></i>
                        <span>Angin: <strong id="weather_wind" class="text-slate-800">-- km/h</strong></span>
                    </div>
                </div>
                <!-- Forecast Section -->
                <div class="border-t border-ops-border mt-2 pt-2">
                    <div
                        class="text-[8px] font-extrabold text-slate-400 tracking-widest mb-2 flex items-center justify-between">
                        <span>Prediksi Cuaca</span>
                        <span>3 Jam berkala</span>
                    </div>
                    <div id="weather_forecast" class="flex flex-col gap-1">
                        <div
                            class="flex items-center justify-between bg-slate-50/50 border border-slate-100 rounded py-0.5 px-2.5">
                            <span class="text-[9px] font-extrabold">--:--</span>
                            <div class="flex items-center space-x-1.5">
                                <div class="w-8 h-8 bg-slate-100 rounded-full animate-pulse my-0.5"></div>
                                <span class="text-xs text-slate-800 font-extrabold">--°</span>
                            </div>
                        </div>
                        <div
                            class="flex items-center justify-between bg-slate-50/50 border border-slate-100 rounded py-0.5 px-2.5">
                            <span class="text-[9px] font-extrabold">--:--</span>
                            <div class="flex items-center space-x-1.5">
                                <div class="w-8 h-8 bg-slate-100 rounded-full animate-pulse my-0.5"></div>
                                <span class="text-xs text-slate-800 font-extrabold">--°</span>
                            </div>
                        </div>
                        <div
                            class="flex items-center justify-between bg-slate-50/50 border border-slate-100 rounded py-0.5 px-2.5">
                            <span class="text-[9px] font-extrabold">--:--</span>
                            <div class="flex items-center space-x-1.5">
                                <div class="w-8 h-8 bg-slate-800 rounded-full animate-pulse my-0.5"></div>
                                <span class="text-xs text-slate-800 font-extrabold">--°</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Weather Provider Attribution -->
                <div
                    class="text-xs text-slate-400 text-center mt-3.5 pt-2.5 border-t border-ops-border/60 flex items-center justify-center space-x-1 select-none">
                    <i class="fa-solid fa-cloud text-[5px] text-slate-350"></i>
                    <span>Data by OpenWeather</span>
                </div>
            </div>
            <div id="weather_error" class="hidden text-center py-3 text-[9px] text-rose-600 font-bold tracking-wider">
                <i class="fa-solid fa-circle-exclamation mr-1"></i> Gagal Memuat Info
            </div>
        </div>
    </div>
@endif