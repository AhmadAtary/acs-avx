<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobStatus extends Model
{
    use HasFactory;

    protected $table = 'job_statuses';

    protected $fillable = [
        'upload_progress_id',
        'serial_number',
        'status',
        'response',
    ];

    public function uploadProgress()
    {
        return $this->belongsTo(UploadProgress::class);
    }
}