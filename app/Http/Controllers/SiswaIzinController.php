<?php

namespace App\Http\Controllers;

use App\Http\Requests\Izin\StoreSiswaIzinRequest;
use App\Models\PengaturanLembaga;
use App\Models\Presensi;
use App\Services\Izin\IzinService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiswaIzinController extends Controller
{
    protected IzinService $izinService;

    public function __construct(IzinService $izinService)
    {
        $this->izinService = $izinService;
    }

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
    public function store(StoreSiswaIzinRequest $request)
    {
        $siswa = Auth::guard('siswa')->user();
        $validated = $request->validated();

        $mulai = Carbon::parse($validated['tanggal_mulai']);
        $selesai = Carbon::parse($validated['tanggal_selesai']);
        if ($mulai->diffInDays($selesai) > 30) {
            return back()->with('error', 'Rentang izin maksimal adalah 30 hari.');
        }

        $created = $this->izinService->recordIzinMultiDays(
            'siswa',
            $siswa->id,
            $validated['tanggal_mulai'],
            $validated['tanggal_selesai'],
            $validated['status'],
            $validated['keterangan'],
            $request->file('bukti'),
            'pending',
            null
        );

        return redirect()->route('siswa.izin')->with('success', "Pengajuan {$validated['status']} untuk {$created} hari berhasil disimpan.");
    }
}
