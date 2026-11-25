<?php

namespace App\Http\Requests\KegiatanKhusus;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKegiatanKhususRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_kegiatan'   => 'required|string|max:255',
            'tipe_kegiatan'   => 'required|in:sekali,rutin',
            'tanggal'         => 'nullable|date',
            'hari'            => 'nullable|array',
            'jam_mulai'       => 'required',
            'jam_selesai'     => 'required',
            'mode_absen'      => 'required|string',
            'sasaran_peserta' => 'required|string',
            'kelas_ids'       => 'nullable|array',
            'kode_kegiatan'   => 'nullable|string|max:50',
            'keterangan'      => 'nullable|string',
            'status'          => 'nullable|string',
            'kirim_notifikasi'=> 'nullable|boolean',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->tipe_kegiatan === 'sekali' && empty($this->tanggal)) {
                $validator->errors()->add('tanggal', 'Tanggal wajib diisi untuk kegiatan sekali saja.');
            }
            if ($this->tipe_kegiatan === 'rutin' && empty($this->hari)) {
                $validator->errors()->add('hari', 'Pilih minimal satu hari untuk kegiatan rutin.');
            }
        });
    }
}
