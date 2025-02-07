<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class DModel extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'Models';

    protected $fillable = ['Model', 'Product_Class'];
}
