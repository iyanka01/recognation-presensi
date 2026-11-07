<?php

namespace App\Http\Controllers;

use App\Models\JadwalPelajaran;
use App\Models\JurnalMengajar;
use App\Models\PengaturanLembaga;
use App\Models\PresensiMapel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AbsensiMapelController extends Controller
{
    const HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    const STATUS = ['Hadir', 'Izin', 'Sakit', 'Alpa'];

    // Daftar jadwal mengajar guru untuk tanggal terpilih
    public function index(Request $request)
    {
        $guru = Auth::guard('guru')->user();
        $tanggal = $request->query('tanggal', Carbon::today()->toDateString());
        $hari = Carbon::parse($tanggal)->locale('id')->dayName;

        $jadwals = JadwalPelajaran::with(['kelas', 'mataPelajaran'])
            ->where('guru_id', $guru->id)
            ->where('hari', $hari)
            ->orderBy('jam_mulai')
            ->get();

        // Status pengisian absensi per jadwal pada tanggal terpilih
        $now = Carbon::now();
        $tanggalObj = Carbon::parse($tanggal);
        $isToday = ($tanggal === Carbon::today()->toDateString());

        foreach ($jadwals as $j) {
            $j->total_terisi = PresensiMapel::where('jadwal_id', $j->id)->whereDate('tanggal', $tanggal)->count();
            $j->total_siswa = $j->kelas ? $j->kelas->siswa()->count() : 0;

            // Cek apakah hari ini dan saat ini masih dalam rentang jam pelajaran
            $start = $tanggalObj->copy()->setTimeFromTimeString($j->jam_mulai);
            $end   = $tanggalObj->copy()->setTimeFromTimeString($j->jam_selesai);
            
            if ($isToday) {
                $j->can_absen = $now->between($start, $end);
                $j->is_past   = $now->gt($end);
                $j->is_future = $now->lt($start);
            } elseif ($tanggalObj->startOfDay()->gt(Carbon::today())) {
                $j->can_absen = false;
                $j->is_past   = false;
                $j->is_future = true;
            } else {
                $j->can_absen = false;
                $j->is_past   = true;
                $j->is_future = false;
            }
        }

        return view('absensi-mapel.index', [
            'jadwals' => $jadwals,
            'tanggal' => $tanggal,
            'hari' => $hari,
            'pengaturan' => PengaturanLembaga::first(),
        ]);
    }

    // Form daftar siswa + status kehadiran
    public function show(Request $request, $id)
    {
        $guru = Auth::guard('guru')->user();
        $jadwal = JadwalPelajaran::with(['kelas.siswa', 'mataPelajaran'])->findOrFail($id);

        $isOwner = ($jadwal->guru_id === $guru->id);
        $isWaliKelas = ($jadwal->kelas && $jadwal->kelas->wali_kelas_id === $guru->id);

        // Guru hanya boleh mengakses jadwal miliknya sendiri ATAU jika dia adalah wali kelas dari kelas tersebut
        if (!$isOwner && !$isWaliKelas) {
            abort(403, 'Anda tidak memiliki akses ke jadwal pelajaran ini.');
        }

        $tanggal = $request->query('tanggal', Carbon::today()->toDateString());
        $hariPilihan = Carbon::parse($tanggal)->locale('id')->dayName;

        // Cek apakah hari dan jam saat ini cocok dengan jam pelajaran
        $now = Carbon::now();
        $tanggalObj = Carbon::parse($tanggal);
        $start = $tanggalObj->copy()->setTimeFromTimeString($jadwal->jam_mulai);
        $end   = $tanggalObj->copy()->setTimeFromTimeString($jadwal->jam_selesai);

        $isToday = ($tanggal === Carbon::today()->toDateString());
        $isHariCocok = (strtolower($jadwal->hari) === strtolower($hariPilihan));
        $canAbsen = $isToday && $isHariCocok && $now->between($start, $end);

        // Proteksi Server-Side: Jika di luar jam pelajaran dan BUKAN wali kelas, tolak akses langsung
        if (!$canAbsen && !$isWaliKelas) {
            $jamRange = substr($jadwal->jam_mulai, 0, 5) . " - " . substr($jadwal->jam_selesai, 0, 5);
            if ($isToday && $now->lt($start)) {
                $pesan = "Akses Ditolak: Belum waktunya jam pelajaran {$jadwal->mataPelajaran?->nama_mapel} ({$jadwal->hari}, {$jamRange}). Absensi hanya dapat dilakukan saat jam pelajaran berlangsung.";
            } else {
                $pesan = "Akses Ditolak: Anda sudah melewati batas waktu absensi untuk {$jadwal->mataPelajaran?->nama_mapel} ({$jadwal->hari}, {$jamRange}). Absensi hanya dapat dilakukan saat jam pelajaran berlangsung.";
            }
            return redirect()->route('absensi-mapel.index', ['tanggal' => $tanggal])->with('error', $pesan);
        }

        // Check if attendance already recorded for this date
        $hasAbsen = PresensiMapel::where('jadwal_id', $jadwal->id)
            ->whereDate('tanggal', $tanggal)
            ->exists();

        // Data absensi yang sudah tersimpan (untuk mode edit)
        $existing = PresensiMapel::where('jadwal_id', $jadwal->id)
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('siswa_id');

        // Jurnal pembelajaran yang sudah pernah diisi (untuk mode edit)
        $jurnal = JurnalMengajar::where('jadwal_id', $jadwal->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        return view('absensi-mapel.form', [
            'jadwal' => $jadwal,
            'canAbsen' => $canAbsen,
            'isWaliKelas' => $isWaliKelas,
            'hasAbsen' => $hasAbsen,
            'siswaList' => $jadwal->kelas ? $jadwal->kelas->siswa()->orderBy('nama_lengkap')->get() : collect(),
            'existing' => $existing,
            'jurnal' => $jurnal,
            'tanggal' => $tanggal,
            'statusList' => self::STATUS,
            'pengaturan' => PengaturanLembaga::first(),
        ]);
    }

    // Simpan jurnal + absensi massal
    public function store(Request $request, $id)
    {
        $guru = Auth::guard('guru')->user();
        $jadwal = JadwalPelajaran::with('kelas')->findOrFail($id);

        $isOwner = ($jadwal->guru_id === $guru->id);
        $isWaliKelas = ($jadwal->kelas && $jadwal->kelas->wali_kelas_id === $guru->id);

        if (!$isOwner && !$isWaliKelas) {
            abort(403, 'Anda tidak memiliki akses ke jadwal pelajaran ini.');
        }

        // Validasi waktu pelajaran di sisi server
        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());
        $hariPilihan = Carbon::parse($tanggal)->locale('id')->dayName;

        $now = Carbon::now();
        $tanggalObj = Carbon::parse($tanggal);
        $start = $tanggalObj->copy()->setTimeFromTimeString($jadwal->jam_mulai);
        $end   = $tanggalObj->copy()->setTimeFromTimeString($jadwal->jam_selesai);

        $isToday = ($tanggal === Carbon::today()->toDateString());
        $isHariCocok = (strtolower($jadwal->hari) === strtolower($hariPilihan));
        $canAbsen = $isToday && $isHariCocok && $now->between($start, $end);

        // Jika di luar jam pelajaran dan bukan wali kelas, tolak simpan
        if (!$canAbsen && !$isWaliKelas) {
            return redirect()
                ->route('absensi-mapel.index', ['tanggal' => $tanggal])
                ->with('error', "Gagal Menyimpan: Sesi jam pelajaran {$jadwal->mataPelajaran?->nama_mapel} ({$jadwal->hari}, " . substr($jadwal->jam_mulai, 0, 5) . " - " . substr($jadwal->jam_selesai, 0, 5) . ") tidak aktif saat ini. Hanya wali kelas yang memiliki hak akses susulan.");
        }
        $validated = $request->validate([
            // Data pembelajaran (wajib diisi sebelum absen siswa)
            'jumlah_jam' => 'required|integer|min:1|max:15',
            'materi' => 'required|string|max:500',
            'materi_selanjutnya' => 'nullable|string|max:500',
            'keterangan_pembelajaran' => 'required|string|max:500',
            // Daftar kehadiran siswa
            'tanggal' => 'required|date',
            'absensi' => 'required|array|min:1',
            'absensi.*.siswa_id' => 'required|exists:siswa,id',
            'absensi.*.status' => ['required', Rule::in(self::STATUS)],
            'absensi.*.keterangan' => 'nullable|string|max:255',
        ]);

        // Simpan jurnal mengajar (satu per jadwal per tanggal)
        JurnalMengajar::updateOrCreate(
            [
                'jadwal_id' => $jadwal->id,
                'tanggal' => $validated['tanggal'],
            ],
            [
                'jumlah_jam' => $validated['jumlah_jam'],
                'materi' => $validated['materi'],
                'materi_selanjutnya' => $validated['materi_selanjutnya'] ?? null,
                'keterangan' => $validated['keterangan_pembelajaran'],
            ]
        );

        foreach ($validated['absensi'] as $row) {
            PresensiMapel::updateOrCreate(
                [
                    'jadwal_id' => $jadwal->id,
                    'siswa_id' => $row['siswa_id'],
                    'tanggal' => $validated['tanggal'],
                ],
                [
                    'status' => $row['status'],
                    'keterangan' => $row['keterangan'] ?? null,
                ]
            );
        }

        return redirect()
            ->route('absensi-mapel.index', ['tanggal' => $validated['tanggal']])
            ->with('success', 'Absensi mata pelajaran berhasil disimpan!');
    }
}
