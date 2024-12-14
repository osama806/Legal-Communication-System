<?php

namespace App\Http\Requests\Agency;

use App\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
            'agency_id' => 'required|numeric|exists:agencies,id',
            'representative_id' => 'required|numeric|exists:representatives,id',
            'type' => 'required|string|in:public,private,legitimacy',
            'authorization_Ids' => 'required|array',
            'authorization_Ids.*' => 'numeric|exists:authorizations,id',
            'exceptions' => 'required|string|min:1',
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        return $this->error($validator->errors(), 400);
    }

    public function attributes()
    {
        return [
            'representative_id' => 'Representative number',
            'agency_id' => 'Agency number',
            'type' => 'Agency type',
            'authorization_Ids' => 'Authorization IDs',
            'exceptions' => 'Exceptions',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'The :attribute field is required and cannot be left blank.',
            'numeric' => 'The :attribute must be a valid numeric value.',
            'agency_id.exists' => 'The selected :attribute does not match any record in the agencies table.',
            'representative_id.exists' => 'The selected :attribute does not match any record in the representatives table.',
            'min' => 'The :attribute must contain at least :min characters.',
            'max' => 'The :attribute must not exceed :max characters.',
            'in' => 'The :attribute must be one of the allowed values: public, private, or legitimacy.',
        ];
    }
}
