<?php

use Illuminate\Support\Facades\Route;
use Modules\Sampark\Http\Controllers\AuthController;

Route::prefix('sampark')->middleware('web')->group(function () {
    // Show Blade pages
    Route::get('/login', [AuthController::class, 'showLogin'])->name('sampark.login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('sampark.register');

    // Protected web routes
    Route::post('/logout', [AuthController::class, 'logout'])->name('sampark.logout');
    Route::get('/dashboard', function () {
        return view('sampark::dashboard');
    });
});
