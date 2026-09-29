<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Services\Admin\AdminFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreAdminRequest;
use Illuminate\Http\Request;

class AdminController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "admin";
        $this->service = AdminFacade::class;
        $this->createRequest = StoreAdminRequest::class;
        $this->updateRequest = StoreAdminRequest::class;
    }
}