<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Services;
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
            'comment' => 'nullable|string|max:500',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        if (!$validator) {
            return response()->json([  'status' => false, 'message' => $validator->errors(),], 422);
        }

        $user_id = Auth::id();
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
            'user_id' => $user_id,
            'service_id' => $service_id,
            'comment' => $validator->validated()['comment'] ?? null,
            'rating' => $validator->validated()['rating'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Review added successfully. Previous review (if any) has been replaced.',
            'review' => $review,
        ], 201);
    }
    public function reviewList(Request $request)
    {
        $service_id = $request->query('service_id');

        if (!$service_id) {
            return response()->json(['status' => 'error','message' => 'Product ID is required.',], 401);
        }

        $reviews = Review::with(['user:id,name,image', 'service:id,title'])
            ->where('product_id', $service_id)
            ->paginate();

        $product = Services::withCount('reviews')
            ->withSum('reviews', 'rating')
            ->find($service_id);

        if (!$product) {
            return response()->json(['status' => 'error','message' => 'Product not found.',], 401);
        }

        $averageRating = $product->reviews_count > 0
        ? $product->reviews_sum_rating / $product->reviews_count
        : 0;

        $product->average_rating = min($averageRating, 5);

        return response()->json([
            'status' => 'success',
            'reviews' => $reviews,
            'average_rating' => $product->average_rating,
            'reviews_count' => $product->reviews_count,
        ], 200);

    }
}
