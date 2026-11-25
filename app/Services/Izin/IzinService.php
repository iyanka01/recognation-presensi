<?php

namespace App\Services\Izin;

use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class IzinService
{
    /**
     * Memproses pencatatan/pengajuan izin multi-hari (range tanggal).
     * Dapat digunakan oleh Admin, Guru, maupun Siswa.
     */
    public function recordIzinMultiDays(
        string $userType,
        string $userId,
        string $tanggalMulai,
        string $tanggalSelesai,
        string $status,
        string $keterangan,
        ?UploadedFile $bukti = null,
        string $statusApproval = 'pending',
        ?string $catatanApproval = null
    ): int {
        $mulai = Carbon::parse($tanggalMulai);
        $selesai = Carbon::parse($tanggalSelesai);
        $diff = $mulai->diffInDays($selesai);

        $buktiPath = null;
        if ($bukti) {
            $buktiPath = $bukti->store('bukti_izin', 'public');
        }

        $berhasil = 0;
        for ($i = 0; $i <= $diff; $i++) {
            $tgl = $mulai->copy()->addDays($i)->toDateString();

            $existing = Presensi::where('user_type', $userType)
                ->where('user_id', $userId)
                ->whereDate('tanggal', $tgl)
                ->first();

            if ($existing) {
                $updateData = [
                    'status' => $status,
                    'keterangan' => $keterangan,
                    'status_approval' => $statusApproval,
                    'catatan_approval' => $catatanApproval,
                ];
                if ($buktiPath) {
                    $updateData['bukti'] = $buktiPath;
                }
                $existing->update($updateData);
            } else {
                Presensi::create([
                    'user_type' => $userType,
                    'user_id' => $userId,
                    'tanggal' => $tgl,
                    'status' => $status,
                    'keterangan' => $keterangan,
                    'bukti' => $buktiPath,
                    'status_approval' => $statusApproval,
                    'catatan_approval' => $catatanApproval,
                ]);
            }
            $berhasil++;
        }

        return $berhasil;
    }

    /**
     * Menyetujui izin oleh admin
     */
    public function approve(Presensi $izin): void
    {
        $izin->update([
            'status_approval' => 'approved',
            'catatan_approval' => 'Disetujui oleh Admin pada ' . now()->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Menolak izin oleh admin
     */
    public function reject(Presensi $izin, ?string $alasan = null): void
    {
        $izin->update([
            'status_approval' => 'rejected',
            'catatan_approval' => $alasan ?: 'Ditolak oleh Admin pada ' . now()->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Menghapus izin dan bukti fisiknya
     */
    public function delete(Presensi $izin): void
    {
        if ($izin->bukti && Storage::disk('public')->exists($izin->bukti)) {
            Storage::disk('public')->delete($izin->bukti);
        }
        $izin->delete();
    }
}
