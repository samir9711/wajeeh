<?php

namespace App\Facades\Services\ContactInfo;

use Illuminate\Support\Facades\Facade;

class ContactInfoFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ContactInfoService';
    }
}