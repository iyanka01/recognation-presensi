<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengaturanLembaga\StorePengaturanLembagaRequest;
use App\Models\Guru;
use App\Models\PengaturanLembaga;
use App\Services\PengaturanLembaga\PengaturanLembagaService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class PengaturanLembagaController extends Controller
{
    protected PengaturanLembagaService $pengaturanLembagaService;

    public function __construct(PengaturanLembagaService $pengaturanLembagaService)
    {
        $this->pengaturanLembagaService = $pengaturanLembagaService;
    }

    public function index()
    {
        $lembaga = PengaturanLembaga::first();
        $user = auth()->user();
        $gurus = Guru::orderBy('nama_lengkap', 'asc')->get();
        return view('pengaturan.lembaga', compact('lembaga', 'user', 'gurus'));
    }

    public function store(StorePengaturanLembagaRequest $request)
    {
        $this->pengaturanLembagaService->saveSettings($request->validated(), $request->file('logo'));

        return redirect()->route('pengaturan-lembaga.index')->with('success', 'Pengaturan Lembaga berhasil disimpan!');
    }

    public function testWhatsApp(Request $request, WhatsAppService $wa)
    {
        $request->validate([
            'test_number' => 'required|string',
            'test_token'  => 'nullable|string',
        ]);

        $lembaga = PengaturanLembaga::first();
        $namaInstansi = $lembaga && !empty($lembaga->nama_yayasan) ? $lembaga->nama_yayasan : ($lembaga->nama_lembaga ?? 'Sistem Presensi Sekolah');

        $pesan = "Halo! Ini adalah pesan uji coba integrasi WhatsApp Gateway (Wanesia) dari {$namaInstansi}. Integrasi BERHASIL aktif! ✅";
        $res = $wa->sendMessage($request->test_number, $pesan, $request->test_token);

        return response()->json($res);
    }
}
