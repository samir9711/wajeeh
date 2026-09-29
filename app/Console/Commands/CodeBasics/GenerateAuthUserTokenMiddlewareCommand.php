<?php

namespace App\Console\Commands\CodeBasics;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class GenerateAuthUserTokenMiddlewareCommand extends Command
{
    protected $signature = 'make:auth-user-token
    {--path= : Path to routes file (default: routes/api.php)}';

    protected $description = 'Generate an auth-user-token middleware under App\Http\Middleware\Auth and register it in the HTTP kernel';

    public function handle(Filesystem $files)
    {
        $name      = "AuthUserToken";
        $namespace = 'App\\Http\\Middleware\\Auth';
        $directory = app_path('Http/Middleware/Auth');
        $filePath  = "{$directory}/{$name}.php";

        // 1) Ensure folder exists
        if (! $files->isDirectory($directory)) {
            $files->makeDirectory($directory, 0755, true);
            $this->info("Created directory: {$directory}");
        }

        // 2) If file already exists, warn and skip
        if ($files->exists($filePath)) {
            $this->error("Middleware already exists: {$filePath}");
        } else {
            // 3) Stub with placeholders
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
                auth('sanctum')->check() &&
                auth('sanctum')->user()
            ) ? $next($request)
              : $this->unAuthorizeResponse();
        }

        return $next($request);
    }
}
PHP;

            // 4) Replace placeholders and write
            $content = str_replace(
                ['DummyNamespace', 'DummyClass'],
                [$namespace, $name],
                $stub
            );

            $files->put($filePath, $content);
            $this->info("Generated middleware: {$filePath}");
        }

        // 5) Register alias in Http Kernel
        $kernelPath = app_path('Http/Kernel.php');
        $kernel = $files->get($kernelPath);
        $aliasLine = "        'user-auth' => \\App\\Http\\Middleware\\Auth\\AuthUserToken::class,";

        if (Str::contains($kernel, $aliasLine)) {
            $this->info('User-auth alias already registered in Kernel.');
        } else {
            $pattern = '/protected\s+\$middlewareAliases\s*=\s*\[([^\]]*)\]/ms';
            $kernel = preg_replace_callback($pattern, function ($m) use ($aliasLine) {
                $inner = trim($m[1]);
                return "protected \$middlewareAliases = [\n" .
                    $inner . "\n" .
                    $aliasLine . "\n]";
            }, $kernel);

            $files->put($kernelPath, $kernel);
            $this->info('Registered user-auth alias in Kernel.');
        }

        $this->call('make:auth-groups', [
            'key'   => 'user-auth',
            '--path'=> $this->option('path') ?: 'routes/api.php',
        ]);

        // 4) Update config/auth.php
        $authPath = config_path('auth.php');
        $raw      = $files->get($authPath);
        $lines    = preg_split("/\r\n|\n/", $raw);

// prepare
        $key        = "users";              // e.g. 'admin'
        $modelFQ    = "App\\Models\\User::class";
        $guardEntry = [
            "    'sanctum' => [",
            "        'driver'   => 'sanctum',",
            "        'provider' => '$key',",
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
