<?php

namespace App\Http\Requests\Employee;

use App\Traits\ResponseTrait;
use Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class UpdateEmployeeInfoRequest extends FormRequest
{
    use ResponseTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role->name === 'employee';
    }

    public function failedAuthorization()
    {
        throw new HttpResponseException($this->getResponse('error', 'This action is unauthorized', 422));
    }

    /**
     * Get the validation rules that apply to the request.
     * @return string[]
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string|min:3|max:50',
            'address' => 'nullable|string|min:5|max:100',
            'birthdate' => 'nullable|date|date_format:Y-m-d',
            'birth_place' => 'nullable|string|min:3|max:100',
            'national_number' => 'nullable|digits:11|unique:users,national_number',
            'phone' => 'nullable|digits:10|unique:users,phone',
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
            'address' => 'Address',
            'birthdate' => 'Birth date',
            'birth_place' => 'Birth place',
            'national_number' => 'National number',
            'phone' => 'Phone number',
        ];
    }

    /**
     * Get custom messages for validator errors.
     * @return string[]
     */
    public function messages()
    {
        return [
            'unique' => 'This :attribute is already registered.',
            'min' => ':attribute must be at least :min characters long.',
            'in' => ':attribute must be either "male" or "female"',
            'date' => 'The :attribute must be a valid date format.',
        ];
    }
}
