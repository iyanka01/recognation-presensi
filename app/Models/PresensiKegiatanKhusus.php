<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PresensiKegiatanKhusus extends Model
{
    use HasUuids;

    protected $table = 'presensi_kegiatan_khusus';
    protected $guarded = [];

    protected $casts = [
        'jam_masuk' => 'datetime',
        'jam_pulang' => 'datetime',
    ];

    public function kegiatan()
    {
        return $this->belongsTo(KegiatanKhusus::class, 'kegiatan_khusus_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'user_id');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'user_id');
    }

    public function getPesertaAttribute()
    {
        return $this->user_type === 'guru' ? $this->guru : $this->siswa;
    }
}
