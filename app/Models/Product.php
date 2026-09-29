<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Product extends BaseModel
{protected $fillable = [
        'name' => 'name',
        'description' => 'description',
        'image' => 'image',
        'price' => 'price',
        'sort_order' => 'sort_order',
    ];

protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'price' => 'float',
        'sort_order' => 'integer',
    ];

    //
}
