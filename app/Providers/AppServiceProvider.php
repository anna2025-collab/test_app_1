<?php

namespace App\Providers;

use App\Services\Ai\ContactAnalyzer;
use App\Services\Ai\GeminiContactAnalyzer;
use App\Services\Ai\OpenAiContactAnalyzer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ContactAnalyzer::class, function () {
            return match (config('services.ai_provider')) {
                'gemini' => app(GeminiContactAnalyzer::class),
                default => app(OpenAiContactAnalyzer::class),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
