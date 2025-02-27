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
        $forum_report->load(['reporterUser:id,full_name', 'reportedForum:id,title']);

        return response()->json([
            'status'  => true,
            'message' => 'Report Send successfully.',
            'data'    => $forum_report,
        ], 201);
    }
    public function forumReportList()
    {
        $forum_report_list = CommunityForumReport::with(['reporterUser:id,full_name'])->paginate();

        if ($forum_report_list->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'There is no data in the forum report list'], 401);
        }

        return response()->json(['status' => true, 'data' => $forum_report_list], 200);
    }

    public function forumReportDetails($forum_id)
    {
        $report_forum_details = CommunityForumReport::with([
            'reporterUser:id,full_name', 'reportedForum:id,title,comment,user_id', 'reportedForum.user:id,full_name,image',
        ])->where('community_forums_id', $forum_id)
            ->get();

        if ($report_forum_details->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No reports found for this forum'], 401);
        }

        return response()->json(['status' => true, 'data' => $report_forum_details], 200);
    }
    public function deleteForumReport($id)
    {
        $forum_report = CommunityForumReport::find($id);

        if (! $forum_report) {
            return response()->json(['status' => false, 'message' => 'Report of Community Forum  Not Found'], 401);
        }

        $forum_report->delete();

        return response()->json(['message' => 'Report of Community Forum  deleted successfully']);
    }

}
