<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Ifsnop\Mysqldump as IMysqldump;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Exception;

class BackupController extends Controller
{
    protected $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    public function index()
    {
        $files = File::files($this->backupPath);
        $backups = [];
        
        foreach ($files as $file) {
            if ($file->getExtension() === 'sql') {
                $backups[] = [
                    'name' => $file->getFilename(),
                    'size' => round($file->getSize() / 1024, 2) . ' KB',
                    'date' => date('Y-m-d H:i:s', $file->getMTime()),
                    'timestamp' => $file->getMTime()
                ];
            }
        }
        
        // Sort by newest first
        usort($backups, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        $departemens = \App\Models\Departemen::orderBy('nama_departemen')->get();

        return view('backup.index', compact('backups', 'departemens'));
    }

    public function create()
    {
        try {
            $dbName = config('database.connections.mysql.database');
            $userName = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            
            $dsn = "mysql:host={$host};dbname={$dbName}";
            
            $filename = 'backup_' . date('Y_m_d_His') . '.sql';
            $filePath = $this->backupPath . '/' . $filename;
            
            $dump = new IMysqldump\Mysqldump($dsn, $userName, $password);
            $dump->start($filePath);

            return redirect()->route('backup.index')->with('success', 'Backup database berhasil dibuat!');
        } catch (Exception $e) {
            return redirect()->route('backup.index')->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    public function download($file)
    {
        // Cegah Path Traversal (Arbitrary File Download)
        $cleanFilename = basename($file);
        if (!str_ends_with($cleanFilename, '.sql')) {
            return redirect()->route('backup.index')->with('error', 'Format file backup tidak valid.');
        }

        $filePath = $this->backupPath . DIRECTORY_SEPARATOR . $cleanFilename;
        $realPath = realpath($filePath);
        $realBackupDir = realpath($this->backupPath);

        if (!$realPath || !str_starts_with($realPath, $realBackupDir) || !File::exists($realPath)) {
            return redirect()->route('backup.index')->with('error', 'File backup tidak ditemukan.');
        }

        return response()->download($realPath);
    }

    public function destroy($file)
    {
        // Cegah Path Traversal (Arbitrary File Deletion)
        $cleanFilename = basename($file);
        if (!str_ends_with($cleanFilename, '.sql')) {
            return redirect()->route('backup.index')->with('error', 'Format file backup tidak valid.');
        }

        $filePath = $this->backupPath . DIRECTORY_SEPARATOR . $cleanFilename;
        $realPath = realpath($filePath);
        $realBackupDir = realpath($this->backupPath);

        if (!$realPath || !str_starts_with($realPath, $realBackupDir) || !File::exists($realPath)) {
            return redirect()->route('backup.index')->with('error', 'File backup tidak ditemukan.');
        }

        File::delete($realPath);
        return redirect()->route('backup.index')->with('success', 'File backup berhasil dihapus.');
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:51200' // Maksimal 50MB untuk mencegah DoS memory exhaustion
        ]);

        try {
            $file = $request->file('backup_file');
            
            // Verifikasi ekstensi file secara ketat
            if (strtolower($file->getClientOriginalExtension()) !== 'sql') {
                return redirect()->route('backup.index')->with('error', 'File harus berformat .sql');
            }

            $sql = file_get_contents($file->getRealPath());
            
            // Execute raw SQL
            DB::unprepared($sql);

            return redirect()->route('backup.index')->with('success', 'Database berhasil dipulihkan (Restore)!');
        } catch (Exception $e) {
            return redirect()->route('backup.index')->with('error', 'Gagal memulihkan database: ' . $e->getMessage());
        }
    }

    /**
     * Tarik dan Gabungkan Data Sekolah Lama via API ke Departemen Tertentu (Tanpa Menghapus Data Lokal)
     */
    public function tarikApi(Request $request)
    {
        $request->validate([
            'departemen_id' => 'required|exists:departemen,id',
            'api_url'       => 'required|url',
            'api_token'     => 'required|string',
        ], [
            'departemen_id.required' => 'Pilih Departemen Tujuan terlebih dahulu.',
            'departemen_id.exists'   => 'Departemen yang dipilih tidak valid.',
            'api_url.required'       => 'Masukkan URL Web Lama yang sedang online di Railway.',
            'api_url.url'            => 'Format URL tidak valid (harus diawali http:// atau https://).',
            'api_token.required'     => 'Masukkan Token Rahasia API.',
        ]);

        $departemenId = $request->input('departemen_id');
        $departemen = \App\Models\Departemen::findOrFail($departemenId);
        $baseUrl = rtrim($request->input('api_url'), '/');
        $token = $request->input('api_token');

        $endpoint = $baseUrl . '/api/export-migrasi-sekolah?api_token=' . urlencode($token);

        @set_time_limit(600); // 10 menit untuk dataset besar
        @ini_set('max_execution_time', '600');

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(120)->withoutVerifying()->get($endpoint);

            if (!$response->successful()) {
                $status = $response->status();
                if ($status === 403) {
                    return redirect()->route('backup.index')->with('error', 'Gagal terhubung: Token API salah atau ditolak oleh server lama.');
                }
                if ($status === 404) {
                    return redirect()->route('backup.index')->with('error', 'Gagal terhubung: Endpoint API tidak ditemukan di web lama. Pastikan route /api/export-migrasi-sekolah sudah dideploy di Railway.');
                }
                return redirect()->route('backup.index')->with('error', "Gagal menghubungi web lama (HTTP Code: {$status}).");
            }

            $json = $response->json();
            if (!isset($json['success']) || !$json['success'] || !isset($json['data'])) {
                return redirect()->route('backup.index')->with('error', 'Format respon data dari web lama tidak valid.');
            }

            $data = $json['data'];

            $countKelas = 0;
            $countGuru = 0;
            $countSiswa = 0;
            $countMapel = 0;
            $countJadwal = 0;
            $countPresensi = 0;

            DB::beginTransaction();

            // 1. KELAS (Ikat langsung ke departemen terpilih)
            $kelasMap = []; // old_id => new_id
            foreach ($data['kelas'] ?? [] as $k) {
                $oldId = $k['id'];
                $namaRombel = trim($k['nama_rombel'] ?? '');
                $tingkat = $k['tingkat'] ?? '10';

                if (empty($namaRombel)) continue;

                $existingKelas = \App\Models\Kelas::where('departemen_id', $departemenId)
                    ->where('nama_rombel', $namaRombel)
                    ->first();

                if ($existingKelas) {
                    $kelasMap[$oldId] = $existingKelas->id;
                } else {
                    $newKelas = \App\Models\Kelas::create([
                        'departemen_id' => $departemenId,
                        'tingkat'       => $tingkat,
                        'nama_rombel'   => $namaRombel,
                    ]);
                    $kelasMap[$oldId] = $newKelas->id;
                    $countKelas++;
                }
            }

            // 2. GURU (Update or Insert berdasarkan NIP atau nama_lengkap)
            $guruMap = []; // old_id => new_id
            foreach ($data['guru'] ?? [] as $g) {
                $oldId = $g['id'];
                $nip = !empty($g['nip']) ? trim($g['nip']) : null;
                $nama = trim($g['nama_lengkap'] ?? '');

                if (empty($nama)) continue;

                $existingGuru = null;
                if ($nip) {
                    $existingGuru = \App\Models\Guru::where('nip', $nip)->first();
                }
                if (!$existingGuru) {
                    $existingGuru = \App\Models\Guru::where('nama_lengkap', $nama)->first();
                }

                if ($existingGuru) {
                    $guruMap[$oldId] = $existingGuru->id;
                } else {
                    $newGuru = \App\Models\Guru::create([
                        'nip'            => $nip,
                        'username'       => !empty($g['username']) ? trim($g['username']) : ($nip ?: null),
                        'nama_lengkap'   => $nama,
                        'jenis_kelamin'  => $g['jenis_kelamin'] ?? 'L',
                        'jabatan'        => $g['jabatan'] ?? 'Guru',
                        'no_hp'          => $g['no_hp'] ?? null,
                        'foto_guru'      => null, // Foto fisik tidak dipindah via json
                        'status'         => $g['status'] ?? 'Aktif',
                        'password'       => $g['password'] ?? \Illuminate\Support\Facades\Hash::make('guru123'),
                    ]);
                    $guruMap[$oldId] = $newGuru->id;
                    $countGuru++;
                }
            }

            // 3. SISWA (Update or Insert berdasarkan NIS)
            $siswaMap = []; // old_id => new_id
            foreach ($data['siswa'] ?? [] as $s) {
                $oldId = $s['id'];
                $nis = trim($s['nis'] ?? '');
                $nama = trim($s['nama_lengkap'] ?? '');
                $oldKelasId = $s['kelas_id'] ?? null;
                $targetKelasId = $kelasMap[$oldKelasId] ?? null;

                if (empty($nis) || empty($nama)) continue;

                $siswaPayload = [
                    'nisn'           => !empty($s['nisn']) ? trim($s['nisn']) : null,
                    'nama_lengkap'   => $nama,
                    'jenis_kelamin'  => $s['jenis_kelamin'] ?? 'L',
                    'agama'          => $s['agama'] ?? 'Islam',
                    'tempat_lahir'   => $s['tempat_lahir'] ?? '-',
                    'tanggal_lahir'  => $s['tanggal_lahir'] ?? now()->toDateString(),
                    'kelas_id'       => $targetKelasId,
                    'no_hp_wali'     => $s['no_hp_wali'] ?? null,
                    'status'         => $s['status'] ?? 'Aktif',
                ];

                $existingSiswa = \App\Models\Siswa::where('nis', $nis)->first();
                if ($existingSiswa) {
                    $existingSiswa->update($siswaPayload);
                    $siswaMap[$oldId] = $existingSiswa->id;
                } else {
                    $siswaPayload['nis'] = $nis;
                    $siswaPayload['password'] = $s['password'] ?? \Illuminate\Support\Facades\Hash::make('siswa123');
                    $siswaPayload['is_password_changed'] = $s['is_password_changed'] ?? false;
                    $newSiswa = \App\Models\Siswa::create($siswaPayload);
                    $siswaMap[$oldId] = $newSiswa->id;
                    $countSiswa++;
                }
            }

            // 4. MATA PELAJARAN
            $mapelMap = []; // old_id => new_id
            foreach ($data['mapel'] ?? [] as $m) {
                $oldId = $m['id'];
                $kode = trim($m['kode_mapel'] ?? '');
                $namaMapel = trim($m['nama_mapel'] ?? '');

                if (empty($namaMapel)) continue;

                $existingMapel = null;
                if (!empty($kode)) {
                    $existingMapel = \App\Models\MataPelajaran::where('kode_mapel', $kode)->first();
                }
                if (!$existingMapel) {
                    $existingMapel = \App\Models\MataPelajaran::where('nama_mapel', $namaMapel)->first();
                }

                if ($existingMapel) {
                    $mapelMap[$oldId] = $existingMapel->id;
                } else {
                    $newMapel = \App\Models\MataPelajaran::create([
                        'kode_mapel' => $kode ?: strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $namaMapel), 0, 4)),
                        'nama_mapel' => $namaMapel,
                    ]);
                    $mapelMap[$oldId] = $newMapel->id;
                    $countMapel++;
                }
            }

            // 5. JADWAL PELAJARAN (Simpan mapping old_id => new_id)
            $jadwalMap = []; // old_id => new_id
            foreach ($data['jadwal'] ?? [] as $j) {
                $oldJadwalId = $j['id'];
                $oldKelas = $j['kelas_id'] ?? null;
                $oldMapel = $j['mata_pelajaran_id'] ?? null;
                $oldGuru = $j['guru_id'] ?? null;

                $newKelasId = $kelasMap[$oldKelas] ?? null;
                $newMapelId = $mapelMap[$oldMapel] ?? null;
                $newGuruId = $guruMap[$oldGuru] ?? null;

                if (!$newKelasId || !$newMapelId) continue;

                $hari = $j['hari'] ?? 'Senin';
                $jamMulai = substr($j['jam_mulai'] ?? '07:00:00', 0, 5);
                $jamSelesai = substr($j['jam_selesai'] ?? '08:00:00', 0, 5);

                $existingJadwal = \App\Models\JadwalPelajaran::where('kelas_id', $newKelasId)
                    ->where('mata_pelajaran_id', $newMapelId)
                    ->where('hari', $hari)
                    ->where('jam_mulai', $jamMulai)
                    ->first();

                if ($existingJadwal) {
                    $jadwalMap[$oldJadwalId] = $existingJadwal->id;
                } else {
                    $newJadwal = \App\Models\JadwalPelajaran::create([
                        'kelas_id'          => $newKelasId,
                        'mata_pelajaran_id' => $newMapelId,
                        'guru_id'           => $newGuruId,
                        'hari'              => $hari,
                        'jam_mulai'         => $jamMulai,
                        'jam_selesai'       => $jamSelesai,
                    ]);
                    $jadwalMap[$oldJadwalId] = $newJadwal->id;
                    $countJadwal++;
                }
            }

            // 6. LOG PRESENSI HARIAN (Merge tanpa duplikat)
            foreach ($data['presensi'] ?? [] as $p) {
                $userType = $p['user_type'] ?? 'siswa';
                $oldUserId = $p['user_id'] ?? null;
                $newUserId = ($userType === 'guru') ? ($guruMap[$oldUserId] ?? null) : ($siswaMap[$oldUserId] ?? null);

                if (!$newUserId) continue;

                $tanggal = $p['tanggal'] ?? null;
                if (!$tanggal) continue;

                $existingLog = \App\Models\Presensi::where('user_type', $userType)
                    ->where('user_id', $newUserId)
                    ->where('tanggal', $tanggal)
                    ->first();

                if (!$existingLog) {
                    \App\Models\Presensi::create([
                        'user_type'       => $userType,
                        'user_id'         => $newUserId,
                        'tanggal'         => $tanggal,
                        'jam_masuk'       => $p['jam_masuk'] ?? ($p['waktu_masuk'] ?? ($p['jam_absen'] ?? ($p['waktu_absen'] ?? ($p['jam_hadir'] ?? null)))),
                        'jam_pulang'      => $p['jam_pulang'] ?? ($p['waktu_pulang'] ?? ($p['jam_keluar'] ?? ($p['waktu_keluar'] ?? null))),
                        'status'          => $p['status'] ?? 'Hadir',
                        'keterangan'      => $p['keterangan'] ?? null,
                        'latitude'        => $p['latitude'] ?? ($p['latitude_masuk'] ?? null),
                        'longitude'       => $p['longitude'] ?? ($p['longitude_masuk'] ?? null),
                    ]);
                    $countPresensi++;
                } else {
                    // Update jam jika sebelumnya kosong (dari sync sebelumnya yang belum membawa jam)
                    $jamMasuk  = $p['jam_masuk'] ?? ($p['waktu_masuk'] ?? ($p['jam_absen'] ?? ($p['waktu_absen'] ?? ($p['jam_hadir'] ?? null))));
                    $jamPulang = $p['jam_pulang'] ?? ($p['waktu_pulang'] ?? ($p['jam_keluar'] ?? ($p['waktu_keluar'] ?? null)));
                    if (($jamMasuk && !$existingLog->jam_masuk) || ($jamPulang && !$existingLog->jam_pulang)) {
                        $existingLog->update([
                            'jam_masuk'  => $jamMasuk  ?? $existingLog->jam_masuk,
                            'jam_pulang' => $jamPulang ?? $existingLog->jam_pulang,
                        ]);
                    }
                }
            }

            // 7. PRESENSI MATA PELAJARAN (Rekap Absen Mapel)
            $countPresensiMapel = 0;
            foreach ($data['presensi_mapel'] ?? [] as $pm) {
                $oldJadwalId = $pm['jadwal_id'] ?? null;
                $oldSiswaId = $pm['siswa_id'] ?? null;
                $tanggal = $pm['tanggal'] ?? null;

                $newJadwalId = $jadwalMap[$oldJadwalId] ?? null;
                $newSiswaId = $siswaMap[$oldSiswaId] ?? null;

                if (!$newJadwalId || !$newSiswaId || !$tanggal) continue;

                $existingPm = \App\Models\PresensiMapel::where('jadwal_id', $newJadwalId)
                    ->where('siswa_id', $newSiswaId)
                    ->where('tanggal', $tanggal)
                    ->first();

                if (!$existingPm) {
                    \App\Models\PresensiMapel::create([
                        'jadwal_id'   => $newJadwalId,
                        'siswa_id'    => $newSiswaId,
                        'tanggal'     => $tanggal,
                        'status'      => $pm['status'] ?? 'Hadir',
                        'keterangan'  => $pm['keterangan'] ?? null,
                    ]);
                    $countPresensiMapel++;
                }
            }

            // 8. JURNAL MENGAJAR (Rekap Jam Mengajar Guru)
            $countJurnal = 0;
            foreach ($data['jurnal_mengajar'] ?? [] as $jm) {
                $oldJadwalId = $jm['jadwal_id'] ?? null;
                $tanggal = $jm['tanggal'] ?? null;

                $newJadwalId = $jadwalMap[$oldJadwalId] ?? null;

                if (!$newJadwalId || !$tanggal) continue;

                $existingJm = \App\Models\JurnalMengajar::where('jadwal_id', $newJadwalId)
                    ->where('tanggal', $tanggal)
                    ->first();

                if (!$existingJm) {
                    \App\Models\JurnalMengajar::create([
                        'jadwal_id'          => $newJadwalId,
                        'tanggal'            => $tanggal,
                        'jumlah_jam'         => $jm['jumlah_jam'] ?? 1,
                        'materi'             => $jm['materi'] ?? '-',
                        'materi_selanjutnya' => $jm['materi_selanjutnya'] ?? null,
                        'keterangan'         => $jm['keterangan'] ?? ($jm['catatan'] ?? null),
                    ]);
                    $countJurnal++;
                }
            }

            DB::commit();

            $msg = "Migrasi API Sukses! Data dari Railway berhasil digabungkan ke Departemen '{$departemen->nama_departemen}'. "
                 . "Rincian: {$countSiswa} Siswa, {$countKelas} Kelas, {$countGuru} Guru, "
                 . "{$countJadwal} Jadwal, {$countPresensi} Log Presensi, "
                 . "{$countPresensiMapel} Absen Mapel, dan {$countJurnal} Jurnal Mengajar Guru berhasil ditambahkan.";

            return redirect()->route('backup.index')->with('success', $msg);

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('backup.index')->with('error', 'Terjadi kesalahan saat memproses migrasi: ' . $th->getMessage());
        }
    }
}
