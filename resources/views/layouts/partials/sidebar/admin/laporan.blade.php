{{-- Navigasi Modul: Laporan & Monitoring Admin --}}
<div class="pb-1.5 pt-4 px-2 nav-section-label" x-show="sidebarOpen">Laporan &amp; Monitoring</div>

{{-- Monitoring KBM Live --}}
<a href="{{ route('monitoring-kbm.index') }}"
   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('monitoring-kbm.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
    </svg>
    <span x-show="sidebarOpen" class="text-sm font-semibold">Monitoring KBM Live</span>
</a>

{{-- Laporan Presensi --}}
<a href="{{ route('laporan.index') }}"
   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('laporan.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    <span x-show="sidebarOpen" class="text-sm font-semibold">Laporan Presensi</span>
</a>

{{-- Rekap Absen Mapel --}}
<a href="{{ route('rekap-mapel.index') }}"
   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('rekap-mapel.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
    </svg>
    <span x-show="sidebarOpen" class="text-sm font-semibold">Rekap Absen Mapel</span>
</a>

{{-- Rekap Jam Mengajar Guru --}}
<a href="{{ route('rekap-jam-guru.index') }}"
   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('rekap-jam-guru.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span x-show="sidebarOpen" class="text-sm font-semibold">Rekap Jam Mengajar</span>
</a>

{{-- Kirim Laporan WhatsApp (Wanesia Gateway) --}}
<a href="{{ route('whatsapp-broadcast.index') }}"
   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('whatsapp-broadcast.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
    <svg class="w-[18px] h-[18px] shrink-0" fill="currentColor" viewBox="0 0 24 24">
        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
    </svg>
    <span x-show="sidebarOpen" class="text-sm font-semibold">Kirim Pesan WhatsApp</span>
</a>
