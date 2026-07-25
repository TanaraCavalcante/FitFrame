<?php

use App\Http\Controllers\GymController;
use App\Http\Middleware\ResolveGym;
use Illuminate\Support\Facades\Route;

Route::middleware(ResolveGym::class)->group(function () {
    Route::get('/', [GymController::class, 'index']);
});
