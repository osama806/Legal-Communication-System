<?php

namespace App\Http\Requests\User;

use App\Traits\ResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class RegisterUserRequest extends FormRequest
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
     * @return string[]
     */
    public function rules()
    {
        return [
            'name' => 'required|string|min:3|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8',
            'address' => 'required|string|min:3|max:100',
            'birthdate' => 'required|date|date_format:Y-m-d',
            'birth_place' => 'required|string|min:3|max:100',
            'national_number' => 'required|digits:11|unique:users,national_number',
            'gender' => 'required|string|in:male,female',
            'phone' => 'required|digits:10|unique:users,phone',
            'avatar' => 'required|file|mimetypes:image/jpeg,image/png,image/gif,image/webp|max:5120'
        ];
    }

    /**
     * Get message that errors explanation.
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @throws \Illuminate\Validation\ValidationException
     * @return never
     */
    public function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, $this->success('errors', $validator->errors(), 422));
    }

    /**
     * Get custom attributes for validator errors.
     * @return string[]
     */
    public function attributes()
    {
        return [
            'name' => 'Full name',
            'email' => 'Email address',
            'password' => 'Password',
            'address' => 'Address',
            'birthdate' => 'Birth date',
            'birth_place' => 'Birth place',
            'national_number' => 'National number',
            'gender' => 'Gender',
            'phone' => 'Phone number',
            'avatar' => 'Avatar'
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
            'email' => 'Please enter a valid :attribute.',
            'unique' => 'This :attribute is already registered.',
            'min' => ':attribute must be at least :min characters long.',
            'confirmed' => ':attribute does not match.',
            'in' => ':attribute must be either "male" or "female"',
            'date' => 'The :attribute must be a valid date format.',
        ];
    }
}
