<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JurnalMengajar extends Model
{
    use HasUuids;

    protected $table = 'jurnal_mengajar';
    protected $guarded = [];

    public function jadwal()
    {
        return $this->belongsTo(JadwalPelajaran::class, 'jadwal_id');
    }
}
