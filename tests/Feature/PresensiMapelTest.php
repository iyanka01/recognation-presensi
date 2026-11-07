<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PresensiMapelTest extends TestCase
{
    use RefreshDatabase;

    private function buatGuru(array $attr = []): Guru
    {
        return Guru::create(array_merge([
            'nama_lengkap' => 'Budi Guru',
            'status' => 'Aktif',
        ], $attr));
    }

    private function buatAkunGuru(array $attr = []): Guru
    {
        return $this->buatGuru(array_merge([
            'nip' => '1234567890',
            'username' => '1234567890',
            'password' => Hash::make('rahasia123'),
        ], $attr));
    }

    private function buatKelasDenganSiswa(): Kelas
    {
        $kelas = Kelas::create(['tingkat' => '10', 'nama_rombel' => 'AKL 1']);
        Siswa::create([
            'nis' => 'NIS001',
            'nama_lengkap' => 'Ani Siswa',
            'agama' => 'Islam',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-01-01',
            'kelas_id' => $kelas->id,
        ]);
        Siswa::create([
            'nis' => 'NIS002',
            'nama_lengkap' => 'Bani Siswa',
            'agama' => 'Islam',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-02-02',
            'kelas_id' => $kelas->id,
        ]);

        return $kelas;
    }

    private function buatJadwal(Guru $guru): JadwalPelajaran
    {
        $mapel = MataPelajaran::create(['kode_mapel' => 'MAT', 'nama_mapel' => 'Matematika']);

        return JadwalPelajaran::create([
            'kelas_id' => Kelas::first()->id,
            'mata_pelajaran_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'Senin',
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:30',
        ]);
    }

    // ==================== LOGIN GURU ====================

    public function test_guru_dapat_login_dan_melihat_dashboard_guru(): void
    {
        $this->buatAkunGuru();

        $response = $this->post('/login', [
            'username' => '1234567890',
            'password' => 'rahasia123',
            'role' => 'guru',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated('guru');

        $dashboard = $this->get('/dashboard');
        $dashboard->assertOk();
        $dashboard->assertSee('Dashboard Guru');
    }

    public function test_guru_nonaktif_tidak_dapat_login(): void
    {
        $this->buatAkunGuru(['status' => 'Nonaktif']);

        $response = $this->post('/login', [
            'username' => '1234567890',
            'password' => 'rahasia123',
            'role' => 'guru',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest('guru');
    }

    public function test_admin_dapat_login_dan_melihat_dashboard_admin(): void
    {
        AdminUser::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'nama_lengkap' => 'Admin Utama',
        ]);

        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'admin123',
            'role' => 'admin',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated('web');
    }

    // ==================== LOGOUT ====================

    public function test_guru_dapat_logout(): void
    {
        $this->buatAkunGuru();
        $this->post('/login', [
            'username' => '1234567890',
            'password' => 'rahasia123',
            'role' => 'guru',
        ]);
        $this->assertAuthenticated('guru');

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest('guru');
        $this->assertGuest('web');
    }

    public function test_admin_dapat_logout(): void
    {
        AdminUser::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'nama_lengkap' => 'Admin Utama',
        ]);
        $this->post('/login', [
            'username' => 'admin',
            'password' => 'admin123',
            'role' => 'admin',
        ]);
        $this->assertAuthenticated('web');

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest('web');
    }

    // ==================== AKSES ====================

    public function test_guest_diarahkan_ke_halaman_login(): void
    {
        $this->get('/mapel')->assertRedirect('/login');
        $this->get('/absensi-mapel')->assertRedirect('/login');
        $this->get('/rekap-mapel')->assertRedirect('/login');
    }

    public function test_guru_tidak_bisa_mengakses_menu_admin(): void
    {
        // Login via POST /login (bukan actingAs) agar default guard tidak berubah
        $this->buatAkunGuru();
        $this->post('/login', [
            'username' => '1234567890',
            'password' => 'rahasia123',
            'role' => 'guru',
        ])->assertRedirect(route('dashboard'));

        $this->get('/siswa')->assertRedirect('/login');
        $this->get('/mapel')->assertRedirect('/login');
    }

    // ==================== CRUD MAPEL ====================

    public function test_admin_bisa_crud_mata_pelajaran(): void
    {
        $admin = AdminUser::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'nama_lengkap' => 'Admin Utama',
        ]);
        $this->actingAs($admin, 'web');

        // Store
        $this->post('/mapel', [
            'kode_mapel' => 'IND',
            'nama_mapel' => 'Bahasa Indonesia',
        ])->assertRedirect()->assertSessionHas('success');

        $mapel = MataPelajaran::where('kode_mapel', 'IND')->first();
        $this->assertNotNull($mapel);

        // Update
        $this->put("/mapel/{$mapel->id}", [
            'kode_mapel' => 'IND',
            'nama_mapel' => 'B. Indonesia',
        ])->assertRedirect();

        $this->assertEquals('B. Indonesia', $mapel->fresh()->nama_mapel);

        // Destroy
        $this->delete("/mapel/{$mapel->id}")->assertRedirect();
        $this->assertNull(MataPelajaran::find($mapel->id));
    }

    public function test_mapel_yang_dipakai_jadwal_tidak_bisa_dihapus(): void
    {
        $admin = AdminUser::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'nama_lengkap' => 'Admin Utama',
        ]);
        $this->actingAs($admin, 'web');

        $guru = $this->buatAkunGuru();
        $this->buatKelasDenganSiswa();
        $jadwal = $this->buatJadwal($guru);

        $this->delete("/mapel/{$jadwal->mata_pelajaran_id}")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNotNull(MataPelajaran::find($jadwal->mata_pelajaran_id));
    }

    // ==================== JADWAL ====================

    public function test_admin_bisa_membuat_jadwal(): void
    {
        $admin = AdminUser::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'nama_lengkap' => 'Admin Utama',
        ]);
        $this->actingAs($admin, 'web');

        $guru = $this->buatAkunGuru();
        $kelas = $this->buatKelasDenganSiswa();
        $mapel = MataPelajaran::create(['kode_mapel' => 'IPA', 'nama_mapel' => 'IPA']);

        $this->post('/jadwal', [
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'Selasa',
            'jam_mulai' => '09:00',
            'jam_selesai' => '10:30',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseCount('jadwal_pelajaran', 1);
    }

    public function test_jadwal_bentrok_ditolak(): void
    {
        $admin = AdminUser::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'nama_lengkap' => 'Admin Utama',
        ]);
        $this->actingAs($admin, 'web');

        $guru = $this->buatAkunGuru();
        $kelas = $this->buatKelasDenganSiswa();
        $this->buatJadwal($guru); // Senin 07:00-08:30

        $mapel2 = MataPelajaran::create(['kode_mapel' => 'FIS', 'nama_mapel' => 'Fisika']);

        // Bentrok kelas: hari sama, jam tumpang tindih
        $this->post('/jadwal', [
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel2->id,
            'guru_id' => null,
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:30',
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseCount('jadwal_pelajaran', 1);
    }

    // ==================== ABSENSI MAPEL OLEH GURU ====================

    public function test_guru_bisa_input_absen_mata_pelajaran(): void
    {
        $guru = $this->buatAkunGuru();
        $this->actingAs($guru, 'guru');

        $kelas = $this->buatKelasDenganSiswa();
        $jadwal = $this->buatJadwal($guru);

        // Halaman daftar jadwal tampil
        $this->get('/absensi-mapel')->assertOk()->assertSee('Absensi Mata Pelajaran');

        // Form absensi tampil dengan daftar siswa
        $form = $this->get("/absensi-mapel/{$jadwal->id}");
        $form->assertOk();
        $form->assertSee('Ani Siswa');
        $form->assertSee('Bani Siswa');

        // Simpan absensi
        $tanggal = now()->toDateString();
        $this->post("/absensi-mapel/{$jadwal->id}", [
            'tanggal' => $tanggal,
            'jumlah_jam' => 2,
            'materi' => 'Persamaan Kuadrat',
            'materi_selanjutnya' => 'Fungsi Kuadrat',
            'keterangan_pembelajaran' => 'KBM berjalan kondusif',
            'absensi' => [
                ['siswa_id' => Kelas::first()->siswa[0]->id, 'status' => 'Hadir', 'keterangan' => null],
                ['siswa_id' => Kelas::first()->siswa[1]->id, 'status' => 'Sakit', 'keterangan' => 'Demam'],
            ],
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseCount('presensi_mapel', 2);
        $this->assertDatabaseHas('presensi_mapel', [
            'jadwal_id' => $jadwal->id,
            'status' => 'Sakit',
            'keterangan' => 'Demam',
        ]);

        // Jurnal mengajar ikut tersimpan
        $this->assertDatabaseHas('jurnal_mengajar', [
            'jadwal_id' => $jadwal->id,
            'tanggal' => $tanggal,
            'jumlah_jam' => 2,
            'materi' => 'Persamaan Kuadrat',
            'materi_selanjutnya' => 'Fungsi Kuadrat',
        ]);
    }

    public function test_input_absen_ditolak_tanpa_data_pembelajaran(): void
    {
        $guru = $this->buatAkunGuru();
        $this->actingAs($guru, 'guru');

        $this->buatKelasDenganSiswa();
        $jadwal = $this->buatJadwal($guru);

        // Tanpa jumlah_jam, materi, dan keterangan pembelajaran
        $this->from("/absensi-mapel/{$jadwal->id}")
            ->post("/absensi-mapel/{$jadwal->id}", [
                'tanggal' => now()->toDateString(),
                'materi_selanjutnya' => 'Fungsi Kuadrat',
                'absensi' => [
                    ['siswa_id' => Siswa::first()->id, 'status' => 'Hadir'],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['jumlah_jam', 'materi', 'keterangan_pembelajaran']);

        $this->assertDatabaseCount('presensi_mapel', 0);
        $this->assertDatabaseCount('jurnal_mengajar', 0);
    }

    public function test_absensi_bisa_diubah_tanpa_duplikat(): void
    {
        $guru = $this->buatAkunGuru();
        $this->actingAs($guru, 'guru');

        $this->buatKelasDenganSiswa();
        $jadwal = $this->buatJadwal($guru);
        $siswa = Kelas::first()->siswa[0];
        $tanggal = now()->toDateString();

        foreach (['Izin', 'Hadir'] as $status) {
            $this->post("/absensi-mapel/{$jadwal->id}", [
                'tanggal' => $tanggal,
                'jumlah_jam' => 2,
                'materi' => 'Trigonometri',
                'keterangan_pembelajaran' => 'KBM kondusif',
                'absensi' => [
                    ['siswa_id' => $siswa->id, 'status' => $status],
                ],
            ])->assertRedirect();
        }

        // updateOrCreate mencegah duplikasi (absensi & jurnal)
        $this->assertDatabaseCount('presensi_mapel', 1);
        $this->assertDatabaseCount('jurnal_mengajar', 1);
        $this->assertDatabaseHas('presensi_mapel', ['status' => 'Hadir']);
    }

    public function test_guru_tidak_bisa_akses_jadwal_guru_lain(): void
    {
        $this->buatAkunGuru(['nip' => '111', 'username' => '111', 'password' => Hash::make('x')]);
        $guruLain = $this->buatAkunGuru(['nip' => '222', 'username' => '222', 'password' => Hash::make('y')]);

        $this->actingAs($guruLain, 'guru');
        $this->buatKelasDenganSiswa();
        $jadwalMilikGuruPertama = $this->buatJadwal(Guru::where('username', '111')->first());

        $this->get("/absensi-mapel/{$jadwalMilikGuruPertama->id}")->assertForbidden();
        $this->post("/absensi-mapel/{$jadwalMilikGuruPertama->id}", [
            'tanggal' => now()->toDateString(),
            'absensi' => [['siswa_id' => Siswa::first()->id, 'status' => 'Hadir']],
        ])->assertForbidden();
    }

    // ==================== REKAP ====================

    public function test_rekap_absensi_mapel_tampil_untuk_admin(): void
    {
        $admin = AdminUser::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'nama_lengkap' => 'Admin Utama',
        ]);
        $this->actingAs($admin, 'web');

        $guru = $this->buatAkunGuru();
        $kelas = $this->buatKelasDenganSiswa();
        $jadwal = $this->buatJadwal($guru);
        [$ani, $bani] = Kelas::first()->siswa;
        $tanggal = now()->toDateString();

        \App\Models\PresensiMapel::create([
            'jadwal_id' => $jadwal->id, 'siswa_id' => $ani->id,
            'tanggal' => $tanggal, 'status' => 'Hadir',
        ]);
        \App\Models\PresensiMapel::create([
            'jadwal_id' => $jadwal->id, 'siswa_id' => $bani->id,
            'tanggal' => $tanggal, 'status' => 'Alpa',
        ]);

        $this->get("/rekap-mapel?kelas_id={$kelas->id}&dari={$tanggal}&sampai={$tanggal}")
            ->assertOk()
            ->assertSee('Ani Siswa')
            ->assertSee('100%')
            ->assertSee('0%');
    }

    // ==================== AUTO-GENERATE AKUN GURU ====================

    public function test_guru_baru_ber_nip_otomatis_punya_akun_login(): void
    {
        $admin = AdminUser::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'nama_lengkap' => 'Admin Utama',
        ]);
        $this->actingAs($admin, 'web');

        $this->post('/guru', [
            'nama_lengkap' => 'Citra Guru',
            'jenis_kelamin' => 'P',
            'agama' => 'Islam',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1995-05-05',
            'jabatan' => 'Guru Mapel',
            'nip' => '999888777',
        ])->assertRedirect();

        $guru = Guru::where('nip', '999888777')->first();
        $this->assertEquals('999888777', $guru->username);
        $this->assertNotNull($guru->password);

        // Password default bisa dipakai login
        $login = $this->post('/logout'); // pastikan sesi admin terputus dulu
        $login->assertRedirect('/login');

        $this->post('/login', [
            'username' => '999888777',
            'password' => 'guru123',
            'role' => 'guru',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated('guru');
    }
}
