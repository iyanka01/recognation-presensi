<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TelegramService;
use App\Models\Siswa;
use App\Models\Guru;
use Illuminate\Support\Str;

class TelegramController extends Controller
{
    public function fetchUpdates(TelegramService $telegram)
    {
        $updates = $telegram->getUpdates();

        return response()->json([
            'success' => true,
            'data' => $updates
        ]);
    }

    public function syncBulk(Request $request, TelegramService $telegram)
    {
        $type = $request->input('type'); // 'siswa' or 'guru'
        $updates = $telegram->getUpdates();
        
        $syncedCount = 0;
        
        foreach ($updates as $update) {
            $text = $update['text'] ?? '';
            $chatId = $update['chat_id'] ?? null;
            
            if (!$chatId || empty($text)) continue;

            // Check if text matches format /sync <number>
            if (preg_match('/^\/sync\s+(.+)$/i', trim($text), $matches)) {
                $identifier = trim($matches[1]);
                
                // clean phone number if they provided one
                // remove spaces, dashes, replace +62 or 62 with 0
                $cleanPhone = preg_replace('/[^0-9]/', '', $identifier);
                if (str_starts_with($cleanPhone, '62')) {
                    $cleanPhone = '0' . substr($cleanPhone, 2);
                }

                if ($type === 'siswa') {
                    // Search by NIS or cleanPhone
                    $siswa = Siswa::where('nis', $identifier);
                    if (!empty($cleanPhone)) {
                        $siswa = $siswa->orWhere('no_hp_wali', 'LIKE', '%' . $cleanPhone . '%');
                    }
                    $siswa = $siswa->first();

                    if ($siswa) {
                        if ($siswa->telegram_chat_id !== (string)$chatId) {
                            $siswa->telegram_chat_id = (string)$chatId;
                            $siswa->save();
                            $telegram->sendMessage($chatId, "✅ Berhasil! Nomor Telegram ini telah dihubungkan dengan data siswa atas nama: *" . $siswa->nama_lengkap . "*.");
                        }
                        $syncedCount++;
                    }
                } elseif ($type === 'guru') {
                    // Search by NIP or cleanPhone
                    $guru = Guru::where('nip', $identifier);
                    if (!empty($cleanPhone)) {
                        $guru = $guru->orWhere('no_hp', 'LIKE', '%' . $cleanPhone . '%');
                    }
                    $guru = $guru->first();

                    if ($guru) {
                        if ($guru->telegram_chat_id !== (string)$chatId) {
                            $guru->telegram_chat_id = (string)$chatId;
                            $guru->save();
                            $telegram->sendMessage($chatId, "✅ Berhasil! Nomor Telegram ini telah dihubungkan dengan data guru atas nama: *" . $guru->nama_lengkap . "*.");
                        }
                        $syncedCount++;
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil menyinkronkan {$syncedCount} data Telegram baru."
        ]);
    }
}
