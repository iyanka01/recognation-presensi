<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PengaturanLembaga extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nama_yayasan',
        'nama_lembaga',
        'logo',
        'telegram_bot_token',
        'akreditasi',
        'website',
        'nama_kepala',
        'nip',
        'no_telp',
        'kepala_sekolah_id',
        'wanesia_token',
        'wanesia_active',
        'batas_absen_guru_hari',
        'notif_peringatan_guru_active',
        'template_peringatan_guru',
        'alamat',
        'tempat',
        'latitude',
        'longitude',
        'radius_meter',
    ];

    public function kepalaSekolah()
    {
        return $this->belongsTo(Guru::class, 'kepala_sekolah_id');
    }
}
