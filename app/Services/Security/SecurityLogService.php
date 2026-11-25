<?php

namespace App\Services\Security;

use App\Models\SecurityAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SecurityLogService
{
    /**
     * Log a security-related event.
     *
     * @param string $event Event identifier (e.g. auth.login.success, auth.login.failed, backup.create)
     * @param string|null $userType Role or guard (admin, guru, siswa, system, anonymous)
     * @param string|null $userId User identifier
     * @param string|null $username Username, NIP, or NIS
     * @param array $details Contextual details (sanitized, never passwords or tokens)
     * @param Request|null $request
     */
    public static function log(
        string $event,
        ?string $userType = null,
        ?string $userId = null,
        ?string $username = null,
        array $details = [],
        ?Request $request = null
    ): void {
        try {
            $req = $request ?: request();
            $ip = $req ? $req->ip() : null;
            $userAgent = $req ? substr((string) $req->userAgent(), 0, 500) : null;

            // Pastikan data sensitif tidak pernah tercatat di audit log
            unset($details['password'], $details['password_confirmation'], $details['token'], $details['api_token']);

            SecurityAuditLog::create([
                'event'      => $event,
                'user_type'  => $userType,
                'user_id'    => $userId ? (string) $userId : null,
                'username'   => $username,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'details'    => !empty($details) ? $details : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Fail safely: Log to Laravel file logger so security log failure does not break the user flow
            Log::error('Gagal mencatat security audit log: ' . $e->getMessage(), [
                'event' => $event,
                'user' => $username,
            ]);
        }
    }
}
