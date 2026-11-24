<?php

namespace App\Http\Controllers;

use App\Models\KegiatanKhusus;
use App\Models\PresensiKegiatanKhusus;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GuruKegiatanController extends Controller
{
    /**
     * Tampilan Halaman Absen Kegiatan untuk Guru
     */
    public function index()
    {
        Carbon::setLocale('id');

        $today = Carbon::today()->toDateString();

        $kegiatans = KegiatanKhusus::where('sasaran_peserta', '!=', 'Khusus Guru')
            ->orderByRaw("CASE WHEN tipe_kegiatan = 'rutin' THEN 0 ELSE 1 END")
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_mulai', 'asc')
            ->get();

        foreach ($kegiatans as $k) {
            $q = PresensiKegiatanKhusus::where('kegiatan_khusus_id', $k->id);
            if ($k->tipe_kegiatan === 'rutin') {
                $q->where('tanggal_presensi', $today);
            }
            $k->total_hadir = $q->whereIn('status', ['Hadir', 'Terlambat'])->count();
        }

        return view('guru.kegiatan.index', compact('kegiatans'));
    }
}
