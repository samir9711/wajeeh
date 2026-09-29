<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class ContactDepartment extends BaseModel
{protected $fillable = [
        'name' => 'name',
        'phone' => 'phone',
        'whatsapp' => 'whatsapp',
        'email' => 'email',
        'sort_order' => 'sort_order',
    ];

protected $casts = [
        'name' => 'array',
        'sort_order' => 'integer',
    ];

    //
}
