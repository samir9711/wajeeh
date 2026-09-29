<?php

namespace App\Console\Commands\CodeBasics;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class GenerateBasicCrudServiceCommand extends Command
{
    protected $signature = 'make:basic-crud-service
     {--soft-deletes : Generate service that uses onlyTrashed/withTrashed/.. instead of a status column}';
    protected $description = 'Generate the BasicCrudService in app/Services/Basic';

    public function handle(Filesystem $files)
    {
        $directory = app_path('Services/Basic');
        if (! $files->isDirectory($directory)) {
            $files->makeDirectory($directory, 0755, true);
            $this->info("Created directory: {$directory}");
        }

        $filePath = $directory . '/BasicCrudService.php';
        if ($files->exists($filePath)) {
            $this->info("Overwriting existing Controller at: {$filePath}");
        }

        $stub = ($this->option("soft-deletes"))? $this->withTrashedStub() : $this->withStatus();

        $files->put($filePath, $stub);
        $this->info("Generated BasicCrudService at: {$filePath}");
        return 0;
    }

    protected function withTrashedStub() : mixed
    {
        return <<<'PHP'
<?php

namespace App\Services\Basic;


use App\Http\Requests\Basic\BasicRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

abstract class BasicCrudService {

    protected $modelColumnsService;

    protected $model;
    protected $resource;
    protected $object;
    protected $relations = [];
    protected $countRelations = [];


    public function __construct() {

        $this->setVariables();

    }

    protected abstract function setVariables() : void;

    protected function allQuery() : object
    {
        return $this->model::withFilters() //The With Filter Method Is Scope For Filter In The Model
        ->with($this->relations)
            ->withCount($this->countRelations)
            ->orderBy('created_at', 'desc');
    }

    public function all(Request $request) : mixed
    {
        $data = $this->allQuery()->get();
        return $this->resource::collection($data);
    }

    public function allPaginated(Request $request): mixed {

        $data = $this->allQuery()->paginate(
            $request->input('per_page', 10),
            ['*'],
            'page',
            $request->input('page', 1)
        );


        return [
            Str::plural(strtolower(class_basename($this->model))) => $this->resource::collection($data),
            'current_page' => $data->currentPage(),
            'next_page' => $data->nextPageUrl(),
            'previous_page' => $data->previousPageUrl(),
            'total_pages' => $data->lastPage(),
        ];

    }

    public function show(Request $request): mixed {


        $this->object = $this->model::withTrashed()->with(
            $this->relations
        )->withCount($this->countRelations)->findOrFail($request->id);

        return $this->resource::make($this->object);

    }

    public function create(BasicRequest $request): mixed {

        $this->object = $this->model::create($request->validated());
        return $this->resource::make(
                $this->object->load($this->relations)->loadCount($this->countRelations)
        );

    }

    public function update(BasicRequest $request): mixed {

        $this->object = $this->model::withTrashed()->findOrFail($request->id);
        $this->object->update(
            $request->validated()
        );

        return $this->resource::make($this->object->load($this->relations)->loadCount($this->countRelations));

    }

    public function activate(Request $request): bool {

        $this->object = $this->model::withTrashed()->findOrFail($request->id);
        return $this->object->restore();

    }

    public function deactivate(Request $request): bool {

        $this->object = $this->model::withTrashed()->findOrFail($request->id);
        return $this->object->delete();

    }

    public function delete(Request $request): bool {

        $this->object = $this->model::withTrashed()->findOrFail($request->id);
        return $this->object->forceDelete();

    }

    public function getObject() : object
    {
        return $this->object;
    }

}
PHP;
    }

    protected function withStatus() : mixed
    {
        return <<<'PHP'
<?php

namespace App\Services\Basic;


use App\Http\Requests\Basic\BasicRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

abstract class BasicCrudService {

    protected $modelColumnsService;

    protected $model;
    protected $resource;
    protected $object;
    protected $relations = [];
    protected $countRelations = [];


    public function __construct() {

        $this->setVariables();

    }

    protected abstract function setVariables() : void;

    protected function allQuery() : object
    {
        return $this->model::withFilters() //The With Filter Method Is Scope For Filter In The Model
        ->with($this->relations)
            ->withCount($this->countRelations)
            ->orderBy('created_at', 'desc');
    }

    public function all(Request $request) : mixed
    {
        $data = $this->allQuery()->get();
        return $this->resource::collection($data);
    }

    public function allPaginated(Request $request): mixed {

        $data = $this->allQuery()->paginate(
            $request->input('per_page', 10),
            ['*'],
            'page',
            $request->input('page', 1)
        );


        return [
            Str::plural(strtolower(class_basename($this->model))) => $this->resource::collection($data),
            'current_page' => $data->currentPage(),
            'next_page' => $data->nextPageUrl(),
            'previous_page' => $data->previousPageUrl(),
            'total_pages' => $data->lastPage(),
        ];

    }

    public function show(Request $request): mixed {


        $this->object = $this->model::with(
            $this->relations
        )->withCount($this->countRelations)->findOrFail($request->id);

        return $this->resource::make($this->object);

    }

    public function create(BasicRequest $request): mixed {

        $this->object = $this->model::create($request->validated());
        return $this->resource::make(
                $this->object->load($this->relations)->loadCount($this->countRelations)
        );

    }

    public function update(BasicRequest $request): mixed {

        $this->object = $this->model::findOrFail($request->id);
        $this->object->update(
            $request->validated()
        );

        return $this->resource::make($this->object->load($this->relations)->loadCount($this->countRelations));

    }

    public function activate(Request $request): bool {

        $this->object = $this->model::findOrFail($request->id);
        return $this->object->update([ "status" => 1 ]);

    }

    public function deactivate(Request $request): bool {

        $this->object = $this->model::findOrFail($request->id);
        return $this->object->update([ "status" => 0 ]);

    }

    public function delete(Request $request): bool {

        $this->object = $this->model::findOrFail($request->id);
        return $this->object->delete();

    }

    public function getObject() : object
    {
        return $this->object;
    }

}
PHP;
    }
}
