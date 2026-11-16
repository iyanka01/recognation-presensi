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
