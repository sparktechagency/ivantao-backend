<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $guarded =['id'];
    public function provider()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reportedService()
    {
        return $this->belongsTo(Services::class, 'service_id');
    }
    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

}
