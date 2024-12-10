<?php

namespace App\Http\Requests\Employee;

use App\Traits\ResponseTrait;
use Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class UpdateUserInfoRequest extends FormRequest
{
    use ResponseTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        return Auth::check() && $user->hasRole('employee');
    }

    public function failedAuthorization()
    {
        throw new HttpResponseException($this->error("This action is unauthorized.", 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|min:3|max:50',
            'address' => 'nullable|string|min:3|max:100',
            'birthdate' => 'nullable|date|date_format:Y-m-d',
            'birth_place' => 'nullable|string|min:3|max:100',
            'phone' => 'nullable|digits:10|unique:users,phone',
            'avatar' => 'nullable|file|mimetypes:image/jpeg,image/png,image/gif,image/webp|max:5120'
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
        throw new ValidationException($validator, $this->success("errors", $validator->errors(), 422));
    }

    /**
     * Get custom attributes for validator errors.
     * @return string[]
     */
    public function attributes()
    {
        return [
            "name" => "Full name",
            'address' => 'Address',
            'birthdate' => 'Birth date',
            'birth_place' => 'Birth place',
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
            'min' => ':attribute must be at least :min characters long.',
            'max' => ':attribute must be at maximum :max characters.',
            'unique' => 'This :attribute is already registered.',
            'date' => 'The :attribute must be a valid date format.'
        ];
    }
}
