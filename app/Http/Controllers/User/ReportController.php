<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reported_user_id' => 'required|exists:users,id',
            'reason'           => 'required|string',
            'description'      => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $user         = Auth::user();
        $reportedUser = User::find($request->reported_user_id);

        // Check if the current user is a provider
        if ($user->role !== 'user') {
            return response()->json(['status'  => false,'message' => 'Only users can report a provider.',], 403);
        }

        // Check if the reported user is a provider
        if ($reportedUser->role !== 'provider') {
            return response()->json(['status'  => false,'message' => 'You can only report service providers.',], 403);
        }

        $report = Report::create([
            'user_id'          => Auth::id(),
            'reported_user_id' => $request->reported_user_id,
            'reason'           => $request->reason,
            'description'      => $request->description,
        ]);

        $report->load('reportedUser:id,full_name','reporter:id,full_name');

        return response()->json([
            'status'  => true,
            'message' => 'Report submitted successfully.',
            'data'    => $report,
        ], 201);
    }

}
