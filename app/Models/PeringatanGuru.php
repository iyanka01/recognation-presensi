<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PeringatanGuru extends Model
{
    use HasUuids;

    protected $table = 'peringatan_gurus';

    protected $fillable = [
        'guru_id',
        'jumlah_hari_alpa',
        'tanggal_kirim',
        'no_hp',
        'pesan',
        'status_kirim',
        'keterangan',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }
}
