<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class AboutUs extends BaseModel
{protected $fillable = [
        'title' => 'title',
        'content' => 'content',
        'image' => 'image',
    ];

protected $casts = [
        'title' => 'array',
        'content' => 'array',
    ];

    //
}
