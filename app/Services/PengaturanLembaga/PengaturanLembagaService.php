<?php

namespace App\Services\PengaturanLembaga;

use App\Models\Departemen;
use App\Models\Guru;
use App\Models\PengaturanLembaga;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PengaturanLembagaService
{
    public function saveSettings(array $validated, ?UploadedFile $logo = null): PengaturanLembaga
    {
        $lembaga = PengaturanLembaga::first();

        if (empty($validated['nama_lembaga'])) {
            $validated['nama_lembaga'] = $lembaga->nama_lembaga ?? (Departemen::first()?->nama_departemen ?? 'Sekolah');
        }

        $validated['wanesia_active'] = !empty($validated['wanesia_active']);
        $validated['notif_peringatan_guru_active'] = !empty($validated['notif_peringatan_guru_active']);

        if (!empty($validated['kepala_sekolah_id'])) {
            $guru = Guru::find($validated['kepala_sekolah_id']);
            if ($guru) {
                $validated['nama_kepala'] = $guru->nama_lengkap;
                if (!empty($guru->nip)) $validated['nip'] = $guru->nip;
                if (!empty($guru->no_hp)) $validated['no_telp'] = $guru->no_hp;
            }
        }

        if (empty($validated['nama_kepala'])) {
            $validated['nama_kepala'] = $lembaga->nama_kepala ?? '-';
        }

        if ($logo) {
            if ($lembaga && $lembaga->logo && Storage::disk('public')->exists($lembaga->logo)) {
                Storage::disk('public')->delete($lembaga->logo);
            }

            $validated['logo'] = $logo->store('lembaga', 'public');

            try {
                $logo->move(public_path(), 'favicon.ico');
                copy(public_path('favicon.ico'), public_path('favicon.png'));
                Storage::disk('public')->put($validated['logo'], file_get_contents(public_path('favicon.ico')));
            } catch (\Exception $e) {
                // Ignore permission issues for favicon
            }
        }

        if ($lembaga) {
            $lembaga->update($validated);
            return $lembaga;
        }

        return PengaturanLembaga::create($validated);
    }
}
