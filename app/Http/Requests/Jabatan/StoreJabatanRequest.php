<?php

namespace App\Http\Requests\Jabatan;

use Illuminate\Foundation\Http\FormRequest;

class StoreJabatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_jabatan' => ['required', 'string', 'max:255', 'unique:jabatans,nama_jabatan'],
            'kategori'     => ['required', 'in:guru,staff'],
            'keterangan'   => ['nullable', 'string'],
            'urutan'       => ['nullable', 'integer'],
        ];
    }
}
