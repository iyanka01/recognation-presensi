<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\PengaturanJam;
use App\Models\PengaturanLembaga;
use App\Models\Presensi;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SiswaAndGuruRegressionTest extends TestCase
{
    use RefreshDatabase;

    private Siswa $siswa;
    private Guru $guru;
    private Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kelas = Kelas::create([
            'tingkat' => '10',
            'nama_rombel' => 'X MIPA 1',
        ]);

        $this->siswa = Siswa::create([
            'id' => (string) Str::uuid(),
            'nis' => '888801',
            'nisn' => '00888801',
            'nama_lengkap' => 'Siti Nurhaliza',
            'jenis_kelamin' => 'P',
            'agama' => 'Islam',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '2009-08-15',
            'kelas_id' => $this->kelas->id,
            'password' => Hash::make('siswa12345'),
            'status' => 'Aktif',
        ]);

        $this->guru = Guru::create([
            'id' => (string) Str::uuid(),
            'nip' => '199001012015011002',
            'nama_lengkap' => 'Fajar Nugraha, S.Pd',
            'jenis_kelamin' => 'L',
            'agama' => 'Islam',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '1990-01-01',
            'jabatan' => 'Guru',
            'status' => 'Aktif',
            'username' => 'fajar_guru',
            'password' => Hash::make('guru12345'),
            'face_descriptor' => json_encode(array_fill(0, 128, 0.1)),
        ]);

        PengaturanLembaga::create([
            'nama_yayasan' => 'Yayasan Mayantara',
            'nama_lembaga' => 'SMK Mayantara',
            'nama_kepala'  => 'Drs. H. Mulyadi',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius_meter' => 500,
        ]);
    }

    // ==================== SISWA PORTAL REGRESSION ====================

    public function test_siswa_can_login_and_view_dashboard(): void
    {
        // 1. Wrong password rejected
        $this->post('/siswa/login', [
            'username' => '888801',
            'password' => 'wrongpass',
        ])->assertSessionHasErrors('password');
        $this->assertGuest('siswa');

        // 2. Correct password success
        $this->post('/siswa/login', [
            'username' => '888801',
            'password' => 'siswa12345',
        ])->assertRedirect(route('siswa.dashboard'));
        $this->assertAuthenticated('siswa');

        // 3. View dashboard
        $this->get('/siswa/dashboard')->assertOk()->assertSee('Siti Nurhaliza');
    }

    public function test_siswa_can_view_presensi_history_and_print(): void
    {
        $this->actingAs($this->siswa, 'siswa');

        $this->get('/siswa/presensi')->assertOk();
        $this->get('/siswa/presensi/cetak')->assertOk();
        $this->get('/siswa/kartu-nama')->assertOk()->assertSee('Siti Nurhaliza');
    }

    public function test_siswa_can_apply_izin_with_validation(): void
    {
        $this->actingAs($this->siswa, 'siswa');

        $this->get('/siswa/izin')->assertOk();

        // 1. Invalid date range > 30 days
        $this->post('/siswa/izin', [
            'status' => 'Izin',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-03-01',
            'keterangan' => 'Keperluan keluarga lama',
        ])->assertRedirect()->assertSessionHas('error');

        // 2. Valid izin
        $this->post('/siswa/izin', [
            'status' => 'Izin',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'keterangan' => 'Acara keluarga di luar kota',
        ])->assertRedirect(route('siswa.izin'))->assertSessionHas('success');

        $this->assertDatabaseHas('presensi', [
            'user_type' => 'siswa',
            'user_id' => $this->siswa->id,
            'status' => 'Izin',
        ]);
    }

    public function test_siswa_can_update_password_and_photo(): void
    {
        $this->actingAs($this->siswa, 'siswa');

        // 1. Wrong current password rejected
        $this->put('/siswa/password', [
            'current_password' => 'wrongpass',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertSessionHasErrors('current_password');

        // 2. Correct current password updates password
        $this->put('/siswa/password', [
            'current_password' => 'siswa12345',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertRedirect(route('siswa.dashboard'))->assertSessionHas('success');

        $this->siswa->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->siswa->password));

        // 3. Update photo
        $fakePhoto = UploadedFile::fake()->image('profil.jpg', 200, 200);
        $this->post('/siswa/foto', [
            'foto_siswa' => $fakePhoto,
        ])->assertRedirect()->assertSessionHas('success');

        $this->siswa->refresh();
        $this->assertNotNull($this->siswa->foto_siswa);
    }

    // ==================== GURU PORTAL REGRESSION ====================

    public function test_guru_can_view_dashboard_and_common_pages(): void
    {
        $this->actingAs($this->guru, 'guru');

        $this->get('/dashboard')->assertOk()->assertSee('Fajar Nugraha');
        $this->get('/monitoring-kbm')->assertOk();
        $this->get('/rekap-mapel')->assertOk();
        $this->get('/guru/jadwal')->assertOk();
        $this->get('/guru/informasi')->assertOk();
    }

    public function test_guru_can_apply_izin_and_filter(): void
    {
        $this->actingAs($this->guru, 'guru');

        $this->get('/guru/izin')->assertOk();

        $this->post('/guru/izin', [
            'status' => 'Sakit',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
            'keterangan' => 'Demam tinggi dan istirahat dokter',
        ])->assertRedirect(route('guru.izin.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('presensi', [
            'user_type' => 'guru',
            'user_id' => $this->guru->id,
            'status' => 'Sakit',
        ]);
    }

    public function test_guru_can_update_password(): void
    {
        $this->actingAs($this->guru, 'guru');

        // 1. Wrong current password rejected
        $this->put('/guru/password', [
            'current_password' => 'wrongpass',
            'password' => 'newpassword999',
            'password_confirmation' => 'newpassword999',
        ])->assertSessionHasErrors('current_password');

        // 2. Correct current password
        $this->put('/guru/password', [
            'current_password' => 'guru12345',
            'password' => 'newpassword999',
            'password_confirmation' => 'newpassword999',
        ])->assertRedirect(route('dashboard'))->assertSessionHas('success');

        $this->guru->refresh();
        $this->assertTrue(Hash::check('newpassword999', $this->guru->password));
    }

    public function test_guru_manual_checkin_validates_schedule_and_geofence(): void
    {
        $this->actingAs($this->guru, 'guru');

        $hariIni = Carbon::now()->locale('id')->dayName;

        // 1. Tanpa jadwal jam absensi -> ditolak
        $responseNoSchedule = $this->postJson('/absen-manual', [
            'tipe' => 'masuk',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ]);
        $responseNoSchedule->assertOk();
        $this->assertFalse($responseNoSchedule->json('success'));

        // 2. Buat jadwal jam absensi
        PengaturanJam::create([
            'hari' => $hariIni,
            'tipe' => 'guru',
            'jam_mulai' => '05:00',
            'jam_terlambat' => '07:30',
            'jam_pulang' => '23:59',
        ]);

        // 3. Check-in di luar radius sekolah (misal koordinat di Bandung saat sekolah di Jakarta)
        $responseOutside = $this->postJson('/absen-manual', [
            'tipe' => 'masuk',
            'latitude' => -6.914744,
            'longitude' => 107.609810,
        ]);
        $responseOutside->assertOk();
        $this->assertFalse($responseOutside->json('success'));

        // 4. Check-in di dalam radius sekolah -> Sukses
        $responseSuccess = $this->postJson('/absen-manual', [
            'tipe' => 'masuk',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ]);
        $responseSuccess->assertOk();
        $this->assertTrue($responseSuccess->json('success'));

        $this->assertDatabaseHas('presensi', [
            'user_type' => 'guru',
            'user_id' => $this->guru->id,
            'tanggal' => Carbon::today()->toDateString(),
        ]);
    }
}
