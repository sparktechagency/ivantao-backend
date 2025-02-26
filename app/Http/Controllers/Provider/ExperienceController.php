<?php
namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExperienceController extends Controller
{
    // Add Experience
    public function addExperience(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name'      => 'required|string|max:255',
            'job_role'          => 'required|string|max:255',
            'description'       => 'required|string',
            'join_date'         => 'required|date',
            'resign_date'       => 'nullable|date|after:join_date',
            'currently_working' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        // Check if resign_date is provided
        $currentlyWorking = ! $request->has('resign_date') || $request->resign_date === null;

        // If resign_date is provided, set currently_working to false
        if ($request->has('resign_date')) {
            $currentlyWorking = false;
        }

        $experience = Experience::create([
            'provider_id'       => $user->id,
            'company_name'      => $request->company_name,
            'job_role'          => $request->job_role,
            'description'       => $request->description,
            'join_date'         => $request->join_date,
            'resign_date'       => $currentlyWorking ? null : $request->resign_date,
            'currently_working' => $currentlyWorking,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Experience saved successfully',
            'data'    => $experience,
        ], 201);
    }

    // Get All Experiences of Logged-in User
    public function getExperiences()
    {
        $user = Auth::user(); // Get the authenticated user

        $experiences = Experience::where('provider_id', $user->id)->get();

        return response()->json([
            'status'  => true,
            'message' => 'Experiences retrieved successfully',
            'data'    => $experiences,
        ]);
    }

    // Update Experience
    public function updateExperience(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'company_name'      => 'nullable|string|max:255',
            'job_role'          => 'nullable|string|max:255',
            'description'       => 'nullable|string',
            'join_date'         => 'required|date',
            'resign_date'       => 'nullable|date|after:join_date',
            'currently_working' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user(); // Get the authenticated user
        $experience = Experience::where('provider_id', $user->id)->where('id', $id)->first();

        if (!$experience) {
            return response()->json(['status' => false, 'message' => 'Experience not found or unauthorized'], 422);
        }

        // Ensure the experience belongs to the authenticated user (provider)
        if ($experience->provider_id !== $user->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized to update this experience'], 403);
        }

        // Update the experience
        $experience->update($validator->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Experience updated successfully',
            'data'    => $experience,
        ]);
    }

    // Delete Experience
    public function deleteExperience($id)
    {
        $user       = Auth::user();
        $experience = Experience::where('provider_id', $user->id)->where('id', $id)->first();

        if (! $experience) {
            return response()->json(['status' => false, 'message' => 'Experience not found'], 422);
        }

        $experience->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Experience deleted successfully',
        ]);
    }
}
