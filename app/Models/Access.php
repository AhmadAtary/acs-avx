<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Access extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_number', 'user_id', 'role', 'permissions', 'account_limit'
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
