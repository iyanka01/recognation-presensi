<?php

namespace App\Services\KegiatanKhusus;

use App\Models\Guru;
use App\Models\KegiatanKhusus;
use App\Models\PengaturanLembaga;
use App\Models\PresensiKegiatanKhusus;
use App\Models\Siswa;
use App\Services\TelegramService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class KegiatanKhususService
{
    /**
     * Menyimpan kegiatan baru dengan generate kode otomatis jika kosong
     */
    public function store(array $validated): KegiatanKhusus
    {
        $kode = $validated['kode_kegiatan'] ?? null;
        if (!$kode) {
            $count = KegiatanKhusus::count() + 1;
            $kode = sprintf('KGT-%03d', $count);
        }

        return KegiatanKhusus::create([
            'kode_kegiatan'    => $kode,
            'nama_kegiatan'    => $validated['nama_kegiatan'],
            'tipe_kegiatan'    => $validated['tipe_kegiatan'],
            'hari'             => $validated['tipe_kegiatan'] === 'rutin' ? ($validated['hari'] ?? null) : null,
            'tanggal'          => $validated['tipe_kegiatan'] === 'sekali' ? ($validated['tanggal'] ?? null) : null,
            'jam_mulai'        => $validated['jam_mulai'],
            'jam_selesai'      => $validated['jam_selesai'],
            'mode_absen'       => $validated['mode_absen'] ?? 'Datang Saja',
            'sasaran_peserta'  => $validated['sasaran_peserta'] ?? 'Semua Siswa',
            'kelas_ids'        => ($validated['sasaran_peserta'] ?? '') === 'Pilih Kelas' ? ($validated['kelas_ids'] ?? null) : null,
            'keterangan'       => $validated['keterangan'] ?? null,
            'status'           => 'Aktif',
            'kirim_notifikasi' => !empty($validated['kirim_notifikasi']),
        ]);
    }

    /**
     * Update data kegiatan khusus
     */
    public function update(KegiatanKhusus $kegiatan, array $validated): bool
    {
        return $kegiatan->update([
            'kode_kegiatan'    => !empty($validated['kode_kegiatan']) ? $validated['kode_kegiatan'] : $kegiatan->kode_kegiatan,
            'nama_kegiatan'    => $validated['nama_kegiatan'],
            'tipe_kegiatan'    => $validated['tipe_kegiatan'],
            'hari'             => $validated['tipe_kegiatan'] === 'rutin' ? ($validated['hari'] ?? null) : null,
            'tanggal'          => $validated['tipe_kegiatan'] === 'sekali' ? ($validated['tanggal'] ?? null) : null,
            'jam_mulai'        => $validated['jam_mulai'],
            'jam_selesai'      => $validated['jam_selesai'],
            'mode_absen'       => $validated['mode_absen'],
            'sasaran_peserta'  => $validated['sasaran_peserta'],
            'kelas_ids'        => $validated['sasaran_peserta'] === 'Pilih Kelas' ? ($validated['kelas_ids'] ?? null) : null,
            'keterangan'       => $validated['keterangan'] ?? null,
            'status'           => $validated['status'] ?? $kegiatan->status,
            'kirim_notifikasi' => !empty($validated['kirim_notifikasi']),
        ]);
    }

    /**
     * Memproses scan QR code untuk kegiatan khusus
     */
    public function processScan(KegiatanKhusus $kegiatan, string $qr): array
    {
        $user = Siswa::find($qr) ?? Guru::find($qr);
        if (!$user) {
            $user = Siswa::where('nis', $qr)->first() ?? Guru::where('nip', $qr)->first();
        }

        if (!$user) {
            return [
                'status_code' => 404,
                'data' => [
                    'success' => false,
                    'message' => 'Data peserta tidak terdaftar di sistem.'
                ]
            ];
        }

        $userType = $user instanceof Guru ? 'guru' : 'siswa';
        $now = Carbon::now();
        $today = $now->toDateString();

        if ($kegiatan->tipe_kegiatan === 'rutin' && !empty($kegiatan->hari)) {
            $hariMap = [
                0 => 'Minggu',
                1 => 'Senin',
                2 => 'Selasa',
                3 => 'Rabu',
                4 => 'Kamis',
                5 => 'Jumat',
                6 => 'Sabtu',
            ];
            $hariIni = $hariMap[$now->dayOfWeek] ?? '';
            if (!in_array($hariIni, $kegiatan->hari)) {
                return [
                    'status_code' => 403,
                    'data' => [
                        'success' => false,
                        'message' => "Kegiatan ini tidak dijadwalkan untuk hari {$hariIni}."
                    ]
                ];
            }
        }

        if ($kegiatan->sasaran_peserta === 'Khusus Guru' && $userType !== 'guru') {
            return [
                'status_code' => 403,
                'data' => [
                    'success' => false,
                    'message' => 'Kegiatan ini dikhususkan hanya untuk Guru/Tendik.'
                ]
            ];
        }

        if ($kegiatan->sasaran_peserta === 'Semua Siswa' && $userType !== 'siswa') {
            return [
                'status_code' => 403,
                'data' => [
                    'success' => false,
                    'message' => 'Kegiatan ini dikhususkan hanya untuk Siswa.'
                ]
            ];
        }

        if ($kegiatan->sasaran_peserta === 'Pilih Kelas' && $userType === 'siswa') {
            $allowedClasses = $kegiatan->kelas_ids ?? [];
            if (!in_array($user->kelas_id, $allowedClasses)) {
                return [
                    'status_code' => 403,
                    'data' => [
                        'success' => false,
                        'message' => 'Siswa dari kelas ini tidak terdaftar pada kegiatan ini.'
                    ]
                ];
            }
        }

        $presensi = PresensiKegiatanKhusus::where('kegiatan_khusus_id', $kegiatan->id)
            ->where('user_type', $userType)
            ->where('user_id', $user->id)
            ->where('tanggal_presensi', $today)
            ->first();

        $jamMulai = Carbon::parse($kegiatan->jam_mulai);

        if ($kegiatan->mode_absen === 'Datang Saja') {
            if ($presensi) {
                return [
                    'status_code' => 200,
                    'data' => [
                        'success' => false,
                        'already' => true,
                        'message' => "{$user->nama_lengkap} sudah absen pada {$presensi->jam_masuk->format('H:i:s')} WIB.",
                        'user' => [
                            'nama' => $user->nama_lengkap,
                            'tipe' => $userType === 'guru' ? 'Guru' : 'Siswa',
                            'detail' => $userType === 'guru' ? ($user->jabatan ?? 'Guru') : ('Kelas ' . ($user->kelas->nama_rombel ?? '-')),
                            'waktu' => $presensi->jam_masuk->format('H:i:s')
                        ]
                    ]
                ];
            }

            $status = $now->gt($jamMulai->copy()->addMinutes(15)) ? 'Terlambat' : 'Hadir';

            PresensiKegiatanKhusus::create([
                'kegiatan_khusus_id' => $kegiatan->id,
                'user_type' => $userType,
                'user_id' => $user->id,
                'tanggal_presensi' => $today,
                'jam_masuk' => $now,
                'status' => $status,
                'metode' => 'QR Scan',
            ]);

            if ($kegiatan->kirim_notifikasi) {
                $this->kirimNotifikasiKehadiran($kegiatan, $user, $userType, $now, $status, 'Masuk');
            }

            return [
                'status_code' => 200,
                'data' => [
                    'success' => true,
                    'message' => "Presensi Berhasil! Selamat Datang, {$user->nama_lengkap}.",
                    'user' => [
                        'nama' => $user->nama_lengkap,
                        'tipe' => $userType === 'guru' ? 'Guru' : 'Siswa',
                        'detail' => $userType === 'guru' ? ($user->jabatan ?? 'Guru') : ('Kelas ' . ($user->kelas->nama_rombel ?? '-')),
                        'status' => $status,
                        'waktu' => $now->format('H:i:s') . ' WIB'
                    ]
                ]
            ];
        }

        if (!$presensi) {
            $status = $now->gt($jamMulai->copy()->addMinutes(15)) ? 'Terlambat' : 'Hadir';

            PresensiKegiatanKhusus::create([
                'kegiatan_khusus_id' => $kegiatan->id,
                'user_type' => $userType,
                'user_id' => $user->id,
                'tanggal_presensi' => $today,
                'jam_masuk' => $now,
                'status' => $status,
                'metode' => 'QR Scan',
            ]);

            if ($kegiatan->kirim_notifikasi) {
                $this->kirimNotifikasiKehadiran($kegiatan, $user, $userType, $now, $status, 'Masuk');
            }

            return [
                'status_code' => 200,
                'data' => [
                    'success' => true,
                    'message' => "Absen Masuk Berhasil! {$user->nama_lengkap}",
                    'user' => [
                        'nama' => $user->nama_lengkap,
                        'tipe' => $userType === 'guru' ? 'Guru' : 'Siswa',
                        'detail' => $userType === 'guru' ? ($user->jabatan ?? 'Guru') : ('Kelas ' . ($user->kelas->nama_rombel ?? '-')),
                        'status' => $status,
                        'waktu' => $now->format('H:i:s') . ' WIB'
                    ]
                ]
            ];
        } else {
            if ($presensi->jam_pulang) {
                return [
                    'status_code' => 200,
                    'data' => [
                        'success' => false,
                        'already' => true,
                        'message' => "{$user->nama_lengkap} sudah menyelesaikan presensi datang & pulang kegiatan ini.",
                        'user' => [
                            'nama' => $user->nama_lengkap,
                            'detail' => $userType === 'guru' ? 'Guru' : 'Siswa',
                            'waktu' => $presensi->jam_pulang->format('H:i:s')
                        ]
                    ]
                ];
            }

            $presensi->update(['jam_pulang' => $now]);

            if ($kegiatan->kirim_notifikasi) {
                $this->kirimNotifikasiKehadiran($kegiatan, $user, $userType, $now, 'Hadir Pulang', 'Pulang');
            }

            return [
                'status_code' => 200,
                'data' => [
                    'success' => true,
                    'message' => "Absen Pulang Berhasil! Terima kasih, {$user->nama_lengkap}.",
                    'user' => [
                        'nama' => $user->nama_lengkap,
                        'tipe' => $userType === 'guru' ? 'Guru' : 'Siswa',
                        'detail' => $userType === 'guru' ? ($user->jabatan ?? 'Guru') : ('Kelas ' . ($user->kelas->nama_rombel ?? '-')),
                        'status' => 'Pulang Selesai',
                        'waktu' => $now->format('H:i:s') . ' WIB'
                    ]
                ]
            ];
        }
    }

    /**
     * Helper Pengiriman Notifikasi Otomatis (WhatsApp & Telegram)
     */
    public function kirimNotifikasiKehadiran($kegiatan, $user, $userType, $waktu, $status, $tipeScan = 'Masuk'): void
    {
        $lembaga = PengaturanLembaga::first();
        if ($userType === 'siswa') {
            $namaSekolah = $user->kelas?->departemen?->nama_departemen ?: ($lembaga->nama_lembaga ?? 'Sekolah');
        } else {
            $namaSekolah = $lembaga && !empty($lembaga->nama_yayasan) ? $lembaga->nama_yayasan : ($lembaga->nama_lembaga ?? 'Sekolah');
        }
        $tglIndo = Carbon::parse($kegiatan->tanggal ?? now())->locale('id')->translatedFormat('l, d F Y');
        $jamFmt = $waktu->format('H:i:s') . ' WIB';

        $detailPeserta = $userType === 'guru' 
            ? ($user->jabatan ?? 'Guru / Tendik') 
            : ('Kelas ' . ($user->kelas->nama_rombel ?? '-'));

        $nomorInduk = $userType === 'guru' ? ($user->nip ?? '-') : ($user->nis ?? '-');

        // WhatsApp Notification
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

        // Telegram Notification
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
}
