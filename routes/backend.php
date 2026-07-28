<?php

use App\Http\Controllers\Backend\AuthenticatedSessionController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\GalleryController;
use App\Http\Controllers\Backend\GymAdminController;
use App\Http\Controllers\Backend\GymClassController;
use App\Http\Controllers\Backend\GymController;
use App\Http\Controllers\Backend\HeroController;
use App\Http\Controllers\Backend\NewPasswordController;
use App\Http\Controllers\Backend\PasswordResetLinkController;
use App\Http\Controllers\Backend\PersonalTrainerController;
use App\Http\Controllers\Backend\PlanController;
use App\Http\Controllers\Backend\SuperAdminController;
use App\Http\Controllers\Backend\TestimonialController;
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

        Route::prefix('setup/corsi')->name('setup.corsi.')->group(function () {
            Route::get('/', [GymClassController::class, 'index'])->name('index');
            Route::get('/create', [GymClassController::class, 'create'])->name('create');
            Route::post('/', [GymClassController::class, 'store'])->name('store');
            Route::get('/{gymClass}/edit', [GymClassController::class, 'edit'])->name('edit');
            Route::put('/{gymClass}', [GymClassController::class, 'update'])->name('update');
            Route::delete('/{gymClass}', [GymClassController::class, 'destroy'])->name('destroy');
            Route::post('/{gymClass}/move-up', [GymClassController::class, 'moveUp'])->name('move-up');
            Route::post('/{gymClass}/move-down', [GymClassController::class, 'moveDown'])->name('move-down');
        });

        Route::get('setup/gallery', [GalleryController::class, 'edit'])->name('setup.gallery');
        Route::put('setup/gallery', [GalleryController::class, 'update'])->name('setup.gallery.update');

        Route::prefix('setup/piani')->name('setup.piani.')->group(function () {
            Route::get('/', [PlanController::class, 'index'])->name('index');
            Route::get('/create', [PlanController::class, 'create'])->name('create');
            Route::post('/', [PlanController::class, 'store'])->name('store');
            Route::get('/{plan}/edit', [PlanController::class, 'edit'])->name('edit');
            Route::put('/{plan}', [PlanController::class, 'update'])->name('update');
            Route::delete('/{plan}', [PlanController::class, 'destroy'])->name('destroy');
            Route::post('/{plan}/move-up', [PlanController::class, 'moveUp'])->name('move-up');
            Route::post('/{plan}/move-down', [PlanController::class, 'moveDown'])->name('move-down');
        });

        Route::prefix('setup/team')->name('setup.team.')->group(function () {
            Route::get('/', [PersonalTrainerController::class, 'index'])->name('index');
            Route::get('/create', [PersonalTrainerController::class, 'create'])->name('create');
            Route::post('/', [PersonalTrainerController::class, 'store'])->name('store');
            Route::get('/{personalTrainer}/edit', [PersonalTrainerController::class, 'edit'])->name('edit');
            Route::put('/{personalTrainer}', [PersonalTrainerController::class, 'update'])->name('update');
            Route::delete('/{personalTrainer}', [PersonalTrainerController::class, 'destroy'])->name('destroy');
            Route::post('/{personalTrainer}/move-up', [PersonalTrainerController::class, 'moveUp'])->name('move-up');
            Route::post('/{personalTrainer}/move-down', [PersonalTrainerController::class, 'moveDown'])->name('move-down');
        });

        Route::prefix('setup/testimonianze')->name('setup.testimonianze.')->group(function () {
            Route::get('/', [TestimonialController::class, 'index'])->name('index');
            Route::get('/create', [TestimonialController::class, 'create'])->name('create');
            Route::post('/', [TestimonialController::class, 'store'])->name('store');
            Route::get('/{testimonial}/edit', [TestimonialController::class, 'edit'])->name('edit');
            Route::put('/{testimonial}', [TestimonialController::class, 'update'])->name('update');
            Route::delete('/{testimonial}', [TestimonialController::class, 'destroy'])->name('destroy');
            Route::post('/{testimonial}/move-up', [TestimonialController::class, 'moveUp'])->name('move-up');
            Route::post('/{testimonial}/move-down', [TestimonialController::class, 'moveDown'])->name('move-down');
        });

        Route::impersonate();
    });
});
