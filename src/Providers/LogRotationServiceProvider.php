<?php

namespace AlkhatibDev\LogRotation\Providers;

use Illuminate\Support\ServiceProvider;

class LogRotationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // Publish config file
        $this->publishes([
            __DIR__ . '/../../config/logrotation.php' => config_path('logrotation.php'),
        ], 'logrotation');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__ . '/../../config/logrotation.php', 'logrotation');

        // Register LogRotator as a singleton
        $this->app->singleton('logrotator', function () {
            return new \AlkhatibDev\LogRotation\LogRotator();
        });
    }
}
