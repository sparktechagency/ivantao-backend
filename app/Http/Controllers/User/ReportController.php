<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reported_user_id' => 'nullable|exists:users,id',
            'service_id'       => 'nullable|exists:services,id',
            'reason'           => 'required|string',
            'description'      => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        // Ensure either a user or a service is reported, but not both null
        if (! $request->reported_user_id && ! $request->service_id) {
            return response()->json(['status' => false, 'message' => 'Either a user or a service must be reported.'], 422);
        }

        $report = Report::create([
            'user_id'          => Auth::id(),
            'reported_user_id' => $request->reported_user_id,
            'service_id'       => $request->service_id,
            'reason'           => $request->reason,
            'description'      => $request->description,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Report submitted successfully.',
            'data'    => $report->load(['reportedUser:id,full_name', 'reportedService:id,title']),
        ], 201);
    }

    //report listing
    public function reportlist()
    {
        $report_list = Report::with(['reportedUser:id,full_name,image','reporter:id,full_name,image','reportedService:id,title,service_type',])->paginate();

        if ($report_list->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'There are no reports available.'], 401);
        }

        return response()->json(['status' => true, 'data' => $report_list], 200);
    }
    //report details
    public function reportDetails(Request $request, $id)
    {
        $reports = Report::with('reportedUser:id,full_name,image','reportedService:id,title,service_type,created_at')->find($id);

        if (! $reports) {
            return response()->json(['status' => false, 'message' => 'Reports Not Found'], 401);
        }
        return response()->json(['status'=>true,'data'=>$reports],200);
    }

}
