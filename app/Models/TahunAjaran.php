<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    use HasUuids;

    protected $table = 'tahun_ajaran';
    protected $guarded = [];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_akhir' => 'date',
    ];

    /**
     * Relasi ke Kelas dalam Tahun Ajaran ini
     */
    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'tahun_ajaran_id');
    }

    /**
     * Scope untuk mendapatkan tahun ajaran yang sedang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Aktif');
    }
}
