<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiswaController extends Controller
{
    public function index()
    {
        $siswa = Siswa::with('kelas')->latest()->get();
        return view('siswa.index', compact('siswa'));
    }

    public function create()
    {
        $kelasList = Kelas::orderBy('tingkat')->orderBy('nama_rombel')->get()->groupBy('tingkat');
        return view('siswa.create', compact('kelasList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis' => 'required|string|unique:siswa,nis',
            'nisn' => 'nullable|string|unique:siswa,nisn',
            'nama_lengkap' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|string',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'kelas_id' => 'required|exists:kelas,id',
            'no_hp_wali' => 'nullable|string',
            'telegram_chat_id' => 'nullable|string',
            'foto_siswa' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('foto_siswa')) {
            $fotoPath = $request->file('foto_siswa')->store('foto_siswa', 'public');
            $validated['foto_siswa'] = $fotoPath;
        }

        $validated['password'] = \Illuminate\Support\Facades\Hash::make('siswa123');
        $validated['is_password_changed'] = false;

        Siswa::create($validated);

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil ditambahkan!');
    }

    public function edit(Siswa $siswa)
    {
        $kelasList = Kelas::orderBy('tingkat')->orderBy('nama_rombel')->get()->groupBy('tingkat');
        return view('siswa.edit', compact('siswa', 'kelasList'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nis' => 'required|string|unique:siswa,nis,' . $siswa->id,
            'nisn' => 'nullable|string|unique:siswa,nisn,' . $siswa->id,
            'nama_lengkap' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|string',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'kelas_id' => 'required|exists:kelas,id',
            'no_hp_wali' => 'nullable|string',
            'telegram_chat_id' => 'nullable|string',
            'foto_siswa' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:Aktif,Lulus,Pindah,Dikeluarkan',
        ]);

        if ($request->hasFile('foto_siswa')) {
            // Delete old photo if it exists
            if ($siswa->foto_siswa) {
                Storage::disk('public')->delete($siswa->foto_siswa);
            }
            $fotoPath = $request->file('foto_siswa')->store('foto_siswa', 'public');
            $validated['foto_siswa'] = $fotoPath;
        }

        $siswa->update($validated);

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil diperbarui!');
    }

    public function destroy(Siswa $siswa)
    {
        if ($siswa->foto_siswa) {
            Storage::disk('public')->delete($siswa->foto_siswa);
        }
        
        $siswa->delete();

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil dihapus!');
    }

    public static function getDummySiswa(): array
    {
        return [
            ['10101', '0081122001', 'Aditya Pratama', 'L', 'Islam', 'Bandung', '2008-01-15', '10-akl', '081223344001'],
            ['10102', '0081122002', 'Aisyah Nur Salsabila', 'P', 'Islam', 'Jakarta', '2008-02-20', '10-akl', '081223344002'],
            ['10103', '0081122003', 'Aldi Bagus Hermawan', 'L', 'Islam', 'Surabaya', '2008-03-12', '10-akl', '081223344003'],
            ['10104', '0081122004', 'Alika Putri Zahra', 'P', 'Islam', 'Semarang', '2008-04-05', '10-akl', '081223344004'],
            ['10105', '0081122005', 'Andi Setiawan', 'L', 'Islam', 'Yogyakarta', '2008-05-18', '10-akl', '081223344005'],
            ['10106', '0081122006', 'Annisa Rahmawati', 'P', 'Islam', 'Malang', '2008-06-22', '10-akl', '081223344006'],
            ['10107', '0081122007', 'Bagas Arya Nugraha', 'L', 'Islam', 'Solo', '2008-07-09', '10-akl', '081223344007'],
            ['10108', '0081122008', 'Cantika Maharani', 'P', 'Islam', 'Bogor', '2008-08-14', '10-akl', '081223344008'],
            ['10109', '0081122009', 'Daniel Christian', 'L', 'Kristen', 'Medan', '2008-09-03', '10-akl', '081223344009'],
            ['10110', '0081122010', 'Desta Rizki Kurniawan', 'L', 'Islam', 'Cirebon', '2008-10-27', '10-akl', '081223344010'],
            ['10111', '0081122011', 'Dewi Anggraini', 'P', 'Islam', 'Tasikmalaya', '2008-11-19', '10-akl', '081223344011'],
            ['10112', '0081122012', 'Dimas Fajar Ramadhan', 'L', 'Islam', 'Sukabumi', '2008-12-08', '10-akl', '081223344012'],
            ['10113', '0081122013', 'Fadhil Muhammad', 'L', 'Islam', 'Bandung', '2008-01-25', '10-akl', '081223344013'],
            ['10114', '0081122014', 'Farhan Maulana', 'L', 'Islam', 'Garut', '2008-02-14', '10-akl', '081223344014'],
            ['10115', '0081122015', 'Fitri Handayani', 'P', 'Islam', 'Sumedang', '2008-03-30', '10-akl', '081223344015'],
            ['10116', '0081122016', 'Gilang Ramadhan', 'L', 'Islam', 'Cimahi', '2008-04-16', '10-akl', '081223344016'],
            ['10117', '0081122017', 'Grace Natalia', 'P', 'Katolik', 'Manado', '2008-05-24', '10-akl', '081223344017'],
            ['10118', '0081122018', 'Hendra Wijaya', 'L', 'Buddha', 'Pontianak', '2008-06-11', '10-akl', '081223344018'],
            ['10119', '0081122019', 'I Gede Made Surya', 'L', 'Hindu', 'Denpasar', '2008-07-07', '10-akl', '081223344019'],
            ['10120', '0081122020', 'Intan Permatasari', 'P', 'Islam', 'Purwakarta', '2008-08-29', '10-akl', '081223344020'],
            ['10121', '0081122021', 'Kevin Sanjaya', 'L', 'Kristen', 'Jakarta', '2008-09-15', '10-akl', '081223344021'],
            ['10122', '0081122022', 'Lestari Wulandari', 'P', 'Islam', 'Subang', '2008-10-04', '10-akl', '081223344022'],
            ['10123', '0081122023', 'M. Rizky Pratama', 'L', 'Islam', 'Bandung', '2008-11-21', '10-akl', '081223344023'],
            ['10124', '0081122024', 'Nabila Syakira', 'P', 'Islam', 'Depok', '2008-12-16', '10-akl', '081223344024'],
            ['10125', '0081122025', 'Naufal Rafif', 'L', 'Islam', 'Bekasi', '2008-01-31', '10-akl', '081223344025'],
            ['10126', '0081122026', 'Putri Ayu Wandira', 'P', 'Islam', 'Tangerang', '2008-02-18', '10-akl', '081223344026'],
            ['10127', '0081122027', 'Rian Hidayat', 'L', 'Islam', 'Serang', '2008-03-08', '10-akl', '081223344027'],
            ['10128', '0081122028', 'Salma Nur Azizah', 'P', 'Islam', 'Bandung', '2008-04-26', '10-akl', '081223344028'],
            ['10129', '0081122029', 'Taufik Hidayat', 'L', 'Islam', 'Cianjur', '2008-05-14', '10-akl', '081223344029'],
            ['10130', '0081122030', 'Zahra Amelia Putri', 'P', 'Islam', 'Bandung', '2008-06-20', '10-akl', '081223344030'],
        ];
    }

    public function downloadTemplate()
    {
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=template_import_siswa.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['NIS', 'NISN', 'Nama Lengkap', 'Jenis Kelamin (L/P)', 'Agama', 'Tempat Lahir', 'Tanggal Lahir (DD-MM-YYYY)', 'Kelas (Contoh: 10 RPL 1)', 'No HP Wali'];
        $dummySiswa = self::getDummySiswa();

        $callback = function() use ($columns, $dummySiswa) {
            $file = fopen('php://output', 'w');
            
            // 1. Tulis UTF-8 BOM (\xEF\xBB\xBF) agar Excel membaca karakter khusus dengan benar
            fwrite($file, "\xEF\xBB\xBF");
            
            // 2. Tulis directive 'sep=,' agar Excel pada Windows (locale Indonesia/Eropa) otomatis memisahkan kolom ke tabel A-I
            fwrite($file, "sep=,\r\n");
            
            // 3. Tulis header kolom
            fputcsv($file, $columns, ',');
            
            // 4. Tulis 30 data dummy siswa dengan format petik tunggal di depan ('...) agar angka 0 tidak hilang di Excel
            foreach ($dummySiswa as $row) {
                // Ubah format tanggal YYYY-MM-DD dummy ke DD-MM-YYYY (contoh: 07-07-2002)
                $tglFmt = $row[6];
                if (!empty($tglFmt) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tglFmt)) {
                    $tglFmt = \Carbon\Carbon::parse($tglFmt)->format('d-m-Y');
                }

                fputcsv($file, [
                    "'" . $row[0],
                    "'" . $row[1],
                    $row[2],
                    $row[3],
                    $row[4],
                    $row[5],
                    $tglFmt,
                    $row[7],
                    "'" . $row[8],
                ], ',');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file_import' => 'required|mimes:csv,txt|max:4096'
        ]);

        $file = $request->file('file_import');
        $filePath = $file->getRealPath();
        
        ini_set('auto_detect_line_endings', true);

        // Baca seluruh baris file
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($lines)) {
            return redirect()->route('siswa.index')->with('error', 'File CSV kosong atau tidak valid.');
        }

        // 1. Bersihkan UTF-8 BOM jika ada di awal baris pertama
        if (!empty($lines) && str_starts_with($lines[0], "\xEF\xBB\xBF")) {
            $lines[0] = substr($lines[0], 3);
        }

        // 2. Jika baris pertama adalah directive 'sep=...', lewati baris tersebut
        if (!empty($lines) && str_starts_with(strtolower(trim($lines[0])), 'sep=')) {
            array_shift($lines);
        }

        if (empty($lines)) {
            return redirect()->route('siswa.index')->with('error', 'File CSV tidak memiliki baris data yang valid.');
        }

        // 3. Deteksi delimiter berdasarkan baris header (apakah titik koma ';' atau koma ',')
        $headerLine = $lines[0];
        $delimiter = strpos($headerLine, ';') !== false ? ';' : ',';

        // Helper untuk membersihkan nilai sel dari format formula Excel ="..." atau petik tunggal ' di awal
        $cleanValue = function($val) {
            $val = trim($val ?? '');
            if (str_starts_with($val, '="') && str_ends_with($val, '"')) {
                $val = substr($val, 2, -1);
            }
            // Hapus petik satu di awal jika ada (format '08123 / '0012)
            if (str_starts_with($val, "'")) {
                $val = substr($val, 1);
            }
            return trim($val, "'\" \t\n\r\0\x0B");
        };

        $sukses = 0;
        $gagal = 0;

        foreach ($lines as $index => $line) {
            if ($index === 0) continue; // Skip baris header

            $row = str_getcsv($line, $delimiter);

            // Jika masih kurang dari 8 kolom, coba delimiter alternatif
            if (count($row) < 8) {
                $altDelimiter = $delimiter === ',' ? ';' : ',';
                $row = str_getcsv($line, $altDelimiter);
            }

            // Jika tetap gagal, berarti baris ini berantakan/kosong
            if (count($row) < 8) {
                \Log::info("Row Gagal: Kurang dari 8 kolom. Data:", (array)$row);
                $gagal++;
                continue;
            }

            try {
                $nis       = $cleanValue($row[0] ?? '');
                $nisn      = $cleanValue($row[1] ?? '');
                $nama      = $cleanValue($row[2] ?? '');
                $jk        = strtoupper($cleanValue($row[3] ?? 'L'));
                $agama     = $cleanValue($row[4] ?? 'Islam');
                $tempat    = $cleanValue($row[5] ?? '-');
                $tanggal   = $cleanValue($row[6] ?? '');
                $namaKelas = $cleanValue($row[7] ?? '');
                $noHp      = $cleanValue($row[8] ?? '');

                // Validasi data krusial
                if (empty($nis) || empty($nama) || empty($namaKelas)) {
                    \Log::info("Row Gagal: NIS/Nama/Kelas kosong. Data:", (array)$row);
                    $gagal++;
                    continue;
                }

                // Normalisasi NISN (jika kosong biarkan null)
                $nisn = $nisn === '' ? null : $nisn;
                // Normalisasi no HP
                $noHp = $noHp === '' ? null : $noHp;

                // Parsing Tanggal Lahir (Mendukung DD-MM-YYYY misal: 07-07-2002, DD/MM/YYYY, maupun YYYY-MM-DD)
                $parsedTanggal = \Carbon\Carbon::now()->format('Y-m-d');
                if (!empty($tanggal)) {
                    try {
                        if (preg_match('/^(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{4})$/', $tanggal, $m)) {
                            // Format: DD-MM-YYYY (contoh: 07-07-2002)
                            $parsedTanggal = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
                        } else {
                            $parsedTanggal = \Carbon\Carbon::parse($tanggal)->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        // Jika format tidak dikenali, gunakan default tanggal hari ini
                    }
                }

                // Cari atau buat kelas otomatis
                $tingkat = explode(' ', $namaKelas)[0];
                if (!is_numeric($tingkat)) {
                    $tingkat = '10';
                }

                $kelas = Kelas::firstOrCreate(
                    ['nama_rombel' => $namaKelas],
                    ['tingkat' => $tingkat]
                );

                // Update or Create Siswa berdasarkan NIS
                $siswaData = [
                    'nisn'          => $nisn,
                    'nama_lengkap'  => $nama,
                    'jenis_kelamin' => in_array($jk, ['L', 'P']) ? $jk : 'L',
                    'agama'         => $agama,
                    'tempat_lahir'  => $tempat,
                    'tanggal_lahir' => $parsedTanggal,
                    'kelas_id'      => $kelas->id,
                    'no_hp_wali'    => $noHp,
                    'status'        => 'Aktif'
                ];

                $existing = Siswa::where('nis', $nis)->first();
                if (!$existing) {
                    $siswaData['password'] = \Illuminate\Support\Facades\Hash::make('siswa123');
                    $siswaData['is_password_changed'] = false;
                    Siswa::create(array_merge(['nis' => $nis], $siswaData));
                } else {
                    $existing->update($siswaData);
                }

                $sukses++;
            } catch (\Exception $e) {
                \Log::error("Exception saat import row: " . $e->getMessage(), (array)$row);
                $gagal++;
            }
        }

        $msg = "Import selesai! Berhasil: $sukses data siswa tersimpan, Gagal/Dilewati: $gagal baris.";
        return redirect()->route('siswa.index')->with('success', $msg);
    }

    public function printIdCard($id)
    {
        $user = Siswa::findOrFail($id);
        $type = 'siswa';
        return view('id-card', compact('user', 'type'));
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:siswa,id',
        ]);

        $siswas = Siswa::whereIn('id', $request->ids)->get();
        foreach ($siswas as $siswa) {
            if ($siswa->foto_siswa) {
                Storage::disk('public')->delete($siswa->foto_siswa);
            }
            $siswa->delete();
        }

        return redirect()->route('siswa.index')->with('success', count($request->ids) . ' data siswa berhasil dihapus!');
    }
}
