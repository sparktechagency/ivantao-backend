<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CommunityForum;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommunityForumController extends Controller
{
    //create forum post
    public function forumPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sub_categories_id' => 'required|exists:service_sub_categories,id',
            'title'             => 'required|string|max:255',
            'comment'           => 'required|string',
            'image'             => 'nullable|file',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        //authenticated user exists
        if (! auth()->user()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized user'], 401);
        }

        $forum_image = null;
        if ($request->hasFile('image')) {
            $image       = $request->file('image');
            $extension   = $image->getClientOriginalExtension();
            $forum_image = time() . '.' . $extension;
            $image->move(public_path('uploads/forum_images'), $forum_image);
        }

        $forum_post = CommunityForum::create([
            'sub_categories_id' => $request->sub_categories_id,
            'title'             => $request->title,
            'comment'           => $request->comment,
            'image'             => $forum_image,
            'user_id'           => auth()->id(), // Assign the logged-in user's ID
        ]);

        $forum_post->load('user:id,full_name,image'); // Load user details

        return response()->json([
            'status'  => true,
            'message' => 'Forum Post Published Successfully',
            'data'    => $forum_post,
        ], 201);
    }

    public function communityForumList()
    {
        $forum_list = CommunityForum::with(['user:id,full_name,image'])->paginate();

        if ($forum_list->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'There is no data in the forum post list'], 401);
        }

        // Convert created_at to "time ago" format
        $forum_list->getCollection()->transform(function ($forum) {
            $forum->time_ago = Carbon::parse($forum->created_at)->diffForHumans();
            return $forum;
        });

        return response()->json(['status' => true, 'data' => $forum_list], 200);
    }

}
