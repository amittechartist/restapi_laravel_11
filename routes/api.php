<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OtpAuthController;
use App\Http\Controllers\Api\EmailConfigController;
use App\Http\Controllers\Api\EmailSenderController;
use App\Http\Controllers\Api\FileController;


Route::get('/login', function () {
    return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
})->name('login');


Route::prefix('v1')->group(function () {
    // Authentication Routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/signup-with-countrycode', [AuthController::class, 'signupWithCountrycode']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    // ADMIN AUTH
    Route::post('/admin-login', [AuthController::class, 'adminLogin']);
    Route::post('/admin-logout', [AuthController::class, 'adminLogout'])->middleware('auth:sanctum', 'admin');
    // Authentication With OTP Routes
    Route::post('/login-container', [OtpAuthController::class, 'loginWithPassword']);
    Route::post('/reset-otp', [OtpAuthController::class, 'resetOtp']);
    Route::post('/login-container/verify-otp', [OtpAuthController::class, 'verifyOtp']);
    // User Routes
    Route::get('/check-auth', [UserController::class, 'checkAuth'])->middleware('auth:sanctum');
    Route::post('/user-update', [UserController::class, 'updateUser'])->middleware('auth:sanctum');
    Route::post('/user-change-password', [UserController::class, 'changePassword'])->middleware('auth:sanctum');
    // send email
    Route::post('/send-email', [EmailSenderController::class, 'send']);
    Route::get('/email-config', [EmailConfigController::class, 'show'])->middleware('auth:sanctum', 'admin');
    Route::put('/email-config', [EmailConfigController::class, 'update'])->middleware('auth:sanctum', 'admin');
    // TEST FILE UPLOAD APIS
    Route::post('/files/upload', [FileController::class, 'upload']);
    Route::delete('/files', [FileController::class, 'delete']);
    Route::post('/files/get-url', [FileController::class, 'getUrl']);
});
