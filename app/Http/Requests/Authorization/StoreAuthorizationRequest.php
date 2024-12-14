<?php

namespace App\Http\Requests\Authorization;

use App\Traits\ResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StoreAuthorizationRequest extends FormRequest
{
    use ResponseTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::guard('api')->check() && Auth::guard('api')->user()->hasRole('admin');
    }

    public function failedAuthorization()
    {
        throw new HttpResponseException($this->error('This action is unauthorized', 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:50|unique:authorizations,name',
        ];
    }

    /**
     * Get message that errors explanation.
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, $this->error($validator->errors(), 400));
    }

    /**
     * Get custom attributes for validator errors.
     * @return string[]
     */
    public function attributes()
    {
        return [
            'name' => 'Authorization name',
        ];
    }

    /**
     * Get custom messages for validator errors.
     * @return string[]
     */
    public function messages()
    {
        return [
            'required' => ':attribute is required.',
            'unique' => 'This :attribute is already registered.',
            'min' => ':attribute must be at least :min characters long.',
            'max' => ':attribute must be at maximum :max characters.',
        ];
    }
}
