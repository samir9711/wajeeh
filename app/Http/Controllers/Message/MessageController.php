<?php

namespace App\Http\Controllers\Message;

use App\Facades\Services\Message\MessageFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreMessageRequest;
use Illuminate\Http\Request;

class MessageController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "message";
        $this->service = MessageFacade::class;
        $this->createRequest = StoreMessageRequest::class;
        $this->updateRequest = StoreMessageRequest::class;
    }
}