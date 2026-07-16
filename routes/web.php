<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response(File::get(resource_path('views/landing.blade.php')))
        ->header('Content-Type', 'text/html; charset=UTF-8');
});

Route::get('/docs', function () {
    return response(File::get(resource_path('views/docs.blade.php')))
        ->header('Content-Type', 'text/html; charset=UTF-8');
});
