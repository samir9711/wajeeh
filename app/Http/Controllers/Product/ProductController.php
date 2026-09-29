<?php

namespace App\Http\Controllers\Product;

use App\Facades\Services\Product\ProductFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreProductRequest;
use Illuminate\Http\Request;

class ProductController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "product";
        $this->service = ProductFacade::class;
        $this->createRequest = StoreProductRequest::class;
        $this->updateRequest = StoreProductRequest::class;
    }
}