<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'category_id',
        'type',
        'mode',
        'status',
        'timesalary',
        'expires_at',
        'end_time'
    ];
    protected $casts = [
        'end_time' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function matches()
    {
        return $this->hasMany(ServiceMatch::class);
    }
    
}