{{-- Navigasi Modul: Utama Admin --}}
<div class="pb-1 pt-3 px-3 nav-section-label" x-show="sidebarOpen">Utama</div>

{{-- Dashboard --}}
<a href="{{ route('dashboard') }}"
   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-[7px] {{ request()->routeIs('dashboard') ? 'nav-active' : '' }}">
    <i class="ti ti-layout-dashboard text-lg shrink-0"></i>
    <span x-show="sidebarOpen" class="text-sm font-semibold">Dashboard</span>
</a>

{{-- Dropdown Presensi (Absen Umum & Absen Kegiatan) --}}
@php
    $isPresensiActive = request()->routeIs(['presensi.*', 'jam-absensi.*', 'kegiatan-khusus.*']);
@endphp
<div x-data="{ open: {{ $isPresensiActive ? 'true' : 'false' }} }" class="space-y-0.5">
    <button type="button" 
            @click="if(!sidebarOpen) { sidebarOpen = true; open = true; } else { open = !open; }"
            class="nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-[7px] transition-all duration-200 {{ $isPresensiActive ? 'font-bold text-blue-600 dark:text-blue-400 bg-blue-50/60 dark:bg-blue-950/30' : '' }} cursor-pointer">
        <div class="flex items-center gap-3 min-w-0">
            <i class="ti ti-calendar-user text-lg shrink-0 {{ $isPresensiActive ? 'text-blue-600 dark:text-blue-400' : '' }}"></i>
            <span x-show="sidebarOpen" class="text-sm font-semibold truncate">Presensi</span>
        </div>
        <i x-show="sidebarOpen" 
           :class="open ? 'rotate-180' : ''" 
           class="ti ti-chevron-down text-xs transition-transform duration-200 text-slate-400"></i>
    </button>

    <div x-show="open && sidebarOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-1"
         class="pl-3.5 ml-4 space-y-0.5 border-l-2 border-slate-200 dark:border-slate-800">

        {{-- Absen Umum --}}
        <a href="{{ route('presensi.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('presensi.index') ? 'nav-active' : '' }}">
            <i class="ti ti-clipboard-list text-base shrink-0"></i>
            <span class="font-medium">Absen Umum</span>
        </a>

        {{-- Scan QR Masuk / Pulang --}}
        <a href="{{ route('presensi.scan') }}" target="_blank"
           class="nav-item flex items-center justify-between px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('presensi.scan') ? 'nav-active' : '' }}">
            <div class="flex items-center gap-2.5">
                <i class="ti ti-qrcode text-base shrink-0"></i>
                <span class="font-medium">Scan QR Umum</span>
            </div>
            <span class="text-[9px] font-bold bg-blue-100 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 px-1.5 py-0.5 rounded">LIVE</span>
        </a>

        {{-- Absen Kegiatan Khusus --}}
        <a href="{{ route('kegiatan-khusus.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('kegiatan-khusus.*') ? 'nav-active' : '' }}">
            <i class="ti ti-calendar-star text-base shrink-0"></i>
            <span class="font-medium">Absen Kegiatan</span>
        </a>

        {{-- Jam Absensi --}}
        <a href="{{ route('jam-absensi.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('jam-absensi.*') ? 'nav-active' : '' }}">
            <i class="ti ti-clock text-base shrink-0"></i>
            <span class="font-medium">Jam Absensi</span>
        </a>
    </div>
</div>
