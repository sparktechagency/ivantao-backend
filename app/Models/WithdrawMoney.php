<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class WithdrawMoney extends Model
{
    protected $guarded = ['id'];

    // In app/Models/WithdrawMoney.php

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = strtolower(trim($value));
    }
    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
   // In app/Models/WithdrawMoney.php

   public function order()
   {
       return $this->belongsTo(Order::class, 'order_id')->with('service');
   }



}
