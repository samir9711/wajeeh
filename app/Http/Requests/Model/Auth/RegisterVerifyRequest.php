<?php

namespace App\Http\Requests\Model\Auth;

use App\Http\Requests\Basic\BasicRequest;

class RegisterVerifyRequest extends BasicRequest
{
    public function rules(): array
    {
        return [
            'id'           => ["nullable",'integer','exists:users,id'],
            "email"        => "nullable|email",
            'otp'          => ['required','digits:6'],
            "purpose" => "required|string|in:register,reset,phone_change",
            'token_device' => ['nullable','string'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required'  => __('validation.required', ['attribute' => 'user']),
            'id.exists'    => __('validation.exists', ['attribute' => 'user']),
            'otp.required' => __('validation.custom.otp.required'),
            'otp.digits'   => __('validation.custom.otp.digits'),
        ];
    }
}
