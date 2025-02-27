<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Career;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CareerController extends Controller
{
    //job add
    public function createCareer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_role'     => 'required|string|max:255',
            'job_category' => 'required|string|max:255',
            'description'  => 'required|string',
            'address'      => 'required|string',
            'job_type'     => 'nullable|string|in:full_time,part_time,full_time_on_site,full_time_remote,part_time_on_site,part_time_remote',
            'deadline'     => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        // Check if the same job role already exists in the same category
        $existingCareer = Career::where('job_category', $request->job_category)
            ->where('job_role', $request->job_role)
            ->first();

        if (! $existingCareer) {
            // If job role does not exist in this category, create new
            $career = Career::create([
                'job_category' => $request->job_category,
                'job_role'     => $request->job_role,
                'description'  => $request->description,
                'address'      => $request->address,
                'job_type'     => $request->job_type,
                'deadline'     => $request->deadline,
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'This job role already exists in this category!',
            ], 401);
        }

        return response()->json([
            'status'  => true,
            'message' => 'New job role added successfully',
            'data'    => $career,
        ], 201);
    }
    //job update
    public function updateCareer(Request $request, $id)
    {
        $career = Career::find($id);

        if (! $career) {
            return response()->json(['status' => false, 'message' => 'Job Not Found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'job_role'     => 'nullable|string|max:255',
            'job_category' => 'nullable|string|max:255',
            'description'  => 'nullable|string',
            'address'      => 'nullable|string',
            'job_type'     => 'nullable|string|in:full_time,part_time,full_time_on_site,full_time_remote,part_time_on_site,part_time_remote',
            'deadline'     => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }
        $career->update($validator->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Career updated successfully',
            'data'    => $career,
        ], 200);
    }
    //delete career
    public function deleteCareer($id)
    {
        $career = Career::find($id);

        if (! $career) {
            return response()->json(['status' => false, 'message' => 'Career Not Found'], 401);
        }

        $career->delete();

        return response()->json(['message' => 'Career deleted successfully']);
    }
    //job list for super admin
    public function careerList(Request $request)
    {
        $career_list = Career::withCount('appliedUsers');

        // Search by job role
        if ($request->has('search')) {
            $career_list->where('job_role', 'LIKE', '%' . $request->search . '%');
        }

        // Sort by the given sort parameter or default to 'newest'
        $sortOrder = match ($request->sort) {
            'oldest' => 'asc',
            'category' => 'asc', // Sort by category
            default => 'desc',   // Default: newest first
        };
        $career_list->orderBy($request->sort === 'category' ? 'job_category' : 'created_at', $sortOrder);

        $career_list = $career_list->paginate(10);

        if ($career_list->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No careers found'], 200);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Career list fetched successfully',
            'data'    => $career_list,
        ], 200);
    }

    //job list for user
    public function jobList(Request $request)
    {
        $joblist = Career::query();

        // Search by job role
        if ($request->has('search') && ! empty($request->search)) {
            $joblist->where('job_role', 'LIKE', '%' . $request->search . '%');
        }

        $jobs = $joblist->orderBy('created_at', 'desc')->paginate(10);

        // Group the jobs by category after pagination
        $groupedJobs = $jobs->getCollection()->groupBy('job_category');

        if ($groupedJobs->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No jobs found'], 200);
        }

        return response()->json([
            'status'     => true,
            'message'    => 'Job list fetched successfully',
            'data'       => $groupedJobs,
            'pagination' => [
                'current_page' => $jobs->currentPage(),
                'total_pages'  => $jobs->lastPage(),
                'per_page'     => $jobs->perPage(),
                'total_count'  => $jobs->total(),
            ],
        ], 200);
    }
    //job details
    public function careerDetails($id)
    {
        $job_details = Career::find($id);

        if (! $job_details) {
            return response()->json(['status' => false, 'message' => 'No job found'], 401);
        }

        // Get the created_at date of the job
        $created_at = Carbon::parse($job_details->created_at);

        // Determine when the job was posted
        $posted_on = $created_at->isToday() ? 'Today' : ($created_at->isYesterday() ? 'Yesterday' : $created_at->toDateString());

        return response()->json([
            'status'    => true,
            'message'   => 'Job details fetched successfully',
            'data'      => $job_details,
            'posted_on' => $posted_on, // Add the date the job was posted
        ], 200);
    }

}
