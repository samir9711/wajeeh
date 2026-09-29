<?php

namespace App\Http\Requests\Model\Auth\ResetPassword;

use App\Http\Requests\Basic\BasicRequest;
use Illuminate\Validation\Rule;

class SendResetOtpRequest extends BasicRequest
{
    public function rules(): array
    {
        return [
            'actor'        => ['required', Rule::in(['user','admin'])],

            // Admin: email required. User: email أو phone
            'email'        => ['nullable','email','required_if:actor,admin','required_without_all:prefix_phone,phone_number'],
            'prefix_phone' => ['nullable','string','max:10','required_with:phone_number','prohibited_if:actor,admin'],
            'phone_number' => ['nullable','string','max:30','required_with:prefix_phone','prohibited_if:actor,admin'],

            // القناة: admin ⇒ email فقط، user ⇒ sms/whatsapp/email
            'channel'      => ['required', Rule::in(['sms','whatsapp','email'])],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            if ($this->input('actor') === 'admin' && $this->input('channel') !== 'email') {
                $v->errors()->add('channel', __('validation.custom.channel.admin_email_only'));
            }
        });
    }

    public function messages(): array
    {
        return [
            'actor.required'   => __('validation.required', ['attribute' => 'actor']),
            'actor.in'         => __('validation.in', ['attribute' => 'actor']),

            'email.required'   => __('validation.custom.email.required'),
            'email.email'      => __('validation.custom.email.email'),

            'prefix_phone.required_with' => __('validation.custom.prefix_phone.required'),
            'phone_number.required_with' => __('validation.custom.phone.required'),

            'channel.required' => __('validation.custom.channel.required'),
            'channel.in'       => __('validation.custom.channel.in'),
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone_number')) {
            $this->merge([
                'phone_number' => preg_replace('/[\s-]+/', '', (string) $this->input('phone_number')),
            ]);
        }
    }
}
