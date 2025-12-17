<?php

namespace NFSe;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class NFSeServiceProvider extends ServiceProvider
{
    use EventMap;

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/nfse.php',
            'nfse'
        );

        $this->app->singleton('nfse', NFSeService::class);
    }

    public function boot()
    {
        $this->registerEvents();

        $this->registerRoutes();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'nfse-migrations');
    }

    protected function registerRoutes(): void
    {
        Route::group($this->routeConfiguration(), function (): void {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        });
    }

    protected function routeConfiguration(): array
    {
        return [
            'prefix' => config('nfse.route.prefix'),
            'middleware' => config('nfse.middleware'),
        ];
    }
}
