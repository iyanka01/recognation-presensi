<?php

namespace App\Http\Requests\Pengumuman;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePengumumanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'kategori' => 'required|in:Umum,Penting,Akademik,Kegiatan,Libur',
            'target' => 'required|in:semua,guru,siswa',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,docx|max:10240',
            'is_aktif' => 'nullable',
        ];
    }
}
