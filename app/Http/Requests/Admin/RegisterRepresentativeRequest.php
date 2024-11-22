<?php

namespace App\Http\Requests\Admin;

use App\Traits\ResponseTrait;
use Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class RegisterRepresentativeRequest extends FormRequest
{
    use ResponseTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role->name === 'admin';
    }

    public function failedAuthorization()
    {
        throw new HttpResponseException($this->getResponse('error', 'This action is unauthorized.', 422));
    }

    /**
     * Get the validation rules that apply to the request.
     * @return string[]
     */
    public function rules()
    {
        return [
            'name' => 'required|string|min:3|max:50',
            'email' => 'required|email|unique:representatives,email',
            'password' => 'required|confirmed|min:8',
            'address' => 'required|string|min:5|max:100',
            'union_branch' => 'required|string|max:100',
            'union_number' => 'required|unique:representatives,union_number|digits:8',
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
        throw new ValidationException($validator, $this->getResponse('errors', $validator->errors(), 422));
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
            'union_branch' => 'Union branch',
            'union_number' => 'Union number',
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
