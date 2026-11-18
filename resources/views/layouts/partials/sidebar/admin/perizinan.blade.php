{{-- Navigasi Modul: Perizinan Admin --}}
<div class="pb-1 pt-4 px-3 nav-section-label" x-show="sidebarOpen">Perizinan</div>

{{-- Input Perizinan & Approval --}}
<a href="{{ route('izin.index') }}"
   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-[7px] {{ request()->routeIs('izin.*') ? 'nav-active' : '' }}">
    <i class="ti ti-file-text text-lg shrink-0"></i>
    <span x-show="sidebarOpen" class="text-sm font-semibold">Input Perizinan</span>
</a>
