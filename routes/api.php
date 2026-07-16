<?php

use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\MetricsController;
use Illuminate\Support\Facades\Route;

Route::post('/contact', ContactController::class);
Route::get('/health', HealthController::class);
Route::get('/metrics', MetricsController::class);
