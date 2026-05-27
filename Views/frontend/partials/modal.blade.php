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
                class="absolute bottom-2 left-2 sm:bottom-[37px] sm:left-4 md:bottom-[41px] z-20 bg-white/95 border border-slate-200 text-slate-800 font-mono-tech text-[8px] sm:text-[10px] tracking-wider px-2 py-0.5 sm:px-2.5 sm:py-1 pointer-events-none select-none shadow-sm rounded-md flex items-center space-x-1.5 sm:space-x-2">
                <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-rose-600 live-pulse shrink-0"></span>
                <span>
                    <span id="cctv_overlay_name" class="font-bold text-slate-900">CAMERA</span> &bull; <span
                        id="cctv_overlay_desc" class="text-slate-600">LOCATION</span>
                </span>
            </div>

            <!-- Bottom-Right overlay: GPS coords (Hidden on mobile to prevent overlay collisions) -->
            <div
                class="hidden md:block absolute md:bottom-[41px] right-4 z-20 bg-white/95 border border-slate-200 text-slate-800 font-mono-tech text-[10px] tracking-wider px-2.5 py-1 pointer-events-none select-none shadow-sm rounded-md">
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
