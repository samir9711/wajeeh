<?php

namespace App\Models;


use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Admin extends BaseAuthModel
{
    use HasRoles;

    protected $fillable = [
        'email' => 'email',
        'name' => 'name',
        'password' => 'password',
        'image' => 'image',
    ];

protected $casts = [
    ];


    protected $hidden = ['password'];

    protected $search=['email', 'name'];


    public function setPasswordAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['password'] = $value;
            return;
        }

        $this->attributes['password'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
    }




    //
}
