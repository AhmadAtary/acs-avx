<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class DeviceSoftwareCount extends Model
{
    protected $connection = 'mongodb'; // Connects to MongoDB
    protected $table = 'device_model_software_counts'; // Collection name

}
