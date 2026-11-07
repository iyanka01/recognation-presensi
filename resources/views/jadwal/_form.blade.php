<div class="px-6 py-6 space-y-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Kelas</label>
        <select name="kelas_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
            <option value="">-- Pilih Kelas --</option>
            @foreach($kelasList as $k)
            <option value="{{ $k->id }}">{{ 'Kelas ' . $k->tingkat . ' ' . $k->nama_rombel }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Mata Pelajaran</label>
        <select name="mata_pelajaran_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
            <option value="">-- Pilih Mata Pelajaran --</option>
            @foreach($mapels as $m)
            <option value="{{ $m->id }}">{{ $m->nama_mapel }} ({{ $m->kode_mapel }})</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Guru Pengajar</label>
        <select name="guru_id" class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
            <option value="">-- Pilih Guru Pengajar --</option>
            @foreach($gurus as $g)
            <option value="{{ $g->id }}">{{ $g->nama_lengkap }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Hari</label>
        <select name="hari" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
            <option value="">-- Pilih Hari --</option>
            @foreach($hariList as $h)
            <option value="{{ $h }}">{{ $h }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Jam Mulai</label>
            <input type="time" name="jam_mulai" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Jam Selesai</label>
            <input type="time" name="jam_selesai" required class="w-full px-4 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 focus:border-sky-400 transition-all">
        </div>
    </div>

    <p class="text-xs text-slate-400 flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Sistem otomatis menolak jika jadwal kelas atau guru pengajar bentrok pada hari & jam yang sama.
    </p>
</div>
