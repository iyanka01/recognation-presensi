<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\PengaturanLembaga;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WhatsAppBroadcastController extends Controller
{
    /**
     * Tampilkan antarmuka Broadcast Laporan WhatsApp
     */
    public function index(Request $request)
    {
        $tanggal = $request->get('tanggal', Carbon::today()->toDateString());
        $kelasId = $request->get('kelas_id', 'all');

        $kelasList = Kelas::with('waliKelas')->orderBy('tingkat')->orderBy('nama_rombel')->get();
        $gurus = Guru::orderBy('nama_lengkap', 'asc')->get();
        $lembaga = PengaturanLembaga::with('kepalaSekolah')->first();

        // Ambil data laporan berdasarkan filter
        $reportData = $this->generateAttendanceData($tanggal, $kelasId);

        return view('whatsapp.broadcast', compact(
            'tanggal',
            'kelasId',
            'kelasList',
            'gurus',
            'lembaga',
            'reportData'
        ));
    }

    /**
     * Preview pesan via JSON (untuk update instan saat user mengganti kelas/tanggal)
     */
    public function previewMessage(Request $request)
    {
        $tanggal = $request->get('tanggal', Carbon::today()->toDateString());
        $kelasId = $request->get('kelas_id', 'all');
        $customNote = $request->get('custom_note', '');

        $reportData = $this->generateAttendanceData($tanggal, $kelasId);
        $message = $this->buildWhatsAppMessage($reportData, $customNote);

        return response()->json([
            'success' => true,
            'message' => $message,
            'stats'   => $reportData['stats'],
        ]);
    }

    /**
     * Eksekusi pengiriman pesan WhatsApp ke penerima terpilih
     */
    public function send(Request $request, WhatsAppService $wa)
    {
        $request->validate([
            'message'       => 'required|string',
            'recipients'    => 'required|array|min:1',
            'recipients.*'  => 'required|string',
        ], [
            'recipients.required' => 'Pilih minimal satu penerima pesan (Kepala Sekolah, Wali Kelas, atau Guru).',
            'message.required'    => 'Isi pesan WhatsApp tidak boleh kosong.',
        ]);

        $lembaga = PengaturanLembaga::first();
        if (!$lembaga || empty($lembaga->wanesia_token)) {
            return redirect()->back()->with('error', 'Token API Wanesia belum diatur di Pengaturan Lembaga.');
        }

        $recipients = array_unique($request->input('recipients', []));
        $message = $request->input('message');

        $successCount = 0;
        $failCount = 0;
        $failedNumbers = [];

        foreach ($recipients as $recipientRaw) {
            // Format bisa berupa nomor hp langsung "08123xxx" atau "nama:08123xxx"
            $phone = $recipientRaw;
            if (strpos($recipientRaw, ':') !== false) {
                $parts = explode(':', $recipientRaw, 2);
                $phone = $parts[1];
            }

            $phone = trim($phone);
            if (empty($phone)) {
                $failCount++;
                continue;
            }

            $res = $wa->sendMessage($phone, $message);
            if ($res['success']) {
                $successCount++;
            } else {
                $failCount++;
                $failedNumbers[] = $phone . ' (' . ($res['message'] ?? 'Gagal') . ')';
            }
        }

        if ($successCount > 0 && $failCount === 0) {
            return redirect()->back()->with('success', "Laporan WhatsApp berhasil dikirimkan ke {$successCount} penerima! 🎉");
        } elseif ($successCount > 0 && $failCount > 0) {
            return redirect()->back()->with('warning', "Pesan berhasil terkirim ke {$successCount} nomor, namun gagal ke {$failCount} nomor: " . implode(', ', $failedNumbers));
        } else {
            return redirect()->back()->with('error', "Gagal mengirimkan pesan ke seluruh penerima: " . implode(', ', $failedNumbers));
        }
    }

    /**
     * Helper menghitung data kehadiran dan perizinan (Hadir, Sakit, Izin, Alpa)
     */
    protected function generateAttendanceData(string $tanggal, string $kelasId): array
    {
        $lembaga = PengaturanLembaga::with('kepalaSekolah')->first();
        $namaLembaga = $lembaga->nama_lembaga ?? 'Sistem Presensi Sekolah';

        $siswaQuery = Siswa::with('kelas');
        $targetKelas = null;

        if ($kelasId !== 'all') {
            $siswaQuery->where('kelas_id', $kelasId);
            $targetKelas = Kelas::with('waliKelas')->find($kelasId);
        }

        $allSiswa = $siswaQuery->get();
        $totalSiswa = $allSiswa->count();
        $siswaIds = $allSiswa->pluck('id')->toArray();

        // Ambil presensi hari tersebut untuk siswa terkait
        $presensiList = Presensi::where('user_type', 'siswa')
            ->whereIn('user_id', $siswaIds)
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('user_id');

        $hadirList = [];
        $terlambatList = [];
        $sakitList = [];
        $izinList = [];
        $alpaList = [];

        foreach ($allSiswa as $siswa) {
            if (isset($presensiList[$siswa->id])) {
                $p = $presensiList[$siswa->id];
                $st = strtolower($p->status);

                if ($st === 'hadir') {
                    $hadirList[] = $siswa;
                } elseif ($st === 'terlambat') {
                    $terlambatList[] = $siswa;
                } elseif ($st === 'sakit') {
                    $sakitList[] = [
                        'siswa' => $siswa,
                        'keterangan' => $p->keterangan ?? 'Sakit'
                    ];
                } elseif (in_array($st, ['izin', 'cuti', 'dispensasi', 'dinas luar'])) {
                    $izinList[] = [
                        'siswa' => $siswa,
                        'keterangan' => $p->keterangan ?? ucfirst($p->status)
                    ];
                } else {
                    $alpaList[] = $siswa;
                }
            } else {
                // Tidak ada record presensi = Alpa / Tanpa Keterangan
                $alpaList[] = $siswa;
            }
        }

        return [
            'tanggal'        => $tanggal,
            'kelasId'        => $kelasId,
            'targetKelas'    => $targetKelas,
            'namaLembaga'    => $namaLembaga,
            'stats'          => [
                'total'      => $totalSiswa,
                'hadir'      => count($hadirList),
                'terlambat'  => count($terlambatList),
                'sakit'      => count($sakitList),
                'izin'       => count($izinList),
                'alpa'       => count($alpaList),
            ],
            'hadirList'      => $hadirList,
            'terlambatList'  => $terlambatList,
            'sakitList'      => $sakitList,
            'izinList'       => $izinList,
            'alpaList'       => $alpaList,
        ];
    }

    /**
     * Susun teks WhatsApp rapi dan profesional
     */
    public function buildWhatsAppMessage(array $data, string $customNote = ''): string
    {
        $tglFormatted = Carbon::parse($data['tanggal'])->locale('id')->isoFormat('dddd, D MMMM Y');
        $namaSekolah = $data['namaLembaga'];

        $namaTarget = "Semua Kelas";
        if (!empty($data['targetKelas'])) {
            $namaTarget = "Kelas {$data['targetKelas']->tingkat} {$data['targetKelas']->nama_rombel}";
            if ($data['targetKelas']->waliKelas) {
                $namaTarget .= " (Wali Kelas: {$data['targetKelas']->waliKelas->nama_lengkap})";
            }
        }

        $msg = "📊 *LAPORAN REKAP KEHADIRAN SISWA*\n";
        $msg .= "🏫 *{$namaSekolah}*\n";
        $msg .= "📅 Tanggal: *{$tglFormatted}*\n";
        $msg .= "🎯 Sasaran: *{$namaTarget}*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";

        $s = $data['stats'];
        $msg .= "📈 *RINGKASAN STATISTIK:*\n";
        $msg .= "• Total Siswa: *{$s['total']}*\n";
        $msg .= "• Hadir Tepat Waktu: *{$s['hadir']}*\n";
        $msg .= "• Terlambat: *{$s['terlambat']}*\n";
        $msg .= "• Sakit (S): *{$s['sakit']}*\n";
        $msg .= "• Izin (I): *{$s['izin']}*\n";
        $msg .= "• Tanpa Keterangan (A): *{$s['alpa']}*\n\n";

        // Daftar Sakit
        if (!empty($data['sakitList'])) {
            $msg .= "🏥 *DAFTAR SISWA SAKIT (" . count($data['sakitList']) . "):*\n";
            foreach ($data['sakitList'] as $idx => $item) {
                $no = $idx + 1;
                $kls = $item['siswa']->kelas ? " ({$item['siswa']->kelas->tingkat} {$item['siswa']->kelas->nama_rombel})" : "";
                $ket = !empty($item['keterangan']) ? " - _{$item['keterangan']}_" : "";
                $msg .= "{$no}. {$item['siswa']->nama_lengkap}{$kls}{$ket}\n";
            }
            $msg .= "\n";
        }

        // Daftar Izin
        if (!empty($data['izinList'])) {
            $msg .= "📝 *DAFTAR SISWA IZIN (" . count($data['izinList']) . "):*\n";
            foreach ($data['izinList'] as $idx => $item) {
                $no = $idx + 1;
                $kls = $item['siswa']->kelas ? " ({$item['siswa']->kelas->tingkat} {$item['siswa']->kelas->nama_rombel})" : "";
                $ket = !empty($item['keterangan']) ? " - _{$item['keterangan']}_" : "";
                $msg .= "{$no}. {$item['siswa']->nama_lengkap}{$kls}{$ket}\n";
            }
            $msg .= "\n";
        }

        // Daftar Alpa
        if (!empty($data['alpaList'])) {
            $msg .= "⚠️ *DAFTAR SISWA ALPA / TANPA KETERANGAN (" . count($data['alpaList']) . "):*\n";
            foreach ($data['alpaList'] as $idx => $item) {
                $no = $idx + 1;
                $kls = $item->kelas ? " ({$item->kelas->tingkat} {$item->kelas->nama_rombel})" : "";
                $msg .= "{$no}. {$item->nama_lengkap}{$kls}\n";
            }
            $msg .= "\n";
        }

        if (!empty(trim($customNote))) {
            $msg .= "📌 *CATATAN TAMBAHAN:*\n";
            $msg .= trim($customNote) . "\n\n";
        }

        $msg .= "━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "_Pesan otomatis dikirim melalui Sistem Presensi {$namaSekolah}_";

        return $msg;
    }
}
