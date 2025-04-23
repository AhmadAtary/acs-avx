<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_name',
        'product_class',
        'oui',
        'image',
    ];

    public function nodes()
    {
        return $this->hasMany(Node::class);
    }

    public function dataModelNodes()
    {
        return $this->hasMany(DataModelNode::class, 'device_model_id');
    }

    public function standardNodes()
    {
        return $this->hasMany(DeviceStandardNode::class);
    }
    
    public function signalNodes()
    {
        return $this->hasMany(SignalNode::class, 'model_id');
    }
}
