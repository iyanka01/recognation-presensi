@extends(Auth::guard('guru')->check() ? 'layouts.guru' : 'layouts.app')

@section('title', 'Rekap Absensi Mapel')

@section('content')
<div class="space-y-6" x-data="rekapMapelWa()">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800">Rekap Absensi Mata Pelajaran</h1>
            <p class="text-sm text-slate-500 mt-1">Rekapitulasi kehadiran siswa per mata pelajaran dalam rentang tanggal.</p>
        </div>
        @if($filterKelas)
        <div class="flex items-center gap-2">
            <!-- Tombol Kirim WhatsApp -->
            <button type="button" @click="openWaModal = true; loadPreview()"
                    class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl flex items-center gap-2 transition-all shadow-sm shadow-emerald-200">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                <span>Kirim Rekap WA</span>
            </button>

            <!-- Tombol Cetak -->
            <a href="{{ route('rekap-mapel.print', request()->only(['departemen_id', 'kelas_id', 'mapel_id', 'dari', 'sampai'])) }}" target="_blank"
               class="px-4 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-medium rounded-xl flex items-center gap-2 transition-all shadow-sm shadow-sky-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Filter -->
    <form method="GET" action="{{ route('rekap-mapel.index') }}"
          class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 p-5 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3 items-end">
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Departemen</label>
            <select name="departemen_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-sky-400/30 transition-all">
                <option value="all">-- Semua Departemen --</option>
                @foreach($departemens as $dep)
                <option value="{{ $dep->id }}" {{ $filterDepartemen == $dep->id ? 'selected' : '' }}>{{ $dep->nama_departemen }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-1 md:col-span-1">
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Kelas</label>
            <select name="kelas_id" required class="w-full px-3 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 transition-all">
                <option value="">-- Pilih Kelas --</option>
                @foreach($kelasList as $k)
                <option value="{{ $k->id }}" {{ $filterKelas == $k->id ? 'selected' : '' }}>Kelas {{ $k->tingkat }} {{ $k->nama_rombel }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Mapel</label>
            <select name="mapel_id" class="w-full px-3 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 transition-all">
                <option value="">Semua Mapel</option>
                @foreach($mapels as $m)
                <option value="{{ $m->id }}" {{ $filterMapel == $m->id ? 'selected' : '' }}>{{ $m->nama_mapel }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Dari</label>
            <input type="date" name="dari" value="{{ $dari }}" class="w-full px-3 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 transition-all">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Sampai</label>
            <input type="date" name="sampai" value="{{ $sampai }}" class="w-full px-3 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-sky-400/30 transition-all">
        </div>
        <button type="submit" class="px-4 py-2.5 bg-slate-700 hover:bg-slate-800 text-white text-xs sm:text-sm font-bold rounded-xl transition-all shadow-sm">Tampilkan Rekap</button>
    </form>

    <!-- Tabel Rekap -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-sky-50/50 border-b border-sky-100 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold w-12">No</th>
                        <th class="p-4 font-semibold">Nama Siswa</th>
                        <th class="p-4 font-semibold text-center bg-emerald-50/40">Hadir</th>
                        <th class="p-4 font-semibold text-center bg-blue-50/40">Izin</th>
                        <th class="p-4 font-semibold text-center bg-amber-50/40">Sakit</th>
                        <th class="p-4 font-semibold text-center bg-rose-50/40">Alpa</th>
                        <th class="p-4 font-semibold text-center">% Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sky-50 text-sm">
                    @forelse($rekap as $i => $r)
                    <tr class="hover:bg-sky-50/30 transition-colors">
                        <td class="p-4 text-slate-400 font-medium">{{ $i + 1 }}</td>
                        <td class="p-4 font-medium text-slate-800">{{ $r->siswa->nama_lengkap }}</td>
                        <td class="p-4 text-center font-bold text-emerald-600">{{ $r->hadir }}</td>
                        <td class="p-4 text-center font-bold text-blue-600">{{ $r->izin }}</td>
                        <td class="p-4 text-center font-bold text-amber-600">{{ $r->sakit }}</td>
                        <td class="p-4 text-center font-bold text-rose-600">{{ $r->alpa }}</td>
                        <td class="p-4 text-center">
                            @if($r->persen !== null)
                            <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold {{ $r->persen >= 90 ? 'bg-emerald-100 text-emerald-700' : ($r->persen >= 75 ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                                {{ $r->persen }}%
                            </span>
                            @else
                            <span class="text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-500">
                            @if(empty($filterKelas))
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="p-3 bg-sky-50 text-sky-600 rounded-2xl text-xl">ℹ️</span>
                                    <span class="font-bold text-slate-700">Silakan pilih kelas terlebih dahulu</span>
                                    <span class="text-xs text-slate-400">Pilih departemen & kelas tujuan, kemudian klik tombol "Tampilkan Rekap".</span>
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="p-3 bg-amber-50 text-amber-600 rounded-2xl text-xl">📂</span>
                                    <span class="font-bold text-slate-700">Tidak ada data siswa ditemukan</span>
                                    <span class="text-xs text-slate-400">Kelas yang dipilih belum memiliki data siswa terdaftar.</span>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Broadcast Rekap Mapel via WhatsApp -->
    <div x-show="openWaModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="openWaModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-emerald-100 overflow-hidden transform transition-all">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Broadcast Rekap Mapel via WhatsApp</h3>
                            <p class="text-xs text-slate-500">Kirimkan ringkasan laporan kehadiran mapel ke Wali Kelas, Guru Mapel, atau Kepala Sekolah.</p>
                        </div>
                    </div>
                    <button type="button" @click="openWaModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Form Kirim -->
                <form action="{{ route('rekap-mapel.send-wa') }}" method="POST" @submit="confirmSend($event)" class="space-y-4">
                    @csrf

                    <!-- Target Penerima Rekomendasi -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Target Penerima WhatsApp</label>
                        <div class="space-y-2">
                            <!-- Wali Kelas -->
                            @if(isset($selectedKelas) && $selectedKelas->waliKelas)
                                @php $wk = $selectedKelas->waliKelas; @endphp
                                <label class="flex items-center justify-between p-3 rounded-xl border border-emerald-200 bg-emerald-50/70 hover:bg-emerald-50 cursor-pointer transition-all">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" name="recipients[]" value="{{ $wk->no_hp }}"
                                               {{ !empty($wk->no_hp) ? 'checked' : '' }}
                                               {{ empty($wk->no_hp) ? 'disabled' : '' }}
                                               class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                        <div>
                                            <span class="text-xs font-bold text-slate-800 block">🎓 Wali Kelas: {{ $wk->nama_lengkap }} (Kelas {{ $selectedKelas->tingkat }} {{ $selectedKelas->nama_rombel }})</span>
                                            <span class="text-[11px] text-slate-500 font-mono">{{ $wk->no_hp ?: '❌ No HP belum diisi di data guru' }}</span>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-semibold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">Rekomendasi</span>
                                </label>
                            @endif

                            <!-- Guru Mapel Terkait (jika mapel dipilih spesifik) -->
                            @if(isset($targetGuruMapel) && $targetGuruMapel)
                                <label class="flex items-center justify-between p-3 rounded-xl border border-sky-200 bg-sky-50/70 hover:bg-sky-50 cursor-pointer transition-all">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" name="recipients[]" value="{{ $targetGuruMapel->no_hp }}"
                                               {{ !empty($targetGuruMapel->no_hp) ? 'checked' : '' }}
                                               {{ empty($targetGuruMapel->no_hp) ? 'disabled' : '' }}
                                               class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                        <div>
                                            <span class="text-xs font-bold text-slate-800 block">👨‍🏫 Guru Pengampu Mapel: {{ $targetGuruMapel->nama_lengkap }}</span>
                                            <span class="text-[11px] text-slate-500 font-mono">{{ $targetGuruMapel->no_hp ?: '❌ No HP belum diisi' }}</span>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-semibold bg-sky-100 text-sky-800 px-2 py-0.5 rounded-full">Guru Mapel</span>
                                </label>
                            @endif

                            <!-- Kepala Sekolah -->
                            @php
                                $kepsekPhone = $pengaturan->no_telp ?? ($pengaturan->kepalaSekolah->no_hp ?? '');
                                $kepsekNama = $pengaturan->nama_kepala ?? ($pengaturan->kepalaSekolah->nama_lengkap ?? 'Kepala Sekolah');
                            @endphp
                            <label class="flex items-center justify-between p-3 rounded-xl border border-indigo-200 bg-indigo-50/60 hover:bg-indigo-50 cursor-pointer transition-all">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="recipients[]" value="{{ $kepsekPhone }}"
                                           {{ empty($kepsekPhone) ? 'disabled' : '' }}
                                           class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 block">👑 Kepala Sekolah: {{ $kepsekNama }}</span>
                                        <span class="text-[11px] text-slate-500 font-mono">{{ $kepsekPhone ?: '❌ No Telp/WA belum diisi di Pengaturan Lembaga' }}</span>
                                    </div>
                                </div>
                                <span class="text-[10px] font-semibold bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded-full">Kepsek</span>
                            </label>
                        </div>
                    </div>

                    <!-- Kirim ke Nomor Manual Tambahan -->
                    <div>
                        <label for="wa_manual_num" class="block text-xs font-semibold text-slate-700 mb-1">Nomor WhatsApp Manual Tambahan (Opsional)</label>
                        <input type="text" name="recipients[]" id="wa_manual_num" placeholder="Contoh: 081234567890"
                               class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    </div>

                    <!-- Preview & Edit Teks Pesan -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Preview Pesan WhatsApp</label>
                            <span class="text-[11px] text-slate-400">Dapat diedit bebas</span>
                        </div>
                        <textarea name="message" id="wa_message_content" rows="9" x-model="waMessage"
                                  class="w-full p-3 font-mono text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-400 text-slate-700 leading-relaxed"
                                  placeholder="Menyiapkan pesan WhatsApp..."></textarea>
                    </div>

                    <!-- Tombol Aksi Modal -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="openWaModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-all">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl flex items-center gap-2 shadow-sm transition-all">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            <span>Kirim WhatsApp Sekarang</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function rekapMapelWa() {
    return {
        openWaModal: false,
        waMessage: '',
        kelasId: '{{ $filterKelas ?? "" }}',
        mapelId: '{{ $filterMapel ?? "" }}',
        dari: '{{ $dari }}',
        sampai: '{{ $sampai }}',
        loadPreview() {
            if (this.waMessage) return; // sudah termuat
            fetch(`{{ route('rekap-mapel.preview-wa') }}?kelas_id=${this.kelasId}&mapel_id=${this.mapelId}&dari=${this.dari}&sampai=${this.sampai}`)
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        this.waMessage = res.message;
                    }
                })
                .catch(err => console.error(err));
        },
        confirmSend(e) {
            const checkedCount = document.querySelectorAll('input[name="recipients[]"]:checked').length;
            const manualVal = document.getElementById('wa_manual_num')?.value.trim();
            if (checkedCount === 0 && !manualVal) {
                alert('Silakan pilih atau masukkan minimal 1 nomor WhatsApp penerima!');
                e.preventDefault();
                return false;
            }
            if (!confirm('Kirimkan rekapitulasi kehadiran mapel ini ke nomor yang dipilih via WhatsApp?')) {
                e.preventDefault();
                return false;
            }
        }
    }
}
</script>
@endsection
