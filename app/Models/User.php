<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'nationality',
        'city',
        'street',
        'phone',
        'password',
        'earnedBalance',
        'balance',
        'average_rating',
        'ratings_count',
        'gender',
        'id_image',
        'national_id',
        'passport_image',
        'national_id_image',

    ];
    public function services()
    {
        return $this->hasMany(Service::class);
    }


    public function volunteerMatches()
    {
        return $this->hasMany(ServiceMatch::class, 'volunteer_id');
    }

    public function customerMatches()
    {
        return $this->hasMany(ServiceMatch::class, 'customer_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function reportsReceived()
    {
        // اليوزر الواحد عنده ريبورتات كتير (HasMany)
        // والربط في جدول الـ reports بيتم عن طريق عمود الـ reported_id
        return $this->hasMany(Report::class, 'reported_id');
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
