@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Hari Libur</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola daftar hari libur nasional atau kegiatan khusus sekolah.</p>
        </div>
        <button @click="$dispatch('open-modal')" class="px-4 py-2.5 bg-rose-500 hover:bg-rose-600 text-white font-medium rounded-xl flex items-center gap-2 transition-colors shadow-sm shadow-rose-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Tambah Libur
        </button>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl shadow-sm flex items-start gap-3">
        <svg class="w-5 h-5 shrink-0 mt-0.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <p class="text-sm font-medium">{{ session('success') }}</p>
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl shadow-sm flex items-start gap-3">
        <svg class="w-5 h-5 shrink-0 mt-0.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <ul class="text-sm font-medium list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-sky-50/50 border-b border-sky-100 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold w-16 text-center">No</th>
                        <th class="p-4 font-semibold w-48">Tanggal</th>
                        <th class="p-4 font-semibold">Keterangan</th>
                        <th class="p-4 font-semibold w-24 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sky-50 text-sm">
                    @forelse($liburs as $idx => $libur)
                    @php
                        $isPast = \Carbon\Carbon::parse($libur->tanggal)->isPast() && !\Carbon\Carbon::parse($libur->tanggal)->isToday();
                    @endphp
                    <tr class="hover:bg-sky-50/30 transition-colors {{ $isPast ? 'opacity-60 bg-slate-50/30' : '' }}">
                        <td class="p-4 text-center text-slate-400 font-medium">{{ $idx + 1 }}</td>
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex flex-col items-center justify-center {{ $isPast ? 'bg-slate-200 text-slate-500' : 'bg-rose-100 text-rose-600' }}">
                                    <span class="text-xs font-bold leading-none">{{ \Carbon\Carbon::parse($libur->tanggal)->format('d') }}</span>
                                    <span class="text-[10px] uppercase font-semibold leading-none mt-1">{{ \Carbon\Carbon::parse($libur->tanggal)->locale('id')->shortMonthName }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-slate-700 block">{{ \Carbon\Carbon::parse($libur->tanggal)->locale('id')->isoFormat('D MMMM YYYY') }}</span>
                                    <span class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($libur->tanggal)->locale('id')->dayName }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="font-medium text-slate-700">{{ $libur->keterangan }}</span>
                            @if($isPast)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-500">
                                    Berlalu
                                </span>
                            @elseif(\Carbon\Carbon::parse($libur->tanggal)->isToday())
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-rose-100 text-rose-600 animate-pulse">
                                    Hari Ini
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            <form action="{{ route('hari-libur.destroy', $libur->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus hari libur ini?');" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-12 text-center bg-white">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 ring-8 ring-slate-50/50">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <h3 class="text-lg font-bold text-slate-700 mb-1">Belum Ada Hari Libur</h3>
                                <p class="text-slate-500 font-medium mb-4">Tidak ada data hari libur yang ditambahkan.</p>
                                <button @click="$dispatch('open-modal')" class="px-5 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-600 font-medium rounded-xl transition-colors">
                                    Tambah Libur Sekarang
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form Tambah Libur -->
<div x-data="{ open: false }" 
    @open-modal.window="open = true"
    x-show="open" 
    class="fixed inset-0 z-50 overflow-y-auto" 
    style="display: none;"
    x-transition.opacity.duration.300ms>
    
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>

        <div class="relative inline-block w-full max-w-md overflow-hidden text-left align-bottom transition-all transform bg-white shadow-2xl rounded-2xl sm:my-8 sm:align-middle"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <div class="px-6 py-4 border-b border-sky-100 flex items-center justify-between bg-sky-50/50/50">
                <h3 class="text-lg font-bold text-slate-800">Tambah Hari Libur</h3>
                <button @click="open = false" class="text-slate-400 hover:text-slate-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('hari-libur.store') }}" method="POST">
                @csrf
                <div class="px-6 py-6 space-y-5">
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Libur <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all font-medium text-slate-700">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Keterangan / Alasan Libur <span class="text-rose-500">*</span></label>
                        <textarea name="keterangan" required rows="3" placeholder="Contoh: Hari Kemerdekaan RI, Cuti Bersama, dll." class="w-full px-4 py-3 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all placeholder:text-slate-400"></textarea>
                    </div>

                </div>
                
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2.5 bg-rose-500 hover:bg-rose-600 text-white text-sm font-medium rounded-xl transition-colors shadow-sm shadow-rose-200">
                        Simpan Hari Libur
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
