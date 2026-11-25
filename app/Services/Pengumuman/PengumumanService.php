<?php

namespace App\Services\Pengumuman;

use App\Models\Pengumuman;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PengumumanService
{
    public function store(array $validated, ?UploadedFile $lampiran = null): Pengumuman
    {
        $lampiranPath = null;
        if ($lampiran) {
            $lampiranPath = $lampiran->store('pengumuman', 'public');
        }

        return Pengumuman::create([
            'judul' => $validated['judul'],
            'konten' => $validated['konten'],
            'kategori' => $validated['kategori'],
            'target' => $validated['target'],
            'lampiran' => $lampiranPath,
            'is_aktif' => true,
        ]);
    }

    public function update(Pengumuman $pengumuman, array $validated, ?UploadedFile $lampiran = null): bool
    {
        $lampiranPath = $pengumuman->lampiran;
        if ($lampiran) {
            if ($lampiranPath && Storage::disk('public')->exists($lampiranPath)) {
                Storage::disk('public')->delete($lampiranPath);
            }
            $lampiranPath = $lampiran->store('pengumuman', 'public');
        }

        return $pengumuman->update([
            'judul' => $validated['judul'],
            'konten' => $validated['konten'],
            'kategori' => $validated['kategori'],
            'target' => $validated['target'],
            'lampiran' => $lampiranPath,
            'is_aktif' => isset($validated['is_aktif']) ? (bool) $validated['is_aktif'] : true,
        ]);
    }

    public function delete(Pengumuman $pengumuman): ?bool
    {
        if ($pengumuman->lampiran && Storage::disk('public')->exists($pengumuman->lampiran)) {
            Storage::disk('public')->delete($pengumuman->lampiran);
        }
        return $pengumuman->delete();
    }
}
