<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Otp;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function sendOtp(Request $request)
    {
        // 1. Validate input
        $validator = Validator::make($request->all(), [
            'phone' => 'required|digits:10'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // 2. Generate 4-digit OTP
        $otp = rand(1000, 9999);

        // 3. Save OTP to database
        Otp::create([
            'phone' => $request->phone,
            'otp' => $otp,
        ]);

        // 4. For now, return OTP in response 
        return response()->json([
            'message' => 'OTP sent successfully',
            'otp' => $otp // Show in response for now
        ]);
    }

    public function verifyOtp(Request $request)
    {
        // 1. Validate inputs
        $request->validate([
            'phone' => 'required|digits:10',
            'otp' => 'required|digits:4'
        ]);

        // 2. Check if OTP exists and matches
        $record = DB::table('otps')
            ->where('phone', $request->phone)
            ->where('otp', $request->otp)
            ->latest()
            ->first();

        if (!$record) {
            return response()->json(['error' => 'Invalid OTP or phone number'], 401);
        }

        // 3. Create user if not exists
        $user = User::firstOrCreate(
            ['phone' => $request->phone],
            [
                'name' => 'New User',
                'email' => $request->phone . '@dropiloot.com', // Generate email from phone
                'password' => bcrypt(Str::random(16)) // Generate random password
            ]
        );

        // 4. Create token using Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'OTP verified successfully',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
{
    // Delete the current token
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Logged out successfully'
    ]);
}




}





