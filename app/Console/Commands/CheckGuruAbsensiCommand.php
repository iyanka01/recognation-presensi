<?php

namespace App\Console\Commands;

use App\Models\Guru;
use App\Models\HariLibur;
use App\Models\PengaturanLembaga;
use App\Models\PeringatanGuru;
use App\Models\Presensi;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckGuruAbsensiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guru:cek-peringatan-absen {--force : Jalankan meskipun pengingat dinonaktifkan di pengaturan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pemeriksaan otomatis ketidakhadiran guru dan pengiriman peringatan via WhatsApp Gateway';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppService $waService)
    {
        $lembaga = PengaturanLembaga::first();

        if (!$lembaga) {
            $this->warn('Pengaturan lembaga belum ditemukan.');
            return 0;
        }

        $isActive = (bool) ($lembaga->notif_peringatan_guru_active ?? true);
        if (!$isActive && !$this->option('force')) {
            $this->info('Fitur notifikasi peringatan guru dinonaktifkan di Pengaturan Lembaga.');
            return 0;
        }

        $batasHari = (int) ($lembaga->batas_absen_guru_hari ?? 14);
        if ($batasHari <= 0) {
            $batasHari = 14;
        }

        $defaultTemplate = "Halo Bapak/Ibu {nama_guru},\n\nKami menginformasikan dari Bagian Kepegawaian & Kurikulum {nama_sekolah}.\n\nBerdasarkan data presensi sistem, tercatat Bapak/Ibu belum melakukan presensi kehadiran selama {jumlah_hari} hari terakhir.\n\nJika terdapat kendala teknis pada aplikasi, perubahan jadwal, atau kondisi izin/sakit, mohon segera mengonfirmasikannya kepada pihak kurikulum atau Kepala Sekolah.\n\nTerima kasih atas perhatian dan kerjasamanya.\nSistem Presensi {nama_sekolah}";
        $template = $lembaga->template_peringatan_guru ?: $defaultTemplate;

        $gurus = Guru::where('status', 'Aktif')->get();
        $this->info("Memeriksa absensi {$gurus->count()} guru aktif (Ambang batas: {$batasHari} hari)...");

        $now = Carbon::today();
        $startDate = $now->copy()->subDays(30);

        // Ambil daftar tanggal efektif 30 hari terakhir (Senin-Sabtu bukan libur)
        $hariLiburs30 = HariLibur::whereBetween('tanggal', [$startDate->toDateString(), $now->toDateString()])
            ->pluck('tanggal')
            ->toArray();

        $effectiveDates30 = [];
        $tempDay = $startDate->copy();
        while ($tempDay->lte($now)) {
            if ($tempDay->dayOfWeek !== Carbon::SUNDAY && !in_array($tempDay->toDateString(), $hariLiburs30)) {
                $effectiveDates30[] = $tempDay->toDateString();
            }
            $tempDay->addDay();
        }

        // Ambil seluruh presensi guru aktif dalam 30 hari terakhir
        $guruPresensis = Presensi::where('user_type', 'guru')
            ->where('tanggal', '>=', $startDate->toDateString())
            ->get()
            ->groupBy('user_id');

        $sentCount = 0;

        foreach ($gurus as $guru) {
            $userPres = $guruPresensis->get($guru->id, collect());
            $recordedDates = $userPres->pluck('tanggal')->map(fn($t) => is_string($t) ? substr($t, 0, 10) : Carbon::parse($t)->toDateString())->toArray();

            // Hitung hari kerja efektif yang tidak dihadiri guru (Alpa)
            $alpaCount = 0;
            foreach ($effectiveDates30 as $effDate) {
                if (!in_array($effDate, $recordedDates)) {
                    $alpaCount++;
                }
            }
            $alpaCount += $userPres->where('status', 'Alpa')->count();

            // Jika alpa melebihi atau sama dengan ambang batas
            if ($alpaCount >= $batasHari) {
                // Cek apakah sudah pernah dikirimkan peringatan dalam 7 hari terakhir
                $alreadySent = PeringatanGuru::where('guru_id', $guru->id)
                    ->where('tanggal_kirim', '>=', $now->copy()->subDays(7))
                    ->exists();

                if ($alreadySent) {
                    $this->line(" - Guru {$guru->nama_lengkap} sudah menerima peringatan dalam 7 hari terakhir. Dilewati.");
                    continue;
                }

                if (empty($guru->no_hp)) {
                    $this->warn(" - Guru {$guru->nama_lengkap} melebihi batas ({$alpaCount} hari alpa), tapi tidak memiliki nomor HP.");
                    continue;
                }

                // Susun pesan sesuai template
                $namaYayasan = !empty($lembaga->nama_yayasan) ? $lembaga->nama_yayasan : ($lembaga->nama_lembaga ?? 'Yayasan / Sekolah');
                
                // Gabungkan seluruh nama departemen (SMA AN NAJA / SMP-AN NAJA BOARDING SCHOOL)
                $semuaDep = \App\Models\Departemen::orderBy('nama_departemen')->pluck('nama_departemen');
                $namaSekolah = $semuaDep->isNotEmpty() ? $semuaDep->implode(' / ') : ($lembaga->nama_lembaga ?? 'Sekolah');

                $pesan = str_replace(
                    ['{nama_guru}', '{jumlah_hari}', '{nama_yayasan}', '{nama_sekolah}'],
                    [$guru->nama_lengkap, $alpaCount, $namaYayasan, $namaSekolah],
                    $template
                );

                $res = $waService->sendMessage($guru->no_hp, $pesan);
                $statusKirim = $res['success'] ? 'Terkirim' : 'Gagal';
                $keterangan = $res['message'] ?? null;

                PeringatanGuru::create([
                    'guru_id' => $guru->id,
                    'jumlah_hari_alpa' => $alpaCount,
                    'tanggal_kirim' => $now->toDateString(),
                    'no_hp' => $guru->no_hp,
                    'pesan' => $pesan,
                    'status_kirim' => $statusKirim,
                    'keterangan' => $keterangan,
                ]);

                if ($res['success']) {
                    $this->info(" [OK] Peringatan WhatsApp terkirim ke {$guru->nama_lengkap} ({$guru->no_hp}).");
                    $sentCount++;
                } else {
                    $this->error(" [GAGAL] Gagal kirim ke {$guru->nama_lengkap}: {$keterangan}");
                }
            }
        }

        $this->info("Pemeriksaan selesai. Total peringatan terkirim: {$sentCount}");
        return 0;
    }
}
