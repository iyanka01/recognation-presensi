<?php

namespace App\Http\Requests\HariLibur;

use Illuminate\Foundation\Http\FormRequest;

class StoreHariLiburRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal' => 'required|date|unique:hari_liburs,tanggal',
            'keterangan' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.unique' => 'Tanggal ini sudah diatur sebagai hari libur.',
        ];
    }
}
