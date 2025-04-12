<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceUser extends Model
{
    protected $table = 'device_user';

    protected $fillable = ['user_id', 'serial_number'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
