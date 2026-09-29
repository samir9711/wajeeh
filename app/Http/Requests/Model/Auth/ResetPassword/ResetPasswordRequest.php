<?php

namespace App\Http\Requests\Model\Auth\ResetPassword;

use App\Http\Requests\Basic\BasicRequest;

class ResetPasswordRequest extends BasicRequest
{
    public function rules(): array
    {
        return [
            'password' => ['required','string','min:6','confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required'  => __('validation.custom.password.required'),
            'password.confirmed' => __('validation.custom.password.confirmed'),
            'password.min'       => __('validation.custom.password.min'),
        ];
    }
}
