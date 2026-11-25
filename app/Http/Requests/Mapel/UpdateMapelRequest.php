<?php

namespace App\Http\Requests\Mapel;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMapelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('mapel');

        return [
            'kode_mapel' => 'required|string|max:20|unique:mata_pelajaran,kode_mapel,' . $id,
            'nama_mapel' => 'required|string|max:100',
        ];
    }
}
