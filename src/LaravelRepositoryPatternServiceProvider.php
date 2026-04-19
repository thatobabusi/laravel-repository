<?php

namespace Thatobabusi\LaravelRepositoryPattern;

use Illuminate\Support\ServiceProvider;
use Thatobabusi\LaravelRepositoryPattern\Console\Commands\MakeCriteriaCommand;
use Thatobabusi\LaravelRepositoryPattern\Console\Commands\MakeRepositoryCommand;

class LaravelRepositoryPatternServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/repository.php', 'repository');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/repository.php' => config_path('repository.php'),
            ], 'repository-config');

            $this->commands([
                MakeRepositoryCommand::class,
                MakeCriteriaCommand::class,
            ]);
        }
    }
}
