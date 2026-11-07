@extends('layouts.app')

@section('title', 'Kelola Pengumuman')

@section('content')
<div class="space-y-6" x-data="{ showModal: false, editMode: false, currentId: null, form: { judul: '', konten: '', kategori: 'Umum', target: 'semua', is_aktif: 1 } }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800">Papan Pengumuman & Informasi</h1>
            <p class="text-sm text-slate-500 mt-1">Publikasikan pengumuman sekolah untuk guru dan siswa.</p>
        </div>
        <button @click="showModal = true; editMode = false; form = { judul: '', konten: '', kategori: 'Umum', target: 'semua', is_aktif: 1 }"
                class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-medium rounded-xl flex items-center gap-2 shadow-sm shadow-sky-200 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Buat Pengumuman Baru
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-sky-50/50 border-b border-sky-100 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold w-16 text-center">No</th>
                        <th class="p-4 font-semibold">Judul Pengumuman</th>
                        <th class="p-4 font-semibold">Kategori</th>
                        <th class="p-4 font-semibold">Target</th>
                        <th class="p-4 font-semibold">Tanggal</th>
                        <th class="p-4 font-semibold text-center">Lampiran</th>
                        <th class="p-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sky-50 text-sm">
                    @forelse($pengumumans as $idx => $p)
                    <tr class="hover:bg-sky-50/30 transition-colors">
                        <td class="p-4 text-center text-slate-400 font-medium">{{ $idx + 1 }}</td>
                        <td class="p-4">
                            <span class="font-bold text-slate-800 block">{{ $p->judul }}</span>
                            <span class="text-xs text-slate-400 line-clamp-1 mt-0.5">{{ Str::limit(strip_tags($p->konten), 60) }}</span>
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-sky-100 text-sky-700">
                                {{ $p->kategori }}
                            </span>
                        </td>
                        <td class="p-4 capitalize text-slate-600 font-medium">{{ $p->target }}</td>
                        <td class="p-4 text-slate-500 text-xs">{{ $p->created_at ? $p->created_at->format('d M Y H:i') : '-' }}</td>
                        <td class="p-4 text-center">
                            @if($p->lampiran)
                            <a href="{{ Storage::url($p->lampiran) }}" target="_blank" class="text-xs text-sky-600 font-bold hover:underline">Lihat</a>
                            @else
                            <span class="text-xs text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="p-4 flex items-center justify-end gap-2">
                            <form action="{{ route('pengumuman.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus pengumuman ini?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center text-slate-400">Belum ada pengumuman yang diterbitkan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form -->
    <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showModal = false"></div>
            <div class="inline-block transform overflow-hidden rounded-2xl bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle w-full relative z-10 p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Buat Pengumuman Baru</h3>
                <form action="{{ route('pengumuman.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Judul Pengumuman</label>
                        <input type="text" name="judul" required placeholder="Contoh: Jadwal Ujian Semester Ganjil" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-sky-400/30">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kategori</label>
                            <select name="kategori" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm">
                                <option value="Umum">Umum</option>
                                <option value="Penting">Penting</option>
                                <option value="Akademik">Akademik</option>
                                <option value="Kegiatan">Kegiatan</option>
                                <option value="Libur">Libur</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Sasaran / Target</label>
                            <select name="target" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm">
                                <option value="semua">Semua (Guru & Siswa)</option>
                                <option value="guru">Khusus Guru</option>
                                <option value="siswa">Khusus Siswa</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Isi Pengumuman</label>
                        <textarea name="konten" rows="5" required placeholder="Tuliskan isi pengumuman..." class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Lampiran File (Opsional)</label>
                        <input type="file" name="lampiran" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-slate-600 font-medium text-sm hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white font-medium text-sm rounded-xl shadow-sm">Terbitkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
