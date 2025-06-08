<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

// Simple test route
Route::get('/test', function() {
    return response()->json(['message' => 'API is working', 'timestamp' => now()]);
});

// Authentication Routes
Route::prefix('auth')->group(function () {
    // Registration routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/membros/register', [AuthController::class, 'registerMembro']);
    Route::post('/empresas/register', [AuthController::class, 'registerEmpresa']);
    
    // Login routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/membros/login', [AuthController::class, 'loginMembro']);
    Route::post('/empresas/login', [AuthController::class, 'loginEmpresa']);
    
    // Token management
    Route::post('/refresh', [AuthController::class, 'refreshToken']);
    Route::get('/check', [AuthController::class, 'checkAuth']);
    
    // Protected routes (require authentication)
    Route::middleware('auth:api')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        
        // Session management routes
        Route::get('/sessions', [AuthController::class, 'getActiveSessions']);
        Route::post('/sessions/revoke', [AuthController::class, 'revokeSession']);
        
        // Refresh token statistics (admin only)
        Route::get('/refresh-stats', [AuthController::class, 'getRefreshTokenStats']);
    });
});
