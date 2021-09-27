<?php

namespace dnj\Request\Providers;

use Illuminate\Support\ServiceProvider;

class RequestServiceProvider extends ServiceProvider
{
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->registerMigrations();
        }
    }

    public function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations/');
    }
}
