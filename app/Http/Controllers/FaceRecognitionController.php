<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\HariLibur;
use App\Models\PengaturanJam;
use App\Models\PengaturanLembaga;
use App\Models\Presensi;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FaceRecognitionController extends Controller
{
    // Halaman face scan untuk guru (absensi masuk/pulang via wajah)
    public function scan()
    {
        $guru = Auth::guard('guru')->user();
        $today = Carbon::today()->toDateString();
        $hariIni = Carbon::now()->locale('id')->dayName;

        // Presensi hari ini
        $presensiHariIni = Presensi::where('user_type', 'guru')
            ->where('user_id', $guru->id)
            ->whereDate('tanggal', $today)
            ->first();

        // Jam sekolah guru dari pengaturan admin
        $pengaturanJam = PengaturanJam::where('tipe', 'guru')
            ->where('hari', $hariIni)
            ->first();

        // Cek apakah guru terkunci karena tidak absen pulang hari sebelumnya
        $lockedPresensi = Presensi::where('user_type', 'guru')
            ->where('user_id', $guru->id)
            ->whereDate('tanggal', '<', $today)
            ->whereNotNull('jam_masuk')
            ->whereNull('jam_pulang')
            ->where('is_unlocked', false)
            ->orderBy('tanggal', 'desc')
            ->first();

        // Apakah guru sudah daftar wajah
        $hasFace = !empty($guru->face_descriptor);

        return view('face-recognition.scan', [
            'guru'            => $guru,
            'presensiHariIni' => $presensiHariIni,
            'lockedPresensi'  => $lockedPresensi,
            'pengaturanJam'   => $pengaturanJam,
            'pengaturan'      => PengaturanLembaga::first(),
            'hasFace'         => $hasFace,
        ]);
    }

    // API: ambil semua face descriptor guru yang sudah terdaftar
    public function getDescriptors()
    {
        $gurus = Guru::whereNotNull('face_descriptor')
            ->select('id', 'nama_lengkap', 'nip', 'face_descriptor')
            ->get()
            ->map(function ($g) {
                return [
                    'id'             => $g->id,
                    'nama_lengkap'   => $g->nama_lengkap,
                    'nip'            => $g->nip,
                    'face_descriptor'=> json_decode($g->face_descriptor),
                ];
            });

        return response()->json($gurus);
    }

    // API: proses absensi setelah wajah dikenali
    public function processFaceScan(Request $request)
    {
        $request->validate([
            'guru_id'   => 'required|exists:guru,id',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $authGuru = Auth::guard('guru')->user();
        if ($authGuru && (string)$request->guru_id !== (string)$authGuru->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Anda hanya dapat memproses absensi wajah untuk akun Anda sendiri.',
            ], 403);
        }

        $guru = Guru::findOrFail($request->guru_id);
        $today = Carbon::today()->toDateString();
        $hariIni = Carbon::now()->locale('id')->dayName;
        $now = Carbon::now();
        $jam_sekarang = $now->format('H:i:s');

        // Cek hari libur
        $hariLibur = HariLibur::where('tanggal', $today)->first();
        if ($hariLibur) {
            return response()->json([
                'success' => false,
                'message' => 'Hari ini adalah hari libur: ' . $hariLibur->keterangan,
            ]);
        }

        // Cek Geofencing GPS Radius (Tolak jika di luar radius)
        $geoCheck = $this->validateGeofence($request);
        if (!$geoCheck['valid']) {
            return response()->json([
                'success' => false,
                'message' => $geoCheck['error'],
            ]);
        }

        // Ambil pengaturan jam guru (dengan fallback jika hari belum diatur)
        $pengaturan = PengaturanJam::where('tipe', 'guru')->where('hari', $hariIni)->first()
            ?: PengaturanJam::where('tipe', 'guru')->first();
        if (!$pengaturan) {
            return response()->json([
                'success' => false,
                'message' => "Jadwal absensi untuk guru belum diatur oleh admin.",
            ]);
        }

        // Belum waktunya absen
        if ($jam_sekarang < $pengaturan->jam_mulai) {
            return response()->json([
                'success' => false,
                'message' => 'Belum waktunya absen. Absen dibuka jam ' . substr($pengaturan->jam_mulai, 0, 5),
            ]);
        }

        // Cek presensi hari ini
        $presensi = Presensi::where('user_type', 'guru')
            ->where('user_id', $guru->id)
            ->whereDate('tanggal', $today)
            ->first();

        if ($presensi) {
            // Sudah absen pulang
            if ($presensi->jam_pulang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan absen pulang hari ini.',
                ]);
            }

            // Belum waktunya pulang
            if ($jam_sekarang < $pengaturan->jam_pulang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah absen masuk! Belum waktunya pulang (buka jam ' . substr($pengaturan->jam_pulang, 0, 5) . ').',
                ]);
            }

            // Proses absen pulang
            $presensi->jam_pulang = $jam_sekarang;
            if ($request->filled('latitude') && $request->filled('longitude')) {
                $presensi->latitude = $request->latitude;
                $presensi->longitude = $request->longitude;
                $presensi->distance_meter = $geoCheck['distance'];
            }
            $presensi->save();

            // Kirim notifikasi Telegram ke Guru
            $telegramSent = $this->sendTelegramNotification($guru, 'pulang', $jam_sekarang, $presensi->status, 'Face Recognition (Wajah)');

            return response()->json([
                'success'         => true,
                'type'            => 'pulang',
                'status'          => $presensi->status,
                'user_name'       => $guru->nama_lengkap,
                'telegram_sent'   => $telegramSent,
                'message'         => "Absensi PULANG berhasil! Hati-hati di jalan, {$guru->nama_lengkap}!" . ($telegramSent ? ' (Notifikasi Telegram terkirim)' : ''),
            ]);
        }

        // Proses absen masuk
        // Cek apakah ada presensi hari sebelumnya di mana guru absen masuk tapi TIDAK absen pulang dan belum di-unlock
        $lockedPresensi = Presensi::where('user_type', 'guru')
            ->where('user_id', $guru->id)
            ->whereDate('tanggal', '<', $today)
            ->whereNotNull('jam_masuk')
            ->whereNull('jam_pulang')
            ->where('is_unlocked', false)
            ->orderBy('tanggal', 'desc')
            ->first();

        if ($lockedPresensi) {
            $tglLocked = Carbon::parse($lockedPresensi->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y');
            return response()->json([
                'success' => false,
                'is_locked' => true,
                'message' => "🔒 Absensi Ditolak: Anda tidak melakukan absen pulang pada hari {$tglLocked}. Silakan hubungi Administrator untuk membuka kunci kehadiran Anda.",
            ]);
        }

        $status = $jam_sekarang > $pengaturan->jam_terlambat ? 'Terlambat' : 'Hadir';

        Presensi::create([
            'user_type'      => 'guru',
            'user_id'        => $guru->id,
            'tanggal'        => $today,
            'jam_masuk'      => $jam_sekarang,
            'status'         => $status,
            'latitude'       => $request->latitude,
            'longitude'      => $request->longitude,
            'distance_meter' => $geoCheck['distance'],
        ]);

        // Kirim notifikasi Telegram ke Guru
        $telegramSent = $this->sendTelegramNotification($guru, 'masuk', $jam_sekarang, $status, 'Face Recognition (Wajah)');

        return response()->json([
            'success'         => true,
            'type'            => 'masuk',
            'status'          => $status,
            'user_name'       => $guru->nama_lengkap,
            'telegram_sent'   => $telegramSent,
            'message'         => "Absensi MASUK berhasil! Selamat datang, {$guru->nama_lengkap}!" . ($status === 'Terlambat' ? ' (Terlambat)' : '') . ($telegramSent ? ' (Notifikasi Telegram terkirim)' : ''),
        ]);
    }

    // API: simpan face descriptor guru (registrasi wajah mandiri dengan proteksi anti-duplikasi)
    public function saveFaceDescriptor(Request $request)
    {
        $request->validate(['descriptor' => 'required|array']);

        $guru = Auth::guard('guru')->user();
        $newDescriptor = $request->descriptor;

        // Validasi format descriptor (standar face-api.js adalah 128 elemen float)
        if (count($newDescriptor) !== 128) {
            return response()->json([
                'success' => false,
                'message' => 'Format data wajah tidak valid. Silakan coba pindai kembali.',
            ]);
        }

        // Cek apakah wajah ini sudah pernah terdaftar pada akun guru lain
        $otherGurus = Guru::where('id', '!=', $guru->id)
            ->whereNotNull('face_descriptor')
            ->select('id', 'nama_lengkap', 'nip', 'face_descriptor')
            ->get();

        $threshold = 0.45; // Ambang batas kemiripan wajah (jarak < 0.45 menunjukkan orang yang sama)
        $matchedGuru = null;
        $minDistance = 999.0;

        foreach ($otherGurus as $other) {
            $existingDescriptor = json_decode($other->face_descriptor, true);
            if (!is_array($existingDescriptor) || count($existingDescriptor) !== 128) {
                continue;
            }

            // Hitung Euclidean Distance: sqrt(sum((A_i - B_i)^2))
            $sum = 0.0;
            for ($i = 0; $i < 128; $i++) {
                $diff = (float)$newDescriptor[$i] - (float)$existingDescriptor[$i];
                $sum += $diff * $diff;
            }
            $distance = sqrt($sum);

            if ($distance < $threshold && $distance < $minDistance) {
                $minDistance = $distance;
                $matchedGuru = $other;
            }
        }

        // Jika wajah terdeteksi cocok dengan akun guru lain, tolak pendaftaran!
        if ($matchedGuru) {
            return response()->json([
                'success' => false,
                'message' => "Pendaftaran Ditolak: Wajah ini sudah terdaftar atas nama {$matchedGuru->nama_lengkap} (NIP: " . ($matchedGuru->nip ?: '-') . "). Satu wajah hanya boleh didaftarkan pada 1 akun guru!",
            ]);
        }

        // Simpan face descriptor
        $guru->face_descriptor = json_encode($newDescriptor);
        $guru->save();

        return response()->json([
            'success' => true,
            'message' => 'Wajah berhasil didaftarkan!',
        ]);
    }

    // Absensi manual masuk/pulang dari dashboard guru
    public function absenManual(Request $request)
    {
        $request->validate([
            'tipe'      => 'required|in:masuk,pulang',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $guru    = Auth::guard('guru')->user();
        $today   = Carbon::today()->toDateString();
        $hariIni = Carbon::now()->locale('id')->dayName;
        $jam     = Carbon::now()->format('H:i:s');

        // Cek hari libur
        $hariLibur = HariLibur::where('tanggal', $today)->first();
        if ($hariLibur) {
            return response()->json([
                'success' => false,
                'message' => 'Hari ini adalah hari libur: ' . $hariLibur->keterangan,
            ]);
        }

        // Cek Geofencing GPS Radius (Tolak jika di luar radius)
        $geoCheck = $this->validateGeofence($request);
        if (!$geoCheck['valid']) {
            return response()->json([
                'success' => false,
                'message' => $geoCheck['error'],
            ]);
        }

        // Wajah harus sudah terdaftar
        if (empty($guru->face_descriptor)) {
            return response()->json([
                'success' => false,
                'message' => 'Wajah Anda belum terdaftar di sistem. Silakan daftarkan wajah terlebih dahulu melalui menu Absen Wajah.',
            ]);
        }

        // Cek pengaturan jam (dengan fallback jika hari belum diatur)
        $pengaturan = PengaturanJam::where('tipe', 'guru')->where('hari', $hariIni)->first()
            ?: PengaturanJam::where('tipe', 'guru')->first();
        if (!$pengaturan) {
            return response()->json([
                'success' => false,
                'message' => "Jadwal absensi untuk guru belum diatur oleh admin.",
            ]);
        }

        // Cek presensi hari ini
        $presensi = Presensi::where('user_type', 'guru')
            ->where('user_id', $guru->id)
            ->whereDate('tanggal', $today)
            ->first();

        if ($request->tipe === 'masuk') {
            // Cek apakah ada presensi hari sebelumnya di mana guru absen masuk tapi TIDAK absen pulang dan belum di-unlock
            $lockedPresensi = Presensi::where('user_type', 'guru')
                ->where('user_id', $guru->id)
                ->whereDate('tanggal', '<', $today)
                ->whereNotNull('jam_masuk')
                ->whereNull('jam_pulang')
                ->where('is_unlocked', false)
                ->orderBy('tanggal', 'desc')
                ->first();

            if ($lockedPresensi) {
                $tglLocked = Carbon::parse($lockedPresensi->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y');
                return response()->json([
                    'success' => false,
                    'is_locked' => true,
                    'message' => "🔒 Absensi Ditolak: Anda tidak melakukan absen pulang pada hari {$tglLocked}. Silakan hubungi Administrator untuk membuka kunci kehadiran Anda.",
                ]);
            }

            if ($presensi && $presensi->jam_masuk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah absen masuk hari ini pukul ' . substr($presensi->jam_masuk, 0, 5) . '.',
                ]);
            }
            if ($jam < $pengaturan->jam_mulai) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum waktunya absen masuk. Absen dibuka jam ' . substr($pengaturan->jam_mulai, 0, 5) . '.',
                ]);
            }
            $status = $jam > $pengaturan->jam_terlambat ? 'Terlambat' : 'Hadir';

            if ($presensi) {
                // Update record yang sudah ada tapi belum ada jam_masuk
                $presensi->update([
                    'jam_masuk'      => $jam,
                    'status'         => $status,
                    'latitude'       => $request->latitude,
                    'longitude'      => $request->longitude,
                    'distance_meter' => $geoCheck['distance'],
                ]);
            } else {
                // Buat record baru jika belum ada
                Presensi::create([
                    'user_type'      => 'guru',
                    'user_id'        => $guru->id,
                    'tanggal'        => $today,
                    'jam_masuk'      => $jam,
                    'status'         => $status,
                    'latitude'       => $request->latitude,
                    'longitude'      => $request->longitude,
                    'distance_meter' => $geoCheck['distance'],
                ]);
            }

            // Kirim notifikasi Telegram ke Guru
            $telegramSent = $this->sendTelegramNotification($guru, 'masuk', $jam, $status, 'Verifikasi Wajah (Dashboard)');

            return response()->json([
                'success'       => true,
                'telegram_sent' => $telegramSent,
                'message'       => 'Absensi masuk berhasil pukul ' . substr($jam, 0, 5) . '. Status: ' . $status . ($telegramSent ? ' (Notifikasi Telegram terkirim)' : ''),
            ]);
        }

        // Tipe pulang
        if (!$presensi) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum absen masuk hari ini.',
            ]);
        }
        if ($presensi->jam_pulang) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah absen pulang hari ini pukul ' . substr($presensi->jam_pulang, 0, 5) . '.',
            ]);
        }
        if ($jam < $pengaturan->jam_pulang) {
            return response()->json([
                'success' => false,
                'message' => 'Belum waktunya absen pulang. Absen pulang dibuka jam ' . substr($pengaturan->jam_pulang, 0, 5) . '.',
            ]);
        }
        $presensi->jam_pulang = $jam;
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $presensi->latitude = $request->latitude;
            $presensi->longitude = $request->longitude;
            $presensi->distance_meter = $geoCheck['distance'];
        }
        $presensi->save();

        // Kirim notifikasi Telegram ke Guru
        $telegramSent = $this->sendTelegramNotification($guru, 'pulang', $jam, $presensi->status, 'Verifikasi Wajah (Dashboard)');

        return response()->json([
            'success'       => true,
            'telegram_sent' => $telegramSent,
            'message'       => 'Absensi pulang berhasil pukul ' . substr($jam, 0, 5) . '. Hati-hati di jalan! 🚪' . ($telegramSent ? ' (Notifikasi Telegram terkirim)' : ''),
        ]);
    }

    /**
     * Validasi radius GPS geofencing
     * Return array: ['valid' => bool, 'distance' => float|null, 'error' => string|null]
     */
    protected function validateGeofence(Request $request): array
    {
        $lembaga = PengaturanLembaga::first();
        if (!$lembaga || empty($lembaga->latitude) || empty($lembaga->longitude)) {
            // Sekolah belum mengatur titik koordinat GPS, izinkan (bypass)
            return [
                'valid'    => true,
                'distance' => null,
                'error'    => null,
            ];
        }

        $userLat = $request->input('latitude');
        $userLng = $request->input('longitude');

        if ($userLat === null || $userLng === null || $userLat === '' || $userLng === '') {
            return [
                'valid'    => false,
                'distance' => null,
                'error'    => 'Presensi DITOLAK: Koordinat GPS tidak terdeteksi. Silakan aktifkan izin lokasi (GPS) pada perangkat/browser Anda.',
            ];
        }

        $schoolLat = (float) $lembaga->latitude;
        $schoolLng = (float) $lembaga->longitude;
        $radius    = (float) ($lembaga->radius_meter ?: 100);

        $distance = $this->calculateDistance((float)$userLat, (float)$userLng, $schoolLat, $schoolLng);

        if ($distance > $radius) {
            $distRounded = round($distance);
            return [
                'valid'    => false,
                'distance' => $distRounded,
                'error'    => "Presensi DITOLAK: Anda berada di luar radius lokasi sekolah. Jarak Anda saat ini: {$distRounded} meter (Batas toleransi maksimal: {$radius} meter).",
            ];
        }

        return [
            'valid'    => true,
            'distance' => round($distance),
            'error'    => null,
        ];
    }

    /**
     * Hitung jarak dua koordinat GPS menggunakan Haversine Formula (dalam satuan meter)
     */
    protected function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meter

        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        $latDelta = $lat2Rad - $lat1Rad;
        $lonDelta = $lon2Rad - $lon1Rad;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($lat1Rad) * cos($lat2Rad) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    /**
     * Kirim notifikasi presensi ke Telegram Guru jika telegram_chat_id terdaftar
     */
    protected function sendTelegramNotification(Guru $guru, string $tipe, string $jam, string $status, string $metode = 'Face Recognition'): bool
    {
        if (empty($guru->telegram_chat_id)) {
            return false;
        }

        try {
            $telegram = new TelegramService();
            $lembaga = PengaturanLembaga::first();
            $namaSekolah = $lembaga && !empty($lembaga->nama_lembaga) ? $lembaga->nama_lembaga : 'Sistem Presensi Sekolah';
            $jamFmt = substr($jam, 0, 5) . ' WIB';

            if ($tipe === 'pulang') {
                $msg = "📢 <b>Laporan Absensi Pulang Guru</b>\n";
                $msg .= "🏫 <b>{$namaSekolah}</b>\n\n";
                $msg .= "👤 <b>Nama:</b> {$guru->nama_lengkap}\n";
                if (!empty($guru->nip)) {
                    $msg .= "🆔 <b>NIP:</b> {$guru->nip}\n";
                }
                $msg .= "🕒 <b>Waktu Pulang:</b> {$jamFmt}\n";
                $msg .= "📊 <b>Status Kehadiran:</b> {$status}\n";
                $msg .= "📷 <b>Metode:</b> {$metode}\n\n";
                $msg .= "<i>Terima kasih atas dedikasi dan pengabdiannya hari ini. Selamat beristirahat dan hati-hati di perjalanan pulang! 🚗💨</i>";
            } else {
                if ($status === 'Terlambat') {
                    $msg = "⚠️ <b>Laporan Absensi Guru (Terlambat)</b>\n";
                } else {
                    $msg = "📢 <b>Laporan Absensi Masuk Guru</b>\n";
                }
                $msg .= "🏫 <b>{$namaSekolah}</b>\n\n";
                $msg .= "👤 <b>Nama:</b> {$guru->nama_lengkap}\n";
                if (!empty($guru->nip)) {
                    $msg .= "🆔 <b>NIP:</b> {$guru->nip}\n";
                }
                $msg .= "🕒 <b>Waktu Masuk:</b> {$jamFmt}\n";
                $msg .= "📊 <b>Status:</b> {$status}\n";
                $msg .= "📷 <b>Metode:</b> {$metode}\n\n";
                if ($status === 'Terlambat') {
                    $msg .= "<i>Presensi kehadiran berhasil tercatat (terlambat). Tetap semangat menjalankan tugas mengajar hari ini! 💪</i>";
                } else {
                    $msg .= "<i>Presensi kehadiran berhasil tercatat tepat waktu. Selamat bertugas dan sukses selalu! ✨</i>";
                }
            }

            return (bool) $telegram->sendMessage($guru->telegram_chat_id, $msg);
        } catch (\Throwable $e) {
            Log::error('Telegram notification error for guru ' . $guru->id . ': ' . $e->getMessage());
            return false;
        }
    }
}
