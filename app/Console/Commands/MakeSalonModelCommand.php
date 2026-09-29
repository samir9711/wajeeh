<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class MakeSalonModelCommand extends Command
{
    protected $signature = 'make:salon-model {name}';
    protected $description = 'Create a new Salon-specific model with migration';

    public function handle(): void
    {
        $name = $this->argument('name');
        $modelClass = 'App\\Models\\' . Str::studly($name);
        $modelPath = app_path('Models/' . Str::studly($name) . '.php');

        // Create model
        if (!file_exists($modelPath)) {
            Artisan::call('make:model', [
                'name' => $name,
                '-m' => true,
            ]);
            $this->info("Model created: {$modelClass}");
        } else {
            $this->warn("Model already exists: {$modelClass}");
        }

        // Add salon_id to migration
        $migrationNamePart = 'create_' . Str::snake(Str::pluralStudly($name)) . '_table';
        $filesystem = new Filesystem();

        $latestMigrationFile = collect($filesystem->files(database_path('migrations')))
            ->filter(fn($file) => str_contains($file->getFilename(), $migrationNamePart))
            ->sortByDesc(fn($file) => $file->getMTime())
            ->first();

        if ($latestMigrationFile) {
            $content = file_get_contents($latestMigrationFile->getPathname());

            // Add salon_id after id column if it doesn't exist
            if (!str_contains($content, 'salon_id')) {
                $content = preg_replace(
                    '/(\$table->id\(\);)/',
                    '$1' . PHP_EOL . '            $table->foreignId(\'salon_id\')->constrained(\'salons\')->onDelete(\'cascade\');',
                    $content
                );

                file_put_contents($latestMigrationFile->getPathname(), $content);
                $this->info("Added salon_id to migration");
            }
        } else {
            $this->warn("No migration found to modify.");
        }
    }
}
