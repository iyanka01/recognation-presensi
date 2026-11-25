<?php

namespace App\Services\Departemen;

use App\Models\Departemen;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DepartemenService
{
    public function store(array $validated, ?UploadedFile $logo = null): Departemen
    {
        if ($logo) {
            $validated['logo'] = $logo->store('departemen', 'public');
        }

        return Departemen::create($validated);
    }

    public function update(Departemen $departemen, array $validated, ?UploadedFile $logo = null): bool
    {
        if ($logo) {
            if ($departemen->logo && Storage::disk('public')->exists($departemen->logo)) {
                Storage::disk('public')->delete($departemen->logo);
            }
            $validated['logo'] = $logo->store('departemen', 'public');
        }

        return $departemen->update($validated);
    }

    public function delete(Departemen $departemen): array
    {
        if ($departemen->kelas()->count() > 0) {
            return [
                'success' => false,
                'message' => 'Departemen tidak dapat dihapus karena masih memiliki data kelas!',
            ];
        }

        if ($departemen->logo && Storage::disk('public')->exists($departemen->logo)) {
            Storage::disk('public')->delete($departemen->logo);
        }

        $departemen->delete();

        return [
            'success' => true,
            'message' => 'Departemen berhasil dihapus!',
        ];
    }
}
