<?php

namespace App\Http\Controllers;

use App\Http\Requests\KegiatanKhusus\StoreKegiatanKhususRequest;
use App\Http\Requests\KegiatanKhusus\UpdateKegiatanKhususRequest;
use App\Models\KegiatanKhusus;
use App\Models\Kelas;
use App\Models\PengaturanLembaga;
use App\Models\PresensiKegiatanKhusus;
use App\Services\KegiatanKhusus\KegiatanKhususService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KegiatanKhususController extends Controller
{
    protected KegiatanKhususService $kegiatanKhususService;

    public function __construct(KegiatanKhususService $kegiatanKhususService)
    {
        $this->kegiatanKhususService = $kegiatanKhususService;
    }

    /**
     * Tampilan Daftar Agenda Kegiatan Khusus
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $kegiatans = KegiatanKhusus::withCount('presensi')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama_kegiatan', 'like', "%{$search}%")
                        ->orWhere('kode_kegiatan', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_mulai', 'asc')
            ->paginate(10)
            ->withQueryString();

        $kelasList = Kelas::orderBy('tingkat')->orderBy('nama_rombel')->get();

        return view('kegiatan-khusus.index', compact('kegiatans', 'kelasList', 'search', 'status'));
    }

    /**
     * Simpan Kegiatan Baru
     */
    public function store(StoreKegiatanKhususRequest $request)
    {
        $this->kegiatanKhususService->store($request->validated());

        return redirect()->route('kegiatan-khusus.index')->with('success', 'Kegiatan khusus berhasil ditambahkan!');
    }

    /**
     * Update Kegiatan
     */
    public function update(UpdateKegiatanKhususRequest $request, $id)
    {
        $kegiatan = KegiatanKhusus::findOrFail($id);
        $this->kegiatanKhususService->update($kegiatan, $request->validated());

        return redirect()->route('kegiatan-khusus.index')->with('success', 'Data kegiatan berhasil diperbarui!');
    }

    /**
     * Hapus Kegiatan
     */
    public function destroy($id)
    {
        $kegiatan = KegiatanKhusus::findOrFail($id);
        $kegiatan->delete();

        return redirect()->route('kegiatan-khusus.index')->with('success', 'Kegiatan dan riwayat presensi terkait berhasil dihapus!');
    }

    /**
     * Tampilan Scanner QR Khusus Kegiatan Ini
     */
    public function scan($id)
    {
        $kegiatan = KegiatanKhusus::findOrFail($id);
        $pengaturan = PengaturanLembaga::first();
        $today = Carbon::today()->toDateString();

        $presensiQuery = PresensiKegiatanKhusus::where('kegiatan_khusus_id', $id);
        if ($kegiatan->tipe_kegiatan === 'rutin') {
            $presensiQuery->where('tanggal_presensi', $today);
        }

        $totalHadir = (clone $presensiQuery)
            ->whereIn('status', ['Hadir', 'Terlambat'])
            ->count();

        $recentLogs = (clone $presensiQuery)
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        return view('kegiatan-khusus.scan', compact('kegiatan', 'pengaturan', 'totalHadir', 'recentLogs'));
    }

    /**
     * API Proses Scan QR Kamera
     */
    public function processScan(Request $request, $id)
    {
        $kegiatan = KegiatanKhusus::findOrFail($id);
        $qr = trim($request->qr_code);

        if (!$qr) {
            return response()->json(['success' => false, 'message' => 'QR Code kosong atau tidak terbaca.'], 400);
        }

        $result = $this->kegiatanKhususService->processScan($kegiatan, $qr);

        return response()->json($result['data'], $result['status_code']);
    }

    /**
     * Tampilan Detail Kehadiran Peserta Per Kegiatan
     */
    public function peserta(Request $request, $id)
    {
        $kegiatan = KegiatanKhusus::with('presensi')->findOrFail($id);
        $tanggalFilter = $request->tanggal;

        $query = PresensiKegiatanKhusus::where('kegiatan_khusus_id', $id);

        if ($tanggalFilter) {
            $query->where('tanggal_presensi', $tanggalFilter);
        }

        $presensis = $query->orderBy('jam_masuk', 'desc')
            ->paginate(20)
            ->withQueryString();

        $daftarTanggal = PresensiKegiatanKhusus::where('kegiatan_khusus_id', $id)
            ->whereNotNull('tanggal_presensi')
            ->select('tanggal_presensi')
            ->distinct()
            ->orderBy('tanggal_presensi', 'desc')
            ->pluck('tanggal_presensi');

        return view('kegiatan-khusus.peserta', compact('kegiatan', 'presensis', 'tanggalFilter', 'daftarTanggal'));
    }
}
