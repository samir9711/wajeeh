<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Services\Admin\AdminFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreAdminRequest;
use App\Http\Requests\Model\UpdateAdminProfileRequest;
use Illuminate\Http\Request;

class AdminController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "admin";
        $this->service = AdminFacade::class;
        $this->createRequest = StoreAdminRequest::class;
        $this->updateRequest = StoreAdminRequest::class;
    }

    public function profile()
    {
        $admin = request()->user('admin');

        $admin = AdminFacade::getProfile($admin);

        return response()->json([
            'message' => 'تم جلب بيانات البروفايل بنجاح.',
            'data' => new \App\Http\Resources\Model\AdminResource($admin),
        ]);
    }

    public function updateProfile(UpdateAdminProfileRequest $request)
    {
        $admin = $request->user('admin');

        $admin = AdminFacade::updateProfile(
            $admin,
            $request->validated()
        );

        return response()->json([
            'message' => 'تم تحديث البروفايل بنجاح.',
            'data' => new \App\Http\Resources\Model\AdminResource($admin),
        ]);
    }
}
