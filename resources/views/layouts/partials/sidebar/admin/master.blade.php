{{-- Navigasi Modul: Data Master Admin (Dropdown Accordion) --}}
@php
    $isMasterActive = request()->routeIs([
        'siswa.*', 
        'kelas.*', 
        'guru.*', 
        'jabatan.*',
        'mapel.*', 
        'jadwal.*', 
        'cetak-kartu.*', 
        'hari-libur.*', 
        'pengumuman.*'
    ]);
@endphp

<div x-data="{ open: {{ $isMasterActive ? 'true' : 'false' }} }" class="space-y-0.5">
    <!-- Trigger Button -->
    <button type="button" 
            @click="if(!sidebarOpen) { sidebarOpen = true; open = true; } else { open = !open; }"
            class="nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-[7px] transition-all duration-200 {{ $isMasterActive ? 'font-bold text-blue-600 dark:text-blue-400 bg-blue-50/60 dark:bg-blue-950/30' : '' }} cursor-pointer">
        <div class="flex items-center gap-3 min-w-0">
            <i class="ti ti-database text-lg shrink-0 {{ $isMasterActive ? 'text-blue-600 dark:text-blue-400' : '' }}"></i>
            <span x-show="sidebarOpen" class="text-sm font-semibold truncate">Data Master</span>
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

        {{-- Data Siswa --}}
        <a href="{{ route('siswa.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('siswa.*') ? 'nav-active' : '' }}">
            <i class="ti ti-users text-base shrink-0"></i>
            <span class="font-medium">Data Siswa</span>
        </a>

        {{-- Data Kelas --}}
        <a href="{{ route('kelas.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('kelas.*') ? 'nav-active' : '' }}">
            <i class="ti ti-school text-base shrink-0"></i>
            <span class="font-medium">Data Kelas</span>
        </a>

        {{-- Data Guru --}}
        <a href="{{ route('guru.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('guru.*') ? 'nav-active' : '' }}">
            <i class="ti ti-user-check text-base shrink-0"></i>
            <span class="font-medium">Data Guru</span>
        </a>

        {{-- Data Jabatan --}}
        <a href="{{ route('jabatan.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('jabatan.*') ? 'nav-active' : '' }}">
            <i class="ti ti-briefcase text-base shrink-0"></i>
            <span class="font-medium">Data Jabatan</span>
        </a>

        {{-- Mata Pelajaran --}}
        <a href="{{ route('mapel.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('mapel.*') ? 'nav-active' : '' }}">
            <i class="ti ti-book-2 text-base shrink-0"></i>
            <span class="font-medium">Mata Pelajaran</span>
        </a>

        {{-- Jadwal Pelajaran --}}
        <a href="{{ route('jadwal.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('jadwal.*') ? 'nav-active' : '' }}">
            <i class="ti ti-calendar-event text-base shrink-0"></i>
            <span class="font-medium">Jadwal Pelajaran</span>
        </a>

        {{-- Cetak Kartu ID --}}
        <a href="{{ route('cetak-kartu.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('cetak-kartu.*') ? 'nav-active' : '' }}">
            <i class="ti ti-id-badge-2 text-base shrink-0"></i>
            <span class="font-medium">Cetak Kartu ID</span>
        </a>

        {{-- Hari Libur --}}
        <a href="{{ route('hari-libur.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('hari-libur.*') ? 'nav-active' : '' }}">
            <i class="ti ti-calendar-off text-base shrink-0"></i>
            <span class="font-medium">Hari Libur</span>
        </a>

        {{-- Pengumuman --}}
        <a href="{{ route('pengumuman.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('pengumuman.*') ? 'nav-active' : '' }}">
            <i class="ti ti-speakerphone text-base shrink-0"></i>
            <span class="font-medium">Pengumuman</span>
        </a>
    </div>
</div>
