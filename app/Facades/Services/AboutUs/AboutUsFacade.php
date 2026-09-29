<?php

namespace App\Facades\Services\AboutUs;

use Illuminate\Support\Facades\Facade;

class AboutUsFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'AboutUsService';
    }
}