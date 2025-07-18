<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderService extends Model
{
    protected $guarded = ['id'];
    public function service_category()
    {
        return $this->belongsTo(ServiceCategory::class);
    }
}
