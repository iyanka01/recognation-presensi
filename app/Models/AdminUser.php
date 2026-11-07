<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
    use HasUuids;

    protected $table = 'admin_users';
    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];
}
