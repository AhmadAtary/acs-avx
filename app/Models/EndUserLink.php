<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EndUserLink extends Model
{

    protected $fillable = [
        'token',
        'username',
        'password',
        'expires_at',
        'is_used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean',
    ];
}
