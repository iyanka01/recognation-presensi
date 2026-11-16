<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengaturanLembaga;

use App\Models\Guru;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Storage;

class PengaturanLembagaController extends Controller
{
    public function index()
    {
        $lembaga = PengaturanLembaga::first();
        $user = auth()->user();
        $gurus = Guru::orderBy('nama_lengkap', 'asc')->get();
        return view('pengaturan.lembaga', compact('lembaga', 'user', 'gurus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_yayasan' => 'nullable|string|max:255',
            'nama_lembaga' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'kepala_sekolah_id' => 'nullable|exists:guru,id',
            'nama_kepala' => 'required|string|max:255',
            'nip' => 'nullable|string|max:255',
            'no_telp' => 'nullable|string|max:255',
            'telegram_bot_token' => 'nullable|string|max:255',
            'wanesia_token' => 'nullable|string|max:255',
            'wanesia_active' => 'nullable|boolean',
            'akreditasi' => 'nullable|in:A,B,C,D',
            'website' => 'nullable|string|max:255',
            'alamat' => 'nullable|string',
            'tempat' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius_meter' => 'nullable|integer|min:10|max:50000',
        ]);

        $data = $request->except('logo');
        $data['wanesia_active'] = $request->has('wanesia_active');
        $lembaga = PengaturanLembaga::first();

        // Jika kepala sekolah dipilih dari data guru, sinkronkan data profilnya
        if ($request->filled('kepala_sekolah_id')) {
            $guru = Guru::find($request->kepala_sekolah_id);
            if ($guru) {
                $data['nama_kepala'] = $guru->nama_lengkap;
                if (!empty($guru->nip)) $data['nip'] = $guru->nip;
                if (!empty($guru->no_hp)) $data['no_telp'] = $guru->no_hp;
            }
        }

        if ($request->hasFile('logo')) {
            if ($lembaga && $lembaga->logo) {
                Storage::disk('public')->delete($lembaga->logo);
            }
            $data['logo'] = $request->file('logo')->store('lembaga', 'public');
        }

        if ($lembaga) {
            $lembaga->update($data);
        } else {
            PengaturanLembaga::create($data);
        }

        return redirect()->route('pengaturan-lembaga.index')->with('success', 'Pengaturan Lembaga berhasil disimpan!');
    }

    public function testWhatsApp(Request $request, WhatsAppService $wa)
    {
        $request->validate([
            'test_number' => 'required|string',
            'test_token'  => 'nullable|string',
        ]);

        $pesan = "Halo! Ini adalah pesan uji coba integrasi WhatsApp Gateway (Wanesia) dari Sistem Presensi Sekolah Mayantara. Integrasi BERHASIL aktif! ✅";
        $res = $wa->sendMessage($request->test_number, $pesan, $request->test_token);

        return response()->json($res);
    }
}
