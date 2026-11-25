<?php

namespace App\Http\Requests\AbsensiMapel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAbsensiMapelRequest extends FormRequest
{
    const STATUS = ['Hadir', 'Izin', 'Sakit', 'Alpa'];

    public function authorize(): bool
    {
        $guru = \Illuminate\Support\Facades\Auth::guard('guru')->user();
        if (!$guru) {
            return false;
        }

        $id = $this->route('id');
        $jadwal = \App\Models\JadwalPelajaran::with('kelas')->find($id);
        if (!$jadwal) {
            return true; // will 404 in controller
        }

        $isOwner = ($jadwal->guru_id === $guru->id);
        $isWaliKelas = ($jadwal->kelas && $jadwal->kelas->wali_kelas_id === $guru->id);

        return $isOwner || $isWaliKelas;
    }

    public function rules(): array
    {
        return [
            // Data pembelajaran (jurnal mengajar)
            'jumlah_jam' => 'required|integer|min:1|max:15',
            'materi' => 'required|string|max:500',
            'materi_selanjutnya' => 'nullable|string|max:500',
            'keterangan_pembelajaran' => 'required|string|max:500',
            // Daftar kehadiran siswa
            'tanggal' => 'required|date',
            'absensi' => 'required|array|min:1',
            'absensi.*.siswa_id' => 'required|exists:siswa,id',
            'absensi.*.status' => ['required', Rule::in(self::STATUS)],
            'absensi.*.keterangan' => 'nullable|string|max:255',
        ];
    }
}
