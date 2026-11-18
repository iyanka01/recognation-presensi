{{-- Navigasi Modul: Pengaturan Admin --}}
<div class="pb-1 pt-4 px-3 nav-section-label" x-show="sidebarOpen">Pengaturan</div>

{{-- Pengaturan Lembaga & Akun --}}
<a href="{{ route('pengaturan-lembaga.index') }}"
   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-[7px] {{ request()->routeIs('pengaturan-lembaga.*') || request()->routeIs('pengaturan-akun.*') ? 'nav-active' : '' }}">
    <i class="ti ti-settings text-lg shrink-0"></i>
    <span x-show="sidebarOpen" class="text-sm font-semibold">Pengaturan</span>
</a>

{{-- Backup & Restore --}}
<a href="{{ route('backup.index') }}"
   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-[7px] {{ request()->routeIs('backup.*') ? 'nav-active' : '' }}">
    <i class="ti ti-database text-lg shrink-0"></i>
    <span x-show="sidebarOpen" class="text-sm font-semibold">Backup &amp; Restore</span>
</a>
