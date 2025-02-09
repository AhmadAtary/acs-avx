<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Host extends Model
{
    protected $table = 'hosts'; // Specify the table name
    protected $fillable = [
        'Model', 
        'Product_Class', 
        'HostName', 
        'IPAddress', 
        'MACAddress', 
        'RSSI', 
        'hostPath'
    ];
}
