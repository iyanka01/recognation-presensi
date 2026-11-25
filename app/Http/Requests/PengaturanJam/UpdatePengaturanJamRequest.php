<?php

namespace App\Http\Requests\PengaturanJam;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePengaturanJamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jam_mulai' => 'required|date_format:H:i',
            'jam_terlambat' => 'required|date_format:H:i',
            'jam_pulang' => 'required|date_format:H:i',
        ];
    }
}
