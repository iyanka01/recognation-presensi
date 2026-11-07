<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    use HasUuids;

    protected $table = 'mata_pelajaran';
    protected $guarded = [];

    public function jadwal()
    {
        return $this->hasMany(JadwalPelajaran::class, 'mata_pelajaran_id');
    }
}
