<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $botToken;

    public function __construct()
    {
        $lembaga = \App\Models\PengaturanLembaga::first();
        $this->botToken = $lembaga ? $lembaga->telegram_bot_token : null;
    }

    /**
     * Send a message to a specific Telegram Chat ID
     *
     * @param string $chatId
     * @param string $message
     * @return bool
     */
    public function sendMessage($chatId, $message)
    {
        if (!$this->botToken || empty($chatId)) {
            return false;
        }

        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
            
            $response = Http::timeout(5)->post($url, [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            if (!$response->successful()) {
                Log::error('Telegram API Error: ' . $response->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Telegram Service Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent updates to find Chat IDs
     * 
     * @return array
     */
    public function getUpdates()
    {
        if (!$this->botToken) {
            return [];
        }

        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/getUpdates";
            
            $response = Http::timeout(5)->get($url);

            if (!$response->successful()) {
                Log::error('Telegram API Error (getUpdates): ' . $response->body());
                return [];
            }

            $data = $response->json();
            $results = $data['result'] ?? [];
            
            $users = [];
            foreach ($results as $update) {
                if (isset($update['message']['from'])) {
                    $from = $update['message']['from'];
                    $chatId = $from['id'];
                    $firstName = $from['first_name'] ?? '';
                    $lastName = $from['last_name'] ?? '';
                    $username = $from['username'] ?? '';
                    $text = $update['message']['text'] ?? '';
                    
                    $name = trim($firstName . ' ' . $lastName);
                    if (empty($name)) {
                        $name = $username;
                    }

                    // Keep only the latest message per chat ID
                    $users[$chatId] = [
                        'chat_id' => $chatId,
                        'name' => $name,
                        'text' => \Illuminate\Support\Str::limit($text, 30)
                    ];
                }
            }

            return array_values($users);
        } catch (\Exception $e) {
            Log::error('Telegram Service Exception (getUpdates): ' . $e->getMessage());
            return [];
        }
    }
}
