{{-- Navigasi Modul: Guru Web/Desktop --}}
<div class="pb-1 pt-3 px-3 nav-section-label" x-show="sidebarOpen">Menu Guru</div>

<a href="{{ route('dashboard') }}"
   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-[7px] {{ request()->routeIs('dashboard') ? 'nav-active' : '' }}">
    <i class="ti ti-layout-dashboard text-lg shrink-0"></i>
    <span x-show="sidebarOpen" class="text-sm font-semibold">Dashboard Guru</span>
</a>

<a href="{{ route('absensi-mapel.index') }}"
   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-[7px] {{ request()->routeIs('absensi-mapel.*') ? 'nav-active' : '' }}">
    <i class="ti ti-clipboard-check text-lg shrink-0"></i>
    <span x-show="sidebarOpen" class="text-sm font-semibold">Absensi Mata Pelajaran</span>
</a>

<a href="{{ route('guru.kegiatan.index') }}"
   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-[7px] {{ request()->routeIs('guru.kegiatan.*') ? 'nav-active' : '' }}">
    <i class="ti ti-qrcode text-lg shrink-0 text-emerald-400"></i>
    <span x-show="sidebarOpen" class="text-sm font-semibold">Absen Kegiatan</span>
</a>

<a href="{{ route('guru.jadwal.index') }}"
   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-[7px] {{ request()->routeIs('guru.jadwal.*') ? 'nav-active' : '' }}">
    <i class="ti ti-calendar-event text-lg shrink-0"></i>
    <span x-show="sidebarOpen" class="text-sm font-semibold">Jadwal Mengajar</span>
</a>

<a href="{{ route('monitoring-kbm.index') }}"
   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-[7px] {{ request()->routeIs('monitoring-kbm.*') ? 'nav-active' : '' }}">
    <i class="ti ti-activity text-lg shrink-0"></i>
    <span x-show="sidebarOpen" class="text-sm font-semibold">Monitoring KBM Live</span>
</a>

<a href="{{ route('rekap-mapel.index') }}"
   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-[7px] {{ request()->routeIs('rekap-mapel.*') ? 'nav-active' : '' }}">
    <i class="ti ti-chart-pie text-lg shrink-0"></i>
    <span x-show="sidebarOpen" class="text-sm font-semibold">Rekap Absen Mapel</span>
</a>

<a href="{{ route('guru.izin.index') }}"
   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-[7px] {{ request()->routeIs('guru.izin.*') ? 'nav-active' : '' }}">
    <i class="ti ti-file-text text-lg shrink-0"></i>
    <span x-show="sidebarOpen" class="text-sm font-semibold">Pengajuan Izin</span>
</a>

<a href="{{ route('guru.informasi.index') }}"
   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-[7px] {{ request()->routeIs('guru.informasi.*') ? 'nav-active' : '' }}">
    <i class="ti ti-speakerphone text-lg shrink-0"></i>
    <span x-show="sidebarOpen" class="text-sm font-semibold">Informasi Sekolah</span>
</a>
