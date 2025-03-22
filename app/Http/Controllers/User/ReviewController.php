<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    //create review
    public function createReview(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'comment'    => 'nullable|string|max:500',
            'rating'     => 'required|integer|min:1|max:5',
        ]);

        if (! $validator) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $user_id    = Auth::id();
        $service_id = $validator->validated()['service_id'];

        // Check if a review already exists for this user and product
        $existingReview = Review::where('user_id', $user_id)
            ->where('service_id', $service_id)
            ->first();

        if ($existingReview) {
            // Delete the old review
            $existingReview->delete();
        }

        // Create a new review
        $review = Review::create([
            'user_id'    => $user_id,
            'service_id' => $service_id,
            'comment'    => $validator->validated()['comment'] ?? null,
            'rating'     => $validator->validated()['rating'] ?? null,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Review added successfully. Previous review (if any) has been replaced.',
            'review'  => $review,
        ], 201);
    }
    public function reviewList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors(),
            ], 422);
        }

        $service_id = $validator->validated()['service_id'];

        // Fetch reviews for the given service
        $reviews = Review::with('user:id,full_name,image')
        ->where('service_id', $service_id)->paginate(10);

        return response()->json([
            'status'  => 'success',
            'reviews' => $reviews,
        ], 200);
    }

}
