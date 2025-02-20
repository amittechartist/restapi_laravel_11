<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Services\FileUploadService;

class UserController extends Controller
{
    protected $uploadService;

    /**
     * Inject the FileUploadService.
     *
     * @param FileUploadService $uploadService
     */
    public function __construct(FileUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

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
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
            'phone_number' => 'sometimes|string|unique:users,phone_number,' . $request->user()->id . '|regex:/^[0-9]{7,15}$/',
            'country_code' => 'sometimes|string|max:5|regex:/^\+\d+$/',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

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
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 403);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ], 200);
    }

    /**
     * Upload or update the user's profile picture (avatar).
     *
     * Expects a multipart/form-data request with:
     * - avatar: The file to upload (png, jpg, jpeg).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|file|mimes:png,jpg,jpeg|max:10240'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = $request->user();

        if ($request->hasFile('avatar')) {
            // Delete previous avatar if exists
            if (!empty($user->avatar)) {
                $this->uploadService->delete($user->avatar);
            }
            // Upload the new avatar to the "avatars" folder
            $result = $this->uploadService->upload($request->file('avatar'), 'avatars');
            $user->avatar = $result['path'];
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile picture updated successfully.',
            'user' => $user
        ]);
    }
}
