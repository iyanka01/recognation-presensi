<?php

namespace App\Http\Requests\Jabatan;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJabatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('jabatan');

        return [
            'nama_jabatan' => ['required', 'string', 'max:255', 'unique:jabatans,nama_jabatan,' . $id],
            'kategori'     => ['required', 'in:guru,staff'],
            'keterangan'   => ['nullable', 'string'],
            'urutan'       => ['nullable', 'integer'],
        ];
    }
}
