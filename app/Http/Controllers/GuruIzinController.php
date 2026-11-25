<?php

namespace App\Http\Controllers;

use App\Http\Requests\Izin\StoreGuruIzinRequest;
use App\Models\PengaturanLembaga;
use App\Models\Presensi;
use App\Services\Izin\IzinService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuruIzinController extends Controller
{
    const KATEGORI = ['Izin', 'Sakit', 'Dinas Luar', 'Cuti', 'Dispensasi'];

    protected IzinService $izinService;

    public function __construct(IzinService $izinService)
    {
        $this->izinService = $izinService;
    }

    public function index(Request $request)
    {
        $guru = Auth::guard('guru')->user();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
        $filterStatus = $request->query('status');

        $query = Presensi::where('user_type', 'guru')
            ->where('user_id', $guru->id)
            ->whereIn('status', self::KATEGORI)
            ->orderBy('tanggal', 'desc');

        if ($filterStatus && in_array($filterStatus, self::KATEGORI)) {
            $query->where('status', $filterStatus);
        }

        $riwayat = $query->get();

        $statBulanIni = [
            'Izin' => Presensi::where('user_type', 'guru')->where('user_id', $guru->id)->where('status', 'Izin')->whereBetween('tanggal', [$startOfMonth, $endOfMonth])->count(),
            'Sakit' => Presensi::where('user_type', 'guru')->where('user_id', $guru->id)->where('status', 'Sakit')->whereBetween('tanggal', [$startOfMonth, $endOfMonth])->count(),
            'Dinas Luar' => Presensi::where('user_type', 'guru')->where('user_id', $guru->id)->where('status', 'Dinas Luar')->whereBetween('tanggal', [$startOfMonth, $endOfMonth])->count(),
            'Cuti' => Presensi::where('user_type', 'guru')->where('user_id', $guru->id)->where('status', 'Cuti')->whereBetween('tanggal', [$startOfMonth, $endOfMonth])->count(),
            'Dispensasi' => Presensi::where('user_type', 'guru')->where('user_id', $guru->id)->where('status', 'Dispensasi')->whereBetween('tanggal', [$startOfMonth, $endOfMonth])->count(),
        ];

        return view('guru.izin.index', [
            'guru' => $guru,
            'riwayat' => $riwayat,
            'kategoriList' => self::KATEGORI,
            'filterStatus' => $filterStatus,
            'statBulanIni' => $statBulanIni,
            'openModal' => $request->query('open') == '1',
            'pengaturan' => PengaturanLembaga::first(),
        ]);
    }

    public function store(StoreGuruIzinRequest $request)
    {
        $guru = Auth::guard('guru')->user();
        $validated = $request->validated();

        $mulai = Carbon::parse($validated['tanggal_mulai']);
        $selesai = Carbon::parse($validated['tanggal_selesai']);
        if ($mulai->diffInDays($selesai) > 30) {
            return redirect()->back()->with('error', 'Rentang tanggal pengajuan maksimal adalah 30 hari.');
        }

        $berhasil = $this->izinService->recordIzinMultiDays(
            'guru',
            $guru->id,
            $validated['tanggal_mulai'],
            $validated['tanggal_selesai'],
            $validated['status'],
            $validated['keterangan'],
            $request->file('bukti'),
            'pending',
            null
        );

        return redirect()->route('guru.izin.index')->with('success', "Permohonan {$validated['status']} untuk {$berhasil} hari berhasil dikirimkan!");
    }
}
