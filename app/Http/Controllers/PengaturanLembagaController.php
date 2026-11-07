<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengaturanLembaga;

use Illuminate\Support\Facades\Storage;

class PengaturanLembagaController extends Controller
{
    public function index()
    {
        $lembaga = PengaturanLembaga::first();
        $user = auth()->user();
        return view('pengaturan.lembaga', compact('lembaga', 'user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_yayasan' => 'nullable|string|max:255',
            'nama_lembaga' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'telegram_bot_token' => 'nullable|string|max:255',
            'akreditasi' => 'nullable|in:A,B,C,D',
            'website' => 'nullable|string|max:255',
            'nama_kepala' => 'required|string|max:255',
            'nip' => 'nullable|string|max:255',
            'no_telp' => 'nullable|string|max:255',
            'alamat' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius_meter' => 'nullable|integer|min:10|max:50000',
        ]);

        $data = $request->except('logo');
        $lembaga = PengaturanLembaga::first();

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
}
