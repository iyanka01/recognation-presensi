<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasUuids;

    protected $table = 'jabatans';
    protected $guarded = [];

    public function gurus()
    {
        return $this->hasMany(Guru::class, 'jabatan', 'nama_jabatan');
    }
}
