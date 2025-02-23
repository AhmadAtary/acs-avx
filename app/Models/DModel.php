<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class DModel extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'DataModel';

}
