<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    use HasUuids;

    protected $table = 'pengumuman';
    protected $guarded = [];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];
}
