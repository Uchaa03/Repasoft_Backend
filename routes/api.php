<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

// Public Routes for all uses and access
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-2fa', [AuthController::class, 'verify2FA']);
    Route::post('/request-password-reset', [AuthController::class, 'requestPasswordReset']);
    Route::post('/register-admin', [AuthController::class, 'registerAdmin']);
});

// Protected routes with auth user all users
Route::middleware(['auth:sanctum'])->group(function () {
    // Password change
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
});
