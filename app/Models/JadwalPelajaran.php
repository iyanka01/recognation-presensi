<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JadwalPelajaran extends Model
{
    use HasUuids;

    protected $table = 'jadwal_pelajaran';
    protected $guarded = [];
    protected $appends = ['jam_range'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function presensiMapel()
    {
        return $this->hasMany(PresensiMapel::class, 'jadwal_id');
    }

    public function jurnalMengajar()
    {
        return $this->hasMany(JurnalMengajar::class, 'jadwal_id');
    }

    public function getJamRangeAttribute()
    {
        return substr($this->jam_mulai, 0, 5) . ' - ' . substr($this->jam_selesai, 0, 5);
    }
}
