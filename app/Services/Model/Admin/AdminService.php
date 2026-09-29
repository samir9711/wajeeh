<?php

namespace App\Services\Model\Admin;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Admin;
use App\Http\Resources\Model\AdminResource;

class AdminService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Admin::class
        );

        $this->resource = AdminResource::class;
    }
}