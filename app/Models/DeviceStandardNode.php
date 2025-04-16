<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceStandardNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_model_id',
        'node_path',
    ];


    public function deviceModel()
    {
        return $this->belongsTo(DeviceModel::class);
    }
}
