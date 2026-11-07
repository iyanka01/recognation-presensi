<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Presensi extends Model
{
    use HasUuids;

    protected $table = 'presensi';
    protected $guarded = [];

    // Helper to get the related user (Siswa or Guru)
    public function getUserAttribute()
    {
        if ($this->user_type === 'siswa') {
            return \App\Models\Siswa::find($this->user_id);
        }
        return \App\Models\Guru::find($this->user_id);
    }
}
