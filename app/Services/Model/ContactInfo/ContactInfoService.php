<?php

namespace App\Services\Model\ContactInfo;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\ContactInfo;
use App\Http\Resources\Model\ContactInfoResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactInfoService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = ContactInfo::class
        );

        $this->resource = ContactInfoResource::class;
    }

    public function show(Request $request): mixed {


        $this->object = $this->model::with(
            $this->relations
        )->withTrashed()->withCount($this->countRelations)->firstOrFail();

        return $this->resource::make($this->object);

    }

    public function create(Request $request): mixed
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            $item = $this->model::withTrashed()->first();
            if ($item) {
                $item->update($data);
            } else {
                $item = $this->model::create($data);
            }

            $this->object = $item->fresh()->load($this->relations);

            return $this->resource::make($this->object);
        });
    }
}
