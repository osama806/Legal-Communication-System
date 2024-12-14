<?php

namespace App\Http\Requests\Agency;

use App\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ApprovedByRepresentativeRequest extends FormRequest
{
    use ResponseTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::guard("representative")->check();
    }

    public function failedAuthorization()
    {
        throw new HttpResponseException($this->error('This action is unauthorized', 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sequential_number' => 'required|string|digits:8|unique:agencies,sequential_number',
            'record_number' => 'required|string|digits:8|unique:agencies,record_number',
            'place_of_issue' => 'required|string|min:2|max:100',
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new ValidationException($validator, $this->error($validator->errors(), 400));
    }

    public function attributes()
    {
        return [
            'sequential_number' => 'Sequential number',
            'record_number' => 'Record number',
            'place_of_issue' => 'Place of issue',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'The :attribute is required.',
            'unique' => 'This :attribute is already exists',
            'min' => 'The :attribute must be at least :min characters long.',
            'max' => 'The :attribute must not exceed :max characters.',
            'in' => ':attribute must be either "approved" or "rejected"',
        ];
    }
}
