<?php

use App\Http\Controllers\Backend\AuthenticatedSessionController;
use App\Http\Controllers\Backend\NewPasswordController;
use App\Http\Controllers\Backend\PasswordResetLinkController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('backend.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');

        Route::get('password/forgot', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('password/forgot', [PasswordResetLinkController::class, 'store'])->name('password.email')->middleware('throttle:password-reset');

        Route::get('password/reset/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('password/reset', [NewPasswordController::class, 'store'])->name('password.update')->middleware('throttle:password-reset');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    });
});
