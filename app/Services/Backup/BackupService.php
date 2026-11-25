<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Ifsnop\Mysqldump\Mysqldump;
use Exception;
use Throwable;
use App\Models\Departemen;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\MataPelajaran;
use App\Models\JadwalPelajaran;
use App\Models\Presensi;
use App\Models\PresensiMapel;
use App\Models\JurnalMengajar;
use App\Models\PengaturanJam;

class BackupService
{
    protected string $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    /**
     * Return an array of backup file information for the index view.
     * Each element contains: name, size, date, timestamp.
     */
    public function listBackups(): array
    {
        $files = File::files($this->backupPath);
        $backups = [];
        foreach ($files as $file) {
            if ($file->getExtension() === 'sql') {
                $backups[] = [
                    'name'      => $file->getFilename(),
                    'size'      => round($file->getSize() / 1024, 2) . ' KB',
                    'date'      => date('Y-m-d H:i:s', $file->getMTime()),
                    'timestamp' => $file->getMTime(),
                ];
            }
        }
        usort($backups, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
        return $backups;
    }

    /**
     * Create a new database dump using mysqldump.
     * Returns the generated filename.
     */
    public function createBackup(): string
    {
        $dbName = config('database.connections.mysql.database');
        $userName = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $dsn = "mysql:host={$host};dbname={$dbName}";
        $filename = 'backup_' . date('Y_m_d_His') . '.sql';
        $filePath = $this->backupPath . '/' . $filename;

        if (config('database.default') === 'sqlite') {
            file_put_contents($filePath, '-- SQLite Test Backup Dump');
            return $filename;
        }

        $dump = new Mysqldump($dsn, $userName, $password);
        $dump->start($filePath);
        return $filename;
    }

    /**
     * Resolve and validate a backup file name, returning the absolute path.
     * Throws Exception on invalid input.
     */
    public function resolvePath(string $file): string
    {
        $cleanFilename = basename($file);
        if (!str_ends_with($cleanFilename, '.sql')) {
            throw new Exception('Format file backup tidak valid.');
        }
        $fullPath = $this->backupPath . DIRECTORY_SEPARATOR . $cleanFilename;
        $realPath = realpath($fullPath);
        $realBackupDir = realpath($this->backupPath);
        if (! $realPath || ! str_starts_with($realPath, $realBackupDir) || ! File::exists($realPath)) {
            throw new Exception('File backup tidak ditemukan.');
        }
        return $realPath;
    }

    public function deleteBackup(string $file): void
    {
        $path = $this->resolvePath($file);
        File::delete($path);
    }

    /**
     * Restore a database from an uploaded .sql file.
     */
    public function restoreBackup($uploadedFile): void
    {
        $extension = strtolower($uploadedFile->getClientOriginalExtension());
        if ($extension !== 'sql') {
            throw new Exception('File harus berformat .sql');
        }
        $sql = file_get_contents($uploadedFile->getRealPath());
        DB::unprepared($sql);
    }

    /**
     * Pull and merge data from an old school via API.
     * Returns an array with a human‑readable message and statistics.
     */
    public function tarikApi(array $data): array
    {
        // Validation is expected to be performed by a FormRequest before this call.
        $departemenId = $data['departemen_id'];
        $departemen   = Departemen::findOrFail($departemenId);
        $baseUrl      = rtrim($data['api_url'], '/');
        $token        = $data['api_token'];
        $endpoint     = $baseUrl . '/api/export-migrasi-sekolah';

        @set_time_limit(600);
        @ini_set('max_execution_time', '600');

        $verifySsl = config('services.migration.verify_ssl', true);
        $client = Http::timeout(120)->withToken($token);
        if (!$verifySsl) {
            $client = $client->withoutVerifying();
        }

        $response = $client->get($endpoint, ['api_token' => $token]);
        if (! $response->successful()) {
            $status = $response->status();
            if ($status === 403) {
                throw new Exception('Gagal terhubung: Token API salah atau ditolak oleh server lama.');
            }
            if ($status === 404) {
                throw new Exception('Gagal terhubung: Endpoint API tidak ditemukan di web lama. Pastikan route /api/export-migrasi-sekolah sudah dideploy di Railway.');
            }
            throw new Exception("Gagal menghubungi web lama (HTTP Code: {$status}).");
        }

        $json = $response->json();
        if (!isset($json['success']) || ! $json['success'] || !isset($json['data'])) {
            throw new Exception('Format respon data dari web lama tidak valid.');
        }
        $payload = $json['data'];

        // Mapping arrays to keep track of old => new IDs
        $kelasMap = [];
        $guruMap  = [];
        $siswaMap = [];
        $mapelMap = [];
        $jadwalMap = [];

        $counts = [
            'kelas' => 0, 'guru' => 0, 'siswa' => 0, 'mapel' => 0,
            'jadwal' => 0, 'presensi' => 0, 'presensi_mapel' => 0,
            'jurnal' => 0, 'pengaturan_jam' => 0,
        ];

        DB::beginTransaction();
        try {
            // ---------- KELAS ----------
            foreach ($payload['kelas'] ?? [] as $k) {
                $oldId = $k['id'];
                $namaRombel = trim($k['nama_rombel'] ?? '');
                $tingkat = $k['tingkat'] ?? '10';
                if (empty($namaRombel)) continue;
                $existing = Kelas::where('departemen_id', $departemenId)
                    ->where('nama_rombel', $namaRombel)
                    ->first();
                if ($existing) {
                    $kelasMap[$oldId] = $existing->id;
                } else {
                    $new = Kelas::create([
                        'departemen_id' => $departemenId,
                        'tingkat' => $tingkat,
                        'nama_rombel' => $namaRombel,
                    ]);
                    $kelasMap[$oldId] = $new->id;
                    $counts['kelas']++;
                }
            }

            // ---------- GURU ----------
            $downloadImage = function ($filePath) use ($baseUrl, $verifySsl) {
                if (empty($filePath)) return null;
                $cleanPath = ltrim($filePath, '/');
                if (Storage::disk('public')->exists($cleanPath)) {
                    return $cleanPath;
                }
                try {
                    $url = $baseUrl . '/storage/' . $cleanPath;
                    $client = Http::timeout(10);
                    if (!$verifySsl) {
                        $client = $client->withoutVerifying();
                    }
                    $res = $client->get($url);
                    if ($res->successful() && strlen($res->body()) > 100) {
                        Storage::disk('public')->put($cleanPath, $res->body());
                    }
                } catch (Throwable $th) {
                    // ignore missing images
                }
                return $cleanPath;
            };

            foreach ($payload['guru'] ?? [] as $g) {
                $oldId = $g['id'];
                $nip = !empty($g['nip']) ? trim($g['nip']) : null;
                $nama = trim($g['nama_lengkap'] ?? '');
                if (empty($nama)) continue;
                $existing = null;
                if ($nip) {
                    $existing = Guru::where('nip', $nip)->first();
                }
                if (! $existing) {
                    $existing = Guru::where('nama_lengkap', $nama)->first();
                }
                if (! $existing) {
                    $cleanNama = trim(explode(',', $nama)[0]);
                    $existing = Guru::where('nama_lengkap', 'like', $cleanNama . '%')->first();
                }
                $fotoWajah = $g['foto_wajah'] ? $downloadImage($g['foto_wajah']) : null;
                $fotoGuru  = $g['foto_guru'] ? $downloadImage($g['foto_guru']) : null;

                if ($existing) {
                    $guruMap[$oldId] = $existing->id;
                    $updates = [];
                    if (!empty($g['face_descriptor'])) $updates['face_descriptor'] = $g['face_descriptor'];
                    if ($fotoWajah) $updates['foto_wajah'] = $fotoWajah;
                    if ($fotoGuru) $updates['foto_guru'] = $fotoGuru;
                    if ($updates) $existing->update($updates);
                } else {
                    $new = Guru::create([
                        'nip' => $nip,
                        'username' => $g['username'] ? trim($g['username']) : ($nip ?: null),
                        'nama_lengkap' => $nama,
                        'jenis_kelamin' => $g['jenis_kelamin'] ?? 'L',
                        'jabatan' => $g['jabatan'] ?? 'Guru',
                        'no_hp' => $g['no_hp'] ?? null,
                        'foto_guru' => $fotoGuru,
                        'status' => $g['status'] ?? 'Aktif',
                        'password' => $g['password'] ?? \Illuminate\Support\Facades\Hash::make('guru123'),
                        'face_descriptor' => $g['face_descriptor'] ?? null,
                        'foto_wajah' => $fotoWajah,
                    ]);
                    $guruMap[$oldId] = $new->id;
                    $counts['guru']++;
                }
            }

            // ---------- SISWA ----------
            foreach ($payload['siswa'] ?? [] as $s) {
                $oldId = $s['id'];
                $nis = trim($s['nis'] ?? '');
                $nama = trim($s['nama_lengkap'] ?? '');
                $oldKelasId = $s['kelas_id'] ?? null;
                $targetKelasId = $kelasMap[$oldKelasId] ?? null;
                if (empty($nis) || empty($nama)) continue;
                $existing = Siswa::where('nis', $nis)->first();
                $payloadSiswa = [
                    'nisn' => $s['nisn'] ?? null,
                    'nama_lengkap' => $nama,
                    'jenis_kelamin' => $s['jenis_kelamin'] ?? 'L',
                    'agama' => $s['agama'] ?? 'Islam',
                    'tempat_lahir' => $s['tempat_lahir'] ?? '-',
                    'tanggal_lahir' => $s['tanggal_lahir'] ?? now()->toDateString(),
                    'kelas_id' => $targetKelasId,
                    'no_hp_wali' => $s['no_hp_wali'] ?? null,
                    'status' => $s['status'] ?? 'Aktif',
                ];
                if ($existing) {
                    $existing->update($payloadSiswa);
                    $siswaMap[$oldId] = $existing->id;
                } else {
                    $payloadSiswa['nis'] = $nis;
                    $payloadSiswa['password'] = $s['password'] ?? \Illuminate\Support\Facades\Hash::make('siswa123');
                    $payloadSiswa['is_password_changed'] = $s['is_password_changed'] ?? false;
                    $new = Siswa::create($payloadSiswa);
                    $siswaMap[$oldId] = $new->id;
                    $counts['siswa']++;
                }
            }

            // ---------- MATA PELAJARAN ----------
            foreach ($payload['mapel'] ?? [] as $m) {
                $oldId = $m['id'];
                $kode = trim($m['kode_mapel'] ?? '');
                $namaMapel = trim($m['nama_mapel'] ?? '');
                if (empty($namaMapel)) continue;
                $existing = null;
                if (!empty($kode)) {
                    $existing = MataPelajaran::where('kode_mapel', $kode)->first();
                }
                if (! $existing) {
                    $existing = MataPelajaran::where('nama_mapel', $namaMapel)->first();
                }
                if ($existing) {
                    $mapelMap[$oldId] = $existing->id;
                } else {
                    $new = MataPelajaran::create([
                        'kode_mapel' => $kode ?: strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $namaMapel), 0, 4)),
                        'nama_mapel' => $namaMapel,
                    ]);
                    $mapelMap[$oldId] = $new->id;
                    $counts['mapel']++;
                }
            }

            // ---------- JADWAL ----------
            foreach ($payload['jadwal'] ?? [] as $j) {
                $oldJadwalId = $j['id'];
                $newKelasId = $kelasMap[$j['kelas_id'] ?? null] ?? null;
                $newMapelId = $mapelMap[$j['mata_pelajaran_id'] ?? null] ?? null;
                $newGuruId = $guruMap[$j['guru_id'] ?? null] ?? null;
                if (! $newKelasId || ! $newMapelId) continue;
                $hari = $j['hari'] ?? 'Senin';
                $jamMulai = substr($j['jam_mulai'] ?? '07:00:00', 0, 5);
                $jamSelesai = substr($j['jam_selesai'] ?? '08:00:00', 0, 5);
                $existing = JadwalPelajaran::where('kelas_id', $newKelasId)
                    ->where('mata_pelajaran_id', $newMapelId)
                    ->where('hari', $hari)
                    ->where('jam_mulai', $jamMulai)
                    ->first();
                if ($existing) {
                    $jadwalMap[$oldJadwalId] = $existing->id;
                } else {
                    $new = JadwalPelajaran::create([
                        'kelas_id' => $newKelasId,
                        'mata_pelajaran_id' => $newMapelId,
                        'guru_id' => $newGuruId,
                        'hari' => $hari,
                        'jam_mulai' => $jamMulai,
                        'jam_selesai' => $jamSelesai,
                    ]);
                    $jadwalMap[$oldJadwalId] = $new->id;
                    $counts['jadwal']++;
                }
            }

            // ---------- PRESENSI ----------
            foreach ($payload['presensi'] ?? [] as $p) {
                $userType = $p['user_type'] ?? 'siswa';
                $oldUserId = $p['user_id'] ?? null;
                $newUserId = $userType === 'guru' ? ($guruMap[$oldUserId] ?? null) : ($siswaMap[$oldUserId] ?? null);
                if (! $newUserId) continue;
                $tanggal = $p['tanggal'] ?? null;
                if (! $tanggal) continue;
                $existing = Presensi::where('user_type', $userType)
                    ->where('user_id', $newUserId)
                    ->where('tanggal', $tanggal)
                    ->first();
                if (! $existing) {
                    Presensi::create([
                        'user_type' => $userType,
                        'user_id' => $newUserId,
                        'tanggal' => $tanggal,
                        'jam_masuk' => $p['jam_masuk'] ?? $p['waktu_masuk'] ?? $p['jam_absen'] ?? $p['waktu_absen'] ?? $p['jam_hadir'] ?? null,
                        'jam_pulang' => $p['jam_pulang'] ?? $p['waktu_pulang'] ?? $p['jam_keluar'] ?? $p['waktu_keluar'] ?? null,
                        'status' => $p['status'] ?? 'Hadir',
                        'keterangan' => $p['keterangan'] ?? null,
                        'latitude' => $p['latitude'] ?? $p['latitude_masuk'] ?? null,
                        'longitude' => $p['longitude'] ?? $p['longitude_masuk'] ?? null,
                    ]);
                    $counts['presensi']++;
                } else {
                    $jamMasuk = $p['jam_masuk'] ?? $p['waktu_masuk'] ?? $p['jam_absen'] ?? $p['waktu_absen'] ?? $p['jam_hadir'] ?? null;
                    $jamPulang = $p['jam_pulang'] ?? $p['waktu_pulang'] ?? $p['jam_keluar'] ?? $p['waktu_keluar'] ?? null;
                    if (($jamMasuk && ! $existing->jam_masuk) || ($jamPulang && ! $existing->jam_pulang)) {
                        $existing->update([
                            'jam_masuk' => $jamMasuk ?? $existing->jam_masuk,
                            'jam_pulang' => $jamPulang ?? $existing->jam_pulang,
                        ]);
                    }
                }
            }

            // ---------- PRESENSI MAPEL ----------
            foreach ($payload['presensi_mapel'] ?? [] as $pm) {
                $newJadwalId = $jadwalMap[$pm['jadwal_id'] ?? null] ?? null;
                $newSiswaId = $siswaMap[$pm['siswa_id'] ?? null] ?? null;
                $tanggal = $pm['tanggal'] ?? null;
                if (! $newJadwalId || ! $newSiswaId || ! $tanggal) continue;
                $existing = PresensiMapel::where('jadwal_id', $newJadwalId)
                    ->where('siswa_id', $newSiswaId)
                    ->where('tanggal', $tanggal)
                    ->first();
                if (! $existing) {
                    PresensiMapel::create([
                        'jadwal_id' => $newJadwalId,
                        'siswa_id' => $newSiswaId,
                        'tanggal' => $tanggal,
                        'status' => $pm['status'] ?? 'Hadir',
                        'keterangan' => $pm['keterangan'] ?? null,
                    ]);
                    $counts['presensi_mapel']++;
                }
            }

            // ---------- JURNAL MENGAJAR ----------
            foreach ($payload['jurnal_mengajar'] ?? [] as $jm) {
                $newJadwalId = $jadwalMap[$jm['jadwal_id'] ?? null] ?? null;
                $tanggal = $jm['tanggal'] ?? null;
                if (! $newJadwalId || ! $tanggal) continue;
                $existing = JurnalMengajar::where('jadwal_id', $newJadwalId)
                    ->where('tanggal', $tanggal)
                    ->first();
                if (! $existing) {
                    JurnalMengajar::create([
                        'jadwal_id' => $newJadwalId,
                        'tanggal' => $tanggal,
                        'jumlah_jam' => $jm['jumlah_jam'] ?? 1,
                        'materi' => $jm['materi'] ?? '-',
                        'materi_selanjutnya' => $jm['materi_selanjutnya'] ?? null,
                        'keterangan' => $jm['keterangan'] ?? $jm['catatan'] ?? null,
                    ]);
                    $counts['jurnal']++;
                }
            }

            // ---------- PENGATURAN JAM ----------
            foreach ($payload['pengaturan_jam'] ?? [] as $pj) {
                $tipe = $pj['tipe'] ?? 'kelas';
                $hari = $pj['hari'] ?? 'Senin';
                $oldKelasId = $pj['kelas_id'] ?? null;
                $newKelasId = $tipe === 'kelas' ? ($kelasMap[$oldKelasId] ?? null) : null;
                if ($tipe === 'kelas' && $oldKelasId && ! $newKelasId) continue;
                $query = PengaturanJam::where('tipe', $tipe)->where('hari', $hari);
                if ($tipe === 'kelas') {
                    $query->where('kelas_id', $newKelasId);
                }
                $existing = $query->first();
                if ($existing) {
                    $existing->update([
                        'jam_mulai' => $pj['jam_mulai'] ?? $existing->jam_mulai,
                        'jam_terlambat' => $pj['jam_terlambat'] ?? $existing->jam_terlambat,
                        'jam_pulang' => $pj['jam_pulang'] ?? $existing->jam_pulang,
                    ]);
                } else {
                    PengaturanJam::create([
                        'tipe' => $tipe,
                        'kelas_id' => $newKelasId,
                        'hari' => $hari,
                        'jam_mulai' => $pj['jam_mulai'] ?? '07:00:00',
                        'jam_terlambat' => $pj['jam_terlambat'] ?? '07:15:00',
                        'jam_pulang' => $pj['jam_pulang'] ?? '15:00:00',
                    ]);
                    $counts['pengaturan_jam']++;
                }
            }

            DB::commit();
            $message = "Migrasi API Sukses! Data dari Railway berhasil digabungkan ke Departemen '{$departemen->nama_departemen}'. "
                . "Rincian: {$counts['siswa']} Siswa, {$counts['kelas']} Kelas, {$counts['guru']} Guru, "
                . "{$counts['jadwal']} Jadwal, {$counts['presensi']} Log Presensi, "
                . "{$counts['presensi_mapel']} Absen Mapel, dan {$counts['jurnal']} Jurnal Mengajar Guru berhasil ditambahkan.";
            return ['message' => $message, 'counts' => $counts];
        } catch (Throwable $th) {
            DB::rollBack();
            throw new Exception('Terjadi kesalahan saat memproses migrasi: ' . $th->getMessage());
        }
    }
}
?>
