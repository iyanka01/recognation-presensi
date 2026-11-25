<?php

namespace App\Http\Controllers;

use App\Http\Requests\Izin\StoreAdminIzinRequest;
use App\Models\Guru;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Services\Izin\IzinService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IzinController extends Controller
{
    protected IzinService $izinService;

    public function __construct(IzinService $izinService)
    {
        $this->izinService = $izinService;
    }

    public function index()
    {
        $siswas = Siswa::with('kelas')->orderBy('nama_lengkap')->get();
        $gurus = Guru::orderBy('nama_lengkap')->get();

        $izins = Presensi::whereIn('status', ['Izin', 'Sakit', 'Alpa', 'Dinas Luar', 'Cuti', 'Dispensasi'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('izin.index', compact('siswas', 'gurus', 'izins'));
    }

    public function store(StoreAdminIzinRequest $request)
    {
        $validated = $request->validated();
        $parts = explode('|', $validated['user_id']);
        if (count($parts) !== 2) {
            return redirect()->back()->with('error', 'Format user tidak valid.');
        }

        $userType = $parts[0];
        $userId = $parts[1];

        $mulai = Carbon::parse($validated['tanggal_mulai']);
        $selesai = Carbon::parse($validated['tanggal_selesai']);
        if ($mulai->diffInDays($selesai) > 30) {
            return redirect()->back()->with('error', 'Rentang tanggal maksimal adalah 30 hari.');
        }

        $berhasil = $this->izinService->recordIzinMultiDays(
            $userType,
            $userId,
            $validated['tanggal_mulai'],
            $validated['tanggal_selesai'],
            $validated['status'],
            $validated['keterangan'] ?? '',
            null,
            'approved',
            'Dicatat langsung oleh Admin'
        );

        return redirect()->route('izin.index')->with('success', "Berhasil mencatat $berhasil hari perizinan/ketidakhadiran.");
    }

    public function approve($id)
    {
        $izin = Presensi::findOrFail($id);
        $this->izinService->approve($izin);

        return redirect()->route('izin.index')->with('success', 'Pengajuan izin berhasil DISETUJUI.');
    }

    public function reject(Request $request, $id)
    {
        $izin = Presensi::findOrFail($id);
        $this->izinService->reject($izin, $request->input('catatan_approval'));

        return redirect()->route('izin.index')->with('success', 'Pengajuan izin berhasil DITOLAK.');
    }

    public function destroy($id)
    {
        $izin = Presensi::findOrFail($id);
        $this->izinService->delete($izin);

        return redirect()->route('izin.index')->with('success', 'Data perizinan berhasil dihapus.');
    }
}
