<?php

use App\Http\Controllers\Backend\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('backend.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    });
});
