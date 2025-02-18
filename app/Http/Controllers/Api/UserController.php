<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UserController extends Controller
{
    // CHECK AUTH
    public function checkAuth(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'User is authenticated',
            'user' => $request->user()
        ], 200);
    }
    // Update User Details
    public function updateUser(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
            'phone_number' => 'sometimes|string|unique:users,phone_number,' . $request->user()->id . '|regex:/^[0-9]{7,15}$/',
            'country_code' => 'sometimes|string|max:5|regex:/^\+\d+$/',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Update user
        $user = $request->user();
        $user->update($request->only(['name', 'email', 'phone_number', 'country_code']));

        return response()->json([
            'success' => true,
            'message' => 'User details updated successfully',
            'user' => $user
        ], 200);
    }
    // Change Password Method
    public function changePassword(Request $request)
    {
        // Validate request data
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        // Check if the current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 403);
        }

        // Update the password
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ], 200);
    }
}
