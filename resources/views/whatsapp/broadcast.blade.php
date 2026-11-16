@extends('layouts.app')

@section('title', 'Kirim Laporan WhatsApp')

@section('content')
<div class="space-y-6" x-data="whatsappBroadcast()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-sm border border-sky-100">
        <div>
            <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                </span>
                Kirim Laporan WhatsApp
            </h1>
            <p class="text-sm text-slate-500 mt-1">Kirim rekapitulasi kehadiran (Hadir, Terlambat, Sakit, Izin, Alpa) ke Kepala Sekolah dan Guru/Wali Kelas.</p>
        </div>

        <div class="flex items-center gap-2">
            @if(empty($lembaga->wanesia_token))
                <div class="px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-xs font-medium flex items-center gap-1.5">
                    <span>⚠️ Token WhatsApp belum disetel</span>
                    <a href="{{ route('pengaturan-lembaga.index') }}" class="underline font-bold hover:text-amber-900">Atur Di Sini</a>
                </div>
            @else
                <div class="px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Gateway Wanesia Aktif
                </div>
            @endif
        </div>
    </div>

    <!-- Filter Kelas & Tanggal -->
    <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-sm border border-sky-100">
        <form method="GET" action="{{ route('whatsapp-broadcast.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label for="filter_tanggal" class="block text-xs font-semibold text-slate-700 mb-1.5">Pilih Tanggal Presensi</label>
                <input type="date" name="tanggal" id="filter_tanggal" value="{{ $tanggal }}" x-model="tanggal" @change="refreshPreview()"
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400">
            </div>

            <div>
                <label for="filter_kelas" class="block text-xs font-semibold text-slate-700 mb-1.5">Pilih Laporan Kelas</label>
                <select name="kelas_id" id="filter_kelas" x-model="kelasId" @change="refreshPreview()"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-sky-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-400">
                    <option value="all" {{ $kelasId == 'all' ? 'selected' : '' }}>📌 Semua Kelas (Rekapitulasi Global)</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>
                            Kelas {{ $k->tingkat }} {{ $k->nama_rombel }} {{ $k->waliKelas ? '— Wali: ' . $k->waliKelas->nama_lengkap : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit" class="w-full px-4 py-2.5 bg-sky-500 hover:bg-sky-600 text-white rounded-xl text-sm font-semibold transition-all shadow-sm">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Ringkasan Statistik Kartu -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
        <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm text-center">
            <span class="text-xs text-slate-500 font-medium">Total Siswa</span>
            <p class="text-xl font-bold text-slate-800 mt-1" x-text="stats.total">{{ $reportData['stats']['total'] }}</p>
        </div>
        <div class="bg-emerald-50/50 p-4 rounded-xl border border-emerald-100 shadow-sm text-center">
            <span class="text-xs text-emerald-600 font-medium">Hadir</span>
            <p class="text-xl font-bold text-emerald-700 mt-1" x-text="stats.hadir">{{ $reportData['stats']['hadir'] }}</p>
        </div>
        <div class="bg-amber-50/50 p-4 rounded-xl border border-amber-100 shadow-sm text-center">
            <span class="text-xs text-amber-600 font-medium">Terlambat</span>
            <p class="text-xl font-bold text-amber-700 mt-1" x-text="stats.terlambat">{{ $reportData['stats']['terlambat'] }}</p>
        </div>
        <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 shadow-sm text-center">
            <span class="text-xs text-blue-600 font-medium">Sakit</span>
            <p class="text-xl font-bold text-blue-700 mt-1" x-text="stats.sakit">{{ $reportData['stats']['sakit'] }}</p>
        </div>
        <div class="bg-purple-50/50 p-4 rounded-xl border border-purple-100 shadow-sm text-center">
            <span class="text-xs text-purple-600 font-medium">Izin</span>
            <p class="text-xl font-bold text-purple-700 mt-1" x-text="stats.izin">{{ $reportData['stats']['izin'] }}</p>
        </div>
        <div class="bg-rose-50/50 p-4 rounded-xl border border-rose-100 shadow-sm text-center">
            <span class="text-xs text-rose-600 font-medium">Alpa</span>
            <p class="text-xl font-bold text-rose-700 mt-1" x-text="stats.alpa">{{ $reportData['stats']['alpa'] }}</p>
        </div>
    </div>

    <!-- Form Kirim Pesan -->
    <form action="{{ route('whatsapp-broadcast.send') }}" method="POST" @submit="confirmSend($event)">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Kolom Kiri: Preview & Edit Pesan -->
            <div class="lg:col-span-7 space-y-4">
                <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-sm border border-sky-100">
                    <div class="flex items-center justify-between mb-3">
                        <label for="message_content" class="text-sm font-bold text-slate-800 flex items-center gap-2">
                            <span>💬</span> Preview &amp; Edit Pesan WhatsApp
                        </label>
                        <span class="text-xs text-slate-400">Pesan dapat disesuaikan manual</span>
                    </div>

                    <textarea name="message" id="message_content" rows="14" x-model="messageContent"
                              class="w-full p-4 font-mono text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-400/40 text-slate-700 transition-all leading-relaxed"
                              placeholder="Menyiapkan pesan laporan..."></textarea>

                    <div class="mt-4 space-y-2">
                        <label for="custom_note" class="block text-xs font-semibold text-slate-700">Catatan Tambahan (Opsional)</label>
                        <input type="text" id="custom_note" x-model="customNote" @input="refreshPreview()"
                               placeholder="Contoh: Bagi siswa yang sakit harap segera mengonfirmasi surat dokter."
                               class="w-full px-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-400/40">
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Pilih Penerima (Kepala Sekolah, Wali Kelas, Daftar Guru) -->
            <div class="lg:col-span-5 space-y-4">
                <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-sm border border-sky-100 flex flex-col h-full">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Target Penerima WhatsApp</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Pilih siapa saja yang akan menerima laporan ini.</p>
                        </div>
                    </div>

                    <!-- Shortcut Cepat: Kepala Sekolah & Wali Kelas -->
                    <div class="space-y-2.5 mb-4">
                        <!-- Kepala Sekolah -->
                        @php
                            $kepsekPhone = $lembaga->no_telp ?? ($lembaga->kepalaSekolah->no_hp ?? '');
                            $kepsekNama = $lembaga->nama_kepala ?? ($lembaga->kepalaSekolah->nama_lengkap ?? 'Kepala Sekolah');
                        @endphp
                        <label class="flex items-center justify-between p-3 rounded-xl border border-sky-200 bg-sky-50/60 hover:bg-sky-50 cursor-pointer transition-all">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" name="recipients[]" value="{{ $kepsekPhone }}"
                                       @checked(!empty($kepsekPhone))
                                       class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">👑 Kepala Sekolah: {{ $kepsekNama }}</span>
                                    <span class="text-[11px] text-slate-500 font-mono">{{ $kepsekPhone ?: '(Nomor WA belum diisi di Pengaturan Lembaga)' }}</span>
                                </div>
                            </div>
                            <span class="text-[10px] font-semibold bg-sky-100 text-sky-700 px-2 py-0.5 rounded-full">Kepsek</span>
                        </label>

                        <!-- Wali Kelas (jika kelas spesifik dipilih) -->
                        @if($reportData['targetKelas'] && $reportData['targetKelas']->waliKelas)
                            @php
                                $wk = $reportData['targetKelas']->waliKelas;
                            @endphp
                            <label class="flex items-center justify-between p-3 rounded-xl border border-emerald-200 bg-emerald-50/60 hover:bg-emerald-50 cursor-pointer transition-all">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="recipients[]" value="{{ $wk->no_hp }}"
                                           @checked(!empty($wk->no_hp))
                                           class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 block">🎓 Wali Kelas: {{ $wk->nama_lengkap }}</span>
                                        <span class="text-[11px] text-slate-500 font-mono">{{ $wk->no_hp ?: '(Belum ada nomor HP)' }}</span>
                                    </div>
                                </div>
                                <span class="text-[10px] font-semibold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">Wali Kelas</span>
                            </label>
                        @endif
                    </div>

                    <!-- Daftar Seluruh Guru dengan Checkbox Select All -->
                    <div class="flex-1 flex flex-col">
                        <div class="flex items-center justify-between py-2 border-y border-slate-100 mb-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" @change="toggleSelectAllGurus($event)"
                                       class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                <span class="text-xs font-bold text-slate-700">Centang Semua Guru ({{ count($gurus) }})</span>
                            </label>
                            <span class="text-[11px] text-slate-400">Pilih perorangan di bawah</span>
                        </div>

                        <!-- Daftar Guru Scrollable -->
                        <div class="space-y-1.5 max-h-60 overflow-y-auto pr-1">
                            @forelse($gurus as $g)
                                <label class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-50 border border-transparent hover:border-slate-200 cursor-pointer transition-all">
                                    <div class="flex items-center gap-2.5">
                                        <input type="checkbox" name="recipients[]" value="{{ $g->no_hp }}"
                                               class="guru-checkbox rounded text-emerald-600 focus:ring-emerald-500 w-3.5 h-3.5"
                                               {{ empty($g->no_hp) ? 'disabled' : '' }}>
                                        <div>
                                            <span class="text-xs font-medium text-slate-800 block leading-tight">{{ $g->nama_lengkap }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono">
                                                {{ $g->no_hp ?: '❌ No HP belum diisi' }}
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            @empty
                                <p class="text-xs text-slate-400 text-center py-4">Belum ada data guru terdaftar.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Nomor Manual / Kustom Tambahan -->
                    <div class="mt-4 pt-4 border-t border-slate-100 space-y-1.5">
                        <label for="manual_number" class="block text-xs font-semibold text-slate-700">Kirim ke Nomor Manual Tambahan</label>
                        <input type="text" name="recipients[]" id="manual_number" placeholder="Contoh: 081234567890 (Opsional)"
                               class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400/40">
                    </div>

                    <!-- Tombol Eksekusi Kirim -->
                    <div class="mt-5 pt-4 border-t border-slate-100">
                        <button type="submit" class="w-full py-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-xl text-sm font-bold flex items-center justify-center gap-2 shadow-sm transition-all">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            <span>Kirimkan Pesan WhatsApp Sekarang</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
function whatsappBroadcast() {
    return {
        tanggal: '{{ $tanggal }}',
        kelasId: '{{ $kelasId }}',
        customNote: '',
        messageContent: `{!! addslashes(app(\App\Http\Controllers\WhatsAppBroadcastController::class)->buildWhatsAppMessage($reportData)) !!}`,
        stats: {
            total: {{ $reportData['stats']['total'] }},
            hadir: {{ $reportData['stats']['hadir'] }},
            terlambat: {{ $reportData['stats']['terlambat'] }},
            sakit: {{ $reportData['stats']['sakit'] }},
            izin: {{ $reportData['stats']['izin'] }},
            alpa: {{ $reportData['stats']['alpa'] }}
        },
        toggleSelectAllGurus(e) {
            const isChecked = e.target.checked;
            document.querySelectorAll('.guru-checkbox:not(:disabled)').forEach(cb => {
                cb.checked = isChecked;
            });
        },
        refreshPreview() {
            fetch(`{{ route('whatsapp-broadcast.preview') }}?tanggal=${this.tanggal}&kelas_id=${this.kelasId}&custom_note=${encodeURIComponent(this.customNote)}`)
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        this.messageContent = res.message;
                        this.stats = res.stats;
                    }
                })
                .catch(e => console.error(e));
        },
        confirmSend(e) {
            const checkedCount = document.querySelectorAll('input[name="recipients[]"]:checked').length;
            const manualVal = document.getElementById('manual_number')?.value.trim();
            if (checkedCount === 0 && !manualVal) {
                alert('Silakan pilih minimal 1 penerima pesan WhatsApp!');
                e.preventDefault();
                return false;
            }
            if (!confirm('Apakah Anda yakin ingin mengirim laporan presensi ini ke seluruh penerima terpilih via WhatsApp?')) {
                e.preventDefault();
                return false;
            }
        }
    }
}
</script>
@endsection
