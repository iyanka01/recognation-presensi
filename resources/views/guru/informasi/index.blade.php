@extends('layouts.guru')

@section('title', 'Informasi & Pengumuman')
@section('back_url', route('dashboard'))

@section('content')
<div class="space-y-5">

    <!-- Header Banner -->
    <div class="bg-gradient-to-br from-rose-500 via-pink-600 to-purple-700 rounded-3xl p-5 text-white shadow-lg shadow-pink-200 relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-36 h-36 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/20 backdrop-blur-md text-white border border-white/20 mb-2">
                <svg class="w-3.5 h-3.5 text-pink-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                Papan Pengumuman
            </span>
            <h1 class="text-xl font-black tracking-tight">Informasi Sekolah</h1>
            <p class="text-xs text-pink-100/90 mt-1">Pemberitahuan resmi, agenda kegiatan, dan edaran penting dari pimpinan madrasah/sekolah.</p>
        </div>
    </div>

    <!-- Filter Kategori -->
    <div class="overflow-x-auto no-scrollbar -mx-4 px-4 pb-1">
        <div class="flex items-center gap-2 min-w-max">
            @foreach(['Semua', 'Penting', 'Akademik', 'Kegiatan', 'Libur', 'Umum'] as $kat)
            @php $isAktif = ($selectedKategori === $kat); @endphp
            <a href="{{ route('guru.informasi.index', ['kategori' => $kat]) }}"
               class="px-4 py-2 rounded-2xl text-xs font-bold transition-all {{ $isAktif ? 'bg-pink-600 text-white shadow-md shadow-pink-200 scale-[1.02]' : 'bg-white/90 text-slate-600 border border-sky-100 hover:bg-sky-50' }}">
                {{ $kat }}
            </a>
            @endforeach
        </div>
    </div>

    <!-- List Pengumuman -->
    <div class="space-y-3">
        @forelse($pengumumans as $p)
        @php
            $badgeColor = match($p->kategori) {
                'Penting' => 'bg-rose-100 text-rose-700 border-rose-200',
                'Akademik' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                'Kegiatan' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                'Libur' => 'bg-amber-100 text-amber-700 border-amber-200',
                default => 'bg-sky-100 text-sky-700 border-sky-200',
            };
        @endphp
        <a href="{{ route('guru.informasi.show', $p->id) }}"
           class="block bg-white/95 rounded-2xl p-4 shadow-sm border border-sky-100 hover:border-pink-200 hover:shadow-md transition-all group">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                        <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black border {{ $badgeColor }}">
                            {{ $p->kategori }}
                        </span>
                        <span class="text-[11px] font-medium text-slate-400">
                            {{ $p->created_at ? $p->created_at->locale('id')->diffForHumans() : '-' }}
                        </span>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-800 group-hover:text-pink-600 transition-colors line-clamp-2">
                        {{ $p->judul }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                        {{ Str::limit(strip_tags($p->konten), 120) }}
                    </p>
                </div>

                <div class="shrink-0 self-center">
                    <div class="w-8 h-8 rounded-xl bg-slate-50 group-hover:bg-pink-50 flex items-center justify-center text-slate-400 group-hover:text-pink-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </div>
            </div>

            @if($p->lampiran)
            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center gap-1.5 text-[11px] font-bold text-slate-500">
                <svg class="w-3.5 h-3.5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                <span>Tersedia Dokumen Lampiran</span>
            </div>
            @endif
        </a>
        @empty
        <div class="bg-white/80 rounded-2xl border border-dashed border-sky-200 p-10 text-center">
            <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-pink-50 text-pink-400 flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            </div>
            <h3 class="font-bold text-slate-700 text-sm">Belum Ada Pengumuman</h3>
            <p class="text-xs text-slate-400 mt-1">Saat ini belum ada informasi atau pengumuman yang diterbitkan.</p>
        </div>
        @endforelse
    </div>

</div>
@endsection
