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

        @if($weather_enabled && !empty($weather_api_key))
            <!-- Toggle Weather Button -->
            <button id="btn_toggle_weather"
                class="h-8 border border-ops-border bg-white hover:bg-slate-100 text-slate-600 hover:text-slate-900 px-2.5 text-xs font-bold uppercase rounded-md transition flex items-center space-x-1"
                title="Tampilkan / Sembunyikan Info Cuaca">
                <i class="fa-solid fa-cloud-sun text-slate-500"></i>
                <span class="hidden xs:inline">CUACA</span>
            </button>
        @endif
    </div>

</section>
