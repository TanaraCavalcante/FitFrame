<?php

use App\Http\Controllers\GymController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GymController::class, 'index']);
