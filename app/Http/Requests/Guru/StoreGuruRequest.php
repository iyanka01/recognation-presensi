<?php

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuruRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nip' => 'nullable|string|unique:guru,nip',
            'nama_lengkap' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|string',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'jabatan' => 'required|string',
            'no_hp' => 'nullable|string',
            'telegram_chat_id' => 'nullable|string',
            'foto_guru' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'nullable|string|min:6',
        ];
    }
}
