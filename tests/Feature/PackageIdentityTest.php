<?php

namespace Laravel\Repository\Tests\Feature;

use Laravel\Repository\Tests\TestCase;

class PackageIdentityTest extends TestCase
{
    public function test_composer_package_name_and_provider_namespace_match_public_identity(): void
    {
        $composer = json_decode(file_get_contents(__DIR__ . '/../../composer.json'), true);

        $this->assertSame('thatobabusi/laravel-repository', $composer['name']);
        $this->assertArrayHasKey('Laravel\\Repository\\', $composer['autoload']['psr-4']);
        $this->assertSame('src/', $composer['autoload']['psr-4']['Laravel\\Repository\\']);
        $this->assertContains(
            'Laravel\\Repository\\LaravelRepositoryServiceProvider',
            $composer['extra']['laravel']['providers']
        );
        $this->assertTrue(class_exists(\Laravel\Repository\LaravelRepositoryServiceProvider::class));
    }
}
