<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FaceRecognitionService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register Face Recognition Service
        $this->app->singleton(FaceRecognitionService::class, function ($app) {
            return new FaceRecognitionService();
        });
    }

    public function boot(): void
    {
        // Add any boot logic here
    }
}