<?php

namespace App\Services\Model\Message;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Message;
use App\Http\Resources\Model\MessageResource;

class MessageService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Message::class
        );

        $this->resource = MessageResource::class;
    }
}