<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Presensi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PresensiController extends Controller
{
    // Display the attendance logs
    public function index(Request $request)
    {
        $tanggal = $request->tanggal ?? Carbon::today()->toDateString();
        
        $presensi = Presensi::whereDate('tanggal', $tanggal)
            ->orderBy('jam_masuk', 'desc')
            ->get();
            
        return view('presensi.index', compact('presensi', 'tanggal'));
    }

    // Display the scanner page
    public function scan()
    {
        $totalHariIni = \App\Models\Presensi::whereDate('tanggal', \Carbon\Carbon::today()->toDateString())->count();
        $pengaturan = \App\Models\PengaturanLembaga::first();
        return view('presensi.scan', compact('totalHariIni', 'pengaturan'));
    }

    // Handle the scanned QR code
    public function processScan(Request $request)
    {
        $qr = $request->qr_code;

        if (!$qr) {
            return response()->json(['success' => false, 'message' => 'QR Code tidak valid.']);
        }

        // Cari user (Siswa atau Guru)
        // Kita periksa dari uuid terlebih dahulu karena qrcode JS menyimpan ID (uuid)
        $user = Siswa::find($qr) ?? Guru::find($qr);
        
        // Fallback jika ternyata QR berisi NIS/NIP
        if (!$user) {
             $user = Siswa::where('nis', $qr)->first() ?? Guru::where('nip', $qr)->first();
        }

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan di sistem.']);
        }

        $today = \Carbon\Carbon::today()->toDateString();

        // CEK HARI LIBUR
        $hariLibur = \App\Models\HariLibur::where('tanggal', $today)->first();
        if ($hariLibur) {
            return response()->json([
                'success' => false,
                'message' => 'Presensi ditolak. Hari ini adalah hari libur: ' . $hariLibur->keterangan
            ]);
        }

        // Tentukan Tipe User
        $userTypeModel = $user instanceof \App\Models\Guru ? 'guru' : 'siswa';

        // CEK APAKAH SUDAH IZIN / SAKIT HARI INI
        $cekIzin = Presensi::where('user_type', $userTypeModel)
            ->where('user_id', $user->id)
            ->where('tanggal', $today)
            ->whereIn('status', ['Izin', 'Sakit', 'Alpa'])
            ->first();
            
        if ($cekIzin) {
            return response()->json([
                'success' => false,
                'message' => "Presensi ditolak. " . $user->nama_lengkap . " sudah tercatat " . $cekIzin->status . " hari ini."
            ]);
        }

        // Ambil Pengaturan Jam
        $hariIni = \Carbon\Carbon::now()->locale('id')->dayName; // "Senin", "Selasa" dst.
        
        $tipeTarget = $userTypeModel === 'guru' ? 'guru' : 'kelas';
        $kelasId = $userTypeModel === 'siswa' ? $user->kelas_id : null;
        
        $pengaturan = \App\Models\PengaturanJam::where('tipe', $tipeTarget)
            ->where('hari', $hariIni)
            ->when($tipeTarget === 'kelas', function($q) use ($kelasId) {
                return $q->where('kelas_id', $kelasId);
            })->first();

        if (!$pengaturan) {
            return response()->json([
                'success' => false,
                'message' => "Jadwal absensi untuk hari $hariIni belum diatur oleh admin."
            ]);
        }

        $now = \Carbon\Carbon::now();
        $jam_sekarang = $now->format('H:i:s');
        
        // Cek jika belum waktunya absen masuk
        if ($jam_sekarang < $pengaturan->jam_mulai) {
            return response()->json([
                'success' => false,
                'message' => "Belum waktunya absen. Absen dibuka jam " . substr($pengaturan->jam_mulai, 0, 5)
            ]);
        }

        $today = Carbon::today()->toDateString();
        $currentTime = Carbon::now();

        $userType = $userTypeModel;

        // Check if already scanned today
        $presensi = Presensi::where('user_type', $userType)
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        if ($presensi) {
            // Jika sudah ada jam_pulang, tolak sama sekali
            if ($presensi->jam_pulang) {
                return response()->json([
                    'success' => false,
                    'message' => "Presensi ditolak. Anda sudah melakukan absen pulang hari ini."
                ]);
            }

            // Jika belum waktunya pulang, beri tahu bahwa mereka sudah absen masuk
            if ($jam_sekarang < $pengaturan->jam_pulang) {
                return response()->json([
                    'success' => false,
                    'message' => "Anda sudah absen masuk! Belum waktunya pulang (buka jam " . substr($pengaturan->jam_pulang, 0, 5) . ")."
                ]);
            }

            // Jika sudah masuk waktu pulang, beri jeda toleransi 30 detik untuk mencegah scan ganda mesin
            $lastScan = Carbon::parse($today . ' ' . $presensi->jam_masuk);
            if ($currentTime->diffInSeconds($lastScan) < 30) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda baru saja absen masuk. Harap tunggu sebentar.'
                ]);
            }

            // Proses absen pulang
            $presensi->jam_pulang = $jam_sekarang;
            $presensi->save();

            // Kirim notifikasi Telegram
            if (!empty($user->telegram_chat_id)) {
                $telegram = new \App\Services\TelegramService();
                $msg = "<b>📢 Laporan Absensi Pulang</b>\n\n";
                $msg .= "<b>Nama:</b> {$user->nama_lengkap}\n";
                $msg .= "<b>Waktu:</b> {$jam_sekarang} WIB\n";
                $msg .= "<b>Keterangan:</b> Telah melakukan absensi pulang.\n\n";
                $msg .= "<i>Terima kasih dan hati-hati di jalan.</i>";
                $telegram->sendMessage($user->telegram_chat_id, $msg);
            }

            return response()->json([
                'success' => true,
                'type' => 'pulang',
                'status' => $presensi->status,
                'user_name' => $user->nama_lengkap,
                'message' => "Presensi PULANG berhasil. Hati-hati di jalan, {$user->nama_lengkap}!"
            ]);
        }

        // First scan of the day -> Check In
        $status = 'Hadir';
        if ($jam_sekarang > $pengaturan->jam_terlambat) {
            $status = 'Terlambat';
        }

        Presensi::create([
            'user_type' => $userType,
            'user_id' => $user->id,
            'tanggal' => $today,
            'jam_masuk' => $jam_sekarang,
            'status' => $status
        ]);

        // Kirim notifikasi Telegram
        if (!empty($user->telegram_chat_id)) {
            $telegram = new \App\Services\TelegramService();
            if ($status === 'Terlambat') {
                $msg = "<b>⚠️ Laporan Absensi (Terlambat)</b>\n\n";
            } else {
                $msg = "<b>📢 Laporan Absensi Masuk</b>\n\n";
            }
            $msg .= "<b>Nama:</b> {$user->nama_lengkap}\n";
            $msg .= "<b>Waktu:</b> {$jam_sekarang} WIB\n";
            $msg .= "<b>Status:</b> {$status}\n\n";
            if ($status === 'Terlambat') {
                $msg .= "<i>Mohon untuk datang lebih tepat waktu besok.</i>";
            } else {
                $msg .= "<i>Semoga harinya menyenangkan!</i>";
            }
            $telegram->sendMessage($user->telegram_chat_id, $msg);
        }

        return response()->json([
            'success' => true,
            'type' => 'masuk',
            'status' => $status,
            'user_name' => $user->nama_lengkap,
            'message' => "Presensi MASUK berhasil. Selamat datang, {$user->nama_lengkap}!" . ($status == 'Terlambat' ? ' (Terlambat)' : '')
        ]);
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'string|exists:presensi,id'
        ]);

        Presensi::whereIn('id', $request->ids)->delete();

        return redirect()->back()->with('success', count($request->ids) . ' data presensi berhasil di-reset (dihapus).');
    }
}
