<?php

namespace Laravel\Repository\Tests\Feature;

use Laravel\Repository\Tests\TestCase;

class ArtisanCommandsTest extends TestCase
{
    private string $tmpBase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tmpBase = sys_get_temp_dir() . '/repo_test_' . uniqid();
        mkdir($this->tmpBase, 0755, true);

        $this->app['config']->set('repository.generator.basePath', $this->tmpBase);
        $this->app['config']->set('repository.generator.rootNamespace', 'App\\');
        $this->app['config']->set('repository.generator.paths.repositories', 'Repositories');
        $this->app['config']->set('repository.generator.paths.criteria', 'Criteria');
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->tmpBase);
        parent::tearDown();
    }

    // ── make:repository ────────────────────────────────────────────────────

    public function test_make_repository_creates_file(): void
    {
        $this->artisan('make:repository', ['name' => 'User'])
            ->assertSuccessful();

        $this->assertFileExists($this->tmpBase . '/Repositories/UserRepository.php');
    }

    public function test_make_repository_file_contains_correct_class(): void
    {
        $this->artisan('make:repository', ['name' => 'User'])->assertSuccessful();

        $content = file_get_contents($this->tmpBase . '/Repositories/UserRepository.php');
        $this->assertStringContainsString('class UserRepository extends BaseRepository', $content);
        $this->assertStringContainsString('namespace App\\Repositories', $content);
    }

    public function test_make_repository_refuses_to_overwrite_existing(): void
    {
        $this->artisan('make:repository', ['name' => 'User'])->assertSuccessful();
        $this->artisan('make:repository', ['name' => 'User'])->assertFailed();
    }

    public function test_make_repository_creates_subdirectory(): void
    {
        $this->artisan('make:repository', ['name' => 'Admin/User'])->assertSuccessful();

        $this->assertFileExists($this->tmpBase . '/Repositories/Admin/UserRepository.php');

        $content = file_get_contents($this->tmpBase . '/Repositories/Admin/UserRepository.php');
        $this->assertStringContainsString('namespace App\\Repositories\\Admin', $content);
    }

    // ── make:criteria ──────────────────────────────────────────────────────

    public function test_make_criteria_creates_file(): void
    {
        $this->artisan('make:criteria', ['name' => 'ActiveUsers'])
            ->assertSuccessful();

        $this->assertFileExists($this->tmpBase . '/Criteria/ActiveUsersCriteria.php');
    }

    public function test_make_criteria_file_contains_correct_class(): void
    {
        $this->artisan('make:criteria', ['name' => 'ActiveUsers'])->assertSuccessful();

        $content = file_get_contents($this->tmpBase . '/Criteria/ActiveUsersCriteria.php');
        $this->assertStringContainsString('class ActiveUsersCriteria implements CriteriaInterface', $content);
        $this->assertStringContainsString('namespace App\\Criteria', $content);
    }

    public function test_make_criteria_appends_criteria_suffix_when_missing(): void
    {
        $this->artisan('make:criteria', ['name' => 'Active'])->assertSuccessful();

        $this->assertFileExists($this->tmpBase . '/Criteria/ActiveCriteria.php');
    }

    public function test_make_criteria_does_not_duplicate_suffix(): void
    {
        $this->artisan('make:criteria', ['name' => 'ActiveCriteria'])->assertSuccessful();

        $this->assertFileExists($this->tmpBase . '/Criteria/ActiveCriteria.php');
        $this->assertFileDoesNotExist($this->tmpBase . '/Criteria/ActiveCriteriaCriteria.php');
    }

    public function test_make_criteria_refuses_to_overwrite_existing(): void
    {
        $this->artisan('make:criteria', ['name' => 'Active'])->assertSuccessful();
        $this->artisan('make:criteria', ['name' => 'Active'])->assertFailed();
    }

    // ── helpers ────────────────────────────────────────────────────────────

    private function deleteDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        foreach (scandir($path) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $full = $path . DIRECTORY_SEPARATOR . $entry;

            is_dir($full) ? $this->deleteDirectory($full) : unlink($full);
        }

        rmdir($path);
    }
}
