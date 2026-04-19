<?php

namespace Thatobabusi\LaravelRepositoryPattern\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Thatobabusi\LaravelRepositoryPattern\LaravelRepositoryPatternServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [LaravelRepositoryPatternServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}
