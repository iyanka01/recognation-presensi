<?php

namespace App\Http\Requests\PengaturanJam;

use Illuminate\Foundation\Http\FormRequest;

class StorePengaturanJamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_id' => 'required', // 'guru', 'dep_UUID', or UUID of kelas
            'hari' => 'required|string',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_terlambat' => 'required|date_format:H:i',
            'jam_pulang' => 'required|date_format:H:i',
            'copy_to_departemens' => 'nullable|array',
            'copy_to_classes' => 'nullable|array',
        ];
    }
}
