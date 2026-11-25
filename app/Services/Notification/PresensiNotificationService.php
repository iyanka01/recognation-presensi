<?php

namespace App\Services\Notification;

use App\Models\Guru;
use App\Models\PengaturanLembaga;
use App\Models\Siswa;
use App\Services\TelegramService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class PresensiNotificationService
{
    public function __construct(
        protected TelegramService $telegramService,
        protected WhatsAppService $whatsAppService
    ) {}

    public function sendMasukNotification(Siswa|Guru $user, string $userType, string $jamMasuk, string $status): void
    {
        $this->sendTelegramMasuk($user, $jamMasuk, $status);
        $this->sendWhatsAppMasuk($user, $userType, $jamMasuk, $status);
    }

    public function sendPulangNotification(Siswa|Guru $user, string $userType, string $jamPulang, string $status): void
    {
        $this->sendTelegramPulang($user, $jamPulang);
        $this->sendWhatsAppPulang($user, $userType, $jamPulang, $status);
    }

    protected function sendTelegramMasuk(Siswa|Guru $user, string $jamMasuk, string $status): void
    {
        if (empty($user->telegram_chat_id)) {
            return;
        }

        $msg = $status === 'Terlambat'
            ? "<b>⚠️ Laporan Absensi (Terlambat)</b>\n\n"
            : "<b>📢 Laporan Absensi Masuk</b>\n\n";

        $msg .= "<b>Nama:</b> {$user->nama_lengkap}\n";
        $msg .= "<b>Waktu:</b> {$jamMasuk} WIB\n";
        $msg .= "<b>Status:</b> {$status}\n\n";
        $msg .= $status === 'Terlambat'
            ? "<i>Mohon untuk datang lebih tepat waktu besok.</i>"
            : "<i>Semoga harinya menyenangkan!</i>";

        $this->telegramService->sendMessage($user->telegram_chat_id, $msg);
    }

    protected function sendTelegramPulang(Siswa|Guru $user, string $jamPulang): void
    {
        if (empty($user->telegram_chat_id)) {
            return;
        }

        $msg = "<b>📢 Laporan Absensi Pulang</b>\n\n";
        $msg .= "<b>Nama:</b> {$user->nama_lengkap}\n";
        $msg .= "<b>Waktu:</b> {$jamPulang} WIB\n";
        $msg .= "<b>Keterangan:</b> Telah melakukan absensi pulang.\n\n";
        $msg .= "<i>Terima kasih dan hati-hati di jalan.</i>";

        $this->telegramService->sendMessage($user->telegram_chat_id, $msg);
    }

    protected function sendWhatsAppMasuk(Siswa|Guru $user, string $userType, string $jamMasuk, string $status): void
    {
        $waNumber = $userType === 'siswa' ? ($user->no_hp_wali ?? null) : ($user->no_hp ?? null);
        if (empty($waNumber)) {
            return;
        }

        try {
            $namaSekolah = $this->resolveNamaSekolah($user, $userType);
            $jamFmt = substr($jamMasuk, 0, 5) . ' WIB';

            $waMsg = $status === 'Terlambat'
                ? "⚠️ *Laporan Presensi Masuk (Terlambat)*\n"
                : "*📢 Laporan Presensi Masuk*\n";

            $waMsg .= "*{$namaSekolah}*\n\n";
            $waMsg .= "Nama: *{$user->nama_lengkap}*\n";
            if ($userType === 'siswa' && $user->kelas) {
                $waMsg .= "Kelas: {$user->kelas->tingkat} {$user->kelas->nama_rombel}\n";
            }
            $waMsg .= "Waktu Masuk: {$jamFmt}\n";
            $waMsg .= "Status: *{$status}*\n\n";
            $waMsg .= $status === 'Terlambat'
                ? "_Telah melakukan presensi masuk (Terlambat). Mohon datang lebih tepat waktu besok._"
                : "_Telah hadir dan melakukan presensi masuk tepat waktu._";

            $this->whatsAppService->sendMessage($waNumber, $waMsg);
        } catch (\Throwable $e) {
            Log::error('WA Masuk Notification Error: ' . $e->getMessage());
        }
    }

    protected function sendWhatsAppPulang(Siswa|Guru $user, string $userType, string $jamPulang, string $status): void
    {
        $waNumber = $userType === 'siswa' ? ($user->no_hp_wali ?? null) : ($user->no_hp ?? null);
        if (empty($waNumber)) {
            return;
        }

        try {
            $namaSekolah = $this->resolveNamaSekolah($user, $userType);
            $jamFmt = substr($jamPulang, 0, 5) . ' WIB';

            $waMsg = "*📢 Laporan Presensi Pulang*\n";
            $waMsg .= "*{$namaSekolah}*\n\n";
            $waMsg .= "Nama: *{$user->nama_lengkap}*\n";
            if ($userType === 'siswa' && $user->kelas) {
                $waMsg .= "Kelas: {$user->kelas->tingkat} {$user->kelas->nama_rombel}\n";
            }
            $waMsg .= "Waktu Pulang: {$jamFmt}\n";
            $waMsg .= "Status: {$status}\n\n";
            $waMsg .= "_Telah melakukan presensi kepulangan. Terima kasih dan hati-hati di perjalanan._";

            $this->whatsAppService->sendMessage($waNumber, $waMsg);
        } catch (\Throwable $e) {
            Log::error('WA Pulang Notification Error: ' . $e->getMessage());
        }
    }

    protected function resolveNamaSekolah(Siswa|Guru $user, string $userType): string
    {
        $lembaga = PengaturanLembaga::first();

        if ($userType === 'siswa') {
            return $user->kelas?->departemen?->nama_departemen ?: ($lembaga->nama_lembaga ?? 'Sekolah');
        }

        return ($lembaga && !empty($lembaga->nama_yayasan))
            ? $lembaga->nama_yayasan
            : ($lembaga->nama_lembaga ?? 'Sekolah');
    }
}
