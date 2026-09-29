<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class MakeTenantModelCommand extends Command
{
    protected $signature = 'make:tenant-model {name}';
    protected $description = 'Create a new Tenant model with migration inside database/migrations/tenant';

    public function handle(): void
    {
        $name = $this->argument('name');
        $modelClass = 'App\\Models\\Tenant\\' . Str::studly($name);
        $modelPath = app_path('Models/Tenant/' . Str::studly($name) . '.php');

        // Create model
        if (!file_exists($modelPath)) {
            Artisan::call('make:model', [
                'name' => 'Tenant/' . $name,
                '-m' => true,
            ]);
            $this->info("Model created: {$modelClass}");
        } else {
            $this->warn("Model already exists: {$modelClass}");
        }

        // Move migration to tenant path
        $migrationNamePart = 'create_' . Str::snake(Str::pluralStudly($name)) . '_table';
        $filesystem = new Filesystem();

        $latestMigrationFile = collect($filesystem->files(database_path('migrations')))
            ->filter(fn($file) => str_contains($file->getFilename(), $migrationNamePart))
            ->sortByDesc(fn($file) => $file->getMTime())
            ->first();

        if ($latestMigrationFile) {
            $tenantPath = database_path('migrations/tenant');
            if (!is_dir($tenantPath)) {
                mkdir($tenantPath, 0755, true);
            }

            $filesystem->move($latestMigrationFile->getPathname(), $tenantPath . '/' . $latestMigrationFile->getFilename());
            $this->info("Migration moved to: migrations/tenant");
        } else {
            $this->warn("No migration found to move.");
        }
    }
}
