<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CommunityForum extends Model
{
    protected $guarded = ['id'];
    protected $appends = ['time_ago']; // Automatically add time_ago
    public function getImageAttribute($image)
    {
        $defaultImage = 'default_user.png';
        return asset('uploads/forum_images/' . ($image ?? $defaultImage));
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    //this for time formate
    public function getTimeAgoAttribute()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }
    public function category()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

}
