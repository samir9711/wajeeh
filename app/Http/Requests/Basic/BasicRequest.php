<?php

namespace App\Http\Requests\Basic;

use App\Http\Traits\GeneralTrait;
use App\Services\Basic\ModelColumnsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class BasicRequest extends FormRequest
{
    use GeneralTrait;

    protected $modelColumnsService;

    public function __construct($model = null) {

        $this->modelColumnsService = ($model)? ModelColumnsService::getServiceFor(
            $model
        ) : null;

    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->requiredField($validator->errors()->first()));
    }
}
