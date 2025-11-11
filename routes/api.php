<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DropController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReviewController;



Route::post('/auth/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Update Profile routes
    Route::get('/user/profile', [AuthController::class, 'viewProfile']);
    Route::put('/user/update', [AuthController::class, 'updateProfile']);
    Route::post('/user/upgrade-to-seller', [AuthController::class, 'upgradeToSeller']);
});

// Public routes
Route::get('/drops', [DropController::class, 'index']);
Route::get('/drops/{id}', [DropController::class, 'show']);

// Protected routes (auth required)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/drops', [DropController::class, 'store']);
    Route::put('/drops/{id}', [DropController::class, 'update']);
    Route::delete('/drops/{id}', [DropController::class, 'destroy']);
});


// Public
Route::get('/categories', [CategoryController::class, 'index']);

// Protected (auth required)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/drops/{id}/category', [CategoryController::class, 'assignToDrop']);
});

// Test route to verify routing works
Route::get('/test-reviews', function() {
    return response()->json(['message' => 'Reviews route test - working!']);
});

// Public route - Get reviews for a seller
Route::get('/sellers/{id}/reviews', [ReviewController::class, 'showSellerReviews']);

// Protected route (must be logged in to write review) - Create a review
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/sellers/{id}/reviews', [ReviewController::class, 'store']);
});