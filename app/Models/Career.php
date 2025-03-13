<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function appliedUsers()
    {
        return $this->hasMany(ApplyForm::class, 'career_id');
    }
}
