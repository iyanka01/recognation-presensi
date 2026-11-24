{{-- Navigasi Modul: Pengaturan Admin (Dropdown Accordion) --}}
@php
    $isPengaturanActive = request()->routeIs([
        'pengaturan-lembaga.*',
        'pengaturan-akun.*',
        'departemen.*',
        'tahun-ajaran.*',
        'backup.*'
    ]);
@endphp

<div class="pb-1 pt-4 px-3 nav-section-label" x-show="sidebarOpen">Pengaturan</div>

<div x-data="{ open: {{ $isPengaturanActive ? 'true' : 'false' }} }" class="space-y-0.5">
    <!-- Trigger Button -->
    <button type="button" 
            @click="if(!sidebarOpen) { sidebarOpen = true; open = true; } else { open = !open; }"
            class="nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-[7px] transition-all duration-200 {{ $isPengaturanActive ? 'font-bold text-blue-600 dark:text-blue-400 bg-blue-50/60 dark:bg-blue-950/30' : '' }} cursor-pointer">
        <div class="flex items-center gap-3 min-w-0">
            <i class="ti ti-settings text-lg shrink-0 {{ $isPengaturanActive ? 'text-blue-600 dark:text-blue-400' : '' }}"></i>
            <span x-show="sidebarOpen" class="text-sm font-semibold truncate">Pengaturan</span>
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

        {{-- Departemen / Instansi --}}
        <a href="{{ route('departemen.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('departemen.*') ? 'nav-active' : '' }}">
            <i class="ti ti-building text-base shrink-0"></i>
            <span class="font-medium">Departemen</span>
        </a>

        {{-- Tahun Ajaran --}}
        <a href="{{ route('tahun-ajaran.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('tahun-ajaran.*') ? 'nav-active' : '' }}">
            <i class="ti ti-calendar-time text-base shrink-0"></i>
            <span class="font-medium">Tahun Ajaran</span>
        </a>

        {{-- Pengaturan Lembaga & Akun --}}
        <a href="{{ route('pengaturan-lembaga.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('pengaturan-lembaga.*') || request()->routeIs('pengaturan-akun.*') ? 'nav-active' : '' }}">
            <i class="ti ti-adjustments-horizontal text-base shrink-0"></i>
            <span class="font-medium">Profil Lembaga</span>
        </a>

        {{-- Backup & Restore --}}
        <a href="{{ route('backup.index') }}"
           class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-[7px] text-xs {{ request()->routeIs('backup.*') ? 'nav-active' : '' }}">
            <i class="ti ti-database text-base shrink-0"></i>
            <span class="font-medium">Backup &amp; Restore</span>
        </a>
    </div>
</div>
