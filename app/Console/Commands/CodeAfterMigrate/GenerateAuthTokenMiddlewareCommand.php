<?php

namespace App\Console\Commands\CodeAfterMigrate;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class GenerateAuthTokenMiddlewareCommand extends BaseScaffoldCommand
{
    protected $signature = 'make:auth-token
        {model : The base name of your user model (e.g. Admin, Employee)}
        {--path= : Path to routes file (default: routes/api.php)}';

    protected $description = 'Create Auth{Model}Token middleware, register it, and add guards/providers to config/auth.php';

    public function handle(Filesystem $files)
    {

        $input = $this->argument('model');
        $modelClass = $this->handelModelScope($input);

        if (! class_exists($modelClass)) {
            return $this->error("Model not found: {$modelClass}");
        }

        $model     = Str::studly($this->argument('model'));
        $lower     = Str::kebab($model);              // e.g. 'admin' or 'employee'
        $className = "Auth{$model}Token";
        $namespace = 'App\\Http\\Middleware\\Auth';
        $dir       = app_path('Http/Middleware/Auth');
        $filePath  = "{$dir}/{$className}.php";

        // 1) Ensure middleware directory exists
        if (! $files->isDirectory($dir)) {
            $files->makeDirectory($dir, 0755, true);
            $this->info("Created directory: {$dir}");
        }

        // 2) Generate middleware if not exists
        if (! $files->exists($filePath)) {
            $stub = <<<'PHP'
<?php

namespace DummyNamespace;

use App\Http\Traits\GeneralTrait;
use Closure;
use Illuminate\Http\Request;

class DummyClass
{
    use GeneralTrait;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('api/*')) {
            return (
                auth('DUMMY_GUARD')->check() &&
                auth('DUMMY_GUARD')->user()
            ) ? $next($request)
              : $this->unAuthorizeResponse();
        }

        return $next($request);
    }
}
PHP;

            $content = str_replace(
                ['DummyNamespace','DummyClass','DUMMY_GUARD'],
                [$namespace, $className, $lower],
                $stub
            );
            $files->put($filePath, $content);
            $this->info("Created middleware: {$filePath}");
        } else {
            $this->info("Middleware already exists: {$filePath}");
        }

        // 3) Register alias in Http/Kernel.php
        $kernelPath = app_path('Http/Kernel.php');
        $kernel     = $files->get($kernelPath);
        $kernelKey = "{$lower}-auth";
        $aliasLine  = "        '$kernelKey' => \\App\\Http\\Middleware\\Auth\\{$className}::class,";

        if (Str::contains($kernel, $aliasLine)) {
            $this->info("Alias '{$lower}-auth' already in Kernel.");
        } else {
            $pattern = '/protected\s+\$middlewareAliases\s*=\s*\[([\s\S]*?)\];/m';
            $kernel = preg_replace_callback($pattern, function($m) use($aliasLine) {
                $inner = rtrim($m[1]);
                return "protected \$middlewareAliases = [\n{$inner}\n{$aliasLine}\n];";
            }, $kernel);
            $files->put($kernelPath, $kernel);
            $this->info("Registered alias '{$lower}-auth' in Kernel.");
        }

        $this->call('make:auth-groups', [
            'key'   => $kernelKey,
            '--path'=> $this->option('path') ?: 'routes/api.php',
        ]);


        // … up above, after registering middleware & kernel alias …

        // … after generating middleware & kernel alias …

        // 4) Update config/auth.php
        $authPath = config_path('auth.php');
        $raw      = $files->get($authPath);
        $lines    = preg_split("/\r\n|\n/", $raw);

// prepare
        $key        = Str::kebab($model);              // e.g. 'admin'
        $modelFQ    = "App\\Models\\{$model}::class";
        $guardEntry = [
            "    '{$key}' => [",
            "        'driver'   => 'sanctum',",
            "        'provider' => '{$key}',",
            "    ],",
        ];
        $provEntry  = [
            "    '{$key}' => [",
            "        'driver' => 'eloquent',",
            "        'model'  => {$modelFQ},",
            "    ],",
        ];

// helper to inject into one block
        $inject = function(array &$lines, string $blockName, array $entry) {
            $inBlock = false;
            $depth   = 0;
            $indent  = '';
            for ($i = 0, $n = count($lines); $i < $n; $i++) {
                $line = $lines[$i];

                // start?
                if (! $inBlock && preg_match("/^(\s*)'{$blockName}'\s*=>\s*\[/", $line, $m)) {
                    $inBlock = true;
                    $indent  = $m[1];
                    continue;
                }

                if ($inBlock) {
                    // track nested “[” vs “]”
                    $depth += substr_count($line, '[');
                    $depth -= substr_count($line, ']');

                    // depth < 0 means we've found the closing of this block
                    if ($depth < 0) {
                        // insert *just before* this line
                        array_splice($lines, $i, 0, array_map(fn($l) => $indent . $l, $entry));
                        return true;
                    }
                }
            }
            return false;
        };

// 4a) guards
        if (strpos($raw, $guardEntry[0]) !== false) {
            $this->info("Guard '{$key}' already present.");
        } else {
            if ($inject($lines, 'guards', $guardEntry)) {
                $this->info("Added guard '{$key}'.");
            } else {
                $this->error("Failed to find top-level 'guards' array for injection.");
            }
        }

// re-assemble and reload
        $raw   = implode("\n", $lines);
        $lines = preg_split("/\r\n|\n/", $raw);

//
// 4b) Inject into the top‐level providers array
//
        $keyLinePattern = "/^\s*'{$key}'\s*=>\s*\[/";

        $inBlock = false;
        $depth   = 0;
        $exists  = false;

        foreach ($lines as $line) {
            if (! $inBlock) {
                if (preg_match("/^\s*'providers'\s*=>\s*\[/", $line)) {
                    $inBlock = true;
                    $depth = 0;
                }
                continue;
            }

            // we’re inside providers now
            $depth += substr_count($line, '[');
            $depth -= substr_count($line, ']');

            // if we see our key, mark exists
            if (preg_match($keyLinePattern, $line)) {
                $exists = true;
                break;
            }

            // once depth < 0, we've left the block
            if ($depth < 0) {
                break;
            }
        }

        if ($exists) {
            $this->info("Provider '{$key}' already present.");
        } else {
            // attempt injection
            if ($inject($lines, 'providers', $provEntry)) {
                $this->info("Added provider '{$key}'.");
            } else {
                $this->error("Failed to find top-level 'providers' array for injection.");
            }
        }

// write back after provider injection
        $files->put($authPath, implode("\n", $lines));


        return 0;
    }
}
