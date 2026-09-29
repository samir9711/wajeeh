<?php

namespace App\Services\Model\ContactDepartment;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\ContactDepartment;
use App\Http\Resources\Model\ContactDepartmentResource;

class ContactDepartmentService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = ContactDepartment::class
        );

        $this->resource = ContactDepartmentResource::class;
    }
}