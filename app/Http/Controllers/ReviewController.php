<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\User;

class ReviewController extends Controller
{
    /**
     * Get all reviews for a specific seller
     */
    public function showSellerReviews($id)
    {
        try {
            $seller = User::find($id);
            if (!$seller) {
                return response()->json(['message' => 'Seller not found'], 404);
            }

            $reviews = Review::where('seller_id', $id)
                ->with(['reviewer:id,name,profile_image'])
                ->latest()
                ->paginate(15);

            return response()->json($reviews);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching reviews',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Create a new review for a seller
     */
    public function store(Request $request, $id)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            // Check if seller exists
            $seller = User::find($id);
            if (!$seller) {
                return response()->json(['message' => 'Seller not found'], 404);
            }

            // Prevent users from reviewing themselves
            if ($seller->id === $user->id) {
                return response()->json(['message' => 'You cannot review yourself'], 422);
            }

            // Check if user has already reviewed this seller
            $existingReview = Review::where('seller_id', $id)
                ->where('user_id', $user->id)
                ->first();

            if ($existingReview) {
                return response()->json(['message' => 'You have already reviewed this seller'], 422);
            }

            $validated = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ]);

            $review = Review::create([
                'seller_id' => $id,
                'user_id' => $user->id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]);

            $review->load(['reviewer:id,name,profile_image']);

            return response()->json($review, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the review',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
