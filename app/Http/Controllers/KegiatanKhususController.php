<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\KegiatanKhusus;
use App\Models\Kelas;
use App\Models\PengaturanLembaga;
use App\Models\PresensiKegiatanKhusus;
use App\Models\Siswa;
use App\Services\TelegramService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KegiatanKhususController extends Controller
{
    /**
     * Tampilan Daftar Agenda Kegiatan Khusus
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $kegiatans = KegiatanKhusus::withCount('presensi')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama_kegiatan', 'like', "%{$search}%")
                        ->orWhere('kode_kegiatan', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_mulai', 'asc')
            ->paginate(10)
            ->withQueryString();

        $kelasList = Kelas::orderBy('tingkat')->orderBy('nama_rombel')->get();

        return view('kegiatan-khusus.index', compact('kegiatans', 'kelasList', 'search', 'status'));
    }

    /**
     * Simpan Kegiatan Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'mode_absen' => 'required|string',
            'sasaran_peserta' => 'required|string',
        ]);

        $kode = $request->kode_kegiatan;
        if (!$kode) {
            $count = KegiatanKhusus::count() + 1;
            $kode = sprintf('KGT-%03d', $count);
        }

        KegiatanKhusus::create([
            'kode_kegiatan' => $kode,
            'nama_kegiatan' => $request->nama_kegiatan,
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'mode_absen' => $request->mode_absen ?? 'Datang Saja',
            'sasaran_peserta' => $request->sasaran_peserta ?? 'Semua Siswa',
            'kelas_ids' => $request->sasaran_peserta === 'Pilih Kelas' ? $request->kelas_ids : null,
            'keterangan' => $request->keterangan,
            'status' => 'Aktif',
            'kirim_notifikasi' => $request->boolean('kirim_notifikasi'),
        ]);

        return redirect()->route('kegiatan-khusus.index')->with('success', 'Kegiatan khusus berhasil ditambahkan!');
    }

    /**
     * Update Kegiatan
     */
    public function update(Request $request, $id)
    {
        $kegiatan = KegiatanKhusus::findOrFail($id);

        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'mode_absen' => 'required|string',
            'sasaran_peserta' => 'required|string',
        ]);

        $kegiatan->update([
            'kode_kegiatan' => $request->kode_kegiatan ?: $kegiatan->kode_kegiatan,
            'nama_kegiatan' => $request->nama_kegiatan,
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'mode_absen' => $request->mode_absen,
            'sasaran_peserta' => $request->sasaran_peserta,
            'kelas_ids' => $request->sasaran_peserta === 'Pilih Kelas' ? $request->kelas_ids : null,
            'keterangan' => $request->keterangan,
            'status' => $request->status ?? $kegiatan->status,
            'kirim_notifikasi' => $request->boolean('kirim_notifikasi'),
        ]);

        return redirect()->route('kegiatan-khusus.index')->with('success', 'Data kegiatan berhasil diperbarui!');
    }

    /**
     * Hapus Kegiatan
     */
    public function destroy($id)
    {
        $kegiatan = KegiatanKhusus::findOrFail($id);
        $kegiatan->delete();

        return redirect()->route('kegiatan-khusus.index')->with('success', 'Kegiatan dan riwayat presensi terkait berhasil dihapus!');
    }

    /**
     * Tampilan Scanner QR Khusus Kegiatan Ini
     */
    public function scan($id)
    {
        $kegiatan = KegiatanKhusus::findOrFail($id);
        $pengaturan = PengaturanLembaga::first();

        $totalHadir = PresensiKegiatanKhusus::where('kegiatan_khusus_id', $id)
            ->whereIn('status', ['Hadir', 'Terlambat'])
            ->count();

        $recentLogs = PresensiKegiatanKhusus::where('kegiatan_khusus_id', $id)
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        return view('kegiatan-khusus.scan', compact('kegiatan', 'pengaturan', 'totalHadir', 'recentLogs'));
    }

    /**
     * API Proses Scan QR Kamera
     */
    public function processScan(Request $request, $id)
    {
        $kegiatan = KegiatanKhusus::findOrFail($id);
        $qr = trim($request->qr_code);

        if (!$qr) {
            return response()->json(['success' => false, 'message' => 'QR Code kosong atau tidak terbaca.'], 400);
        }

        // Cari peserta (Siswa atau Guru)
        $user = Siswa::find($qr) ?? Guru::find($qr);
        if (!$user) {
            $user = Siswa::where('nis', $qr)->first() ?? Guru::where('nip', $qr)->first();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Data peserta tidak terdaftar di sistem.'
            ], 404);
        }

        $userType = $user instanceof Guru ? 'guru' : 'siswa';
        $now = Carbon::now();

        // Validasi Sasaran Peserta
        if ($kegiatan->sasaran_peserta === 'Khusus Guru' && $userType !== 'guru') {
            return response()->json([
                'success' => false,
                'message' => 'Kegiatan ini dikhususkan hanya untuk Guru/Tendik.'
            ], 403);
        }

        if ($kegiatan->sasaran_peserta === 'Semua Siswa' && $userType !== 'siswa') {
            return response()->json([
                'success' => false,
                'message' => 'Kegiatan ini dikhususkan hanya untuk Siswa.'
            ], 403);
        }

        if ($kegiatan->sasaran_peserta === 'Pilih Kelas' && $userType === 'siswa') {
            $allowedClasses = $kegiatan->kelas_ids ?? [];
            if (!in_array($user->kelas_id, $allowedClasses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa dari kelas ini tidak terdaftar pada kegiatan ini.'
                ], 403);
            }
        }

        // Cek log presensi yang sudah ada
        $presensi = PresensiKegiatanKhusus::where('kegiatan_khusus_id', $kegiatan->id)
            ->where('user_type', $userType)
            ->where('user_id', $user->id)
            ->first();

        $jamMulai = Carbon::parse($kegiatan->jam_mulai);

        // Jika mode absen adalah "Datang Saja"
        if ($kegiatan->mode_absen === 'Datang Saja') {
            if ($presensi) {
                return response()->json([
                    'success' => false,
                    'already' => true,
                    'message' => "{$user->nama_lengkap} sudah absen pada {$presensi->jam_masuk->format('H:i:s')} WIB.",
                    'user' => [
                        'nama' => $user->nama_lengkap,
                        'tipe' => $userType === 'guru' ? 'Guru' : 'Siswa',
                        'detail' => $userType === 'guru' ? ($user->jabatan ?? 'Guru') : ('Kelas ' . ($user->kelas->nama_rombel ?? '-')),
                        'waktu' => $presensi->jam_masuk->format('H:i:s')
                    ]
                ]);
            }

            // Tentukan status kehadiran (Hadir / Terlambat)
            $status = $now->gt($jamMulai->copy()->addMinutes(15)) ? 'Terlambat' : 'Hadir';

            PresensiKegiatanKhusus::create([
                'kegiatan_khusus_id' => $kegiatan->id,
                'user_type' => $userType,
                'user_id' => $user->id,
                'jam_masuk' => $now,
                'status' => $status,
                'metode' => 'QR Scan',
            ]);

            // Kirim Notifikasi Otomatis (WhatsApp & Telegram) jika diaktifkan di form kegiatan
            if ($kegiatan->kirim_notifikasi) {
                $this->kirimNotifikasiKehadiran($kegiatan, $user, $userType, $now, $status, 'Masuk');
            }

            return response()->json([
                'success' => true,
                'message' => "Presensi Berhasil! Selamat Datang, {$user->nama_lengkap}.",
                'user' => [
                    'nama' => $user->nama_lengkap,
                    'tipe' => $userType === 'guru' ? 'Guru' : 'Siswa',
                    'detail' => $userType === 'guru' ? ($user->jabatan ?? 'Guru') : ('Kelas ' . ($user->kelas->nama_rombel ?? '-')),
                    'status' => $status,
                    'waktu' => $now->format('H:i:s') . ' WIB'
                ]
            ]);
        }

        // Jika mode absen adalah "Datang & Pulang"
        if (!$presensi) {
            // Absen Datang
            $status = $now->gt($jamMulai->copy()->addMinutes(15)) ? 'Terlambat' : 'Hadir';

            PresensiKegiatanKhusus::create([
                'kegiatan_khusus_id' => $kegiatan->id,
                'user_type' => $userType,
                'user_id' => $user->id,
                'jam_masuk' => $now,
                'status' => $status,
                'metode' => 'QR Scan',
            ]);

            if ($kegiatan->kirim_notifikasi) {
                $this->kirimNotifikasiKehadiran($kegiatan, $user, $userType, $now, $status, 'Masuk');
            }

            return response()->json([
                'success' => true,
                'message' => "Absen Masuk Berhasil! {$user->nama_lengkap}",
                'user' => [
                    'nama' => $user->nama_lengkap,
                    'tipe' => $userType === 'guru' ? 'Guru' : 'Siswa',
                    'detail' => $userType === 'guru' ? ($user->jabatan ?? 'Guru') : ('Kelas ' . ($user->kelas->nama_rombel ?? '-')),
                    'status' => $status,
                    'waktu' => $now->format('H:i:s') . ' WIB'
                ]
            ]);
        } else {
            // Cek apakah sudah absen pulang
            if ($presensi->jam_pulang) {
                return response()->json([
                    'success' => false,
                    'already' => true,
                    'message' => "{$user->nama_lengkap} sudah menyelesaikan presensi datang & pulang kegiatan ini.",
                    'user' => [
                        'nama' => $user->nama_lengkap,
                        'detail' => $userType === 'guru' ? 'Guru' : 'Siswa',
                        'waktu' => $presensi->jam_pulang->format('H:i:s')
                    ]
                ]);
            }

            // Simpan Absen Pulang
            $presensi->update(['jam_pulang' => $now]);

            if ($kegiatan->kirim_notifikasi) {
                $this->kirimNotifikasiKehadiran($kegiatan, $user, $userType, $now, 'Hadir Pulang', 'Pulang');
            }

            return response()->json([
                'success' => true,
                'message' => "Absen Pulang Berhasil! Terima kasih, {$user->nama_lengkap}.",
                'user' => [
                    'nama' => $user->nama_lengkap,
                    'tipe' => $userType === 'guru' ? 'Guru' : 'Siswa',
                    'detail' => $userType === 'guru' ? ($user->jabatan ?? 'Guru') : ('Kelas ' . ($user->kelas->nama_rombel ?? '-')),
                    'status' => 'Pulang Selesai',
                    'waktu' => $now->format('H:i:s') . ' WIB'
                ]
            ]);
        }
    }

    /**
     * Helper Pengiriman Notifikasi Otomatis (WhatsApp & Telegram) ke Orang Tua / Wali
     */
    private function kirimNotifikasiKehadiran($kegiatan, $user, $userType, $waktu, $status, $tipeScan = 'Masuk')
    {
        $lembaga = PengaturanLembaga::first();
        if ($userType === 'siswa') {
            $namaSekolah = $user->kelas?->departemen?->nama_departemen ?: ($lembaga->nama_lembaga ?? 'Sekolah');
        } else {
            $namaSekolah = $lembaga && !empty($lembaga->nama_yayasan) ? $lembaga->nama_yayasan : ($lembaga->nama_lembaga ?? 'Sekolah');
        }
        $tglIndo = Carbon::parse($kegiatan->tanggal)->locale('id')->translatedFormat('l, d F Y');
        $jamFmt = $waktu->format('H:i:s') . ' WIB';

        $detailPeserta = $userType === 'guru' 
            ? ($user->jabatan ?? 'Guru / Tendik') 
            : ('Kelas ' . ($user->kelas->nama_rombel ?? '-'));

        $nomorInduk = $userType === 'guru' ? ($user->nip ?? '-') : ($user->nis ?? '-');

        // 1. WhatsApp Notification ke Orang Tua / Wali (Siswa) atau Nomor HP (Guru)
        $waNumber = $userType === 'siswa' ? ($user->no_hp_wali ?? $user->no_hp) : $user->no_hp;
        if (!empty($waNumber)) {
            try {
                $waService = new WhatsAppService();
                $waMsg = "*📢 LAPORAN PRESENSI KEGIATAN KHUSUS*\n";
                $waMsg .= "*{$namaSekolah}*\n\n";
                $waMsg .= "Yth. Orang Tua / Wali Murid,\n";
                $waMsg .= "Berikut informasi presensi kegiatan ananda:\n\n";
                $waMsg .= "Nama: *{$user->nama_lengkap}*\n";
                $waMsg .= "NIS / NIP: {$nomorInduk}\n";
                $waMsg .= "Kelas / Jabatan: {$detailPeserta}\n";
                $waMsg .= "Kegiatan: *{$kegiatan->nama_kegiatan}* ({$kegiatan->kode_kegiatan})\n";
                $waMsg .= "Hari, Tanggal: {$tglIndo}\n";
                $waMsg .= "Waktu Presensi: *{$jamFmt}*\n";
                $waMsg .= "Jenis: Absen {$tipeScan}\n";
                $waMsg .= "Status: *{$status}*\n\n";
                $waMsg .= "_Telah berhasil tercatat pada sistem presensi kegiatan khusus. Terima kasih._";

                $waService->sendMessage($waNumber, $waMsg);
            } catch (\Throwable $e) {
                Log::error('WA Kegiatan Khusus Error: ' . $e->getMessage());
            }
        }

        // 2. Telegram Notification ke Orang Tua / Peserta
        if (!empty($user->telegram_chat_id)) {
            try {
                $tgService = new TelegramService();
                $tgMsg = "<b>📢 LAPORAN PRESENSI KEGIATAN KHUSUS</b>\n";
                $tgMsg .= "<b>{$namaSekolah}</b>\n\n";
                $tgMsg .= "<b>Nama:</b> {$user->nama_lengkap}\n";
                $tgMsg .= "<b>Kelas/Jabatan:</b> {$detailPeserta}\n";
                $tgMsg .= "<b>Kegiatan:</b> {$kegiatan->nama_kegiatan}\n";
                $tgMsg .= "<b>Hari, Tanggal:</b> {$tglIndo}\n";
                $tgMsg .= "<b>Waktu:</b> {$jamFmt}\n";
                $tgMsg .= "<b>Jenis:</b> Absen {$tipeScan}\n";
                $tgMsg .= "<b>Status:</b> <b>{$status}</b>\n\n";
                $tgMsg .= "<i>Presensi kegiatan khusus berhasil dicatat.</i>";

                $tgService->sendMessage($user->telegram_chat_id, $tgMsg);
            } catch (\Throwable $e) {
                Log::error('Telegram Kegiatan Khusus Error: ' . $e->getMessage());
            }
        }
    }

    /**
     * Tampilan Detail Kehadiran Peserta Per Kegiatan
     */
    public function peserta($id)
    {
        $kegiatan = KegiatanKhusus::with('presensi')->findOrFail($id);

        $presensis = PresensiKegiatanKhusus::where('kegiatan_khusus_id', $id)
            ->orderBy('jam_masuk', 'desc')
            ->paginate(20);

        return view('kegiatan-khusus.peserta', compact('kegiatan', 'presensis'));
    }
}
