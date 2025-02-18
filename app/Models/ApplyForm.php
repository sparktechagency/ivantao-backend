<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplyForm extends Model
{
    protected $guarded = ['id'];

    public function career()
    {
        return $this->belongsTo(Career::class, 'carrer_id');
    }
}
