@extends('layouts.guru')

@section('title', 'Detail Pengumuman')
@section('back_url', route('guru.informasi.index'))

@section('content')
<div class="space-y-4">

    <!-- Card Detail Pengumuman -->
    <div class="bg-white/95 rounded-3xl p-5 shadow-sm border border-sky-100">
        
        <!-- Metadata -->
        <div class="flex items-center gap-2 mb-3 flex-wrap">
            @php
                $badgeColor = match($pengumuman->kategori) {
                    'Penting' => 'bg-rose-100 text-rose-700 border-rose-200',
                    'Akademik' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                    'Kegiatan' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                    'Libur' => 'bg-amber-100 text-amber-700 border-amber-200',
                    default => 'bg-sky-100 text-sky-700 border-sky-200',
                };
            @endphp
            <span class="px-3 py-1 rounded-xl text-xs font-black border {{ $badgeColor }}">
                {{ $pengumuman->kategori }}
            </span>
            <span class="text-xs font-medium text-slate-400">
                {{ $pengumuman->created_at ? $pengumuman->created_at->locale('id')->isoFormat('dddd, D MMMM YYYY • HH:mm') : '-' }}
            </span>
        </div>

        <h1 class="text-lg font-black text-slate-800 leading-snug mb-4">
            {{ $pengumuman->judul }}
        </h1>

        <div class="prose prose-sm max-w-none text-slate-700 text-xs sm:text-sm leading-relaxed whitespace-pre-line border-t border-slate-100 pt-4">
            {{ $pengumuman->konten }}
        </div>

        @if($pengumuman->lampiran)
        <div class="mt-6 pt-4 border-t border-slate-100">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Dokumen Lampiran</h3>
            <a href="{{ Storage::url($pengumuman->lampiran) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 font-bold text-xs border border-sky-200 transition-colors shadow-sm">
                <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Unduh / Buka Dokumen Lampiran
            </a>
        </div>
        @endif

    </div>

    <div>
        <a href="{{ route('guru.informasi.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Pengumuman
        </a>
    </div>

</div>
@endsection
