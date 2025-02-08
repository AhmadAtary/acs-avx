<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'otp_code', 'attempts', 'expires_at'];

    public function isExpired()
    {
        return Carbon::now()->gt($this->expires_at);
    }
}
