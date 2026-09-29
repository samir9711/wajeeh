<?php

namespace App\Facades\Services\Product;

use Illuminate\Support\Facades\Facade;

class ProductFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ProductService';
    }
}