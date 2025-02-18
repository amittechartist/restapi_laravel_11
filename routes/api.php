<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;

Route::get('/login', function () {
    return response()->json(['success' => false,'message' => 'Unauthorized.'], 401);
})->name('login');


Route::prefix('v1')->group(function () {
    // Authentication Routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    // User Routes
    Route::get('/check-auth', [UserController::class, 'checkAuth'])->middleware('auth:sanctum');
    Route::post('/user-update', [UserController::class, 'updateUser'])->middleware('auth:sanctum');
    Route::post('/user-change-password', [UserController::class, 'changePassword'])->middleware('auth:sanctum');
});
