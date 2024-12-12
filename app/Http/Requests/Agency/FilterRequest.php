<?php

namespace App\Http\Requests\Agency;

use App\Traits\ResponseTrait;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class FilterRequest extends FormRequest
{
    use ResponseTrait;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::guard('api')->check() && !Auth::guard('api')->user()->hasRole('user');
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
            'sequential_number' => 'nullable|numeric',
            'record_number' => 'nullable|numeric',
            'status' => 'nullable|string|in:approved,rejected',
            'type' => 'nullable|string|in:public,private,legitimacy',
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
            'sequential_number' => 'Sequential number',
            'record_number' => 'Record number',
            'status' => 'Agency status',
            'type' => 'Agency type'
        ];
    }

    public function messages()
    {
        return [
            'integer' => 'The :attribute must be a valid integer.',
            'min' => 'The :attribute must be at least :min characters long.',
            'numeric' => 'The :attribute must be a numeric value.',
        ];
    }
}
