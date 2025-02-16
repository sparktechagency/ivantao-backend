<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferService extends Model
{
    protected $guarded=['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Services::class);
    }

}
