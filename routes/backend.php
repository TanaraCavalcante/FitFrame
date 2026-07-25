<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('backend.')->group(function () {
    Route::middleware('guest')->group(function () {
        //
    });

    Route::middleware('auth')->group(function () {
        //
    });
});
