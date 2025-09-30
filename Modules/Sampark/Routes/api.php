<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Modules\Sampark\Http\Controllers\AuthController;

// Public API endpoints
Route::post('/login', [AuthController::class, 'login'])->name('sampark.login');
Route::post('/register', [AuthController::class, 'register'])->name('sampark.register');
Route::post('/check-username', [AuthController::class, 'checkUsername'])->name('sampark.checkUsername');

// Protected API endpoints
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/dashboard', function (Request $request) {
        return response()->json([
            'success' => true,
            'message' => 'Welcome to dashboard',
            'user'    => $request->user()
        ]);
    });
});
