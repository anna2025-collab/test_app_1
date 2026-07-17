<?php

namespace App\Providers;

use App\Services\Ai\ContactAnalyzer;
use App\Services\Ai\GeminiContactAnalyzer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ContactAnalyzer::class, GeminiContactAnalyzer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
