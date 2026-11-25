<?php

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGuruRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $guru = $this->route('guru');
        $guruId = is_object($guru) ? $guru->id : $guru;

        return [
            'nip' => 'nullable|string|unique:guru,nip,' . $guruId,
            'nama_lengkap' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|string',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'jabatan' => 'required|string',
            'no_hp' => 'nullable|string',
            'telegram_chat_id' => 'nullable|string',
            'foto_guru' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:Aktif,Nonaktif,Pensiun,Pindah',
            'password' => 'nullable|string|min:6|confirmed',
        ];
    }
}
