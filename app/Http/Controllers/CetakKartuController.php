<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Kelas;
use Illuminate\Http\Request;

class CetakKartuController extends Controller
{
    public function index()
    {
        $kelasList = Kelas::orderBy('tingkat')->orderBy('nama_rombel')->get();
        $guruList = Guru::orderBy('nama_lengkap')->get();
        
        return view('cetak-kartu.index', compact('kelasList', 'guruList'));
    }

    public function getSiswa($kelas_id)
    {
        $siswa = Siswa::where('kelas_id', $kelas_id)->orderBy('nama_lengkap')->get(['id', 'nama_lengkap']);
        return response()->json($siswa);
    }

    public function print(Request $request)
    {
        $type = $request->input('type'); // 'siswa' or 'guru'
        $filter = $request->input('filter'); // 'semua', 'kelas', 'individu'
        $users = collect();

        if ($type === 'siswa') {
            if ($filter === 'kelas') {
                $kelasId = $request->input('kelas_id');
                $users = Siswa::where('kelas_id', $kelasId)->with('kelas')->orderBy('nama_lengkap')->get();
            } elseif ($filter === 'individu') {
                $siswaId = $request->input('siswa_id');
                $users = Siswa::where('id', $siswaId)->with('kelas')->get();
            }
        } elseif ($type === 'guru') {
            if ($filter === 'semua') {
                $users = Guru::orderBy('nama_lengkap')->get();
            } elseif ($filter === 'individu') {
                $guruId = $request->input('guru_id');
                $users = Guru::where('id', $guruId)->get();
            }
        }

        if ($users->isEmpty()) {
            return back()->with('error', 'Tidak ada data yang ditemukan untuk dicetak.');
        }

        return view('cetak-kartu.print', compact('users', 'type'));
    }
}
