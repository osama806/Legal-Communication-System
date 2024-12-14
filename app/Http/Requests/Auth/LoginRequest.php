<?php

namespace App\Http\Requests\Auth;

use App\Traits\ResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    use ResponseTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "email" => "required|email",
            "password" => "required|min:8"
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
            "email" => "Email address",
            "password" => "Password"
        ];
    }

    /**
     * Get custom messages for validator errors.
     * @return string[]
     */
    public function messages()
    {
        return [
            "required" => ":attribute is required",
            "email" => "Please enter a valid :attribute",
            "min" => ":attribute must be at least :min characters long"
        ];
    }
}
