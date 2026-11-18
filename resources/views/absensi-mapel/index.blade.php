@extends('layouts.guru')

@section('title', 'Absensi Mata Pelajaran')
@section('back_url', route('dashboard'))
@section('body_class', 'bg-white min-h-screen')
@section('body_style', 'background: #ffffff !important;')
@section('title_class', 'text-slate-900')
@section('back_btn_class', 'bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 shadow-sm')

@section('content')
<div class="space-y-4 pb-6">

    @php
        $activeJadwal = $jadwals->firstWhere('can_absen', true) ?: $jadwals->first();
        $userGuru = Auth::guard('guru')->user();
        $tglFormat = \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMMM Y');
    @endphp

    {{-- ======== KARTU HERO EMERALD ELEGAN (SAMA PERSIS DENGAN KARTU TERMINAL GURU) ======== --}}
    @if($activeJadwal)
    <div class="bg-gradient-to-br from-emerald-600 via-teal-700 to-emerald-900 rounded-[28px] p-5 text-white shadow-xl shadow-emerald-950/20 relative overflow-hidden border border-emerald-500/30">
        <!-- Background Ornaments -->
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-emerald-400/20 rounded-full blur-xl pointer-events-none"></div>

        <div class="relative z-10">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white backdrop-blur-sm mb-1.5 uppercase tracking-wider">
                        Jadwal Hari {{ $hari }}
                    </span>
                    <h2 class="text-2xl font-black text-white truncate leading-tight tracking-tight">
                        {{ $activeJadwal->mataPelajaran?->nama_mapel ?? 'Mata Pelajaran' }}
                    </h2>
                    <p class="text-xs text-emerald-100/95 font-semibold mt-1">
                        {{ $activeJadwal->hari }} &bull; {{ substr($activeJadwal->jam_mulai,0,5) }} &ndash; {{ substr($activeJadwal->jam_selesai,0,5) }} WIB
                    </p>
                </div>

                {{-- Status Chip --}}
                <div class="shrink-0">
                    @if($activeJadwal->can_absen)
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-300 text-amber-950 shadow-sm animate-pulse">
                            Sekarang
                        </span>
                    @elseif($activeJadwal->total_terisi > 0)
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-white/20 text-white border border-white/30">
                            Selesai
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-white/20 text-white border border-white/30 backdrop-blur-sm">
                            {{ $jadwals->count() }} Mapel
                        </span>
                    @endif
                </div>
            </div>

            {{-- Footer Guru Profile Info di dalam Hero Card --}}
            <div class="mt-4 pt-3.5 border-t border-white/20 flex items-center justify-between">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-2xl bg-white/15 backdrop-blur-md border border-white/25 flex items-center justify-center shrink-0 overflow-hidden text-white font-black text-sm shadow-md">
                        @if($userGuru && $userGuru->foto_guru)
                            <img src="{{ Storage::url($userGuru->foto_guru) }}" class="w-full h-full object-cover" alt="Guru">
                        @else
                            {{ $userGuru ? strtoupper(substr($userGuru->nama_lengkap, 0, 1)) : 'G' }}
                        @endif
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] uppercase font-bold text-emerald-200 tracking-wider block leading-none">Guru Pengajar</span>
                        <p class="text-xs font-black text-white truncate mt-0.5">{{ $userGuru->nama_lengkap ?? 'Guru Pengajar' }}</p>
                        <p class="text-[11px] text-emerald-100/90 font-medium truncate mt-0.5">
                            Kelas {{ $activeJadwal->kelas?->tingkat }} {{ $activeJadwal->kelas?->nama_rombel }} &bull; {{ $activeJadwal->total_siswa }} Siswa
                        </p>
                    </div>
                </div>

                @if($activeJadwal->can_absen)
                    <a href="{{ route('absensi-mapel.show', ['id' => $activeJadwal->id, 'tanggal' => $tanggal]) }}" 
                       class="px-3.5 py-2 rounded-xl bg-white hover:bg-emerald-50 text-emerald-800 font-black text-xs shadow-md transition-all active:scale-95 shrink-0 flex items-center gap-1.5">
                        <span>Input Absen</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ======== TIMELINE JADWAL KELAS DENGAN TEKS SOLID GELAP & POSISI PRESISI ======== --}}
    <div class="mt-6">
        <div class="flex items-center justify-between mb-4 px-1">
            <h3 class="text-sm font-black text-slate-900 tracking-tight">
                {{ $tglFormat }}
            </h3>
            <span class="text-xs font-black text-slate-500">
                {{ $jadwals->count() }} Sesi Pelajaran
            </span>
        </div>

        {{-- Timeline Container: 
             - Jam berada di sisi kiri (lebar 44px)
             - Garis vertikal di posisi left-[54px]
             - Titik bullet di posisi left-[48px] (pas di garis, TIDAK menabrak teks jam)
             - Card putih berada di sebelah kanan (pl-16)
        --}}
        <div class="relative pl-16 space-y-4">
            
            {{-- Garis Timeline Vertikal --}}
            <div class="absolute left-[54px] top-4 bottom-4 w-0.5 bg-slate-300"></div>

            @forelse($jadwals as $index => $j)
                <div class="relative group">
                    
                    {{-- Timestamp Jam (Murni teks hitam/slate-800 di sebelah kiri garis) --}}
                    <div class="absolute -left-16 top-3 w-11 text-right pr-1">
                        <span class="text-xs font-black text-slate-900 block leading-tight">
                            {{ substr($j->jam_mulai, 0, 5) }}
                        </span>
                        <span class="text-[10px] font-bold text-slate-500 block">
                            {{ substr($j->jam_selesai, 0, 5) }}
                        </span>
                    </div>

                    {{-- Lingkaran Bullet Timeline Tepat di Garis Vertikal --}}
                    <div class="absolute -left-[14px] top-3.5 w-3.5 h-3.5 rounded-full border-2 {{ $j->can_absen ? 'bg-teal-600 border-white ring-4 ring-teal-200' : ($j->total_terisi > 0 ? 'bg-emerald-600 border-white ring-2 ring-emerald-200' : 'bg-white border-slate-400') }} transition-all"></div>

                    {{-- Card Jadwal Putih Bersih --}}
                    <div class="bg-white rounded-2xl p-4 border {{ $j->can_absen ? 'border-teal-500 ring-2 ring-teal-500/20 shadow-md shadow-teal-500/10' : 'border-slate-200/90 shadow-sm' }} transition-all duration-200 hover:shadow-md">
                        
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="text-sm font-black text-slate-900 truncate">
                                        {{ $j->mataPelajaran?->nama_mapel ?? 'Mapel Terhapus' }}
                                    </h4>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px] font-mono text-slate-700 font-bold">
                                        {{ $j->mataPelajaran?->kode_mapel }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 font-bold mt-1">
                                    Kelas {{ $j->kelas?->tingkat }} {{ $j->kelas?->nama_rombel }} &bull; {{ $j->total_siswa }} Siswa
                                </p>
                            </div>

                            {{-- Badge Status Terisi / Belum Diisi --}}
                            <div class="shrink-0 text-right">
                                @if($j->total_terisi > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <svg class="w-3 h-3 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span>Terisi ({{ $j->total_terisi }}/{{ $j->total_siswa }})</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-50 text-amber-800 border border-amber-200">
                                        Belum diisi
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Profil Kelas & Tombol Aksi Input/Edit (Lock Sesuai Jam Pelajaran) --}}
                        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500">
                                ⏰ {{ substr($j->jam_mulai,0,5) }} &ndash; {{ substr($j->jam_selesai,0,5) }} WIB
                            </span>

                            <div>
                                @if($j->can_absen)
                                    <a href="{{ route('absensi-mapel.show', ['id' => $j->id, 'tanggal' => $tanggal]) }}"
                                       class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-black text-xs shadow-sm transition-all active:scale-95">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span>{{ $j->total_terisi > 0 ? 'Edit' : 'Input' }}</span>
                                    </a>
                                @else
                                    <button type="button"
                                            onclick="document.getElementById('popup-expired-{{ $j->id }}').classList.remove('hidden')"
                                            class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition-all active:scale-95">
                                        <span>{{ $j->total_terisi > 0 ? 'Lihat/Edit' : 'Input' }}</span>
                                    </button>

                                    {{-- Modal Popup Keterangan Jam Tidak Aktif / Belum Absen Masuk --}}
                                    <div id="popup-expired-{{ $j->id }}" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
                                        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="this.parentElement.classList.add('hidden')"></div>
                                        <div class="relative bg-white rounded-3xl shadow-2xl max-w-xs w-full p-5 text-center z-10 mx-auto animate-fade-in">
                                            
                                            <button type="button" onclick="this.closest('#popup-expired-{{ $j->id }}').classList.add('hidden')"
                                                    class="absolute top-3.5 right-3.5 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>

                                            <div class="w-12 h-12 mx-auto mb-2 rounded-full {{ ($j->belum_absen_masuk ?? false) ? 'bg-rose-100 text-rose-600' : ($j->is_future ? 'bg-sky-100 text-sky-600' : 'bg-amber-100 text-amber-600') }} flex items-center justify-center">
                                                @if($j->belum_absen_masuk ?? false)
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                @elseif($j->is_future)
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                @else
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                @endif
                                            </div>

                                            <h3 class="text-sm font-extrabold text-slate-800 mb-1">
                                                {{ ($j->belum_absen_masuk ?? false) ? 'Wajib Absen Masuk Dulu' : ($j->is_future ? 'Belum Waktunya Absensi' : 'Jam Pelajaran Selesai') }}
                                            </h3>
                                            <p class="text-xs text-slate-500 mb-2 leading-relaxed">
                                                Jam pelajaran <strong>{{ $j->mataPelajaran?->nama_mapel }}</strong> adalah <strong>{{ substr($j->jam_mulai,0,5) }} &ndash; {{ substr($j->jam_selesai,0,5) }}</strong>.
                                            </p>

                                            <div class="text-[11px] {{ ($j->belum_absen_masuk ?? false) ? 'text-rose-700 bg-rose-50 border-rose-100' : 'text-slate-600 bg-slate-50 border-slate-100' }} border rounded-xl p-2.5 mb-3 leading-relaxed">
                                                @if($j->belum_absen_masuk ?? false)
                                                    <span>Anda belum melakukan <strong>Absensi Masuk</strong> hari ini. Silakan lakukan absen masuk terlebih dahulu.</span>
                                                @elseif($j->is_future)
                                                    <span>Belum waktunya jam pelajaran. Absensi hanya dapat dilakukan saat jam pelajaran berlangsung.</span>
                                                @else
                                                    <span>Batas waktu absensi telah lewat.</span>
                                                @endif
                                            </div>

                                            @if($j->belum_absen_masuk ?? false)
                                            <a href="{{ route('dashboard') }}"
                                               class="w-full mb-2 inline-flex items-center justify-center gap-1.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold rounded-xl transition-all shadow-md">
                                                <span>Absen Masuk di Dashboard</span>
                                            </a>
                                            @endif

                                            <button type="button"
                                                    onclick="this.closest('#popup-expired-{{ $j->id }}').classList.add('hidden')"
                                                    class="w-full py-2 bg-slate-800 hover:bg-slate-900 active:scale-95 text-white text-xs font-bold rounded-xl transition-all shadow-sm">
                                                Tutup
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl border border-dashed border-slate-200 p-8 text-center ml-0 -pl-16">
                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h4 class="font-extrabold text-slate-700 text-sm">Tidak Ada Jadwal Mengajar Hari Ini</h4>
                    <p class="text-xs text-slate-400 mt-1">Anda tidak memiliki jadwal mengajar pada hari {{ $hari }}.</p>
                </div>
            @endforelse

        </div>
    </div>

</div>
@endsection
