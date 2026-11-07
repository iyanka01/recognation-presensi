<?php

namespace App\Http\Controllers;

use App\Models\PengaturanLembaga;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiswaIzinController extends Controller
{
    /**
     * Halaman Pengajuan Izin / Sakit oleh Wali Murid
     */
    public function index()
    {
        $siswa = Auth::guard('siswa')->user();

        $riwayatIzin = Presensi::where('user_type', 'siswa')
            ->where('user_id', $siswa->id)
            ->whereIn('status', ['Izin', 'Sakit', 'Dispensasi'])
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('siswa-portal.izin', [
            'siswa'       => $siswa,
            'riwayatIzin' => $riwayatIzin,
            'lembaga'     => PengaturanLembaga::first(),
        ]);
    }

    /**
     * Simpan Pengajuan Izin / Sakit Baru
     */
    public function store(Request $request)
    {
        $siswa = Auth::guard('siswa')->user();

        $request->validate([
            'status'          => 'required|in:Izin,Sakit',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'      => 'required|string|max:500',
            'bukti'           => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
        ], [
            'status.required'          => 'Jenis perizinan wajib dipilih.',
            'tanggal_mulai.required'   => 'Tanggal mulai izin wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai izin wajib diisi.',
            'keterangan.required'      => 'Alasan izin wajib dijelaskan.',
            'bukti.image'              => 'Bukti surat harus berupa file gambar (JPG/PNG).',
            'bukti.max'                => 'Ukuran foto bukti surat maksimal 3MB.',
        ]);

        $mulai   = Carbon::parse($request->tanggal_mulai);
        $selesai = Carbon::parse($request->tanggal_selesai);
        $diff    = $mulai->diffInDays($selesai);

        if ($diff > 30) {
            return back()->with('error', 'Rentang izin maksimal adalah 30 hari.');
        }

        $keteranganLengkap = $request->keterangan;

        $created = 0;
        for ($i = 0; $i <= $diff; $i++) {
            $tgl = $mulai->copy()->addDays($i)->toDateString();

            // Cek apakah sudah ada presensi pada tanggal tersebut
            $existing = Presensi::where('user_type', 'siswa')
                ->where('user_id', $siswa->id)
                ->whereDate('tanggal', $tgl)
                ->first();

            if ($existing) {
                $existing->update([
                    'status' => $request->status,
                ]);
            } else {
                Presensi::create([
                    'user_type' => 'siswa',
                    'user_id'   => $siswa->id,
                    'tanggal'   => $tgl,
                    'status'    => $request->status,
                ]);
            }
            $created++;
        }

        return redirect()->route('siswa.izin')->with('success', "Pengajuan {$request->status} untuk {$created} hari berhasil disimpan.");
    }
}
