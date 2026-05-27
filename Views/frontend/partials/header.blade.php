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
                PEMERINTAH {{ strtoupper(setting('sebutan_desa') ?: 'DESA') }}
                {{ strtoupper($desa->nama_desa ?: 'WIRADESA') }} &bull; PORTAL GIS
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