<?php

namespace Laravel\Repository;

use Illuminate\Support\ServiceProvider;
use Laravel\Repository\Console\Commands\MakeCriteriaCommand;
use Laravel\Repository\Console\Commands\MakeRepositoryCommand;

class LaravelRepositoryServiceProvider extends ServiceProvider
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
