<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class ContactInfo extends BaseModel
{
    protected $fillable = [
        'phone' => 'phone',
        'whatsapp' => 'whatsapp',
        'email' => 'email',
        'address_ar' => 'address_ar',
        'address_en' => 'address_en',
        'instagram' => 'instagram',
        'facebook' => 'facebook',
        'tiktok' => 'tiktok',
        'twitter' => 'twitter',
    ];

    protected $casts = [
    ];

    //
}
