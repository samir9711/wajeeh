<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Message extends BaseModel
{protected $fillable = [
        'name' => 'name',
        'email' => 'email',
        'phone' => 'phone',
        'subject' => 'subject',
        'message' => 'message',
    ];

protected $casts = [
    ];

    //
}
