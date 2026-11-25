<?php

namespace App\Http\Controllers;

use App\Http\Requests\AbsensiMapel\StoreAbsensiMapelRequest;
use App\Models\JadwalPelajaran;
use App\Services\AbsensiMapel\AbsensiMapelService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiMapelController extends Controller
{
    protected AbsensiMapelService $absensiMapelService;

    public function __construct(AbsensiMapelService $absensiMapelService)
    {
        $this->absensiMapelService = $absensiMapelService;
    }

    // Daftar jadwal mengajar guru untuk tanggal terpilih
    public function index(Request $request)
    {
        $guru = Auth::guard('guru')->user();
        $tanggal = $request->query('tanggal', Carbon::today()->toDateString());

        $data = $this->absensiMapelService->getJadwalMengajarList($guru, $tanggal);

        return view('absensi-mapel.index', $data);
    }

    // Form daftar siswa + status kehadiran
    public function show(Request $request, $id)
    {
        $guru = Auth::guard('guru')->user();
        $jadwal = JadwalPelajaran::with(['kelas.siswa', 'mataPelajaran'])->findOrFail($id);
        $tanggal = $request->query('tanggal', Carbon::today()->toDateString());

        $eval = $this->absensiMapelService->validateAccessAndTiming($jadwal, $guru, $tanggal);

        if (!$eval['allowed']) {
            if (!empty($eval['status_code'])) {
                abort($eval['status_code'], $eval['error']);
            }
            if (!empty($eval['redirect_dashboard'])) {
                return redirect()->route('dashboard')->with('error', $eval['error']);
            }
            if (!empty($eval['redirect_index'])) {
                return redirect()->route('absensi-mapel.index', ['tanggal' => $tanggal])->with('error', $eval['error']);
            }
        }

        $formData = $this->absensiMapelService->getFormData($jadwal, $tanggal, $eval['canAbsen'], $eval['isWaliKelas']);

        return view('absensi-mapel.form', $formData);
    }

    // Simpan jurnal + absensi massal
    public function store(StoreAbsensiMapelRequest $request, $id)
    {
        $guru = Auth::guard('guru')->user();
        $jadwal = JadwalPelajaran::with('kelas')->findOrFail($id);
        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());

        $eval = $this->absensiMapelService->validateAccessAndTiming($jadwal, $guru, $tanggal);

        if (!$eval['allowed']) {
            if (!empty($eval['status_code'])) {
                abort($eval['status_code'], $eval['error']);
            }
            if (!empty($eval['redirect_dashboard'])) {
                return redirect()->route('dashboard')->with('error', $eval['error']);
            }
            if (!empty($eval['redirect_index'])) {
                return redirect()->route('absensi-mapel.index', ['tanggal' => $tanggal])->with('error', $eval['error']);
            }
        }

        $this->absensiMapelService->simpanAbsensiDanJurnal($jadwal, $request->validated());

        return redirect()
            ->route('absensi-mapel.index', ['tanggal' => $tanggal])
            ->with('success', 'Absensi mata pelajaran berhasil disimpan!');
    }
}
