{{-- Navigasi Modul: Laporan & Monitoring Admin (Dropdown Accordion) --}}
@php
    $isLaporanActive = request()->routeIs([
        'monitoring-kbm.*', 
        'laporan.*', 
        'laporan-kegiatan.*',
        'rekap-mapel.*', 
        'rekap-jam-guru.*', 
        'whatsapp-broadcast.*'
    ]);
@endphp

<div x-data="{ open: {{ $isLaporanActive ? 'true' : 'false' }} }" class="space-y-0.5">
    <!-- Trigger Button -->
    <button type="button" 
            @click="if(!sidebarOpen) { sidebarOpen = true; open = true; } else { open = !open; }"
            class="nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-[7px] transition-all duration-200 {{ $isLaporanActive ? 'font-bold text-blue-600 dark:text-blue-400 bg-blue-50/60 dark:bg-blue-950/30' : '' }} cursor-pointer">
        <div class="flex items-center gap-3 min-w-0">
            <i class="ti ti-chart-bar text-lg shrink-0 {{ $isLaporanActive ? 'text-blue-600 dark:text-blue-400' : '' }}"></i>
            <span x-show="sidebarOpen" class="text-sm font-semibold truncate">Laporan &amp; Monitoring</span>
        </div>
        <i x-show="sidebarOpen" 
           :class="open ? 'rotate-180' : ''" 
           class="ti ti-chevron-down text-xs transition-transform duration-200 text-slate-400"></i>
    </button>

    <!-- Submenu Accordion -->
    <div x-show="open && sidebarOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-1"
         class="pl-3.5 ml-4 space-y-0.5 border-l-2 border-slate-200 dark:border-slate-800">

        {{-- Monitoring KBM Live --}}
        <a href="{{ route('monitoring-kbm.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('monitoring-kbm.*') ? 'nav-active' : '' }}">
            <i class="ti ti-activity text-base shrink-0"></i>
            <span class="font-medium">Monitoring KBM Live</span>
        </a>

        {{-- Laporan Presensi Harian --}}
        <a href="{{ route('laporan.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('laporan.*') ? 'nav-active' : '' }}">
            <i class="ti ti-report-analytics text-base shrink-0"></i>
            <span class="font-medium">Laporan Presensi</span>
        </a>

        {{-- Laporan Kegiatan Khusus (Baru) --}}
        <a href="{{ route('laporan-kegiatan.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('laporan-kegiatan.*') ? 'nav-active' : '' }}">
            <i class="ti ti-file-certificate text-base shrink-0"></i>
            <span class="font-medium">Laporan Kegiatan Khusus</span>
        </a>

        {{-- Rekap Absen Mapel --}}
        <a href="{{ route('rekap-mapel.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('rekap-mapel.*') ? 'nav-active' : '' }}">
            <i class="ti ti-chart-pie text-base shrink-0"></i>
            <span class="font-medium">Rekap Absen Mapel</span>
        </a>

        {{-- Rekap Jam Mengajar Guru --}}
        <a href="{{ route('rekap-jam-guru.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('rekap-jam-guru.*') ? 'nav-active' : '' }}">
            <i class="ti ti-clock-hour-4 text-base shrink-0"></i>
            <span class="font-medium">Rekap Jam Mengajar</span>
        </a>

        {{-- Kirim Pesan WhatsApp --}}
        <a href="{{ route('whatsapp-broadcast.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('whatsapp-broadcast.*') ? 'nav-active' : '' }}">
            <i class="ti ti-brand-whatsapp text-base shrink-0"></i>
            <span class="font-medium">Kirim Pesan WhatsApp</span>
        </a>
    </div>
</div>
