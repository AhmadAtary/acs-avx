<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class DeviceCount extends Model
{
    protected $connection = 'mongodb'; // Connects to MongoDB
    protected $table = 'device_counts'; // Collection name

    protected $fillable = [
        'Date',
        'New_devices',
        'Total_devices',
        'updatedAt'
    ];

    public $timestamps = false; // Disable Laravel timestamps
}
