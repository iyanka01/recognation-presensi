<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityAuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'security_audit_logs';

    protected $fillable = [
        'event',
        'user_type',
        'user_id',
        'username',
        'ip_address',
        'user_agent',
        'details',
        'created_at',
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];
}
