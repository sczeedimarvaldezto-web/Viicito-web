<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// SPA fallback: sirve la aplicación Vue en todas las rutas del frontend
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
