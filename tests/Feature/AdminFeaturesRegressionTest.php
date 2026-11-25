<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Departemen;
use App\Models\Guru;
use App\Models\HariLibur;
use App\Models\Jabatan;
use App\Models\Kelas;
use App\Models\PengaturanJam;
use App\Models\PengaturanLembaga;
use App\Models\Pengumuman;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminFeaturesRegressionTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = AdminUser::create([
            'username' => 'admin_test',
            'password' => Hash::make('password123'),
            'nama_lengkap' => 'Admin Regression',
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_crud_departemen(): void
    {
        $this->actingAs($this->admin, 'web');

        // 1. Index
        $this->get('/departemen')->assertOk()->assertSee('Departemen');

        // 2. Store
        $response = $this->post('/departemen', [
            'kode_departemen' => 'SMK',
            'nama_departemen' => 'SMK Mayantara',
            'tingkatan' => 'SMK',
            'status' => 'Aktif',
        ]);
        $response->assertRedirect('/departemen');
        $this->assertDatabaseHas('departemen', ['kode_departemen' => 'SMK']);

        $dept = Departemen::where('kode_departemen', 'SMK')->first();

        // 3. Update
        $this->put("/departemen/{$dept->id}", [
            'kode_departemen' => 'SMK',
            'nama_departemen' => 'SMK Mayantara Unggul',
            'tingkatan' => 'SMK',
            'status' => 'Aktif',
        ])->assertRedirect('/departemen');
        $this->assertDatabaseHas('departemen', ['nama_departemen' => 'SMK Mayantara Unggul']);

        // 4. Destroy
        $this->delete("/departemen/{$dept->id}")->assertRedirect('/departemen');
        $this->assertDatabaseMissing('departemen', ['id' => $dept->id]);
    }

    public function test_admin_can_crud_tahun_ajaran(): void
    {
        $this->actingAs($this->admin, 'web');

        $this->get('/tahun-ajaran')->assertOk();

        // Store
        $this->post('/tahun-ajaran', [
            'tahun_ajaran' => '2026/2027',
            'tanggal_mulai' => '2026-07-15',
            'tanggal_akhir' => '2026-12-20',
            'status' => 'Aktif',
        ])->assertRedirect('/tahun-ajaran');

        $this->assertDatabaseHas('tahun_ajaran', ['tahun_ajaran' => '2026/2027']);
        $ta = TahunAjaran::where('tahun_ajaran', '2026/2027')->first();

        // Update
        $this->put("/tahun-ajaran/{$ta->id}", [
            'tahun_ajaran' => '2026/2027 - Genap',
            'tanggal_mulai' => '2027-01-10',
            'tanggal_akhir' => '2027-06-20',
            'status' => 'Aktif',
        ])->assertRedirect('/tahun-ajaran');

        $this->assertDatabaseHas('tahun_ajaran', ['tahun_ajaran' => '2026/2027 - Genap']);

        // Delete
        $this->delete("/tahun-ajaran/{$ta->id}")->assertRedirect('/tahun-ajaran');
        $this->assertDatabaseMissing('tahun_ajaran', ['id' => $ta->id]);
    }

    public function test_admin_can_crud_jabatan(): void
    {
        $this->actingAs($this->admin, 'web');

        $this->get('/jabatan')->assertOk();

        // Store
        $this->post('/jabatan', [
            'nama_jabatan' => 'Guru Pengajar',
            'kategori' => 'guru',
            'keterangan' => 'Staff Pengajar Reguler',
        ])->assertRedirect('/jabatan');

        $this->assertDatabaseHas('jabatans', ['nama_jabatan' => 'Guru Pengajar']);
        $jabatan = Jabatan::where('nama_jabatan', 'Guru Pengajar')->first();

        // Update
        $this->put("/jabatan/{$jabatan->id}", [
            'nama_jabatan' => 'Guru Senior',
            'kategori' => 'guru',
            'keterangan' => 'Staff Pengajar Senior',
        ])->assertRedirect('/jabatan');

        $this->assertDatabaseHas('jabatans', ['nama_jabatan' => 'Guru Senior']);

        // Delete
        $this->delete("/jabatan/{$jabatan->id}")->assertRedirect('/jabatan');
        $this->assertDatabaseMissing('jabatans', ['id' => $jabatan->id]);
    }

    public function test_admin_can_crud_kelas(): void
    {
        $this->actingAs($this->admin, 'web');

        $dept = Departemen::create([
            'kode_departemen' => 'SMA',
            'nama_departemen' => 'SMA Mayantara',
            'tingkatan' => 'SMA',
            'status' => 'Aktif',
        ]);

        $this->get('/kelas')->assertOk();

        // Store
        $this->post('/kelas', [
            'departemen_id' => $dept->id,
            'tingkat' => '10',
            'nama_rombel' => ['X IPA 1'],
        ])->assertRedirect('/kelas?departemen_id=' . $dept->id);

        $this->assertDatabaseHas('kelas', ['nama_rombel' => 'X IPA 1']);
        $kelas = Kelas::where('nama_rombel', 'X IPA 1')->first();

        // Update
        $this->put("/kelas/{$kelas->id}", [
            'departemen_id' => $dept->id,
            'tingkat' => '10',
            'nama_rombel' => 'X MIPA 1',
        ])->assertRedirect('/kelas?departemen_id=' . $dept->id);

        $this->assertDatabaseHas('kelas', ['nama_rombel' => 'X MIPA 1']);

        // Delete
        $this->delete("/kelas/{$kelas->id}")->assertRedirect('/kelas');
        $this->assertDatabaseMissing('kelas', ['id' => $kelas->id]);
    }

    public function test_admin_can_crud_siswa_and_bulk_delete(): void
    {
        $this->actingAs($this->admin, 'web');

        $kelas = Kelas::create([
            'tingkat' => '10',
            'nama_rombel' => 'X RPL',
        ]);

        // 1. Store Siswa
        $this->post('/siswa', [
            'nis' => '1001',
            'nisn' => '001001',
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'agama' => 'Islam',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '2009-05-10',
            'kelas_id' => $kelas->id,
        ])->assertRedirect('/siswa');

        $this->assertDatabaseHas('siswa', ['nis' => '1001']);
        $siswa = Siswa::where('nis', '1001')->first();

        // 2. Update Siswa
        $this->put("/siswa/{$siswa->id}", [
            'nis' => '1001',
            'nisn' => '001001',
            'nama_lengkap' => 'Budi Santoso Pratama',
            'jenis_kelamin' => 'L',
            'agama' => 'Islam',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '2009-05-10',
            'kelas_id' => $kelas->id,
            'status' => 'Aktif',
        ])->assertRedirect('/siswa');

        $this->assertDatabaseHas('siswa', ['nama_lengkap' => 'Budi Santoso Pratama']);

        // 3. Bulk Delete Siswa
        $this->post('/siswa/bulk-delete', [
            'ids' => [$siswa->id],
        ])->assertRedirect('/siswa');

        $this->assertDatabaseMissing('siswa', ['id' => $siswa->id]);
    }

    public function test_admin_can_crud_guru(): void
    {
        $this->actingAs($this->admin, 'web');

        // Store Guru
        $this->post('/guru', [
            'nip' => '198501012010011001',
            'nama_lengkap' => 'Drs. Ahmad Dahlan',
            'jenis_kelamin' => 'L',
            'agama' => 'Islam',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '1985-01-01',
            'jabatan' => 'Guru',
            'no_hp' => '081234567890',
        ])->assertRedirect('/guru');

        $this->assertDatabaseHas('guru', ['nip' => '198501012010011001']);
        $guru = Guru::where('nip', '198501012010011001')->first();

        // Update Guru
        $this->put("/guru/{$guru->id}", [
            'nip' => '198501012010011001',
            'nama_lengkap' => 'Dr. Ahmad Dahlan, M.Pd',
            'jenis_kelamin' => 'L',
            'agama' => 'Islam',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '1985-01-01',
            'jabatan' => 'Guru',
            'no_hp' => '081234567899',
            'status' => 'Aktif',
        ])->assertRedirect('/guru');

        $this->assertDatabaseHas('guru', ['nama_lengkap' => 'Dr. Ahmad Dahlan, M.Pd']);

        // Delete Guru
        $this->delete("/guru/{$guru->id}")->assertRedirect('/guru');
        $this->assertDatabaseMissing('guru', ['id' => $guru->id]);
    }

    public function test_admin_can_crud_hari_libur(): void
    {
        $this->actingAs($this->admin, 'web');

        $this->get('/hari-libur')->assertOk();

        // Store
        $this->post('/hari-libur', [
            'tanggal' => '2026-08-17',
            'keterangan' => 'HUT Kemerdekaan RI',
        ])->assertRedirect('/hari-libur');

        $this->assertDatabaseHas('hari_liburs', ['tanggal' => '2026-08-17']);
        $libur = HariLibur::where('tanggal', '2026-08-17')->first();

        // Delete
        $this->delete("/hari-libur/{$libur->id}")->assertRedirect('/hari-libur');
        $this->assertDatabaseMissing('hari_liburs', ['id' => $libur->id]);
    }

    public function test_admin_can_manage_pengaturan_jam(): void
    {
        $this->actingAs($this->admin, 'web');

        $this->get('/jam-absensi')->assertOk();

        // Store
        $this->post('/jam-absensi', [
            'target_id' => 'guru',
            'hari' => 'Senin',
            'jam_mulai' => '06:30',
            'jam_terlambat' => '07:15',
            'jam_pulang' => '15:00',
        ])->assertRedirect('/jam-absensi');

        $this->assertDatabaseHas('pengaturan_jams', [
            'hari' => 'Senin',
            'tipe' => 'guru',
        ]);
    }

    public function test_admin_can_crud_pengumuman(): void
    {
        $this->actingAs($this->admin, 'web');

        $this->get('/pengumuman')->assertOk();

        // Store
        $this->post('/pengumuman', [
            'judul' => 'Rapat Awal Tahun',
            'konten' => 'Diharapkan seluruh staf menghadiri rapat.',
            'kategori' => 'Penting',
            'target' => 'semua',
        ])->assertRedirect('/pengumuman');

        $this->assertDatabaseHas('pengumuman', ['judul' => 'Rapat Awal Tahun']);
        $pengumuman = Pengumuman::where('judul', 'Rapat Awal Tahun')->first();

        // Update
        $this->put("/pengumuman/{$pengumuman->id}", [
            'judul' => 'Rapat Awal Tahun Ajaran 2026',
            'konten' => 'Diharapkan seluruh staf menghadiri rapat di aula.',
            'kategori' => 'Penting',
            'target' => 'guru',
        ])->assertRedirect('/pengumuman');

        $this->assertDatabaseHas('pengumuman', ['judul' => 'Rapat Awal Tahun Ajaran 2026']);

        // Delete
        $this->delete("/pengumuman/{$pengumuman->id}")->assertRedirect('/pengumuman');
        $this->assertDatabaseMissing('pengumuman', ['id' => $pengumuman->id]);
    }

    public function test_admin_can_save_pengaturan_lembaga(): void
    {
        $this->actingAs($this->admin, 'web');

        $this->get('/pengaturan-lembaga')->assertOk();

        $this->post('/pengaturan-lembaga', [
            'nama_yayasan' => 'Yayasan Pendidikan Mayantara',
            'nama_lembaga' => 'SMA Mayantara Unggul',
            'akreditasi' => 'A',
            'no_telp' => '021-12345678',
            'alamat' => 'Jl. Pendidikan No. 1',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius_meter' => 150,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('pengaturan_lembagas', [
            'nama_lembaga' => 'SMA Mayantara Unggul',
            'radius_meter' => 150,
        ]);
    }

    public function test_admin_can_create_and_delete_backup(): void
    {
        $this->actingAs($this->admin, 'web');

        $this->get('/backup')->assertOk();

        // Create backup via POST
        $this->post('/backup/create')->assertRedirect()->assertSessionHas('success');

        $backups = File::files(storage_path('app/backups'));
        if (!empty($backups)) {
            $latest = basename(end($backups));

            // Download backup
            $this->get("/backup/download/{$latest}")->assertOk();

            // Delete backup via DELETE
            $this->delete("/backup/delete/{$latest}")->assertRedirect('/backup');
        }
    }
}
