<?php

namespace App\Http\Requests\Code;

use App\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class VerifyRequest extends FormRequest
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
            'email' => [
                'required',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                'exists:code_generates,email'
            ],
            'code' => 'required|digits:6|exists:code_generates,code'
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new ValidationException($validator, $this->error($validator->errors(), 400));
    }

    public function attributes()
    {
        return [
            'email' => 'Email address',
            'code' => 'Code number'
        ];
    }

    public function messages()
    {
        return [
            "required" => ":attribute is required",
            "regex" => "Please enter a valid :attribute",
            'exists' => 'The selected :attribute does not exist in code_generates table.',
            "digits" => "The :attribute must be exactly 6 digits.",
        ];
    }
}
