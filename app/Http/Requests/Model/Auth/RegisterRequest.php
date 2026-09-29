<?php

namespace App\Http\Requests\Model\Auth;

use App\Http\Requests\Basic\BasicRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends BasicRequest
{
    public function rules(): array
    {
        return [
            'first_name'    => ['required','string','max:10'],
            'last_name'     => ['nullable','string','max:255'],
            'father_name'   => ['nullable','string','max:255'],
            'phone'         => ['required','string','max:30'],
            'email'         => ['required','email','max:255'],
            'password'      => ['required','string','min:6','max:100','confirmed'],
            'gender'        => ['required',Rule::in(['male','female'])],
            'country'       => ['required','string'],
            'city'          => ['required','string'],
            'birth_date'    => ['required','date_format:Y-m-d'],

            // قناة إرسال OTP
            'otp_delivery_method'      => ['required', Rule::in(['sms','whatsapp','email'])],


            'token_device' => ['nullable','string'],
        ];
    }

    public function messages(): array
    {
        return [

            'first_name.required' => __('validation.custom.first_name.required'),
            'first_name.max'      => __('validation.custom.first_name.max'),

            'last_name.max'       => __('validation.custom.last_name.max'),

            'father_name.max'     => __('validation.custom.father_name.max'),

            'phone.required'      => __('validation.custom.phone.required'),
            'phone.max'           => __('validation.custom.phone.max'),

            'email.required'      => __('validation.custom.email.required'),
            'email.email'         => __('validation.custom.email.email'),
            'email.max'           => __('validation.custom.email.max'),

            'password.required'   => __('validation.custom.password.required'),
            'password.confirmed'  => __('validation.custom.password.confirmed'),
            'password.min'        => __('validation.custom.password.min'),
            'password.max'        => __('validation.custom.password.max'),

            'gender.required'     => __('validation.custom.gender.required'),
            'gender.in'           => __('validation.custom.gender.in'),

            'country.required'    => __('validation.custom.country.required'),
            'city.required'       => __('validation.custom.city.required'),

            'birth_date.required'      => __('validation.custom.birth_date.required'),
            'birth_date.date_format'   => __('validation.custom.birth_date.format'),

            'otp_delivery_method.required' => __('validation.custom.otp_delivery_method.required'),
            'otp_delivery_method.in'       => __('validation.custom.otp_delivery_method.in'),
        ];
    }


    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => preg_replace('/[\s-]+/', '', (string) $this->input('phone')),
            ]);
        }
    }
}
