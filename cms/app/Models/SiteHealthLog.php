<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteHealthLog extends Model
{
    protected $fillable = [
        'check_name',
        'status',
        'message',
        'details',
        'checked_at',
    ];

    protected $casts = [
        'details' => 'array',
        'checked_at' => 'datetime',
    ];
}
