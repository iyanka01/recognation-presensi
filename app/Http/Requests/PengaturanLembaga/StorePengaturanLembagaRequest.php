<?php

namespace App\Http\Requests\PengaturanLembaga;

use Illuminate\Foundation\Http\FormRequest;

class StorePengaturanLembagaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_yayasan' => 'nullable|string|max:255',
            'nama_lembaga' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'kepala_sekolah_id' => 'nullable|exists:guru,id',
            'nama_kepala' => 'nullable|string|max:255',
            'nip' => 'nullable|string|max:255',
            'no_telp' => 'nullable|string|max:255',
            'telegram_bot_token' => 'nullable|string|max:255',
            'wanesia_token' => 'nullable|string|max:255',
            'wanesia_active' => 'nullable|boolean',
            'batas_absen_guru_hari' => 'nullable|integer|min:1|max:365',
            'notif_peringatan_guru_active' => 'nullable|boolean',
            'template_peringatan_guru' => 'nullable|string|max:2000',
            'akreditasi' => 'nullable|in:A,B,C,D',
            'website' => 'nullable|string|max:255',
            'alamat' => 'nullable|string',
            'tempat' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius_meter' => 'nullable|integer|min:10|max:50000',
        ];
    }
}
