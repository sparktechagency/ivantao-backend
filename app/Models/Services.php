<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }
    public function subCategory()
    {
        return $this->belongsTo(ServiceSubCategory::class, 'service_sub_categories_id');
    }

    public function getImageAttribute($image)
    {
        $defaultImage = 'default_user.png';
        return asset('uploads/service_images/' . ($image ?? $defaultImage));
    }
    public function reviews()
    {
        return $this->hasMany(Review::class, 'service_id'); // Correct the foreign key to 'service_id'
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'service_id');
    }
    public function serviceSubCategory()
    {
        return $this->belongsTo(ServiceSubCategory::class, 'service_sub_categories_id');
    }
    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

}
