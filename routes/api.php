<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OtpAuthController;
use App\Http\Controllers\Api\EmailConfigController;
use App\Http\Controllers\Api\EmailSenderController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\SiteSettingsController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\PaymentGatewayController;
use App\Http\Controllers\Api\ContactMessageController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\SupportTicketController;



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
    Route::post('/user/avatar', [UserController::class, 'updateAvatar'])->middleware('auth:sanctum');
    Route::post('/user-change-password', [UserController::class, 'changePassword'])->middleware('auth:sanctum');
    // Site settings
    Route::get('/settings/site-settings', [SiteSettingsController::class, 'show'])->middleware('auth:sanctum', 'admin');
    Route::post('/settings/site-settings', [SiteSettingsController::class, 'update'])->middleware('auth:sanctum', 'admin');
    Route::post('/settings/social-links', [SiteSettingsController::class, 'updateSocialLinks'])->middleware('auth:sanctum', 'admin');

    // send email
    Route::post('/send-email', [EmailSenderController::class, 'send']);
    Route::get('/email-config', [EmailConfigController::class, 'show'])->middleware('auth:sanctum', 'admin');
    Route::put('/email-config', [EmailConfigController::class, 'update'])->middleware('auth:sanctum', 'admin');
    // TEST FILE UPLOAD APIS
    Route::post('/files/upload', [FileController::class, 'upload']);
    Route::delete('/files', [FileController::class, 'delete']);
    Route::post('/files/get-url', [FileController::class, 'getUrl']);
    // Wallet routes (authenticated users)
    Route::get('/wallet', [WalletController::class, 'show'])->middleware('auth:sanctum');
    Route::post('/wallet/add-funds', [WalletController::class, 'addFunds'])->middleware('auth:sanctum');
    Route::get('/wallet/transactions', [WalletController::class, 'transactions'])
        ->middleware('auth:sanctum');
    Route::post('/wallet/use-funds', [WalletController::class, 'useFunds'])->middleware('auth:sanctum');
    Route::post('/payment/callback', [PaymentGatewayController::class, 'callback']);

    Route::post('/admin/wallet/adjust', [WalletController::class, 'adjustWallet'])
        ->middleware('auth:sanctum', 'admin');
    Route::get('/payment/logs', [PaymentGatewayController::class, 'logs'])
        ->middleware('auth:sanctum', 'admin');
    // Contact Us endpoints
    Route::post('/contact', [ContactMessageController::class, 'store']);
    Route::get('/contact', [ContactMessageController::class, 'index'])
        ->middleware('auth:sanctum', 'admin');

    // Subscription endpoints
    Route::post('/subscribe', [SubscriptionController::class, 'store']);
    Route::get('/subscribe', [SubscriptionController::class, 'index'])
        ->middleware('auth:sanctum', 'admin');
    // Public/user endpoints (protected by auth:sanctum)
    Route::post('/tickets', [SupportTicketController::class, 'createTicket'])->middleware('auth:sanctum');
    Route::get('/tickets/{ticketId}', [SupportTicketController::class, 'showTicket'])->middleware('auth:sanctum');
    Route::post('/tickets/{ticketId}/reply', [SupportTicketController::class, 'replyTicket'])->middleware('auth:sanctum');

    // Admin endpoints (apply admin middleware as needed)
    Route::get('/admin/tickets', [SupportTicketController::class, 'listTickets'])->middleware('auth:sanctum', 'admin');
    Route::post('/admin/tickets/{ticketId}/reply', [SupportTicketController::class, 'replyTicket'])->middleware('auth:sanctum', 'admin');
    Route::post('/admin/tickets/{ticketId}/close', [SupportTicketController::class, 'closeTicket'])->middleware('auth:sanctum', 'admin');
});
