<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'action', 'response', 'device_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
