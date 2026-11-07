<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasUuids;

    protected $table = 'kelas';
    protected $guarded = [];

    public function wali_kelas()
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    public function waliKelas()
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }
}
