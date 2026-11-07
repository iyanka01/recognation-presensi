<?php

namespace App\Http\Controllers;

use App\Models\PengaturanLembaga;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuruInformasiController extends Controller
{
    public function index(Request $request)
    {
        $kategori = $request->query('kategori');

        $query = Pengumuman::where('is_aktif', true)
            ->whereIn('target', ['semua', 'guru'])
            ->orderBy('created_at', 'desc');

        if ($kategori && $kategori !== 'Semua') {
            $query->where('kategori', $kategori);
        }

        $pengumumans = $query->get();

        return view('guru.informasi.index', [
            'pengumumans' => $pengumumans,
            'selectedKategori' => $kategori ?: 'Semua',
            'pengaturan' => PengaturanLembaga::first(),
        ]);
    }

    public function show($id)
    {
        $pengumuman = Pengumuman::where('is_aktif', true)
            ->whereIn('target', ['semua', 'guru'])
            ->findOrFail($id);

        return view('guru.informasi.show', [
            'pengumuman' => $pengumuman,
            'pengaturan' => PengaturanLembaga::first(),
        ]);
    }
}
