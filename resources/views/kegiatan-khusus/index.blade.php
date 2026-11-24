@extends('layouts.app')

@section('title', 'Absen Kegiatan Khusus')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Absen Kegiatan Khusus</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola agenda kegiatan sekolah, jadwal absensi masuk/pulang, dan scanner QR kegiatan.</p>
        </div>

        <div class="flex items-center gap-2.5 w-full sm:w-auto">
            <button onclick="openModal('modal-tambah')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-[7px] flex items-center justify-center gap-2 transition-all shadow-sm w-full sm:w-auto cursor-pointer">
                <i class="ti ti-plus text-base"></i>
                <span>Tambah Kegiatan</span>
            </button>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white dark:bg-slate-800 p-4 rounded-[7px] border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('kegiatan-khusus.index') }}" class="flex flex-wrap sm:flex-nowrap items-center gap-2.5 w-full">
            <div class="relative flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode kegiatan..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <i class="ti ti-search text-slate-400 absolute left-3 top-2.5 text-sm"></i>
            </div>

            <select name="status" class="px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-700 dark:text-slate-200 focus:outline-none">
                <option value="">Semua Status</option>
                <option value="Aktif" {{ request('status') === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="Selesai" {{ request('status') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-[7px] transition-colors flex items-center gap-1.5 cursor-pointer">
                <i class="ti ti-filter text-sm"></i>
                <span>Filter</span>
            </button>

            @if(request('search') || request('status'))
                <a href="{{ route('kegiatan-khusus.index') }}" class="text-xs text-rose-600 dark:text-rose-400 hover:underline px-2 py-1 font-medium">Reset</a>
            @endif
        </form>
    </div>

    <!-- Data Table Card (Persis Model Referensi Screenshot) -->
    <div class="bg-white dark:bg-slate-800 rounded-[7px] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Kode</th>
                        <th class="py-3.5 px-4">Nama Kegiatan</th>
                        <th class="py-3.5 px-4">Hari / Tanggal</th>
                        <th class="py-3.5 px-4">Jam Mulai</th>
                        <th class="py-3.5 px-4">Jam Selesai</th>
                        <th class="py-3.5 px-4">Mode Absen</th>
                        <th class="py-3.5 px-4">Peserta Hadir</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                    @forelse($kegiatans as $index => $k)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40 transition-colors">
                        <!-- No -->
                        <td class="py-3.5 px-4 text-center font-medium text-slate-500">
                            {{ $kegiatans->firstItem() + $index }}
                        </td>

                        <!-- Kode -->
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-800 dark:text-slate-100">
                            <span class="inline-flex px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700 text-[11px]">
                                {{ $k->kode_kegiatan }}
                            </span>
                        </td>

                        <!-- Nama Kegiatan -->
                        <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-slate-100">
                            <div class="flex flex-col">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-xs font-bold">{{ $k->nama_kegiatan }}</span>
                                    @if($k->tipe_kegiatan === 'rutin')
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200/80">
                                            Rutin
                                        </span>
                                    @endif
                                </div>
                                <span class="text-[10px] text-slate-400 font-normal mt-0.5">
                                    Sasaran: {{ $k->sasaran_peserta }}
                                    @if($k->keterangan) &bull; {{ \Illuminate\Support\Str::limit($k->keterangan, 35) }} @endif
                                </span>
                            </div>
                        </td>

                        <!-- Tanggal / Hari -->
                        <td class="py-3.5 px-4 font-medium text-slate-700 dark:text-slate-300 whitespace-nowrap">
                            @if($k->tipe_kegiatan === 'rutin')
                                <div class="flex items-center gap-1.5 text-xs text-blue-700 dark:text-blue-300 font-semibold">
                                    <i class="ti ti-repeat text-sm"></i>
                                    <span>{{ $k->hari_label }}</span>
                                </div>
                            @else
                                {{ $k->tanggal ? $k->tanggal->isoFormat('D MMM Y') : '-' }}
                            @endif
                        </td>

                        <!-- Jam Mulai -->
                        <td class="py-3.5 px-4 font-mono font-semibold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                            {{ substr($k->jam_mulai, 0, 5) }}
                        </td>

                        <!-- Jam Selesai -->
                        <td class="py-3.5 px-4 font-mono font-semibold text-rose-600 dark:text-rose-400 whitespace-nowrap">
                            {{ substr($k->jam_selesai, 0, 5) }}
                        </td>

                        <!-- Mode Absen -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="inline-flex px-2.5 py-1 rounded-[5px] text-[11px] font-semibold {{ $k->mode_absen === 'Datang Saja' ? 'bg-sky-50 text-sky-700 border border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800' : 'bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800' }}">
                                {{ $k->mode_absen }}
                            </span>
                        </td>

                        <!-- Peserta Hadir -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 font-bold text-slate-800 dark:text-slate-200">
                                <i class="ti ti-users text-blue-600 text-sm"></i>
                                {{ $k->presensi_count }} Hadir
                            </span>
                        </td>

                        <!-- Aksi (Sesuai Tombol Referensi Screenshot: Edit, +User, Absen, Hapus) -->
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center justify-center gap-1.5">
                                <!-- Tombol Absen / Scan QR Kamera (Kuning Cerah) -->
                                <a href="{{ route('kegiatan-khusus.scan', $k->id) }}" target="_blank"
                                   title="Buka Scanner QR Kegiatan"
                                   class="px-2.5 py-1 rounded-[5px] bg-amber-500 hover:bg-amber-600 text-white font-bold text-[11px] flex items-center gap-1 shadow-sm transition-all">
                                    <i class="ti ti-qrcode text-xs"></i>
                                    <span>Absen</span>
                                </a>

                                <!-- Tombol + User / Peserta (Hijau) -->
                                <a href="{{ route('kegiatan-khusus.peserta', $k->id) }}"
                                   title="Lihat Daftar Peserta & Kehadiran"
                                   class="px-2.5 py-1 rounded-[5px] bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-[11px] flex items-center gap-1 shadow-sm transition-all">
                                    <i class="ti ti-user-plus text-xs"></i>
                                    <span>+ User</span>
                                </a>

                                <!-- Tombol Edit (Cyan/Biru Muda) -->
                                <button type="button"
                                        onclick='openEditModal(@json($k))'
                                        title="Edit Kegiatan"
                                        class="px-2.5 py-1 rounded-[5px] bg-sky-500 hover:bg-sky-600 text-white font-semibold text-[11px] flex items-center gap-1 shadow-sm transition-all cursor-pointer">
                                    <i class="ti ti-edit text-xs"></i>
                                    <span>Edit</span>
                                </button>

                                <!-- Tombol Hapus (Merah) -->
                                <form action="{{ route('kegiatan-khusus.destroy', $k->id) }}" method="POST" onsubmit="return confirm('Hapus kegiatan ini beserta seluruh data absensinya?');" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Hapus Kegiatan" class="w-7 h-7 rounded-[5px] text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 flex items-center justify-center transition-colors cursor-pointer">
                                        <i class="ti ti-trash text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center text-slate-400 dark:text-slate-500">
                            <i class="ti ti-calendar-off text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                            <p class="text-xs font-semibold">Belum ada agenda kegiatan khusus.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kegiatans->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-700">
                {{ $kegiatans->links() }}
            </div>
        @endif
    </div>

    @push('modals')
    <!-- Modal Tambah Kegiatan -->
    <div id="modal-tambah" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal('modal-tambah')"></div>
            <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

            <div class="inline-block transform overflow-hidden rounded-[7px] bg-white dark:bg-slate-800 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle w-full relative z-10 border border-slate-200 dark:border-slate-700">
                <div class="bg-white dark:bg-slate-800 px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="ti ti-calendar-plus text-blue-600 text-lg"></i>
                        Tambah Kegiatan Khusus
                    </h3>
                    <button type="button" onclick="closeModal('modal-tambah')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i class="ti ti-x text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('kegiatan-khusus.store') }}" method="POST">
                    @csrf
                    <div class="px-6 py-5 space-y-3.5">
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Kegiatan</label>
                                <input type="text" name="kode_kegiatan" placeholder="Otomatis" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Kegiatan <span class="text-rose-500">*</span></label>
                                <input type="text" name="nama_kegiatan" required placeholder="Contoh: Diklat, Upacara, dll" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tipe Kegiatan <span class="text-rose-500">*</span></label>
                                <select name="tipe_kegiatan" id="tambah_tipe" onchange="toggleTipeKegiatan('tambah')" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-semibold text-slate-800 dark:text-slate-200 focus:outline-none">
                                    <option value="sekali">Sekali Saja (Insidental)</option>
                                    <option value="rutin">Rutin (Pilih Hari)</option>
                                </select>
                            </div>
                            <div id="tambah_tanggal_wrapper">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal <span class="text-rose-500">*</span></label>
                                <input type="date" id="tambah_tanggal_input" name="tanggal" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jam Mulai <span class="text-rose-500">*</span></label>
                                <input type="time" name="jam_mulai" value="07:00" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jam Selesai <span class="text-rose-500">*</span></label>
                                <input type="time" name="jam_selesai" value="12:00" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none">
                            </div>
                        </div>

                        <!-- Pilihan Hari Rutin (Checkboxes) -->
                        <div id="tambah_hari_wrapper" class="hidden p-3 rounded-[7px] bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200/80 dark:border-blue-800/40">
                            <label class="block text-xs font-bold text-blue-900 dark:text-blue-300 mb-2">
                                <i class="ti ti-calendar-repeat text-blue-600 mr-1"></i>
                                Pilih Hari Pelaksanaan Rutin:
                            </label>
                            <div class="grid grid-cols-4 sm:grid-cols-7 gap-2">
                                @php($listHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])
                                @foreach($listHari as $hari)
                                    <label class="flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 font-medium cursor-pointer">
                                        <input type="checkbox" name="hari[]" value="{{ $hari }}" class="tambah-hari-cb rounded text-blue-600 focus:ring-blue-500">
                                        <span>{{ $hari }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mode Absen <span class="text-rose-500">*</span></label>
                                <select name="mode_absen" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none">
                                    <option value="Datang Saja">Datang Saja</option>
                                    <option value="Datang & Pulang">Datang &amp; Pulang</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Sasaran Peserta <span class="text-rose-500">*</span></label>
                                <select name="sasaran_peserta" id="tambah_sasaran" onchange="toggleKelasSelect('tambah')" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none">
                                    <option value="Semua Siswa">Semua Siswa</option>
                                    <option value="Pilih Kelas">Pilih Rombel Kelas Tertentu</option>
                                    <option value="Khusus Guru">Khusus Guru / Tendik</option>
                                    <option value="Semua">Semua (Siswa &amp; Guru)</option>
                                </select>
                            </div>
                        </div>

                        <div id="tambah_kelas_wrapper" class="hidden">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Kelas</label>
                            <div class="grid grid-cols-2 gap-2 max-h-32 overflow-y-auto p-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px]">
                                @foreach($kelasList as $kelas)
                                    <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                        <input type="checkbox" name="kelas_ids[]" value="{{ $kelas->id }}" class="rounded text-blue-600">
                                        <span>Kelas {{ $kelas->tingkat }} {{ $kelas->nama_rombel }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Keterangan / Catatan</label>
                            <textarea name="keterangan" rows="2" placeholder="Catatan tambahan lokasi, dress code, dll..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none"></textarea>
                        </div>

                        <!-- Opsi Kirim Auto Notifikasi (WhatsApp & Telegram) -->
                        <div class="p-3 rounded-[7px] bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200/80 dark:border-emerald-800/40">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="kirim_notifikasi" value="1" checked class="w-4 h-4 mt-0.5 rounded text-emerald-600 focus:ring-emerald-500">
                                <div class="text-xs">
                                    <span class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-1.5">
                                        <i class="ti ti-bell-ringing text-emerald-600 text-sm"></i>
                                        Kirim Auto-Notifikasi ke Orang Tua / Wali
                                    </span>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Kirim laporan kehadiran otomatis via WhatsApp (Wanesia) dan Telegram saat peserta scan kartu QR.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-3.5 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-2.5">
                        <button type="button" onclick="closeModal('modal-tambah')" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-[7px] transition-colors cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-[7px] transition-all shadow-sm cursor-pointer">Simpan Kegiatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Kegiatan -->
    <div id="modal-edit" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal('modal-edit')"></div>
            <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

            <div class="inline-block transform overflow-hidden rounded-[7px] bg-white dark:bg-slate-800 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle w-full relative z-10 border border-slate-200 dark:border-slate-700">
                <div class="bg-white dark:bg-slate-800 px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="ti ti-edit text-blue-600 text-lg"></i>
                        Edit Kegiatan Khusus
                    </h3>
                    <button type="button" onclick="closeModal('modal-edit')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i class="ti ti-x text-lg"></i>
                    </button>
                </div>

                <form id="form-edit" method="POST">
                    @csrf @method('PUT')
                    <div class="px-6 py-5 space-y-3.5">
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Kegiatan</label>
                                <input type="text" id="edit_kode" name="kode_kegiatan" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Kegiatan <span class="text-rose-500">*</span></label>
                                <input type="text" id="edit_nama" name="nama_kegiatan" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tipe Kegiatan <span class="text-rose-500">*</span></label>
                                <select name="tipe_kegiatan" id="edit_tipe" onchange="toggleTipeKegiatan('edit')" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-semibold text-slate-800 dark:text-slate-200 focus:outline-none">
                                    <option value="sekali">Sekali Saja (Insidental)</option>
                                    <option value="rutin">Rutin (Pilih Hari)</option>
                                </select>
                            </div>
                            <div id="edit_tanggal_wrapper">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal</label>
                                <input type="date" id="edit_tanggal" name="tanggal" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jam Mulai</label>
                                <input type="time" id="edit_mulai" name="jam_mulai" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jam Selesai</label>
                                <input type="time" id="edit_selesai" name="jam_selesai" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none">
                            </div>
                        </div>

                        <!-- Pilihan Hari Rutin (Checkboxes Edit) -->
                        <div id="edit_hari_wrapper" class="hidden p-3 rounded-[7px] bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200/80 dark:border-blue-800/40">
                            <label class="block text-xs font-bold text-blue-900 dark:text-blue-300 mb-2">
                                <i class="ti ti-calendar-repeat text-blue-600 mr-1"></i>
                                Pilih Hari Pelaksanaan Rutin:
                            </label>
                            <div class="grid grid-cols-4 sm:grid-cols-7 gap-2">
                                @foreach($listHari as $hari)
                                    <label class="flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 font-medium cursor-pointer">
                                        <input type="checkbox" name="hari[]" value="{{ $hari }}" class="edit-hari-cb rounded text-blue-600 focus:ring-blue-500">
                                        <span>{{ $hari }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mode Absen</label>
                                <select id="edit_mode" name="mode_absen" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none">
                                    <option value="Datang Saja">Datang Saja</option>
                                    <option value="Datang & Pulang">Datang &amp; Pulang</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Sasaran Peserta</label>
                                <select id="edit_sasaran" name="sasaran_peserta" onchange="toggleKelasSelect('edit')" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none">
                                    <option value="Semua Siswa">Semua Siswa</option>
                                    <option value="Pilih Kelas">Pilih Rombel Kelas Tertentu</option>
                                    <option value="Khusus Guru">Khusus Guru / Tendik</option>
                                    <option value="Semua">Semua (Siswa &amp; Guru)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Kegiatan</label>
                                <select id="edit_status" name="status" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none">
                                    <option value="Aktif">Aktif</option>
                                    <option value="Selesai">Selesai</option>
                                </select>
                            </div>
                        </div>

                        <div id="edit_kelas_wrapper" class="hidden">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Kelas</label>
                            <div class="grid grid-cols-2 gap-2 max-h-32 overflow-y-auto p-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px]">
                                @foreach($kelasList as $kelas)
                                    <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                        <input type="checkbox" name="kelas_ids[]" value="{{ $kelas->id }}" class="edit-kelas-cb rounded text-blue-600">
                                        <span>Kelas {{ $kelas->tingkat }} {{ $kelas->nama_rombel }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Keterangan / Catatan</label>
                            <textarea id="edit_keterangan" name="keterangan" rows="2" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-[7px] text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none"></textarea>
                        </div>

                        <!-- Opsi Kirim Auto Notifikasi (WhatsApp & Telegram) -->
                        <div class="p-3 rounded-[7px] bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200/80 dark:border-emerald-800/40">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" id="edit_notifikasi" name="kirim_notifikasi" value="1" class="w-4 h-4 mt-0.5 rounded text-emerald-600 focus:ring-emerald-500">
                                <div class="text-xs">
                                    <span class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-1.5">
                                        <i class="ti ti-bell-ringing text-emerald-600 text-sm"></i>
                                        Kirim Auto-Notifikasi ke Orang Tua / Wali
                                    </span>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Kirim laporan kehadiran otomatis via WhatsApp (Wanesia) dan Telegram saat peserta scan kartu QR.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-3.5 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-2.5">
                        <button type="button" onclick="closeModal('modal-edit')" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-[7px] transition-colors cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-[7px] transition-all shadow-sm cursor-pointer">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endpush

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        function toggleTipeKegiatan(prefix) {
            const tipe = document.getElementById(prefix + '_tipe').value;
            const hariWrap = document.getElementById(prefix + '_hari_wrapper');
            const tglWrap = document.getElementById(prefix + '_tanggal_wrapper');
            const tglInput = prefix === 'tambah' 
                ? document.getElementById('tambah_tanggal_input') 
                : document.getElementById('edit_tanggal');

            if (tipe === 'rutin') {
                hariWrap.classList.remove('hidden');
                tglWrap.classList.add('hidden');
                if (tglInput) tglInput.required = false;
            } else {
                hariWrap.classList.add('hidden');
                tglWrap.classList.remove('hidden');
                if (tglInput) tglInput.required = true;
            }
        }

        function toggleKelasSelect(prefix) {
            const val = document.getElementById(prefix + '_sasaran').value;
            const wrap = document.getElementById(prefix + '_kelas_wrapper');
            if (val === 'Pilih Kelas') {
                wrap.classList.remove('hidden');
            } else {
                wrap.classList.add('hidden');
            }
        }

        function openEditModal(data) {
            const form = document.getElementById('form-edit');
            form.action = '{{ url('kegiatan-khusus') }}/' + data.id;

            document.getElementById('edit_kode').value = data.kode_kegiatan || '';
            document.getElementById('edit_nama').value = data.nama_kegiatan || '';
            document.getElementById('edit_tipe').value = data.tipe_kegiatan || 'sekali';
            document.getElementById('edit_tanggal').value = data.tanggal ? data.tanggal.substring(0, 10) : '';
            document.getElementById('edit_mulai').value = data.jam_mulai ? data.jam_mulai.substring(0, 5) : '';
            document.getElementById('edit_selesai').value = data.jam_selesai ? data.jam_selesai.substring(0, 5) : '';
            document.getElementById('edit_mode').value = data.mode_absen || 'Datang Saja';
            document.getElementById('edit_sasaran').value = data.sasaran_peserta || 'Semua Siswa';
            document.getElementById('edit_status').value = data.status || 'Aktif';
            document.getElementById('edit_keterangan').value = data.keterangan || '';
            document.getElementById('edit_notifikasi').checked = Boolean(data.kirim_notifikasi !== false && data.kirim_notifikasi !== 0);

            toggleTipeKegiatan('edit');
            toggleKelasSelect('edit');

            // Reset dan set checked hari
            const selectedHari = data.hari || [];
            document.querySelectorAll('.edit-hari-cb').forEach(cb => {
                cb.checked = selectedHari.includes(cb.value);
            });

            // Reset dan set checked kelas
            const selectedClasses = data.kelas_ids || [];
            document.querySelectorAll('.edit-kelas-cb').forEach(cb => {
                cb.checked = selectedClasses.includes(cb.value);
            });

            openModal('modal-edit');
        }
    </script>
</div>
@endsection
