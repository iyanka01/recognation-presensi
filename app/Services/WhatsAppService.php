<?php

namespace App\Services;

use App\Models\PengaturanLembaga;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $token;
    protected $isActive;
    protected $endpoint = 'https://wanesia.com/api/send_express';

    public function __construct()
    {
        $lembaga = PengaturanLembaga::first();
        $this->token = $lembaga ? $lembaga->wanesia_token : null;
        $this->isActive = $lembaga ? (bool) $lembaga->wanesia_active : false;
    }

    /**
     * Format nomor HP Indonesia menjadi standar internasional (628xxx)
     *
     * @param string $phone
     * @return string
     */
    public static function formatNumber($phone)
    {
        if (empty($phone)) return '';

        // Hapus semua karakter selain angka
        $clean = preg_replace('/[^0-9]/', '', $phone);

        // Jika diawali 08, ubah jadi 628
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        }
        // Jika diawali 8, tambahkan 62
        elseif (str_starts_with($clean, '8')) {
            $clean = '62' . $clean;
        }

        return $clean;
    }

    /**
     * Kirim pesan WhatsApp melalui API Wanesia Express
     *
     * @param string $number Nomor tujuan (format 08xx atau 628xx)
     * @param string $message Isi pesan
     * @param string|null $customToken Token opsional (untuk fitur tes)
     * @return array
     */
    public function sendMessage($number, $message, $customToken = null)
    {
        $token = $customToken ?: $this->token;

        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Token API Wanesia belum diatur pada Pengaturan Lembaga.'
            ];
        }

        if (!$customToken && !$this->isActive) {
            return [
                'success' => false,
                'message' => 'Layanan WhatsApp Gateway Wanesia saat ini sedang dinonaktifkan di pengaturan.'
            ];
        }

        $formattedNumber = self::formatNumber($number);
        if (empty($formattedNumber) || strlen($formattedNumber) < 9) {
            return [
                'success' => false,
                'message' => "Nomor telepon '{$number}' tidak valid."
            ];
        }

        try {
            $response = Http::timeout(10)->asForm()->post($this->endpoint, [
                'token'   => $token,
                'number'  => $formattedNumber,
                'message' => $message,
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['result']) && ($result['result'] === true || $result['result'] === 'true')) {
                return [
                    'success' => true,
                    'message' => $result['message'] ?? 'Pesan WhatsApp berhasil dikirim!',
                    'data'    => $result,
                ];
            }

            Log::warning('Wanesia Send Failed: ' . $response->body());
            return [
                'success' => false,
                'message' => $result['message'] ?? 'Gagal mengirim pesan via Wanesia (' . $response->status() . ').',
                'raw'     => $result,
            ];
        } catch (\Throwable $e) {
            Log::error('Wanesia Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Koneksi error: ' . $e->getMessage(),
            ];
        }
    }
}
