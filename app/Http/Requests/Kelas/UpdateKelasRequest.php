<?php

namespace App\Http\Requests\Kelas;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'departemen_id' => 'nullable|exists:departemen,id',
            'tingkat'       => 'required|string',
            'nama_rombel'   => 'required|string',
            'wali_kelas_id' => 'nullable|exists:guru,id',
        ];
    }
}
