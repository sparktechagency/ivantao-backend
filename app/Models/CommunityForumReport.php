<?php

namespace App\Models;

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
        return $this->belongsTo(Services::class, 'community_forums_id');
    }

}
