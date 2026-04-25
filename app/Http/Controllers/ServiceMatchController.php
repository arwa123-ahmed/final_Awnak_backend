<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceMatch extends Model
{
    protected $fillable = [
        'volunteer_id',
        'customer_id',
        'service_id',
        'status',
        'delay_minutes',
        'delay_reason',
        'delay_status',
        'inquiry_messages',
        'status',
    ];

    public function volunteer()
    {
        return $this->belongsTo(User::class, 'volunteer_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    // app/Models/ServiceMatch.php

    public function messages()
    {
        return $this->hasMany(Message::class, 'service_match_id');
    }
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}
