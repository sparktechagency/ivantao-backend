<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    protected $guarded = ['id'];

    public function getImageAttribute($image)
    {
        return asset('uploads/contact_images/' . ($image ?? null));
    }
}
