<?php

namespace App\Http\Requests\Model;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        $admin = auth('admin')->user();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('admins', 'email')->ignore($admin->id),
            ],

            'image' => [
                'nullable',
                'string',
                'max:2048',
            ],

            /*
             * لا نطلب كلمة المرور الجديدة إلا عندما يريد المستخدم تغييرها.
             */
            'password' => [
                'sometimes',
                'string',
                'min:8',
                'confirmed',
            ],

            /*
             * مطلوبة فقط عندما يتم إرسال password.
             */
            'current_password' => [
                'required_with:password',
                'current_password:admin',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب.',
            'name.string' => 'الاسم يجب أن يكون نصًا.',
            'name.max' => 'الاسم يجب ألا يتجاوز 255 حرفًا.',

            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'البريد الإلكتروني غير صالح.',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل.',

            'password.min' => 'كلمة المرور يجب ألا تقل عن 8 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',

            'current_password.required_with' => 'كلمة المرور الحالية مطلوبة عند تغيير كلمة المرور.',
            'current_password.current_password' => 'كلمة المرور الحالية غير صحيحة.',
        ];
    }
}
