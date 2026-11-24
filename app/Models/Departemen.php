<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Departemen extends Model
{
    use HasUuids;

    protected $table = 'departemen';
    protected $guarded = [];

    /**
     * Relasi ke Guru sebagai Kepala Sekolah unit
     */
    public function kepalaSekolah()
    {
        return $this->belongsTo(Guru::class, 'kepala_sekolah_id');
    }

    /**
     * Relasi ke Kelas dalam Departemen ini
     */
    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'departemen_id');
    }
}
