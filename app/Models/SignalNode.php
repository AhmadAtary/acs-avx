<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignalNode extends Model
{
    use HasFactory;

    protected $fillable = ['model_id', 'param_name', 'node_path'];

    public function model()
    {
        return $this->belongsTo(DeviceModel::class, 'model_id');
    }
}
