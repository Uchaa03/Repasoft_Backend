<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\TechnicianController;

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

Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard/metrics', [DashboardController::class, 'getDashboardMetrics']);

    // Stores
    Route::get('/stores', [StoreController::class, 'listStores']);
    Route::post('/stores', [StoreController::class, 'createStore']);
    Route::delete('/stores/{store}', [StoreController::class, 'deleteStore']);

    // Technicians
    Route::get('/technicians', [TechnicianController::class, 'listTechnicians']);
    Route::post('/technicians', [TechnicianController::class, 'createTechnician']);
    Route::put('/technicians/{technician}', [TechnicianController::class, 'updateTechnician']);
    Route::delete('/technicians/{technician}', [TechnicianController::class, 'deleteTechnician']);
    Route::get('/repairs', [TechnicianController::class, 'listAllRepairs']);
});
