<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        
        'en_name',
        'ar_name',
        'mode',
        'en_description',
        'ar_description'
    ];

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
