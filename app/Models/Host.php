<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Host extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'Host';

}
