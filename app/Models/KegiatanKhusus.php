<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class KegiatanKhusus extends Model
{
    use HasUuids;

    protected $table = 'kegiatan_khusus';
    protected $guarded = [];

    protected $casts = [
        'tanggal' => 'date',
        'kelas_ids' => 'array',
        'kirim_notifikasi' => 'boolean',
    ];

    public function presensi()
    {
        return $this->hasMany(PresensiKegiatanKhusus::class, 'kegiatan_khusus_id');
    }

    public function getJamRangeAttribute()
    {
        return substr($this->jam_mulai, 0, 5) . ' - ' . substr($this->jam_selesai, 0, 5);
    }

    public function getSasaranLabelAttribute()
    {
        if ($this->sasaran_peserta === 'Pilih Kelas') {
            if (!empty($this->kelas_ids) && is_array($this->kelas_ids)) {
                $kelasNames = Kelas::whereIn('id', $this->kelas_ids)
                    ->get()
                    ->map(fn($k) => "Kelas {$k->tingkat} {$k->nama_rombel}")
                    ->implode(', ');
                return $kelasNames ?: 'Pilih Kelas';
            }
            return 'Pilih Kelas';
        }
        return $this->sasaran_peserta;
    }
}
