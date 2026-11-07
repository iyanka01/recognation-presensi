@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Backup & Restore Database</h1>
            <p class="text-slate-500 mt-1 text-sm font-medium">Amankan data presensi dan sistem Anda secara berkala.</p>
        </div>
        
        <form action="{{ route('backup.create') }}" method="POST">
            @csrf
            <button type="submit" class="bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-sky-200 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                Buat Backup Baru
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center gap-3">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-bold">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 flex items-center gap-3">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-bold">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- List Backups -->
        <div class="lg:col-span-2">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-sky-100 flex items-center gap-3 bg-sky-50/30">
                    <div class="w-9 h-9 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-800">Daftar Backup</h2>
                </div>
                
                <div class="p-0 overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[500px]">
                        <thead>
                            <tr class="bg-sky-50/50 border-b border-sky-100 text-slate-500 text-xs font-bold uppercase tracking-wider">
                                <th class="p-4 pl-6">Nama File</th>
                                <th class="p-4">Tanggal</th>
                                <th class="p-4">Ukuran</th>
                                <th class="p-4 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sky-50 text-sm">
                            @forelse($backups as $backup)
                                <tr class="hover:bg-sky-50/30 transition-colors group">
                                    <td class="p-4 pl-6 font-medium text-slate-700 flex items-center gap-3">
                                        <svg class="w-5 h-5 text-slate-400 group-hover:text-sky-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        {{ $backup['name'] }}
                                    </td>
                                    <td class="p-4 text-slate-500 text-sm">{{ $backup['date'] }}</td>
                                    <td class="p-4 text-slate-500 text-sm font-medium">{{ $backup['size'] }}</td>
                                    <td class="p-4 pr-6 text-right space-x-2">
                                        <a href="{{ route('backup.download', $backup['name']) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition-all animate-smooth" title="Download">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        </a>
                                        <form action="{{ route('backup.destroy', $backup['name']) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus file backup ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white transition-all animate-smooth" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-slate-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                            <span class="font-medium text-lg text-slate-600">Belum Ada Backup</span>
                                            <p class="text-xs mt-1 text-slate-400">Silakan buat backup pertama Anda untuk mengamankan data.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Restore Database -->
        <div>
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 overflow-hidden sticky top-6">
                <div class="px-5 py-4 border-b border-sky-100 flex items-center gap-3 bg-sky-50/30">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-800">Restore Database</h2>
                </div>
                
                <div class="p-6">
                    <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 mb-6">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <div class="text-xs text-rose-800">
                                <p class="font-bold mb-1">Peringatan Keras!</p>
                                <p>Proses restore akan <b class="font-bold text-rose-900">menimpa seluruh data</b> yang ada saat ini dengan data dari file backup. Pastikan Anda mengunggah file yang benar.</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('backup.restore') }}" method="POST" enctype="multipart/form-data" onsubmit="return confirm('PERINGATAN! Seluruh data saat ini akan digantikan oleh data backup. Apakah Anda yakin ingin melanjutkan?')">
                        @csrf
                        
                        <div class="mb-5">
                            <label class="block text-xs font-bold text-slate-700 mb-2">Upload File Backup (.sql)</label>
                            <input type="file" name="backup_file" accept=".sql" required
                                class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 transition-colors border border-sky-200 rounded-xl cursor-pointer bg-slate-50">
                            @error('backup_file')
                                <p class="text-xs text-rose-500 mt-2 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white px-5 py-3 rounded-xl font-bold transition-colors flex items-center justify-center gap-2 shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            Jalankan Restore
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
    </div>
</div>
@endsection

