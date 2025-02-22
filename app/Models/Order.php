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
    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    // Relationship with Service
    public function service()
    {
        return $this->belongsTo(Services::class, 'service_id');
    }

}
