<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = ['id'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with Service
    public function service()
    {
        return $this->belongsTo(Services::class);
    }
}
