<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Helpers\OtpHelper;

class OtpAuthController extends Controller
{
    /**
     * Step 1: Login with email and password, then generate OTP.
     */
    public function loginWithPassword(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Authenticate user
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        // Generate OTP
        $otp = OtpHelper::assignOtp($user);

        // Return OTP message (for dev purposes we show OTP here; later hide it)
        return response()->json([
            'success' => true,
            'message' => 'Password verified. OTP has been generated.',
            'email' => $user->email,
            'otp' => $otp, // Show OTP for dev/testing (remove later in production)
        ], 200);
    }
    /**
     * Reset OTP for a user.
     */
    public function resetOtp(Request $request)
    {
        // Validate the email
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Find the user
        $user = User::where('email', $request->email)->first();

        // Generate and assign a new OTP
        $newOtp = OtpHelper::assignOtp($user);

        return response()->json([
            'success' => true,
            'message' => 'New OTP has been generated.',
            'email' => $user->email,
            'otp' => $newOtp, // Display only for dev/testing purposes
        ], 200);
    }
    /**
     * Step 2: Verify OTP and log the user in.
     */
    public function verifyOtp(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Find user
        $user = User::where('email', $request->email)->first();

        // Verify OTP
        if (!$user || !OtpHelper::verifyOtp($user, $request->otp)) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP.'], 401);
        }

        // OTP is correct, so issue a token and invalidate the OTP
        OtpHelper::invalidateOtp($user);

        // Revoke old tokens and create a new one
        $user->tokens()->delete();
        $token = $user->createToken('login-container')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully. Login complete.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }
}
