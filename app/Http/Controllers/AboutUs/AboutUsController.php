<?php

namespace App\Http\Controllers\AboutUs;

use App\Facades\Services\AboutUs\AboutUsFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreAboutUsRequest;
use Illuminate\Http\Request;

class AboutUsController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "about_us";
        $this->service = AboutUsFacade::class;
        $this->createRequest = StoreAboutUsRequest::class;
        $this->updateRequest = StoreAboutUsRequest::class;
    }
}