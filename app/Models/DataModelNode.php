<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataModelNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_model_id', // ✅ Allows mass assignment for the device model
        'name',
        'path',
        'type',
    ];

    // Define relationship with DeviceModel
    public function deviceModel()
    {
        return $this->belongsTo(DeviceModel::class);
    }
}
