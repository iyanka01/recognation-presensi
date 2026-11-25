<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Guru;
use App\Models\SecurityAuditLog;
use App\Models\Siswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_present_on_all_responses(): void
    {
        $response = $this->get('/login');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString('camera=(self)', $response->headers->get('Permissions-Policy'));
    }

    public function test_dev_routes_init_admin_and_cek_are_not_accessible(): void
    {
        $this->get('/init-admin')->assertNotFound();
        $this->get('/cek')->assertNotFound();
    }

    public function test_storage_fallback_rejects_path_traversal(): void
    {
        // Up-directory traversal attempt (rejected with 403 Forbidden or 404 Not Found)
        $response = $this->get('/storage/subfolder/../../../../etc/passwd');
        $this->assertTrue(in_array($response->status(), [403, 404]));

        // Direct non-existent file in storage
        $response404 = $this->get('/storage/non-existent-secret-file.txt');
        $response404->assertNotFound();
    }

    public function test_migration_api_requires_valid_bearer_token(): void
    {
        // Tanpa token -> 403 Ditolak
        $unauthorized = $this->getJson('/api/export-migrasi-sekolah');
        $unauthorized->assertStatus(403);
        $unauthorized->assertJson(['success' => false]);

        // Token salah -> 403 Ditolak
        $invalidToken = $this->withToken('wrong-secret-token')->getJson('/api/export-migrasi-sekolah');
        $invalidToken->assertStatus(403);

        // Token benar -> 200 Sukses
        $validToken = config('services.migration.token', env('MIGRATION_API_TOKEN', 'mayantara-migrasi-2026'));
        $authorized = $this->withToken($validToken)->getJson('/api/export-migrasi-sekolah');
        $authorized->assertOk();
        $authorized->assertJsonStructure(['success', 'timestamp', 'data']);

        // Password hash siswa dan guru tidak diekspos
        $json = $authorized->json('data');
        if (!empty($json['guru'])) {
            $this->assertArrayNotHasKey('password', $json['guru'][0]);
        }
        if (!empty($json['siswa'])) {
            $this->assertArrayNotHasKey('password', $json['siswa'][0]);
        }
    }

    public function test_plaintext_password_fallback_is_completely_rejected(): void
    {
        $siswa = Siswa::create([
            'id' => (string) Str::uuid(),
            'nis' => '999901',
            'nisn' => '00999901',
            'nama_lengkap' => 'Siswa Test',
            'jenis_kelamin' => 'L',
            'agama' => 'Islam',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'password' => 'siswa123', // plaintext di DB (legacy tidak ter-hash)
        ]);

        // Login dengan password 'siswa123' harus ditolak karena password di DB belum di-hash dengan Bcrypt
        $response = $this->post('/siswa/login', [
            'username' => '999901',
            'password' => 'siswa123',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest('siswa');
    }

    public function test_login_success_and_failure_are_recorded_in_security_audit_logs(): void
    {
        $admin = AdminUser::create([
            'username' => 'secadmin',
            'password' => Hash::make('SuperSecret123!'),
            'nama_lengkap' => 'Security Admin',
        ]);

        // 1. Percobaan login gagal
        $this->post('/admin/login', [
            'username' => 'secadmin',
            'password' => 'WrongPassword',
        ]);
        $this->assertGuest('web');

        $this->assertDatabaseHas('security_audit_logs', [
            'event' => 'auth.login.failed',
            'username' => 'secadmin',
            'user_type' => 'admin',
        ]);

        // 2. Percobaan login sukses
        $this->post('/admin/login', [
            'username' => 'secadmin',
            'password' => 'SuperSecret123!',
        ]);
        $this->assertAuthenticated('web');

        $this->assertDatabaseHas('security_audit_logs', [
            'event' => 'auth.login.success',
            'username' => 'secadmin',
            'user_type' => 'admin',
        ]);
    }

    public function test_direct_get_logout_does_not_log_out_user(): void
    {
        $admin = AdminUser::create([
            'username' => 'activeadmin',
            'password' => Hash::make('password123'),
            'nama_lengkap' => 'Active Admin',
        ]);
        $this->actingAs($admin, 'web');

        // GET request ke logout tidak boleh memutus sesi (hanya redirect)
        $this->get('/logout')->assertRedirect('/login');
        $this->assertAuthenticated('web');

        // POST request sah memutus sesi
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest('web');
    }
}
