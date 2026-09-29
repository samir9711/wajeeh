<?php

namespace App\Console\Commands\CodeAfterMigrate;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ScaffoldRouteCommand extends Command
{
    protected $signature = 'scaffold:routes
        {model   : The Eloquent model base name (e.g. Food)}
        {--path=  : Custom routes file (default: routes/api.php)}';

    protected $description = 'Inject CRUD or simple routes for a model into admin, user, or public routes';

    public function handle(Filesystem $files)
    {
        $input      = $this->argument('model');
        $base       = Str::studly($input);
        $uri        = Str::kebab($base);
        $controller = "\\App\\Http\\Controllers\\{$base}\\{$base}Controller";
        $path       = base_path($this->option('path') ?: 'routes/api.php');

        if (! $files->exists($path)) {
            return $this->error("Routes file not found: {$path}");
        }

        $content = $files->get($path);

        // Define lines sets
        $adminLines = [
            "Route::get('/all/paginated', [{$controller}::class, 'allPaginated']);",
            "Route::get('/all',           [{$controller}::class, 'all']);",
            "Route::post('/show',         [{$controller}::class, 'show']);",
            "Route::post('/create',       [{$controller}::class, 'store']);",
            "Route::post('/update',       [{$controller}::class, 'update']);",
            "Route::post('/activate',     [{$controller}::class, 'activate']);",
            "Route::post('/deactivate',   [{$controller}::class, 'deactivate']);",
        ];
        $simpleLines = array_slice($adminLines, 0, 3);

        // Admin injection
        /*if ($this->option('admin')) {
            $content = $this->injectIntoGroup($files, $content, $path, 'admin-auth', 'admin', $uri, $adminLines, $base);
        }

        // User injection
        if ($this->option('user')) {
            $content = $this->injectIntoGroup($files, $content, $path, 'user-auth', 'user', $uri, $simpleLines, $base);
        }*/

        // Public append
            $publicMarker = "// {$base} PUBLIC ROUTES";

            // Only check for our public marker so we don't get fooled by admin/user groups
            if (! Str::contains($content, $publicMarker)) {
                $block = PHP_EOL . "{$publicMarker}" . PHP_EOL;
                $block .= "Route::prefix('{$uri}')->group(function () {" . PHP_EOL;
                foreach ($adminLines as $line) {
                    $block .= "    {$line}" . PHP_EOL;
                }
                $block .= "});" . PHP_EOL;

                $files->append($path, $block);
                $this->info("Appended public routes for '{$uri}'.");
            } else {
                $this->info("Public routes for '{$uri}' already exist.");
            }

        return 0;
    }

    protected function injectIntoGroup(
        Filesystem $files,
        string $content,
        string $path,
        string $middlewareAlias,
        string $prefixUri,
        string $uri,
        array $lines,
        string $base
    ) {
        // Simplified pattern matching literal single‐quoted middleware and prefix
        $pattern = '/Route::middleware\(\s*' . preg_quote("'{$middlewareAlias}'") . '\s*\)' .
            '\s*->prefix\(\s*' . preg_quote("'{$prefixUri}'") . '\s*\)' .
            '\s*->group\(\s*function\s*\(\)\s*\{(.*?)\}\s*\)/ms';

        if (! preg_match($pattern, $content, $match)) {
            $this->error("No existing group found for middleware '{$middlewareAlias}'.");
            return $content;
        }

        $inner = trim($match[1]);
        if (strpos($inner, "prefix('{$uri}')") !== false) {
            $this->info("Routes for '{$uri}' already exist in '{$middlewareAlias}' group.");
            return $content;
        }

        // Build injection stub
        $stub = PHP_EOL . "    // {$base} ROUTES" . PHP_EOL;
        $stub .= "    Route::prefix('{$uri}')->group(function () {" . PHP_EOL;
        foreach ($lines as $line) {
            $stub .= "        {$line}" . PHP_EOL;
        }
        $stub .= "    });" . PHP_EOL;

        // Inject and persist
        $newGroup = str_replace($match[1], $match[1] . $stub, $match[0]);
        $updated  = str_replace($match[0], $newGroup, $content);
        $files->put($path, $updated);

        $this->info("Injected '{$base}' routes into '{$middlewareAlias}' group.");
        return $updated;
    }
}
