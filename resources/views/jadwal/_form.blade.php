<div class="px-6 py-5 space-y-4">
    <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Kelas &amp; Rombel</label>
        <select name="kelas_id" required class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm">
            <option value="">-- Pilih Kelas --</option>
            @foreach($kelasList as $k)
                @php
                    $namaTingkat = \Illuminate\Support\Str::startsWith(strtolower($k->tingkat), 'kelas') ? $k->tingkat : 'Kelas ' . $k->tingkat;
                @endphp
                <option value="{{ $k->id }}">{{ $namaTingkat }} {{ $k->nama_rombel }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Mata Pelajaran</label>
        <select name="mata_pelajaran_id" required class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm">
            <option value="">-- Pilih Mata Pelajaran --</option>
            @foreach($mapels as $m)
                <option value="{{ $m->id }}">{{ $m->nama_mapel }} ({{ $m->kode_mapel }})</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Guru Pengajar</label>
        <select name="guru_id" class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm">
            <option value="">-- Pilih Guru Pengajar --</option>
            @foreach($gurus as $g)
                <option value="{{ $g->id }}">{{ $g->nama_lengkap }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Hari Pembelajaran</label>
        <select name="hari" required class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm">
            <option value="">-- Pilih Hari --</option>
            @foreach($hariList as $h)
                <option value="{{ $h }}">{{ $h }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Jam Mulai</label>
            <input type="time" name="jam_mulai" required class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Jam Selesai</label>
            <input type="time" name="jam_selesai" required class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm">
        </div>
    </div>

    <div class="p-3 rounded-[7px] bg-blue-50/60 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/40 text-[11px] text-blue-700 dark:text-blue-300 flex items-start gap-2">
        <i class="ti ti-info-circle text-base shrink-0 mt-0.5"></i>
        <span>Sistem akan otomatis memvalidasi jadwal agar tidak terjadi bentrok antara guru dan rombel kelas pada hari dan jam yang sama.</span>
    </div>
</div>
