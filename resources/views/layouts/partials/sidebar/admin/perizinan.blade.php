{{-- Navigasi Modul: Perizinan Admin --}}
<div class="pb-1.5 pt-4 px-2 nav-section-label" x-show="sidebarOpen">Perizinan</div>

{{-- Input Perizinan & Approval --}}
<a href="{{ route('izin.index') }}"
   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('izin.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
    </svg>
    <span x-show="sidebarOpen" class="text-sm font-semibold">Input Perizinan</span>
</a>
