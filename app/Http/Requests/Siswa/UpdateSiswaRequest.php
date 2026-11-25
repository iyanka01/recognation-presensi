<?php

namespace App\Http\Requests\Siswa;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $siswa = $this->route('siswa');
        $siswaId = is_object($siswa) ? $siswa->id : $siswa;

        return [
            'nis' => 'required|string|unique:siswa,nis,' . $siswaId,
            'nisn' => 'nullable|string|unique:siswa,nisn,' . $siswaId,
            'nama_lengkap' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|string',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'kelas_id' => 'required|exists:kelas,id',
            'no_hp_wali' => 'nullable|string',
            'telegram_chat_id' => 'nullable|string',
            'foto_siswa' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:Aktif,Lulus,Pindah,Dikeluarkan',
        ];
    }
}
