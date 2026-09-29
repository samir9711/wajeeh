<?php

namespace App\Http\Requests\Model\Auth;

use App\Http\Requests\Basic\BasicRequest;

class ResendRegisterOtpRequest extends BasicRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required','integer','exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => __('validation.required', ['attribute' => 'user']),
            'id.exists'   => __('validation.exists', ['attribute' => 'user']),
        ];
    }
}
