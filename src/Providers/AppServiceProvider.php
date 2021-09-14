<?php

namespace dnj\Request\Providers;

use Jalno\Userpanel\Exceptions;
use Illuminate\Support\ServiceProvider;
use Jalno\Userpanel\Rules\ConfigValidators;
use Jalno\Userpanel\ConfigValidatorContainer;
use Jalno\Userpanel\Contracts\IConfigValidatorContainer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
		if ($this->app->runningInConsole()) {
			$this->registerMigrations();
		}
    }

    /**
     * Boot the config validator service for the application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

	public function registerMigrations(): void
	{
		$this->loadMigrationsFrom(__DIR__ . '/../Database/migrations/');
	}
}
