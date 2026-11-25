<?php

namespace App\Http\Requests\Izin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiswaIzinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'          => 'required|in:Izin,Sakit',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'      => 'required|string|max:500',
            'bukti'           => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required'          => 'Jenis perizinan wajib dipilih.',
            'tanggal_mulai.required'   => 'Tanggal mulai izin wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai izin wajib diisi.',
            'keterangan.required'      => 'Alasan izin wajib dijelaskan.',
            'bukti.image'              => 'Bukti surat harus berupa file gambar (JPG/PNG).',
            'bukti.max'                => 'Ukuran foto bukti surat maksimal 3MB.',
        ];
    }
}
