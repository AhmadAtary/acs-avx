<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_model_id',
        'name',
        'path',
        'type',
        'category',
    ];

    public function deviceModel()
    {
        return $this->belongsTo(DeviceModel::class);
    }
}
