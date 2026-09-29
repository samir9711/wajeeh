<?php

namespace App\Http\Controllers\ContactDepartment;

use App\Facades\Services\ContactDepartment\ContactDepartmentFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreContactDepartmentRequest;
use Illuminate\Http\Request;

class ContactDepartmentController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "contact_department";
        $this->service = ContactDepartmentFacade::class;
        $this->createRequest = StoreContactDepartmentRequest::class;
        $this->updateRequest = StoreContactDepartmentRequest::class;
    }
}