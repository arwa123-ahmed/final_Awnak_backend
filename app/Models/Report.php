<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    
    protected $fillable = ['reporter_id', 'reported_id', 'servicematch_id', 'reason', 'status'];

    
    public function serviceMatch()
    {
        return $this->belongsTo(ServiceMatch::class, 'servicematch_id');
    }
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_id');
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}
