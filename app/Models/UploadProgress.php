<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadProgress extends Model
{
    use HasFactory;

    protected $table = 'upload_progress';

    protected $fillable = [
        'total',
        'processed',
        'success_count',
        'fail_count',
        'not_found_count',
        'status',
        'paused_at',
        'resumed_at',
        'deleted_at',
        'action',
        'nodePath',
        'newValue',
        'nodeTypeDetailed',
        'deviceModel',
    ];

    protected $dates = ['paused_at', 'resumed_at', 'deleted_at'];

    public function jobs()
    {
        return $this->hasMany(JobStatus::class);
    }
}