<?php

namespace App\Http\Requests\Izin;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuruIzinRequest extends FormRequest
{
    const KATEGORI = ['Izin', 'Sakit', 'Dinas Luar', 'Cuti', 'Dispensasi'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:' . implode(',', self::KATEGORI),
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'required|string|max:500',
            'bukti' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ];
    }
}
