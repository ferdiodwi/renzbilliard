<?php

use Illuminate\Support\Facades\Route;

// SPA - All routes handled by Vue Router
Route::get('/{any?}', function () {
    return view('welcome');
})->where('any', '.*');
