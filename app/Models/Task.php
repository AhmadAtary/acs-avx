<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'username',
        'device_id',
        'subject',
        'description',
        'user_email',
        'email',
    ];

    public $timestamps = true;
}
