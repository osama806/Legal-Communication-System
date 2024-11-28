<?php

namespace App\Http\Requests\Rate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class FilterAiRequest extends FormRequest
{
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
            "per_page" => "nullable|integer|min:1",
            'rate' => 'nullable|numeric|min:1|max:5',
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new ValidationException($validator, $this->getResponse('errors', $validator->errors(), 400));
    }

    public function attributes()
    {
        return [
            'per_page' => 'Items per page',
            'rate' => 'Lawyer rating',
        ];
    }

    public function messages()
    {
        return [
            'integer' => 'The :attribute must be a valid integer.',
            'numeric' => 'The :attribute must be a numeric value.',
            'max' => ':attribute must not exceed :max characters.',
            'min' => ':attribute must be at least :min characters long.',
        ];
    }
}
