<?php

namespace App\Facades\Services\ContactDepartment;

use Illuminate\Support\Facades\Facade;

class ContactDepartmentFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ContactDepartmentService';
    }
}