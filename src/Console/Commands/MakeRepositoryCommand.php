<?php

namespace Laravel\Repository\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeRepositoryCommand extends Command
{
    protected $signature = 'make:repository {name : The model name (e.g. User or Models/User)}';

    protected $description = 'Create a new repository class';

    public function handle(): int
    {
        $name        = $this->argument('name');
        $modelParts  = explode('/', str_replace('\\', '/', $name));
        $model       = array_pop($modelParts);
        $subPath     = implode('/', $modelParts);

        $repositoryPath = config('repository.generator.paths.repositories', 'Repositories');
        $basePath       = config('repository.generator.basePath', app_path());
        $rootNamespace  = rtrim(config('repository.generator.rootNamespace', 'App\\'), '\\');

        $directory = $basePath . DIRECTORY_SEPARATOR . $repositoryPath
            . ($subPath ? DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $subPath) : '');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $className  = $model . 'Repository';
        $filePath   = $directory . DIRECTORY_SEPARATOR . $className . '.php';

        if (file_exists($filePath)) {
            $this->error("Repository already exists: {$className}");

            return self::FAILURE;
        }

        $namespace = $rootNamespace . '\\' . $repositoryPath
            . ($subPath ? '\\' . str_replace('/', '\\', $subPath) : '');

        $modelNamespace = $rootNamespace . '\\Models';

        $stub = file_get_contents(__DIR__ . '/../stubs/repository.stub');
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ model }}', '{{ modelNamespace }}'],
            [$namespace, $className, $model, $modelNamespace],
            $stub
        );

        file_put_contents($filePath, $stub);

        $this->info("Repository created successfully: {$className}");

        return self::SUCCESS;
    }
}
