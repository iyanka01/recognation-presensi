<?php

namespace App\Http\Requests\Departemen;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartemenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $departemen = $this->route('departemen') ?? $this->route('departeman') ?? $this->route('id');
        $id = is_object($departemen) ? $departemen->id : $departemen;

        return [
            'kode_departemen'    => ['required', 'string', 'max:50', 'unique:departemen,kode_departemen,' . $id],
            'nama_departemen'    => ['required', 'string', 'max:255'],
            'tingkatan'          => ['required', 'string', 'max:100'],
            'kepala_sekolah_id'  => ['nullable', 'exists:guru,id'],
            'status'             => ['required', 'in:Aktif,Tidak Aktif'],
            'logo'               => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'alamat'             => ['nullable', 'string'],
            'no_telp'            => ['nullable', 'string', 'max:50'],
            'keterangan'         => ['nullable', 'string'],
        ];
    }
}
