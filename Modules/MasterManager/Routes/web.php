<?php

use Illuminate\Support\Facades\Route;
use Modules\MasterManager\Http\Controllers\Api\MasterManagerController;

Route::prefix('master')->middleware('web')->group(function () {

    // Views
    Route::get('viewlogin', fn() => view('mastermanager::login'))->name('mastermanager.login.view');
    Route::get('viewregister', fn() => view('mastermanager::register'))->name('mastermanager.register.view');

    // Dashboard
    Route::get('dashboard', function () {
        return session('logged_in_user_id')
            ? view('mastermanager::dashboard')
            : redirect()->route('mastermanager.login.view');
    })->name('master.dashboard');

    // Actions
    Route::post('login', [MasterManagerController::class, 'login'])->name('master.login');
    Route::post('register', [MasterManagerController::class, 'register'])->name('master.register');
    Route::post('logout', [MasterManagerController::class, 'logout'])->name('master.logout');
    Route::post('check-username', [MasterManagerController::class, 'checkUsername'])->name('master.checkUsername');
});
