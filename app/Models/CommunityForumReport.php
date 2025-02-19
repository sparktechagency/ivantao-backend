<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CommunityForumReport extends Model
{
    protected $guarded = ['id'];

    public function reporterUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reportedForum()
    {
        return $this->belongsTo(CommunityForum::class, 'community_forums_id');
    }
    protected $appends = ['time_ago']; // Automatically add time_ago

    public function getTimeAgoAttribute()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

}
