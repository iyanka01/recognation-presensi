<?php

namespace App\Http\Requests\Izin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminIzinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required', // Format: "siswa|uuid" or "guru|uuid"
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:Izin,Sakit,Alpa,Dinas Luar,Cuti,Dispensasi',
            'keterangan' => 'nullable|string|max:255',
        ];
    }
}
