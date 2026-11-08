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
        'alamat',
        'tempat',
        'latitude',
        'longitude',
        'radius_meter',
    ];
}
