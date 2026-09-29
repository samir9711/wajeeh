<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Functional\AdminAuthService;
use App\Services\Functional\UserAuthService;
use App\Http\Traits\GeneralTrait;
use App\Services\Functional\CombinedAuthService;

class PublicAuthController extends Controller
{
    use GeneralTrait;

    public function login(Request $request, CombinedAuthService $service)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $data = $service->login($request);
            return $this->apiResponse($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
