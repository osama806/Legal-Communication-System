<?php

namespace App\Http\Requests\Employee;

use App\Traits\ResponseTrait;
use Auth;
use Illuminate\Foundation\Http\FormRequest;

class IndexFilterRequest extends FormRequest
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
        return $this->error('This action is unauthorized', 422);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "per_page" => "nullable|integer|min:1",
            'name' => 'nullable|string',
            'national_number' => 'nullable|numeric',
            'gender' => 'nullable|string|in:male,female',
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        return $this->error($validator->errors(), 400);
    }

    public function attributes()
    {
        return [
            'per_page' => 'Items per page',
            'name' => 'User name',
            'national_number' => 'National number',
            'gender' => 'Gender',
        ];
    }

    public function messages()
    {
        return [
            'integer' => 'The :attribute must be a valid integer.',
            "string" => "The :attribute must be a string.",
            "in" => "The selected :attribute is invalid.",
            'min' => ':attribute must be at least :min characters long.',
            'numeric' => 'The :attribute must be a numeric value.',
        ];
    }
}
