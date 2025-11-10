<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Drop;

class DropController extends Controller
{
    public function index()
    {
        $drops = Drop::latest()->paginate(15);
        return response()->json($drops);
    }

    public function show($id)
    {
        $drop = Drop::find($id);
        if (!$drop) {
            return response()->json(['message' => 'Drop not found'], 404);
        }
        return response()->json($drop);
    }

    public function store(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'location' => 'nullable|string|max:255',
                'category_id' => 'nullable|integer',
                'media' => 'nullable|array',
                'media.*' => 'string',
                'campaign_type' => 'nullable|string|max:255',
            ]);

            $drop = Drop::create([
                'user_id' => $user->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'location' => $validated['location'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'media' => $validated['media'] ?? null,
                'campaign_type' => $validated['campaign_type'] ?? null,
            ]);

            return response()->json($drop, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the drop',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $drop = Drop::find($id);
            if (!$drop) {
                return response()->json(['message' => 'Drop not found'], 404);
            }

            // Check if user is authenticated
            $user = $request->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            // Ensure only owner can update
            if ($drop->user_id !== $user->id) {
                return response()->json(['message' => 'Forbidden. You can only update your own drops.'], 403);
            }

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'location' => 'nullable|string|max:255',
                'category_id' => 'nullable|integer',
                'media' => 'nullable|array',
                'media.*' => 'string',
                'campaign_type' => 'nullable|string|max:255',
            ]);

            $drop->fill($validated);
            $drop->save();

            return response()->json($drop);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the drop',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $drop = Drop::find($id);
            if (!$drop) {
                return response()->json(['message' => 'Drop not found'], 404);
            }

            $user = $request->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            if ($drop->user_id !== $user->id) {
                return response()->json(['message' => 'Forbidden. You can only delete your own drops.'], 403);
            }

            $drop->delete();
            return response()->json(['message' => 'Drop deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the drop',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
