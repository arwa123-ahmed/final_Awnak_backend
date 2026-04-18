<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RechargeBalance extends Model
{
    use HasFactory;
    protected $fillable = [
    'user_id',
    'image',
    'amount',//a
    'status',//a
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
