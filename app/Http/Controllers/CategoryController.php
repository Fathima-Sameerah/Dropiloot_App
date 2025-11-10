<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Drop;

class CategoryController extends Controller
{
    // 1️⃣ Get all categories (public)
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    // 2️⃣ Assign a category to a drop (protected)
    public function assignToDrop(Request $request, $id)
    {
        try {
            // Check if user is authenticated
            $user = $request->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $request->validate([
                'category_id' => 'required|integer|exists:categories,id'
            ]);

            $drop = Drop::find($id);

            if (!$drop) {
                return response()->json([
                    'message' => 'Drop not found',
                    'drop_id' => $id
                ], 404);
            }

            // Optional: ensure only owner can assign category
            if ($drop->user_id !== $user->id) {
                return response()->json(['message' => 'Forbidden. You can only assign categories to your own drops.'], 403);
            }

            $drop->category_id = $request->category_id;
            $drop->save();

            // Reload the drop with category relationship
            $drop->refresh();
            $drop->load('category');

            return response()->json([
                'message' => 'Category assigned successfully',
                'drop' => $drop
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while assigning the category',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
