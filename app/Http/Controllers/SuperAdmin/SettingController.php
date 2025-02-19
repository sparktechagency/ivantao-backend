<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    //create about us and how it works
    public function createSetting(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type'        => 'required|string|in:about_us,how_it_works',
            'description' => 'required|string',
            'image'       => 'nullable|array|max:2',
            'video'       => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 401);
        }

        // Find the existing setting
        $setting = Setting::where('type', $request->type)->first();

        //image uploaded
        $newImages = [];

        if ($request->hasFile('image')) {
            $existingImages = is_array($setting->image) ? $setting->image : json_decode($setting->image, true) ?? [];

            foreach ($existingImages as $image) {
                $relativePath = parse_url($image, PHP_URL_PATH);
                $relativePath = ltrim($relativePath, '/');
                $filePath     = public_path($relativePath);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Upload new images
            foreach ($request->file('image') as $image) {
                $imageName = time() . uniqid() . $image->getClientOriginalName();
                $image->move(public_path('uploads/setting_images'), $imageName);
                $newImages[] = $imageName;
            }
        }

        // Handle video uploads
        $newVideos = [];

        if ($request->hasFile('video')) {
            $existingvideos = is_array($setting->video) ? $setting->video : json_decode($setting->video, true) ?? [];

            foreach ($existingvideos as $video) {
                $relativePath = parse_url($video, PHP_URL_PATH);
                $relativePath = ltrim($relativePath, '/');
                $filePath     = public_path($relativePath);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Upload new videos
            foreach ($request->file('video') as $video) {
                $videoName = time() . uniqid() . $video->getClientOriginalName();
                $video->move(public_path('uploads/setting_videos'), $videoName);
                $newVideos[] = $videoName;
            }
        }

        // Update or Create setting record
        $setting = Setting::updateOrCreate(
            ['type' => $request->type],
            [
                'description' => $request->description,
                'image'       => json_encode($newImages),
                'video'       => json_encode($newVideos),
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => $setting,
        ]);
    }

    public function settingList(Request $request)
    {
        $setting = Setting::where('type', $request->type)->first();

        if (! $setting) {
            return response()->json([
                'status' => false, 'message' => "Setting not found."], 401);
        }

        return response()->json([
            'status'  => true,
            'message' => $setting,
        ], 200);
    }
}
