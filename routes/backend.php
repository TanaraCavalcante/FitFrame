<?php

use App\Http\Controllers\Backend\AuthenticatedSessionController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\GymAdminController;
use App\Http\Controllers\Backend\GymController;
use App\Http\Controllers\Backend\HeroController;
use App\Http\Controllers\Backend\NewPasswordController;
use App\Http\Controllers\Backend\PasswordResetLinkController;
use App\Http\Controllers\Backend\SuperAdminController;
use Illuminate\Support\Facades\Route;

Route::domain(config('app.admin_domain'))->name('backend.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');

        Route::get('password/forgot', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('password/forgot', [PasswordResetLinkController::class, 'store'])->name('password.email')->middleware('throttle:password-reset');

        Route::get('password/reset/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('password/reset', [NewPasswordController::class, 'store'])->name('password.update')->middleware('throttle:password-reset');
    });

    Route::middleware('auth')->group(function () {
        Route::redirect('/', 'dashboard');
        Route::get('dashboard', DashboardController::class)->name('dashboard');
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::resource('strutture', GymController::class)->except('show')->parameters(['strutture' => 'gym']);

        Route::resource('utenti', GymAdminController::class)->except('show')->parameters(['utenti' => 'user']);

        Route::resource('super-admin', SuperAdminController::class)->except('show')->parameters(['super-admin' => 'user']);

        Route::get('setup/hero', [HeroController::class, 'edit'])->name('setup.hero');
        Route::put('setup/hero', [HeroController::class, 'update'])->name('setup.hero.update');

        Route::impersonate();
    });
});
