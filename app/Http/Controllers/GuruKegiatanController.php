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

        // Ambil kegiatan khusus yang sasarannya untuk Siswa (bukan 'Khusus Guru')
        // Menampilkan kegiatan aktif / yang berlangsung / akan datang (termasuk hari ini)
        $kegiatans = KegiatanKhusus::where('sasaran_peserta', '!=', 'Khusus Guru')
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_mulai', 'asc')
            ->get();

        foreach ($kegiatans as $k) {
            $k->total_hadir = PresensiKegiatanKhusus::where('kegiatan_khusus_id', $k->id)
                ->whereIn('status', ['Hadir', 'Terlambat'])
                ->count();
        }

        return view('guru.kegiatan.index', compact('kegiatans'));
    }
}
