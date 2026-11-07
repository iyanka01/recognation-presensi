<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Siswa extends Authenticatable
{
    use HasUuids;

    protected $table = 'siswa';
    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_password_changed' => 'boolean',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
}
