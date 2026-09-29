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
        return $this->model::withFilters()
            ->with($this->relations)
            ->orderBy('created_at', 'desc');
    }

    /** ✅ add: pureAll used by FatherCrudController */
    public function pureAll(Request $request): mixed
    {
        $data = $this->allQuery()->get();
        return $this->resource::collection($data);
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
        $this->object = $this->model::with($this->relations)
            ->withTrashed()
            ->withCount($this->countRelations)
            ->findOrFail($request->id);

        return $this->resource::make($this->object);
    }

    public function create(BasicRequest $request): mixed {
        $this->object = $this->model::create($request->validated());
        return $this->resource::make($this->object->load($this->relations));
    }

    public function update(BasicRequest $request): mixed {
        $this->object =  $this->model::with($this->relations)->findOrFail($request->id);
        $this->object->update($request->validated());
        return $this->resource::make($this->object);
    }

    public function activate(Request $request): bool {
        $this->object =  $this->model::withTrashed()->findOrFail($request->id);
        return $this->object->restore();
    }

    public function deactivate(Request $request): bool {
        $this->object =  $this->model::findOrFail($request->id);
        return $this->object->delete();
    }

    public function delete(Request $request): bool {
        $this->object =  $this->model::findOrFail($request->id);
        return $this->object->forceDelete();
    }

    public function getObject() : object
    {
        return $this->object;
    }
}
