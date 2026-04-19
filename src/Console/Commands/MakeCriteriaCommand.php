<?php

namespace Thatobabusi\LaravelRepositoryPattern\Console\Commands;

use Illuminate\Console\Command;

class MakeCriteriaCommand extends Command
{
    protected $signature = 'make:criteria {name : The criteria class name (e.g. ActiveUsers)}';

    protected $description = 'Create a new criteria class';

    public function handle(): int
    {
        $name = $this->argument('name');

        $criteriaPath  = config('repository.generator.paths.criteria', 'Criteria');
        $basePath      = config('repository.generator.basePath', app_path());
        $rootNamespace = rtrim(config('repository.generator.rootNamespace', 'App\\'), '\\');

        $directory = $basePath . DIRECTORY_SEPARATOR . $criteriaPath;

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $className = str_ends_with($name, 'Criteria') ? $name : $name . 'Criteria';
        $filePath  = $directory . DIRECTORY_SEPARATOR . $className . '.php';

        if (file_exists($filePath)) {
            $this->error("Criteria already exists: {$className}");

            return self::FAILURE;
        }

        $namespace = $rootNamespace . '\\' . $criteriaPath;

        $stub = file_get_contents(__DIR__ . '/../stubs/criteria.stub');
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$namespace, $className],
            $stub
        );

        file_put_contents($filePath, $stub);

        $this->info("Criteria created successfully: {$className}");

        return self::SUCCESS;
    }
}
