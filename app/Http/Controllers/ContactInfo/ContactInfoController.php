<?php

namespace App\Http\Controllers\ContactInfo;

use App\Facades\Services\ContactInfo\ContactInfoFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreContactInfoRequest;
use Illuminate\Http\Request;

class ContactInfoController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "contact_info";
        $this->service = ContactInfoFacade::class;
        $this->createRequest = StoreContactInfoRequest::class;
        $this->updateRequest = StoreContactInfoRequest::class;
    }
}