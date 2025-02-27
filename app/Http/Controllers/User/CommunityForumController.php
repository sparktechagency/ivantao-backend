<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CommunityForum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommunityForumController extends Controller
{
    //create forum post
    public function forumPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categories_id' => 'required|exists:service_categories,id',
            'title'         => 'required|string|max:255',
            'comment'       => 'required|string',
            'image'         => 'nullable|file',
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
            'categories_id' => $request->categories_id,
            'title'         => $request->title,
            'comment'       => $request->comment,
            'image'         => $forum_image,
            'user_id'       => auth()->id(), // Assign the logged-in user's ID
        ]);

        $forum_post->load('user:id,full_name,image'); // Load user details

        return response()->json([
            'status'  => true,
            'message' => 'Forum Post Published Successfully',
            'data'    => $forum_post,
        ], 201);
    }

    public function communityForumList(Request $request)
    {
        $query = CommunityForum::with(['user:id,full_name,image']);

        // filter by that category
        if ($request->has('categories_id')) {
            $query->where('categories_id', $request->categories_id); // Filter by category ID
        }

        $forum_list = $query->paginate();

        if ($forum_list->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No forum posts found in this category'], 200);
        }

        return response()->json(['status' => true, 'data' => $forum_list], 200);
    }

    public function deleteCommnityForum($id)
    {
        $forum = CommunityForum::find($id);

        if (! $forum) {
            return response()->json(['status' => false, 'message' => 'Community Forum Not Found'], 401);
        }

        $forum->delete();

        return response()->json(['message' => 'Community Forum deleted successfully']);
    }

}
