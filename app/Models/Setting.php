<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = ['id'];

    //for image
    public function getImageAttribute($image)
    {
        // Only decode if it's a string
        $images = is_string($image) ? json_decode($image, true) : $image;

        if (is_array($images) && count($images) > 0) {
            return array_map(function ($img) {
                return asset('uploads/setting_images/' . $img);
            }, $images);
        }
        return [];
    }

    //for video
    public function getVideoAttribute($video)
    {
        // Only decode if it's a string
        $videos = is_string($video) ? json_decode($video, true) : $video;

        if (is_array($videos) && count($videos) > 0) {
            return array_map(function ($img) {
                return asset('uploads/setting_videos/' . $img);
            }, $videos);
        }
        return [];
    }

}
