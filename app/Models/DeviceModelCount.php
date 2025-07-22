<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class DeviceModelCount extends Model
{
    protected $connection = 'mongodb'; // Connects to MongoDB
    protected $table = 'device_counts_by_model'; // Collection name

}
