<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NetworkAnalysisResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'cell_id',
        'device_count',
        'avg_rsrp',
        'avg_rssi',
    ];
}
