<?php

namespace App\Http\Controllers;

use App\Models\Departemen;
use App\Models\Guru;
use App\Models\HariLibur;
use App\Models\Kelas;
use App\Models\PengaturanLembaga;
use App\Models\PeringatanGuru;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Services\WhatsAppBroadcast\WhatsAppBroadcastService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WhatsAppBroadcastController extends Controller
{
    protected WhatsAppBroadcastService $broadcastService;

    public function __construct(WhatsAppBroadcastService $broadcastService)
    {
        $this->broadcastService = $broadcastService;
    }

    /**
     * Tampilkan antarmuka Broadcast Laporan WhatsApp
     */
    public function index(Request $request)
    {
        $tanggal = $request->get('tanggal', Carbon::today()->toDateString());
        $kelasId = $request->get('kelas_id', 'all');

        $kelasList = Kelas::with('waliKelas')->orderBy('tingkat')->orderBy('nama_rombel')->get();
        $departemens = Departemen::orderBy('nama_departemen')->get();
        $gurus = Guru::orderBy('nama_lengkap', 'asc')->get();
        $lembaga = PengaturanLembaga::with('kepalaSekolah')->first();

        $reportData = $this->broadcastService->generateAttendanceData($tanggal, $kelasId);
        $batasHari = (int) ($lembaga->batas_absen_guru_hari ?? 14);

        $monitoringGurus = $this->broadcastService->getMonitoringGurus($gurus, $batasHari);
        $riwayatPeringatan = PeringatanGuru::with('guru')->latest()->take(20)->get();

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
     * Preview pesan via JSON
     */
    public function previewMessage(Request $request)
    {
        $tanggal = $request->get('tanggal', Carbon::today()->toDateString());
        $kelasId = $request->get('kelas_id', 'all');
        $customNote = $request->get('custom_note', '');

        $reportData = $this->broadcastService->generateAttendanceData($tanggal, $kelasId);
        $message = $this->broadcastService->buildWhatsAppMessage($reportData, $customNote);

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
     * Kirim Pesan WhatsApp Manual
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

        $hariLiburs30 = HariLibur::whereBetween('tanggal', [$startDate->toDateString(), Carbon::today()->toDateString()])
            ->pluck('tanggal')
            ->toArray();

        $effectiveDates30 = [];
        $tempDay = $startDate->copy();
        while ($tempDay->lte(Carbon::today())) {
            if (!in_array($tempDay->toDateString(), $hariLiburs30)) {
                $effectiveDates30[] = $tempDay->toDateString();
            }
            $tempDay->addDay();
        }

        $guruPresensi = Presensi::where('user_type', 'guru')
            ->where('user_id', $guru->id)
            ->where('tanggal', '>=', $startDate->toDateString())
            ->get();

        $recordedDates = $guruPresensi->pluck('tanggal')->map(fn($t) => is_string($t) ? substr($t, 0, 10) : Carbon::parse($t)->toDateString())->toArray();

        $alpaCount = 0;
        foreach ($effectiveDates30 as $effDate) {
            if (!in_array($effDate, $recordedDates)) {
                $alpaCount++;
            }
        }
        $alpaCount += $guruPresensi->where('status', 'Alpa')->count();

        $defaultTemplate = "Halo Bapak/Ibu {nama_guru},\n\nKami menginformasikan dari Bagian Kepegawaian & Kurikulum {nama_yayasan}.\n\nBerdasarkan data presensi sistem, tercatat Bapak/Ibu belum melakukan presensi kehadiran selama {jumlah_hari} hari terakhir.\n\nJika terdapat kendala teknis pada aplikasi, perubahan jadwal, atau kondisi izin/sakit, mohon segera mengonfirmasikannya kepada pihak kurikulum atau Kepala Sekolah.\n\nTerima kasih atas perhatian dan kerjasamanya.\nSistem Presensi {nama_sekolah}";
        $template = $lembaga->template_peringatan_guru ?: $defaultTemplate;

        $namaYayasan = !empty($lembaga->nama_yayasan) ? $lembaga->nama_yayasan : ($lembaga->nama_lembaga ?? 'Yayasan / Sekolah');
        $semuaDep = Departemen::orderBy('nama_departemen')->pluck('nama_departemen');
        $namaSekolah = $semuaDep->isNotEmpty() ? $semuaDep->implode(' / ') : ($lembaga->nama_lembaga ?? 'Sekolah');

        $pesan = str_replace(
            ['{nama_guru}', '{jumlah_hari}', '{nama_yayasan}', '{nama_sekolah}'],
            [$guru->nama_lengkap, $alpaCount > 0 ? $alpaCount : ($lembaga->batas_absen_guru_hari ?? 14), $namaYayasan, $namaSekolah],
            $template
        );

        $res = $wa->sendMessage($guru->no_hp, $pesan);
        $statusKirim = $res['success'] ? 'Terkirim' : 'Gagal';
        $keterangan = $res['message'] ?? null;

        PeringatanGuru::create([
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
}
