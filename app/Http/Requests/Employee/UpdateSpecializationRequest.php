<?php

namespace App\Http\Requests\Employee;

use App\Traits\ResponseTrait;
use Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class UpdateSpecializationRequest extends FormRequest
{
    use ResponseTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->hasRole('employee');
    }

    public function failedAuthorization()
    {
        throw new HttpResponseException($this->getResponse('error', 'This action is unauthorized.', 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|min:4|max:20|unique:specializations,name',
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
            'name' => 'Specialization name',
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
            'max' => ':attribute must be at maximum :max characters.',
        ];
    }
}
