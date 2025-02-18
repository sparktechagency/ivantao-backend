<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityForum extends Model
{
    protected $guarded = ['id'];
    public function getImageAttribute($image)
    {
        $defaultImage = 'default_user.png';
        return asset('uploads/forum_images/' . ($image ?? $defaultImage));
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
