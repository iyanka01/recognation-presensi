{{-- Navigasi Modul: Pengaturan Admin --}}
<div class="pb-1.5 pt-4 px-2 nav-section-label" x-show="sidebarOpen">Pengaturan</div>

{{-- Pengaturan Lembaga & Akun --}}
<a href="{{ route('pengaturan-lembaga.index') }}"
   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('pengaturan-lembaga.*') || request()->routeIs('pengaturan-akun.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
    </svg>
    <span x-show="sidebarOpen" class="text-sm font-semibold">Pengaturan</span>
</a>

{{-- Backup & Restore --}}
<a href="{{ route('backup.index') }}"
   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('backup.*') ? 'nav-active text-white' : 'text-sky-100/80 hover:text-white' }}">
    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
    </svg>
    <span x-show="sidebarOpen" class="text-sm font-semibold">Backup &amp; Restore</span>
</a>
