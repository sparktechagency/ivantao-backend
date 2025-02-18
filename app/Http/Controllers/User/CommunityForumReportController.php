<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CommunityForumReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommunityForumReportController extends Controller
{
    public function forumReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'community_forums_id' => 'required|exists:community_forums,id',
            'reason'              => 'required|string',
            'description'         => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $forum_report = CommunityForumReport::create([
            'user_id'             => Auth::id(),
            'community_forums_id' => $request->community_forums_id,
            'reason'              => $request->reason,
            'description'         => $request->description,
        ]);

        // $forum_report->load(['reportedUser:id,full_name', 'reportedForum:id,title']);

        return response()->json([
            'status'  => true,
            'message' => 'Report Send successfully.',
            'data'    => $forum_report
        ], 201);
    }
}
