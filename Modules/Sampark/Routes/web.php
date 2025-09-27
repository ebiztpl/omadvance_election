<?php

use Illuminate\Support\Facades\Route;
use Modules\Sampark\Http\Controllers\AuthController;

Route::prefix('sampark')->middleware('web')->group(function () {

    // Public routes
    Route::get('/login', [AuthController::class, 'showLogin'])->name('sampark.login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('sampark.register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/check-username', [AuthController::class, 'checkUsername'])->name('sampark.checkUsername');


    Route::middleware('sampark.auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('sampark.logout');
        Route::get('/dashboard', function () {
            return view('sampark::dashboard');
        })->name('sampark.dashboard');
    });
});
