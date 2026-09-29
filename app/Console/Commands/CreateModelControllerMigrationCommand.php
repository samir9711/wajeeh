<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class CreateModelControllerMigrationCommand extends Command
{
    protected $signature = 'make:mcm {name?}
        {--model-folder= : The folder to save the model (default: Models)}
        {--controller-folder= : The folder to save the controller (default: Http/Controllers)}
        {--resource-folder= : The folder to save the resource (default: Http/Resources)}
        {--skip-model}
        {--skip-controller}
        {--skip-resource}
        {--skip-request}
        {--skip-service}
        {--skip-facade}';

    protected $description = 'Create model, controller, migration, service, facade, and resource with full interactive or option-based skipping.';

    public function handle()
    {
        // 1. Get the main name
        $name = $this->argument('name');
        if (!$name) {
            $name = $this->ask('What is the base name? (ex: User)');
        }

        // 2. Ask for folders or use option/default
        $modelFolder = $this->option('model-folder') ?? $this->ask('Model folder? (empty for default)', 'Models');
        $controllerFolder = $this->option('controller-folder') ?? $this->ask('Controller folder? (empty for default)', 'Http/Controllers');
        $resourceFolder = $this->option('resource-folder') ?? $this->ask('Resource folder? (empty for default)', 'Http/Resources');

        // 3. Handle skips
        $skipModel      = $this->option('skip-model')      ? true : !$this->confirm('Create Model & Migration?', true);
        $skipController = $this->option('skip-controller') ? true : !$this->confirm('Create Controller?', true);
        $skipResource   = $this->option('skip-resource')   ? true : !$this->confirm('Create Resource?', true);
        $skipRequest    = $this->option('skip-request')    ? true : !$this->confirm('Create FormRequest?', true);
        $skipService    = $this->option('skip-service')    ? true : !$this->confirm('Create Service?', true);
        $skipFacade     = $this->option('skip-facade')     ? true : !$this->confirm('Create Facade?', true);

        // ========== Model & Migration ==========
        if (!$skipModel) {
            $modelPath = ($modelFolder && $modelFolder != "Models") ? "{$modelFolder}/{$name}" : $name;
            Artisan::call('make:model', [
                'name' => $modelPath,
                '-m' => true,
            ]);
            $this->info("Model and migration created: app/{$modelPath}.php");
        }

        // ========== Controller ==========
        if (!$skipController) {
            $controllerPath = ($controllerFolder) ? "{$controllerFolder}/{$name}Controller" : "Http/Controllers/{$name}Controller";
            Artisan::call('make:controller', [
                'name' => "Api/{$controllerPath}",
                '--api' => true,
            ]);
            $this->info("Controller created: app/Http/Controllers/Api/{$controllerPath}.php");
        }

        // ========== Resource ==========
        if (!$skipResource) {
            $resourcePath = ($resourceFolder) ? "{$resourceFolder}/{$name}Resource" : "Model/{$name}Resource";
            Artisan::call('make:resource', [
                'name' => $resourcePath,
            ]);
            $this->info("Resource created: app/{$resourcePath}.php");
        }

        // ========== Form Request ==========
        if (!$skipRequest) {
            Artisan::call('make:request', [
                'name' => "{$name}/Store{$name}Request"
            ]);
            $this->info("FormRequest created: app/Http/Requests/{$name}/Store{$name}Request.php");
        }

        // ========== Service ==========
        if (!$skipService) {
            $serviceFolder = app_path("Services/Model/{$name}");
            if (!File::exists($serviceFolder)) {
                File::makeDirectory($serviceFolder, 0755, true);
            }
            $serviceFile = $serviceFolder . "/{$name}Service.php";
            if (!File::exists($serviceFile)) {
                $serviceStub = "<?php

namespace App\Services\Model\\{$name};

class {$name}Service
{
    // Your service methods here.
}
";
                File::put($serviceFile, $serviceStub);
                $this->info("Service file created: {$serviceFile}");
            } else {
                $this->info("Service file already exists: {$serviceFile}");
            }
        }

        // ========== Facade ==========
        if (!$skipFacade) {
            $facadeDir = app_path("Facades/Services/{$name}");
            if (!File::exists($facadeDir)) {
                File::makeDirectory($facadeDir, 0755, true);
            }
            $facadeFile = $facadeDir . "/{$name}Facade.php";
            if (!File::exists($facadeFile)) {
                $facadeStub = "<?php

namespace App\Facades\Services\\{$name};

use Illuminate\Support\Facades\Facade;

class {$name}Facade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return '{$name}Service';
    }
}
";
                File::put($facadeFile, $facadeStub);
                $this->info("Facade file created: {$facadeFile}");
            } else {
                $this->info("Facade file already exists: {$facadeFile}");
            }
        }
    }
}
