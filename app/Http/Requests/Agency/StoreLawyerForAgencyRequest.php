<?php

namespace App\Http\Requests\Agency;

use App\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StoreLawyerForAgencyRequest extends FormRequest
{
    use ResponseTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::guard("lawyer")->check();
    }

    public function failedAuthorization()
    {
        throw new HttpResponseException($this->error('This action is unauthorized', 422));
    }

    protected function prepareForValidation()
    {
        // تحويل القيم إلى أرقام لضمان عمل distinct بشكل صحيح
        $this->merge([
            'authorization_Ids' => array_map('intval', $this->input('authorization_Ids', [])),
            'exception_Ids' => array_map('intval', $this->input('exception_Ids', [])),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'agency_id' => 'required|numeric|exists:agencies,id',
            'representative_id' => 'required|numeric|exists:representatives,id',
            'type' => 'required|string|in:public,private,legitimacy',
            'authorization_Ids' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    if (count($value) !== count(array_unique($value))) {
                        $fail('The ' . $attribute . ' must not contain duplicate values.');
                    }
                }
            ],
            'authorization_Ids.*' => 'numeric|exists:authorizations,id',
            'exception_Ids' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    if (count($value) !== count(array_unique($value))) {
                        $fail('The ' . $attribute . ' must not contain duplicate values.');
                    }
                }
            ],
            'exception_Ids.*' => 'numeric|exists:exceptions,id',
        ];
    }


    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new ValidationException($validator, $this->error($validator->errors(), 400));
    }

    public function attributes()
    {
        return [
            'representative_id' => 'Representative number',
            'agency_id' => 'Agency number',
            'type' => 'Agency type',
            'authorization_Ids' => 'Authorization IDs',
            'exception_Ids' => 'Exception IDs',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'The :attribute field is required and cannot be left blank.',
            'numeric' => 'The :attribute must be a valid numeric value.',
            'agency_id.exists' => 'The selected :attribute does not match any record in the agencies table.',
            'representative_id.exists' => 'The selected :attribute does not match any record in the representatives table.',
            'distinct' => 'The :attribute must be unique.',
            'authorization_Ids.distinct' => 'Authorization IDs must not contain duplicates.',
            'exception_Ids.distinct' => 'Exception IDs must not contain duplicates.',
        ];
    }
}
