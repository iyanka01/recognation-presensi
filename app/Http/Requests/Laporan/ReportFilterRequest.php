<?php

namespace App\Http\Requests\Laporan;

use Illuminate\Foundation\Http\FormRequest;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mode' => 'nullable|in:rentang,bulan',
            'bulan' => 'nullable|string',
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date',
            'tipe' => 'nullable|in:siswa,guru,staff',
            'kelas_id' => 'nullable|string',
            'departemen_id' => 'nullable|string',
            'jabatan' => 'nullable|string',
            'id' => 'nullable|string', // Untuk printSiswa
        ];
    }
}
