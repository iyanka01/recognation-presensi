<?php

namespace App\Http\Requests\Kelas;

use Illuminate\Foundation\Http\FormRequest;

class StoreKelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'departemen_id' => 'required|exists:departemen,id',
            'tingkat'       => 'required|string',
            'nama_rombel'   => 'required|array|min:1',
            'nama_rombel.*' => 'required|string',
            'wali_kelas_id' => 'nullable|exists:guru,id',
        ];
    }
}
