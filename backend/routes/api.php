<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

// Authentication Routes
Route::prefix('auth')->group(function () {
    // Registration routes
    Route::post('/register/membro', [AuthController::class, 'registerMembro']);
    Route::post('/register/empresa', [AuthController::class, 'registerEmpresa']);
    
    // Authentication routes
    Route::post('/login', [AuthController::class, 'login']);
      // Protected routes (require authentication)
    Route::middleware('auth:api')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        
        // Session management routes
        Route::get('/sessions', [AuthController::class, 'getActiveSessions']);
        Route::post('/sessions/revoke', [AuthController::class, 'revokeSession']);
    });
});
