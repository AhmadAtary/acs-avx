<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Node extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'Nodes';

    // protected $fillable = ['Model', 'Product_Class'];
}