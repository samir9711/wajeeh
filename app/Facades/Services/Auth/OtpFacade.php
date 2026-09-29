<?php

namespace App\Facades\Services\Auth;

use Illuminate\Support\Facades\Facade;

class OtpFacade extends Facade
{

    protected static function getFacadeAccessor(): string
    {
        return 'OtpService';
    }
}
