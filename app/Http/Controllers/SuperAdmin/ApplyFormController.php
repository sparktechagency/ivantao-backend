<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ApplyForm;
use App\Models\Career;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApplyFormController extends Controller
{
    //apply by users
    public function applyForm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'career_id'    => 'required|exists:careers,id',
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:apply_forms,email',
            'cover_letter' => 'required|string',
            'document'     => 'nullable|file',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $new_document = null;
        if ($request->hasFile('document')) {
            $document     = $request->file('document');
            $extension    = $document->getClientOriginalExtension();
            $new_document = time() . '.' . $extension;
            $document->move(public_path('uploads/documents'), $new_document);
        }

        $apply_form = ApplyForm::create([
            'career_id'    => $request->career_id,
            'name'         => $request->name,
            'email'        => $request->email,
            'cover_letter' => $request->cover_letter,
            'document'     => $new_document,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Application submitted successfully',
            'data'    => $apply_form,
        ], 201);

    }
    //get applicants list
    public function appliedUsersList(Request $request, $career_id)
    {
        $search = $request->input('search');
        $sortOrder = $request->input('sort', 'desc');

        $career = Career::find($career_id);

        if (!$career) {
            return response()->json(['status' => false, 'message' => 'Job role not found'], 404);
        }

        $appliedUsers = ApplyForm::where('career_id', $career_id)
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'LIKE', "%$search%");
            })
            ->orderBy('created_at', $sortOrder)
            ->paginate(10);

        return response()->json([
            'status'  => true,
            'message' => 'Applied users fetched successfully',
            'data'    => $appliedUsers
        ], 200);
    }

    //get applied user details
    public function appliedUsersDetails($id)
    {
        $applied_user = ApplyForm::find($id);

        if (! $applied_user) {
            return response()->json(['status' => false, 'message' => 'User Not Found'], 401);
        }

        return response()->json([
            'status' => true,
            'data'   => $applied_user,
        ], 200);
    }
    //appliation status update
    public function updateApplicationStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'application_status' => 'required|string|in:approve,reject',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $appliedForm = ApplyForm::findOrFail($id);

        $appliedForm->update(['application_status' => $request->application_status]);

        return response()->json([
            'status'  => true,
            'message' => "Application form status updated to {$request->application_status}.",
            'data'   => $appliedForm,
        ]);
    }

}
