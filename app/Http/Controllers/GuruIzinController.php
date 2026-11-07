<?php

namespace App\Http\Controllers;

use App\Models\PengaturanLembaga;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GuruIzinController extends Controller
{
    const KATEGORI = ['Izin', 'Sakit', 'Dinas Luar', 'Cuti', 'Dispensasi'];

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

        // Statistik bulan ini per kategori
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

    public function store(Request $request)
    {
        $guru = Auth::guard('guru')->user();

        $request->validate([
            'status' => 'required|in:' . implode(',', self::KATEGORI),
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'required|string|max:500',
            'bukti' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120', // max 5MB
        ]);

        $mulai = Carbon::parse($request->tanggal_mulai);
        $selesai = Carbon::parse($request->tanggal_selesai);
        $diff = $mulai->diffInDays($selesai);

        if ($diff > 30) {
            return redirect()->back()->with('error', 'Rentang tanggal pengajuan maksimal adalah 30 hari.');
        }

        // Upload bukti jika ada
        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('bukti_izin', 'public');
        }

        $berhasil = 0;
        for ($i = 0; $i <= $diff; $i++) {
            $tgl = $mulai->copy()->addDays($i)->toDateString();

            $existing = Presensi::where('user_type', 'guru')
                ->where('user_id', $guru->id)
                ->where('tanggal', $tgl)
                ->first();

            if ($existing) {
                $existing->update([
                    'status' => $request->status,
                    'keterangan' => $request->keterangan,
                    'bukti' => $buktiPath ?: $existing->bukti,
                ]);
            } else {
                Presensi::create([
                    'user_type' => 'guru',
                    'user_id' => $guru->id,
                    'tanggal' => $tgl,
                    'status' => $request->status,
                    'keterangan' => $request->keterangan,
                    'bukti' => $buktiPath,
                ]);
            }
            $berhasil++;
        }

        return redirect()->route('guru.izin.index')->with('success', "Permohonan {$request->status} untuk {$berhasil} hari berhasil dikirimkan!");
    }
}
