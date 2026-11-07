@extends('layouts.app')

@section('title', 'Edit Data Kelas')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('kelas.index') }}" class="p-2 text-slate-500 hover:bg-slate-100 rounded-xl transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h2 class="text-2xl font-bold text-slate-800">Edit Data Kelas</h2>
    </div>

    @if ($errors->any())
        <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-xl shadow-sm">
            <ul class="list-disc pl-4 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('kelas.update', $kelas->id) }}" method="POST" class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 overflow-hidden">
        @csrf
        @method('PUT')
        
        <div class="bg-slate-50/50 px-8 py-5 border-b border-slate-100">
            <h3 class="text-lg font-semibold text-slate-800">Informasi Rombel</h3>
        </div>

        <div class="p-8 space-y-6">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tingkat Kelas <span class="text-rose-500">*</span></label>
                <input type="text" name="tingkat" value="{{ old('tingkat', $kelas->tingkat) }}" placeholder="Misal: 7, 8, 10, A, B" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Sub Kelas / Rombel <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_rombel" value="{{ old('nama_rombel', $kelas->nama_rombel) }}" placeholder="Misal: 10 AKL" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Wali Kelas (Opsional)</label>
                <select name="wali_kelas_id" class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
                    <option value="">-- Pilih Wali Kelas --</option>
                    @foreach($gurus as $g)
                    <option value="{{ $g->id }}" {{ old('wali_kelas_id', $kelas->wali_kelas_id) == $g->id ? 'selected' : '' }}>
                        {{ $g->nama_lengkap }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="bg-sky-50/30 px-8 py-5 border-t border-sky-100 flex items-center justify-end gap-3">
            <a href="{{ route('kelas.index') }}" class="px-6 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-200 rounded-xl transition-colors">Batal</a>
            <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg shadow-sm shadow-amber-300/40 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Perbarui Data Kelas
            </button>
        </div>
    </form>
</div>
@endsection
