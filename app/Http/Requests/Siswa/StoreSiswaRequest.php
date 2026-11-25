<?php

namespace App\Http\Requests\Siswa;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nis' => 'required|string|unique:siswa,nis',
            'nisn' => 'nullable|string|unique:siswa,nisn',
            'nama_lengkap' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|string',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'kelas_id' => 'required|exists:kelas,id',
            'no_hp_wali' => 'nullable|string',
            'telegram_chat_id' => 'nullable|string',
            'foto_siswa' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
