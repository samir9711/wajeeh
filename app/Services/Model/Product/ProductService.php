<?php

namespace App\Services\Model\Product;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Product;
use App\Http\Resources\Model\ProductResource;

class ProductService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Product::class
        );

        $this->resource = ProductResource::class;
    }
}