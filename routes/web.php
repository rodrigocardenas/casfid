<?php

use Illuminate\Support\Facades\Route;

// SPA - Catch-all route that loads the frontend app
Route::get('/{any?}', function () {
    return view('welcome');
})->where('any', '.*');
