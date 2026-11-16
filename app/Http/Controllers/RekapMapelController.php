<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\PengaturanLembaga;
use App\Models\PresensiMapel;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RekapMapelController extends Controller
{
    public function index(Request $request)
    {
        $kelasId = $request->query('kelas_id');
        $mapelId = $request->query('mapel_id');
        $dari = $request->query('dari', Carbon::now()->startOfMonth()->toDateString());
        $sampai = $request->query('sampai', Carbon::today()->toDateString());

        // Rekap agregat per siswa
        $rekap = collect();
        if ($kelasId) {
            $siswaList = Kelas::findOrFail($kelasId)->siswa()->orderBy('nama_lengkap')->get();

            $base = PresensiMapel::query()
                ->selectRaw('siswa_id,
                    SUM(status = "Hadir") as hadir,
                    SUM(status = "Izin") as izin,
                    SUM(status = "Sakit") as sakit,
                    SUM(status = "Alpa") as alpa,
                    COUNT(*) as total')
                ->whereBetween('tanggal', [$dari, $sampai])
                ->when($mapelId, function ($q) use ($mapelId) {
                    $q->whereHas('jadwal', fn($j) => $j->where('mata_pelajaran_id', $mapelId));
                })
                ->groupBy('siswa_id');

            $data = $base->get()->keyBy('siswa_id');

            foreach ($siswaList as $s) {
                $row = $data->get($s->id);
                $total = (int) ($row->total ?? 0);
                $hadir = (int) ($row->hadir ?? 0);
                $rekap->push((object)[
                    'siswa' => $s,
                    'hadir' => $hadir,
                    'izin' => (int) ($row->izin ?? 0),
                    'sakit' => (int) ($row->sakit ?? 0),
                    'alpa' => (int) ($row->alpa ?? 0),
                    'total' => $total,
                    'persen' => $total > 0 ? round($hadir / $total * 100, 1) : null,
                ]);
            }
        }

        $selectedKelas = $kelasId ? Kelas::with('waliKelas')->find($kelasId) : null;
        $targetGuruMapel = null;
        if ($kelasId && $mapelId) {
            $jadwal = \App\Models\JadwalPelajaran::with('guru')
                ->where('kelas_id', $kelasId)
                ->where('mata_pelajaran_id', $mapelId)
                ->first();
            $targetGuruMapel = $jadwal?->guru;
        }

        return view('rekap-mapel.index', [
            'rekap' => $rekap,
            'kelasList' => Kelas::orderBy('tingkat')->orderBy('nama_rombel')->get(),
            'mapels' => MataPelajaran::orderBy('nama_mapel')->get(),
            'filterKelas' => $kelasId,
            'filterMapel' => $mapelId,
            'dari' => $dari,
            'sampai' => $sampai,
            'pengaturan' => PengaturanLembaga::with('kepalaSekolah')->first(),
            'selectedKelas' => $selectedKelas,
            'targetGuruMapel' => $targetGuruMapel,
            'gurus' => \App\Models\Guru::orderBy('nama_lengkap', 'asc')->get(),
        ]);
    }

    public function print(Request $request)
    {
        // Halaman cetak memakai logika rekap yang sama, tampilan print-friendly
        return view('rekap-mapel.print', $this->rekapData($request));
    }

    private function rekapData(Request $request): array
    {
        $kelasId = $request->query('kelas_id');
        $mapelId = $request->query('mapel_id');
        $dari = $request->query('dari', Carbon::now()->startOfMonth()->toDateString());
        $sampai = $request->query('sampai', Carbon::today()->toDateString());

        $rekap = collect();
        $kelas = null;
        if ($kelasId) {
            $kelas = Kelas::find($kelasId);
            $siswaList = $kelas->siswa()->orderBy('nama_lengkap')->get();

            $data = PresensiMapel::query()
                ->selectRaw("siswa_id,
                    SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as hadir,
                    SUM(CASE WHEN status = 'Izin' THEN 1 ELSE 0 END) as izin,
                    SUM(CASE WHEN status = 'Sakit' THEN 1 ELSE 0 END) as sakit,
                    SUM(CASE WHEN status = 'Alpa' THEN 1 ELSE 0 END) as alpa,
                    COUNT(*) as total")
                ->whereBetween('tanggal', [$dari, $sampai])
                ->when($mapelId, function ($q) use ($mapelId) {
                    $q->whereHas('jadwal', fn($j) => $j->where('mata_pelajaran_id', $mapelId));
                })
                ->groupBy('siswa_id')
                ->get()
                ->keyBy('siswa_id');

            foreach ($siswaList as $s) {
                $row = $data->get($s->id);
                $total = (int) ($row->total ?? 0);
                $hadir = (int) ($row->hadir ?? 0);
                $rekap->push((object)[
                    'siswa' => $s,
                    'hadir' => $hadir,
                    'izin' => (int) ($row->izin ?? 0),
                    'sakit' => (int) ($row->sakit ?? 0),
                    'alpa' => (int) ($row->alpa ?? 0),
                    'total' => $total,
                    'persen' => $total > 0 ? round($hadir / $total * 100, 1) : null,
                ]);
            }
        }

        return [
            'rekap' => $rekap,
            'kelas' => $kelas,
            'mapel' => $mapelId ? MataPelajaran::find($mapelId) : null,
            'dari' => $dari,
            'sampai' => $sampai,
            'pengaturan' => PengaturanLembaga::first(),
        ];
    }

    /**
     * Preview pesan WhatsApp rekap mapel via JSON
     */
    public function previewWhatsApp(Request $request)
    {
        $data = $this->rekapData($request);
        $customNote = $request->query('custom_note', '');
        $message = $this->buildWhatsAppMessage($data, $customNote);

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Kirimkan pesan rekap mapel ke WhatsApp penerima
     */
    public function sendWhatsApp(Request $request, \App\Services\WhatsAppService $wa)
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
            return redirect()->back()->with('error', 'Pilih minimal satu nomor penerima pesan WhatsApp.');
        }

        $request->validate([
            'message' => 'required|string',
        ], [
            'message.required' => 'Isi pesan WhatsApp tidak boleh kosong.',
        ]);

        $lembaga = PengaturanLembaga::first();
        if (!$lembaga || empty($lembaga->wanesia_token)) {
            return redirect()->back()->with('error', 'Token API Wanesia belum diatur di Pengaturan Lembaga.');
        }

        $message = $request->input('message');
        $successCount = 0;
        $failCount = 0;
        $failedNumbers = [];

        foreach ($cleanedRecipients as $phone) {
            $res = $wa->sendMessage($phone, $message);
            if ($res['success']) {
                $successCount++;
            } else {
                $failCount++;
                $failedNumbers[] = $phone . ' (' . ($res['message'] ?? 'Gagal') . ')';
            }
        }

        if ($successCount > 0 && $failCount === 0) {
            return redirect()->back()->with('success', "Laporan Rekap Mapel WhatsApp berhasil dikirim ke {$successCount} penerima! 🎉");
        } elseif ($successCount > 0 && $failCount > 0) {
            return redirect()->back()->with('warning', "Pesan terkirim ke {$successCount} nomor, gagal ke {$failCount} nomor: " . implode(', ', $failedNumbers));
        } else {
            return redirect()->back()->with('error', "Gagal mengirim pesan ke semua nomor tujuan: " . implode(', ', $failedNumbers));
        }
    }

    /**
     * Susun teks pesan WhatsApp rekapitulasi mapel
     */
    public function buildWhatsAppMessage(array $data, string $customNote = ''): string
    {
        $lembaga = $data['pengaturan'] ?? PengaturanLembaga::first();
        $namaSekolah = $lembaga->nama_lembaga ?? 'Sistem Presensi';
        $kelas = $data['kelas'];
        $mapel = $data['mapel'];
        $rekap = $data['rekap'] ?? collect();

        $dariFmt = Carbon::parse($data['dari'])->locale('id')->isoFormat('D MMM Y');
        $sampaiFmt = Carbon::parse($data['sampai'])->locale('id')->isoFormat('D MMM Y');
        $periode = ($data['dari'] === $data['sampai']) ? $dariFmt : "{$dariFmt} s/d {$sampaiFmt}";

        $namaKelas = $kelas ? "Kelas {$kelas->tingkat} {$kelas->nama_rombel}" : "-";
        $namaMapel = $mapel ? $mapel->nama_mapel : "Semua Mata Pelajaran";

        $msg = "📊 *LAPORAN REKAP ABSENSI MATA PELAJARAN*\n";
        $msg .= "🏫 *{$namaSekolah}*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "🎯 Sasaran: *{$namaKelas}*\n";
        $msg .= "📖 Mata Pelajaran: *{$namaMapel}*\n";
        $msg .= "📅 Periode: *{$periode}*\n";
        if ($kelas && $kelas->waliKelas) {
            $msg .= "🎓 Wali Kelas: {$kelas->waliKelas->nama_lengkap}\n";
        }
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";

        if ($rekap->isEmpty()) {
            $msg .= "_Tidak ada data absensi pada periode ini._\n\n";
        } else {
            $totalSiswa = $rekap->count();
            $totalPersen = $rekap->whereNotNull('persen')->avg('persen');
            $avgPersen = $totalPersen ? round($totalPersen, 1) : 0;

            $msg .= "📈 *RINGKASAN KEHADIRAN KELAS:*\n";
            $msg .= "• Total Siswa: *{$totalSiswa} Siswa*\n";
            $msg .= "• Rata-rata Kehadiran: *{$avgPersen}%*\n\n";

            // Filter siswa bermasalah (ada alpa atau kehadiran < 75%)
            $perluPerhatian = $rekap->filter(function ($item) {
                return $item->alpa > 0 || ($item->persen !== null && $item->persen < 75);
            })->sortByDesc('alpa');

            if ($perluPerhatian->isNotEmpty()) {
                $msg .= "⚠️ *SISWA DENGAN ALPA / PERLU PERHATIAN (" . $perluPerhatian->count() . "):*\n";
                $no = 1;
                foreach ($perluPerhatian as $item) {
                    $ket = [];
                    if ($item->alpa > 0) $ket[] = "Alpa: {$item->alpa}x";
                    if ($item->sakit > 0) $ket[] = "Sakit: {$item->sakit}x";
                    if ($item->izin > 0) $ket[] = "Izin: {$item->izin}x";
                    $ketStr = implode(', ', $ket);
                    $prs = $item->persen !== null ? " ({$item->persen}%)" : "";

                    $msg .= "{$no}. *{$item->siswa->nama_lengkap}*{$prs}\n";
                    $msg .= "    ↳ _{$ketStr}_\n";
                    $no++;
                }
                $msg .= "\n";
            }

            // Siswa kehadiran rajin
            $rajinCount = $rekap->filter(fn($i) => $i->persen >= 90)->count();
            if ($rajinCount > 0) {
                $msg .= "✨ *Siswa Kehadiran Baik (≥90%):* {$rajinCount} Siswa\n\n";
            }
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
