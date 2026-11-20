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
        $departemens = \App\Models\Departemen::orderBy('nama_departemen')->get();
        $gurus = Guru::orderBy('nama_lengkap', 'asc')->get();
        $lembaga = PengaturanLembaga::with('kepalaSekolah')->first();

        // Ambil data laporan berdasarkan filter
        $reportData = $this->generateAttendanceData($tanggal, $kelasId);

        // Ambil data Guru untuk Tab Monitoring & Peringatan Otomatis Guru
        $batasHari = (int) ($lembaga->batas_absen_guru_hari ?? 14);
        $startDate = Carbon::today()->subDays(30);

        // Hitung akumulasi alpa 30 hari terakhir untuk setiap guru
        $alpaGuruStats = Presensi::where('user_type', 'guru')
            ->where('status', 'Alpa')
            ->where('tanggal', '>=', $startDate)
            ->selectRaw('user_id, COUNT(*) as total_alpa')
            ->groupBy('user_id')
            ->pluck('total_alpa', 'user_id')
            ->toArray();

        $latestPeringatan = \App\Models\PeringatanGuru::latest()
            ->get()
            ->groupBy('guru_id')
            ->map(fn($items) => $items->first());

        $monitoringGurus = $gurus->map(function ($g) use ($alpaGuruStats, $latestPeringatan, $batasHari) {
            $alpa = $alpaGuruStats[$g->id] ?? 0;
            $peringatan = $latestPeringatan->get($g->id);

            return (object) [
                'id' => $g->id,
                'nama_lengkap' => $g->nama_lengkap,
                'nip' => $g->nip,
                'no_hp' => $g->no_hp,
                'total_alpa' => $alpa,
                'batas_hari' => $batasHari,
                'is_over_limit' => ($alpa >= $batasHari),
                'terakhir_kirim' => $peringatan ? Carbon::parse($peringatan->tanggal_kirim)->translatedFormat('d M Y') : null,
                'status_kirim' => $peringatan ? $peringatan->status_kirim : null,
            ];
        })->sortByDesc('total_alpa')->values();

        $riwayatPeringatan = \App\Models\PeringatanGuru::with('guru')->latest()->take(20)->get();

        return view('whatsapp.broadcast', compact(
            'tanggal',
            'kelasId',
            'kelasList',
            'departemens',
            'gurus',
            'lembaga',
            'reportData',
            'monitoringGurus',
            'riwayatPeringatan',
            'batasHari'
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
        $rawRecipients = $request->input('recipients', []);
        $cleanedRecipients = [];
        foreach ($rawRecipients as $item) {
            $val = trim($item ?? '');
            if (!empty($val)) {
                $cleanedRecipients[] = $val;
            }
        }
        $cleanedRecipients = array_values(array_unique($cleanedRecipients));

        if (empty($cleanedRecipients)) {
            return redirect()->back()->with('error', 'Pilih minimal satu penerima pesan (pastikan nomor HP guru / kepala sekolah sudah terisi atau ketik nomor manual).');
        }

        $request->validate([
            'message' => 'required|string',
        ], [
            'message.required' => 'Isi pesan WhatsApp tidak boleh kosong.',
        ]);

        $lembaga = PengaturanLembaga::first();
        if (!$lembaga || empty($lembaga->wanesia_token)) {
            return redirect()->back()->with('error', 'Token API Wanesia belum disimpan di database. Silakan klik "Simpan Profil Lembaga" di Pengaturan Lembaga terlebih dahulu.');
        }

        $recipients = $cleanedRecipients;
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
     * Kirim Pesan WhatsApp Manual (Sesuai Form Desain Jendela Sekolah / Broadcast Kelas)
     */
    public function sendManualBroadcast(Request $request, WhatsAppService $wa)
    {
        $request->validate([
            'pesan' => 'required|string|max:1000',
        ], [
            'pesan.required' => 'Pesan broadcast tidak boleh kosong.',
        ]);

        $lembaga = PengaturanLembaga::first();
        if (!$lembaga || empty($lembaga->wanesia_token)) {
            return redirect()->back()->with('error', 'Token API WhatsApp (Wanesia) belum dikonfigurasi di Pengaturan Lembaga.');
        }

        $tujuan = $request->input('tujuan', 'kelas');
        $kelasIds = $request->input('kelas_ids', []);
        $pesan = $request->input('pesan');

        $recipients = [];

        if ($tujuan === 'siswa_per_kelas' || $tujuan === 'kelas') {
            if (empty($kelasIds)) {
                return redirect()->back()->with('error', 'Pilih minimal satu kelas tujuan pengiriman.');
            }

            // Ambil seluruh no_hp_wali dari siswa pada kelas terpilih
            $siswas = Siswa::whereIn('kelas_id', $kelasIds)
                ->whereNotNull('no_hp_wali')
                ->where('no_hp_wali', '!=', '')
                ->get();

            foreach ($siswas as $s) {
                $recipients[] = $s->no_hp_wali;
            }
        } elseif ($tujuan === 'guru') {
            $gurus = Guru::whereNotNull('no_hp')->where('no_hp', '!=', '')->get();
            foreach ($gurus as $g) {
                $recipients[] = $g->no_hp;
            }
        }

        // Nomor manual tambahan jika ada
        if ($request->filled('manual_number')) {
            $recipients[] = $request->input('manual_number');
        }

        $recipients = array_values(array_unique(array_filter(array_map('trim', $recipients))));

        if (empty($recipients)) {
            return redirect()->back()->with('error', 'Tidak ditemukan nomor tujuan (nomor WhatsApp orang tua/wali siswa di kelas terpilih belum diisi).');
        }

        $successCount = 0;
        $failCount = 0;
        $failedNumbers = [];

        foreach ($recipients as $phone) {
            $res = $wa->sendMessage($phone, $pesan);
            if ($res['success']) {
                $successCount++;
            } else {
                $failCount++;
                $failedNumbers[] = $phone;
            }
        }

        if ($successCount > 0 && $failCount === 0) {
            return redirect()->back()->with('success', "Pesan broadcast manual berhasil dikirimkan ke {$successCount} nomor! 🎉");
        } elseif ($successCount > 0 && $failCount > 0) {
            return redirect()->back()->with('warning', "Pesan berhasil terkirim ke {$successCount} nomor, gagal ke {$failCount} nomor.");
        } else {
            return redirect()->back()->with('error', "Gagal mengirimkan pesan broadcast ke seluruh nomor tujuan.");
        }
    }

    /**
     * Kirim Pesan Peringatan Guru Secara Manual dari Tombol Monitoring Admin
     */
    public function kirimPeringatanGuruManual(Request $request, WhatsAppService $wa)
    {
        $guruId = $request->input('guru_id');
        $guru = Guru::findOrFail($guruId);

        if (empty($guru->no_hp)) {
            return redirect()->back()->with('error', "Guru {$guru->nama_lengkap} belum memiliki nomor WhatsApp terdaftar.");
        }

        $lembaga = PengaturanLembaga::first();
        if (!$lembaga || empty($lembaga->wanesia_token)) {
            return redirect()->back()->with('error', 'Token WhatsApp Gateway (Wanesia) belum dikonfigurasi di Pengaturan Lembaga.');
        }

        $startDate = Carbon::today()->subDays(30);
        $alpaCount = Presensi::where('user_type', 'guru')
            ->where('user_id', $guru->id)
            ->where('status', 'Alpa')
            ->where('tanggal', '>=', $startDate)
            ->count();

        $defaultTemplate = "Halo Bapak/Ibu {nama_guru},\n\nKami menginformasikan dari Bagian Kepegawaian & Kurikulum {nama_yayasan}.\n\nBerdasarkan data presensi sistem, tercatat Bapak/Ibu belum melakukan presensi kehadiran selama {jumlah_hari} hari terakhir.\n\nJika terdapat kendala teknis pada aplikasi, perubahan jadwal, atau kondisi izin/sakit, mohon segera mengonfirmasikannya kepada pihak kurikulum atau Kepala Sekolah.\n\nTerima kasih atas perhatian dan kerjasamanya.\nSistem Presensi {nama_sekolah}";
        $template = $lembaga->template_peringatan_guru ?: $defaultTemplate;

        $namaYayasan = !empty($lembaga->nama_yayasan) ? $lembaga->nama_yayasan : ($lembaga->nama_lembaga ?? 'Yayasan / Sekolah');
        
        // Gabungkan seluruh nama departemen (SMA AN NAJA / SMP-AN NAJA BOARDING SCHOOL)
        $semuaDep = \App\Models\Departemen::orderBy('nama_departemen')->pluck('nama_departemen');
        $namaSekolah = $semuaDep->isNotEmpty() ? $semuaDep->implode(' / ') : ($lembaga->nama_lembaga ?? 'Sekolah');

        $pesan = str_replace(
            ['{nama_guru}', '{jumlah_hari}', '{nama_yayasan}', '{nama_sekolah}'],
            [$guru->nama_lengkap, $alpaCount > 0 ? $alpaCount : ($lembaga->batas_absen_guru_hari ?? 14), $namaYayasan, $namaSekolah],
            $template
        );

        $res = $wa->sendMessage($guru->no_hp, $pesan);
        $statusKirim = $res['success'] ? 'Terkirim' : 'Gagal';
        $keterangan = $res['message'] ?? null;

        \App\Models\PeringatanGuru::create([
            'guru_id' => $guru->id,
            'jumlah_hari_alpa' => $alpaCount,
            'tanggal_kirim' => Carbon::today()->toDateString(),
            'no_hp' => $guru->no_hp,
            'pesan' => $pesan,
            'status_kirim' => $statusKirim,
            'keterangan' => $keterangan,
        ]);

        if ($res['success']) {
            return redirect()->back()->with('success', "Pesan peringatan WhatsApp berhasil dikirimkan ke {$guru->nama_lengkap} ({$guru->no_hp})! 🚀");
        } else {
            return redirect()->back()->with('error', "Gagal mengirimkan pesan peringatan WhatsApp ke {$guru->nama_lengkap}: {$keterangan}");
        }
    }

    /**
     * Helper menghitung data kehadiran dan perizinan (Hadir, Sakit, Izin, Alpa)
     */
    protected function generateAttendanceData(string $tanggal, string $kelasId): array
    {
        $lembaga = PengaturanLembaga::with('kepalaSekolah')->first();
        $defaultNama = $lembaga && !empty($lembaga->nama_yayasan) ? $lembaga->nama_yayasan : ($lembaga->nama_lembaga ?? 'Sistem Presensi Sekolah');

        $siswaQuery = Siswa::with('kelas');
        $targetKelas = null;

        if ($kelasId !== 'all') {
            $siswaQuery->where('kelas_id', $kelasId);
            $targetKelas = Kelas::with(['waliKelas', 'departemen'])->find($kelasId);
        }

        $namaLembaga = $targetKelas?->departemen?->nama_departemen ?: $defaultNama;

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
