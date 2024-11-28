<?php

namespace App\Http\Requests\Lawyer;

use App\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class FilterAiRequest extends FormRequest
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
            "per_page" => "nullable|integer|min:1",
            'name' => 'nullable|string',
            'union_branch' => 'nullable|string',
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
            'name' => 'Lawyer name',
            'union_branch' => 'Union branch',
        ];
    }

    public function messages()
    {
        return [
            'integer' => 'The :attribute must be a valid integer.',
            "string" => "The :attribute must be a string.",
            "in" => "The selected :attribute is invalid.",
        ];
    }
}
