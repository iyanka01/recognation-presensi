<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class GuruController extends Controller
{
    const PASSWORD_DEFAULT = 'guru123';

    public function index()
    {
        $guru = Guru::latest()->get();
        return view('guru.index', compact('guru'));
    }

    public function create()
    {
        $jabatans = \App\Models\Jabatan::orderBy('kategori', 'asc')
            ->orderBy('urutan', 'asc')
            ->orderBy('nama_jabatan', 'asc')
            ->get();
        return view('guru.create', compact('jabatans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip' => 'nullable|string|unique:guru,nip',
            'nama_lengkap' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|string',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'jabatan' => 'required|string',
            'no_hp' => 'nullable|string',
            'telegram_chat_id' => 'nullable|string',
            'foto_guru' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'nullable|string|min:6',
        ]);

        if ($request->hasFile('foto_guru')) {
            $fotoPath = $request->file('foto_guru')->store('foto_guru', 'public');
            $validated['foto_guru'] = $fotoPath;
        }

        // Auto-generate akun login guru (username = NIP, password custom atau default)
        if (!empty($validated['nip'])) {
            $validated['username'] = $validated['nip'];
            $passwordRaw = $request->filled('password') ? $request->password : self::PASSWORD_DEFAULT;
            $validated['password'] = Hash::make($passwordRaw);
        }

        Guru::create($validated);

        return redirect()->route('guru.index')->with('success', 'Data guru berhasil ditambahkan!');
    }

    public function edit(Guru $guru)
    {
        $jabatans = \App\Models\Jabatan::orderBy('kategori', 'asc')
            ->orderBy('urutan', 'asc')
            ->orderBy('nama_jabatan', 'asc')
            ->get();
        return view('guru.edit', compact('guru', 'jabatans'));
    }

    public function update(Request $request, Guru $guru)
    {
        $validated = $request->validate([
            'nip' => 'nullable|string|unique:guru,nip,' . $guru->id,
            'nama_lengkap' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|string',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'jabatan' => 'required|string',
            'no_hp' => 'nullable|string',
            'telegram_chat_id' => 'nullable|string',
            'foto_guru' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:Aktif,Nonaktif,Pensiun,Pindah',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($request->hasFile('foto_guru')) {
            if ($guru->foto_guru) {
                Storage::disk('public')->delete($guru->foto_guru);
            }
            $fotoPath = $request->file('foto_guru')->store('foto_guru', 'public');
            $validated['foto_guru'] = $fotoPath;
        }

        // Sinkronkan akun login guru dengan NIP & update password jika diisi
        if (!empty($validated['nip'])) {
            $validated['username'] = $validated['nip'];
            if ($request->filled('password')) {
                $validated['password'] = Hash::make($request->password);
            } elseif (empty($guru->password)) {
                $validated['password'] = Hash::make(self::PASSWORD_DEFAULT);
            } else {
                unset($validated['password']);
            }
        } else {
            $validated['username'] = null;
            $validated['password'] = null;
        }

        $guru->update($validated);

        return redirect()->route('guru.index')->with('success', 'Data guru berhasil diperbarui!');
    }

    public function destroy(Guru $guru)
    {
        if ($guru->foto_guru) {
            Storage::disk('public')->delete($guru->foto_guru);
        }
        if ($guru->foto_wajah) {
            Storage::disk('public')->delete($guru->foto_wajah);
        }
        $guru->delete();
        return redirect()->route('guru.index')->with('success', 'Data guru berhasil dihapus!');
    }

    public function resetFace($id)
    {
        $guru = Guru::findOrFail($id);

        if ($guru->foto_wajah && Storage::disk('public')->exists($guru->foto_wajah)) {
            Storage::disk('public')->delete($guru->foto_wajah);
        }

        $guru->face_descriptor = null;
        $guru->foto_wajah = null;
        $guru->save();

        return redirect()->route('guru.index')->with('success', "Data scan wajah guru {$guru->nama_lengkap} berhasil direset! Guru dapat mendaftarkan wajahnya kembali.");
    }

    public function resetDevice($id)
    {
        $guru = Guru::findOrFail($id);
        $guru->device_id = null;
        $guru->device_name = null;
        $guru->device_locked_at = null;
        $guru->save();

        return redirect()->route('guru.index')->with('success', "Kunci perangkat (Lock Device) guru {$guru->nama_lengkap} berhasil direset! Guru dapat login di perangkat baru.");
    }

    public function printIdCard($id)
    {
        $user = Guru::findOrFail($id);
        $type = 'guru';
        return view('id-card', compact('user', 'type'));
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:guru,id',
        ]);

        $gurus = Guru::whereIn('id', $request->ids)->get();
        foreach ($gurus as $guru) {
            if ($guru->foto_guru) {
                Storage::disk('public')->delete($guru->foto_guru);
            }
            if ($guru->foto_wajah) {
                Storage::disk('public')->delete($guru->foto_wajah);
            }
            $guru->delete();
        }

        return redirect()->route('guru.index')->with('success', count($request->ids) . ' data guru berhasil dihapus!');
    }

    public static function getDummyGuru(): array
    {
        return [
            ['198001012005011001', 'Drs. H. Ahmad Fauzi, M.Pd.', 'L', 'Islam', 'Bandung', '15-01-1980', 'Guru Mata Pelajaran', '081223344501'],
            ['198203152008012002', 'Siti Nurhaliza, S.Pd.', 'P', 'Islam', 'Jakarta', '15-03-1982', 'Guru Mata Pelajaran', '081223344502'],
            ['198507202010011003', 'Budi Santoso, S.Kom.', 'L', 'Islam', 'Surabaya', '20-07-1985', 'Kepala Program Keahlian', '081223344503'],
            ['198811122014022004', 'Rina Wulandari, S.Si.', 'P', 'Islam', 'Yogyakarta', '12-11-1988', 'Guru BK', '081223344504'],
            ['199004252019031005', 'Dedi Kurniawan, S.Pd.', 'L', 'Islam', 'Semarang', '25-04-1990', 'Guru Mata Pelajaran', '081223344505'],
        ];
    }

    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Guru');

        $columns = [
            'A' => 'NIP',
            'B' => 'Nama Lengkap',
            'C' => 'Jenis Kelamin (L/P)',
            'D' => 'Agama',
            'E' => 'Tempat Lahir',
            'F' => 'Tanggal Lahir (DD-MM-YYYY)',
            'G' => 'Jabatan',
            'H' => 'No HP'
        ];

        // Header Styling
        foreach ($columns as $col => $title) {
            $sheet->setCellValue("{$col}1", $title);
        }

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0284C7'], // Sky 600
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $dummyGuru = self::getDummyGuru();
        $rowIdx = 2;

        foreach ($dummyGuru as $item) {
            $sheet->setCellValueExplicit("A{$rowIdx}", "'" . $item[0], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("B{$rowIdx}", $item[1]);
            $sheet->setCellValue("C{$rowIdx}", $item[2]);
            $sheet->setCellValue("D{$rowIdx}", $item[3]);
            $sheet->setCellValue("E{$rowIdx}", $item[4]);
            $sheet->setCellValueExplicit("F{$rowIdx}", $item[5], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("G{$rowIdx}", $item[6]);
            $sheet->setCellValueExplicit("H{$rowIdx}", "'" . $item[7], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $rowIdx++;
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, 'template_import_guru.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file_import' => 'required|file|mimes:xlsx,xls,csv,txt|max:8192'
        ]);

        $file = $request->file('file_import');
        $filePath = $file->getRealPath();
        $ext = strtolower($file->getClientOriginalExtension());

        $cleanValue = function($val) {
            $val = trim((string)($val ?? ''));
            if (str_starts_with($val, '="') && str_ends_with($val, '"')) {
                $val = substr($val, 2, -1);
            }
            if (str_starts_with($val, "'")) {
                $val = substr($val, 1);
            }
            return trim($val, "'\" \t\n\r\0\x0B");
        };

        $rows = [];

        if (in_array($ext, ['xlsx', 'xls'])) {
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                $sheetData = $sheet->toArray(null, true, true, false);

                foreach ($sheetData as $rIdx => $rData) {
                    if ($rIdx === 0) continue; // Skip header
                    $rows[] = array_values($rData);
                }
            } catch (\Throwable $e) {
                return redirect()->route('guru.index')->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
            }
        } else {
            ini_set('auto_detect_line_endings', true);
            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (empty($lines)) {
                return redirect()->route('guru.index')->with('error', 'File CSV kosong atau tidak valid.');
            }

            if (!empty($lines) && str_starts_with($lines[0], "\xEF\xBB\xBF")) {
                $lines[0] = substr($lines[0], 3);
            }
            if (!empty($lines) && str_starts_with(strtolower(trim($lines[0])), 'sep=')) {
                array_shift($lines);
            }

            if (!empty($lines)) {
                $headerLine = $lines[0];
                $delimiter = strpos($headerLine, ';') !== false ? ';' : ',';

                foreach ($lines as $index => $line) {
                    if ($index === 0) continue;
                    $row = str_getcsv($line, $delimiter);
                    if (count($row) < 7) {
                        $altDelimiter = $delimiter === ',' ? ';' : ',';
                        $row = str_getcsv($line, $altDelimiter);
                    }
                    $rows[] = $row;
                }
            }
        }

        if (empty($rows)) {
            return redirect()->route('guru.index')->with('error', 'File tidak memiliki baris data yang valid.');
        }

        $suksesBaru = 0;
        $suksesUpdate = 0;
        $gagal = 0;

        // Tambah batas eksekusi & memori agar tidak timeout saat data banyak
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        // Pre-hash password satu kali di luar loop
        $defaultPasswordHash = Hash::make(self::PASSWORD_DEFAULT);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                if (count($row) < 7) {
                    $gagal++;
                    continue;
                }

                try {
                    $nip       = $cleanValue($row[0] ?? '');
                    $nama      = $cleanValue($row[1] ?? '');
                    $jk        = strtoupper($cleanValue($row[2] ?? 'L'));
                    $agama     = $cleanValue($row[3] ?? 'Islam');
                    $tempat    = $cleanValue($row[4] ?? '-');
                    $tanggal   = $cleanValue($row[5] ?? '');
                    $jabatan   = $cleanValue($row[6] ?? 'Guru Mata Pelajaran');
                    $noHp      = $cleanValue($row[7] ?? '');

                    if (empty($nama)) {
                        $gagal++;
                        continue;
                    }

                    $nip = $nip === '' ? null : $nip;
                    $noHp = $noHp === '' ? null : $noHp;

                    // Parsing Tanggal Lahir (DD-MM-YYYY misal: 15-01-1980 atau YYYY-MM-DD)
                    $parsedTanggal = \Carbon\Carbon::now()->format('Y-m-d');
                    if (!empty($tanggal)) {
                        try {
                            if (preg_match('/^(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{4})$/', $tanggal, $m)) {
                                $parsedTanggal = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
                            } else {
                                $parsedTanggal = \Carbon\Carbon::parse($tanggal)->format('Y-m-d');
                            }
                        } catch (\Exception $e) {
                            // fallback
                        }
                    }

                    $guruData = [
                        'nama_lengkap'  => $nama,
                        'jenis_kelamin' => in_array($jk, ['L', 'P']) ? $jk : 'L',
                        'agama'         => $agama,
                        'tempat_lahir'  => $tempat,
                        'tanggal_lahir' => $parsedTanggal,
                        'jabatan'       => $jabatan,
                        'no_hp'         => $noHp,
                        'status'        => 'Aktif'
                    ];

                    if (!empty($nip)) {
                        $guruData['nip'] = $nip;
                        $guruData['username'] = $nip;
                    }

                    // Cari existing guru: utamakan NIP jika ada, jika tidak ada cari berdasarkan nama_lengkap
                    $existing = null;
                    if (!empty($nip)) {
                        $existing = Guru::where('nip', $nip)->first();
                    }
                    if (!$existing) {
                        $existing = Guru::where('nama_lengkap', $nama)->first();
                    }

                    if (!$existing) {
                        $guruData['password'] = $defaultPasswordHash;
                        Guru::create($guruData);
                        $suksesBaru++;
                    } else {
                        // Update biodata tanpa menghapus foto profil, foto wajah, face descriptor, atau password
                        $existing->update($guruData);
                        $suksesUpdate++;
                    }
                } catch (\Exception $e) {
                    \Log::error("Exception saat import guru row: " . $e->getMessage(), (array)$row);
                    $gagal++;
                }
            }

            \Illuminate\Support\Facades\DB::commit();
        } catch (\Throwable $th) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->route('guru.index')->with('error', 'Terjadi kesalahan saat memproses data guru: ' . $th->getMessage());
        }

        $totalProses = $suksesBaru + $suksesUpdate;
        $msg = "Import guru selesai! Total diproses: {$totalProses} ({$suksesUpdate} data diperbarui, {$suksesBaru} data baru ditambahkan). Dilewati/Gagal: {$gagal} baris.";
        return redirect()->route('guru.index')->with('success', $msg);
    }
}
